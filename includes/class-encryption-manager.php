<?php
/**
 * Azure AI Chatbot - Encryption Manager
 * 
 * 암호화/복호화를 전담하는 중앙 관리 클래스
 * - Single Source of Truth: 모든 암호화는 이 클래스를 통해서만
 * - Versioned Encryption: 버전 관리로 마이그레이션 지원
 * - Graceful Degradation: 복호화 실패 시 fallback
 * 
 * @package Azure_AI_Chatbot
 * @since 3.0.46
 */

if (!defined('ABSPATH')) {
    exit;
}

class Azure_AI_Chatbot_Encryption_Manager {
    
    /**
     * 싱글톤 인스턴스
     */
    private static $instance = null;
    
    /**
     * 암호화 키
     */
    private $encryption_key;
    
    /**
     * 현재 암호화 버전
     */
    private $encryption_version = 'v2';
    
    /**
     * 싱글톤 인스턴스 가져오기
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 생성자 (private)
     */
    private function __construct() {
        $this->init_encryption_key();
    }
    
    /**
     * 암호화 키 초기화
     * 
     * WordPress 보안 키들을 조합하여 안전한 암호화 키 생성
     */
    private function init_encryption_key() {
        // 1. WordPress 보안 키 조합
        $key_parts = [
            defined('AUTH_KEY') ? AUTH_KEY : '',
            defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : '',
            defined('LOGGED_IN_KEY') ? LOGGED_IN_KEY : '',
            defined('NONCE_KEY') ? NONCE_KEY : ''
        ];
        
        $combined_key = implode('', $key_parts);
        
        // 2. 기본값 감지 및 경고
        $default_phrases = ['put your unique phrase here', 'unique phrase'];
        $is_default = false;
        
        foreach ($default_phrases as $phrase) {
            if (stripos($combined_key, $phrase) !== false) {
                $is_default = true;
                break;
            }
        }
        
        if (empty($combined_key) || $is_default) {
            // 3. Fallback: 사이트별 고유 키 생성
            error_log('[Azure AI Chatbot Encryption] WARNING: Using fallback encryption key. Please configure WordPress security keys in wp-config.php');
            $combined_key = 'azure-ai-chatbot-' . get_site_url() . '-' . wp_salt();
        }
        
        // 4. SHA256 해시로 일정한 길이의 키 생성 (32 bytes)
        $this->encryption_key = hash('sha256', $combined_key, true);
        
        error_log('[Azure AI Chatbot Encryption] Encryption key initialized (length: ' . strlen($this->encryption_key) . ' bytes)');
    }
    
    /**
     * 값이 암호화되었는지 확인
     * 
     * @param string $value 확인할 값
     * @return bool 암호화 여부
     */
    public function is_encrypted($value) {
        if (empty($value)) {
            return false;
        }
        
        // 암호화된 값의 형식: base64:v2:암호화된데이터
        if (strpos($value, 'base64:') === 0) {
            $parts = explode(':', $value);
            return count($parts) >= 3 && $parts[0] === 'base64';
        }
        
        return false;
    }
    
    /**
     * 데이터 암호화
     * 
     * @param string $data 암호화할 데이터
     * @return string 암호화된 데이터 (형식: base64:v2:암호화된값)
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        // 이미 암호화된 경우 그대로 반환
        if ($this->is_encrypted($data)) {
            error_log('[Azure AI Chatbot Encryption] Data already encrypted, returning as is');
            return $data;
        }
        
        // 마스킹된 값 감지 (••••)
        if (strpos($data, '•') !== false) {
            error_log('[Azure AI Chatbot Encryption] Masked value detected, not encrypting');
            return '';  // 마스킹된 값은 암호화하지 않음
        }
        
        // OpenSSL 사용 불가 시 fallback
        if (!function_exists('openssl_encrypt')) {
            error_log('[Azure AI Chatbot Encryption] OpenSSL not available, using base64 fallback');
            return 'base64:v1:' . base64_encode($data);
        }
        
        $method = 'aes-256-cbc';
        $iv_length = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        $encrypted = openssl_encrypt($data, $method, $this->encryption_key, OPENSSL_RAW_DATA, $iv);
        
        if ($encrypted === false) {
            $error = openssl_error_string();
            error_log('[Azure AI Chatbot Encryption] Encryption failed: ' . $error);
            // Fallback to base64
            return 'base64:v1:' . base64_encode($data);
        }
        
        // 형식: base64:버전:IV+암호화된데이터
        $result = 'base64:' . $this->encryption_version . ':' . base64_encode($iv . $encrypted);
        
        error_log('[Azure AI Chatbot Encryption] Data encrypted successfully (version: ' . $this->encryption_version . ', length: ' . strlen($result) . ')');
        
        return $result;
    }
    
    /**
     * 데이터 복호화
     * 
     * @param string $data 복호화할 데이터
     * @return string 복호화된 데이터
     */
    public function decrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        // 암호화되지 않은 값 처리
        if (!$this->is_encrypted($data)) {
            // 이전 버전 호환성: 단순 base64
            $decoded = base64_decode($data, true);
            if ($decoded !== false && mb_detect_encoding($decoded, 'UTF-8', true)) {
                error_log('[Azure AI Chatbot Encryption] Legacy base64 value detected and decoded');
                return $decoded;
            }
            
            error_log('[Azure AI Chatbot Encryption] Value not encrypted, returning as is');
            return $data;
        }
        
        // 형식 파싱: base64:버전:데이터
        $parts = explode(':', $data, 3);
        if (count($parts) < 3) {
            error_log('[Azure AI Chatbot Encryption] Invalid encrypted format');
            return '';
        }
        
        $format = $parts[0];
        $version = $parts[1];
        $encrypted_data = $parts[2];
        
        error_log('[Azure AI Chatbot Encryption] Attempting to decrypt (version: ' . $version . ')');
        
        // v1: 단순 base64 (fallback)
        if ($version === 'v1') {
            $result = base64_decode($encrypted_data);
            error_log('[Azure AI Chatbot Encryption] Decrypted using v1 (base64)');
            return $result;
        }
        
        // v2: AES-256-CBC
        if ($version === 'v2') {
            if (!function_exists('openssl_decrypt')) {
                error_log('[Azure AI Chatbot Encryption] OpenSSL not available for decryption');
                return '';
            }
            
            $method = 'aes-256-cbc';
            $iv_length = openssl_cipher_iv_length($method);
            $decoded = base64_decode($encrypted_data);
            
            if ($decoded === false) {
                error_log('[Azure AI Chatbot Encryption] Base64 decode failed');
                return '';
            }
            
            if (strlen($decoded) <= $iv_length) {
                error_log('[Azure AI Chatbot Encryption] Invalid encrypted data length (got: ' . strlen($decoded) . ', expected > ' . $iv_length . ')');
                return '';
            }
            
            $iv = substr($decoded, 0, $iv_length);
            $encrypted = substr($decoded, $iv_length);
            
            $decrypted = openssl_decrypt($encrypted, $method, $this->encryption_key, OPENSSL_RAW_DATA, $iv);
            
            if ($decrypted === false) {
                $error = openssl_error_string();
                error_log('[Azure AI Chatbot Encryption] Decryption failed: ' . $error);
                return '';
            }
            
            error_log('[Azure AI Chatbot Encryption] Decrypted successfully using v2 (AES-256-CBC), length: ' . strlen($decrypted));
            return $decrypted;
        }
        
        error_log('[Azure AI Chatbot Encryption] Unknown encryption version: ' . $version);
        return '';
    }
    
    /**
     * 마이그레이션: 이전 형식의 암호화된 값을 새 형식으로 변환
     * 
     * @param string $old_value 이전 형식의 암호화된 값
     * @return string 새 형식의 암호화된 값
     */
    public function migrate_encrypted_value($old_value) {
        if (empty($old_value)) {
            return '';
        }
        
        // 이미 새 형식인 경우
        if ($this->is_encrypted($old_value)) {
            error_log('[Azure AI Chatbot Encryption] Value already in new format');
            return $old_value;
        }
        
        error_log('[Azure AI Chatbot Encryption] Attempting migration from old format');
        
        // 이전 형식 시도 1: 단순 base64 + AES (플러그인 이전 버전)
        if (function_exists('openssl_decrypt')) {
            $method = 'aes-256-cbc';
            $iv_length = openssl_cipher_iv_length($method);
            $decoded = base64_decode($old_value, true);
            
            if ($decoded !== false && strlen($decoded) > $iv_length) {
                $iv = substr($decoded, 0, $iv_length);
                $encrypted = substr($decoded, $iv_length);
                
                $decrypted = @openssl_decrypt($encrypted, $method, $this->encryption_key, OPENSSL_RAW_DATA, $iv);
                
                if ($decrypted !== false && !empty($decrypted)) {
                    error_log('[Azure AI Chatbot Encryption] Successfully migrated from old AES format');
                    // 새 형식으로 재암호화
                    return $this->encrypt($decrypted);
                }
            }
        }
        
        // 이전 형식 시도 2: 단순 base64
        $decoded = base64_decode($old_value, true);
        if ($decoded !== false && mb_detect_encoding($decoded, 'UTF-8', true)) {
            error_log('[Azure AI Chatbot Encryption] Migrating from base64 format');
            return $this->encrypt($decoded);
        }
        
        // 마이그레이션 실패
        error_log('[Azure AI Chatbot Encryption] Failed to migrate encrypted value (format not recognized)');
        return '';
    }
    
    /**
     * 암호화 시스템 상태 확인
     * 
     * @return array 시스템 상태
     */
    public function get_system_status() {
        return [
            'encryption_version' => $this->encryption_version,
            'key_initialized' => !empty($this->encryption_key),
            'key_length' => strlen($this->encryption_key),
            'openssl_available' => function_exists('openssl_encrypt') && function_exists('openssl_decrypt'),
            'openssl_version' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'N/A'
        ];
    }
}
