<?php
/**
 * Azure AI Chatbot - Encryption Validator
 * 
 * 암호화 시스템 검증 및 테스트 도구
 * - 암호화/복호화 기능 검증
 * - OAuth 설정 검증
 * - 연결 테스트 통합
 * 
 * @package Azure_AI_Chatbot
 * @since 3.0.46
 */

if (!defined('ABSPATH')) {
    exit;
}

class Azure_AI_Chatbot_Encryption_Validator {
    
    /**
     * 암호화 시스템 전체 검증
     * 
     * @return array 검증 결과
     */
    public static function validate_encryption_system() {
        $results = [];
        
        // 0. 암호화 매니저 로드
        require_once plugin_dir_path(__FILE__) . 'class-encryption-manager.php';
        
        // 1. 암호화 매니저 인스턴스 확인
        try {
            $manager = Azure_AI_Chatbot_Encryption_Manager::get_instance();
            $results['manager_initialized'] = !empty($manager);
        } catch (Exception $e) {
            $results['manager_initialized'] = false;
            error_log('[Azure AI Chatbot Validator] Manager initialization failed: ' . $e->getMessage());
            return $results;  // 매니저 없으면 더 이상 진행 불가
        }
        
        // 2. 암호화 시스템 상태
        $status = $manager->get_system_status();
        $results['system_status'] = $status;
        $results['openssl_available'] = $status['openssl_available'];
        
        // 3. 암호화/복호화 테스트
        $test_string = 'test_secret_' . time();
        $encrypted = $manager->encrypt($test_string);
        $results['encryption_works'] = !empty($encrypted) && $encrypted !== $test_string;
        $results['encrypted_format'] = substr($encrypted, 0, 20) . '...';
        
        if ($results['encryption_works']) {
            $decrypted = $manager->decrypt($encrypted);
            $results['decryption_works'] = $decrypted === $test_string;
        } else {
            $results['decryption_works'] = false;
        }
        
        // 4. 마스킹 감지 테스트
        $masked = '••••••••';
        $encrypted_masked = $manager->encrypt($masked);
        $results['masking_detection'] = empty($encrypted_masked);
        
        // 5. 이중 암호화 방지 테스트
        if ($results['encryption_works']) {
            $double_encrypted = $manager->encrypt($encrypted);
            $results['double_encryption_prevented'] = $double_encrypted === $encrypted;
        } else {
            $results['double_encryption_prevented'] = null;
        }
        
        // 6. OAuth 설정 검증
        $oauth_settings = get_option('azure_chatbot_oauth_settings', []);
        if (!empty($oauth_settings['client_secret'])) {
            $decrypted_oauth = $manager->decrypt($oauth_settings['client_secret']);
            $results['oauth_secret_valid'] = !empty($decrypted_oauth);
            $results['oauth_secret_length'] = strlen($decrypted_oauth);
        } else {
            $results['oauth_secret_valid'] = null;  // Not set
            $results['oauth_secret_length'] = 0;
        }
        
        // 7. 메인 설정 검증
        $main_settings = get_option('azure_chatbot_settings', []);
        if (!empty($main_settings['client_secret_encrypted'])) {
            $decrypted_main = $manager->decrypt($main_settings['client_secret_encrypted']);
            $results['main_secret_valid'] = !empty($decrypted_main);
            $results['main_secret_length'] = strlen($decrypted_main);
        } else {
            $results['main_secret_valid'] = null;  // Not set
            $results['main_secret_length'] = 0;
        }
        
        // 8. API Key 검증
        if (!empty($main_settings['api_key_encrypted'])) {
            $decrypted_api_key = $manager->decrypt($main_settings['api_key_encrypted']);
            $results['api_key_valid'] = !empty($decrypted_api_key);
            $results['api_key_length'] = strlen($decrypted_api_key);
        } else {
            $results['api_key_valid'] = null;  // Not set
            $results['api_key_length'] = 0;
        }
        
        // 9. 결과 로깅
        error_log('[Azure AI Chatbot Validator] ========================================');
        error_log('[Azure AI Chatbot Validator] Encryption System Validation Results');
        error_log('[Azure AI Chatbot Validator] ========================================');
        foreach ($results as $key => $value) {
            if ($key === 'system_status') {
                error_log('[Azure AI Chatbot Validator]   📊 system_status:');
                foreach ($value as $k => $v) {
                    error_log('[Azure AI Chatbot Validator]      - ' . $k . ': ' . json_encode($v));
                }
            } else {
                $status = $value === true ? '✅' : ($value === false ? '❌' : '⏭️');
                error_log('[Azure AI Chatbot Validator]   ' . $status . ' ' . $key . ': ' . json_encode($value));
            }
        }
        error_log('[Azure AI Chatbot Validator] ========================================');
        
        return $results;
    }
    
    /**
     * 연결 테스트 시 암호화 검증
     * 
     * @return array 테스트 결과
     */
    public static function test_connection_with_validation() {
        error_log('[Azure AI Chatbot Validator] Running connection test with encryption validation');
        
        $results = self::validate_encryption_system();
        
        // 1. 기본 암호화 기능 확인
        if (!$results['encryption_works']) {
            return [
                'success' => false,
                'message' => '❌ 암호화 시스템 오류: 데이터를 암호화할 수 없습니다.',
                'debug' => $results,
                'recommendation' => 'OpenSSL 확장이 활성화되어 있는지 확인하세요.'
            ];
        }
        
        if (!$results['decryption_works']) {
            return [
                'success' => false,
                'message' => '❌ 암호화 시스템 오류: 데이터를 복호화할 수 없습니다.',
                'debug' => $results,
                'recommendation' => 'WordPress 보안 키가 변경되었을 수 있습니다. wp-config.php를 확인하세요.'
            ];
        }
        
        // 2. OAuth Client Secret 검증
        if ($results['oauth_secret_valid'] === false) {
            return [
                'success' => false,
                'message' => '❌ OAuth Client Secret을 복호화할 수 없습니다.',
                'debug' => $results,
                'recommendation' => 'OAuth 설정을 다시 저장해주세요. (설정 > Azure OAuth 설정)'
            ];
        }
        
        // 3. 메인 Client Secret 검증 (Agent 모드)
        if ($results['main_secret_valid'] === false) {
            return [
                'success' => false,
                'message' => '❌ Client Secret을 복호화할 수 없습니다.',
                'debug' => $results,
                'recommendation' => '설정을 다시 저장해주세요. (설정 > Agent 설정)'
            ];
        }
        
        // 4. API Key 검증 (Chat 모드)
        if ($results['api_key_valid'] === false) {
            return [
                'success' => false,
                'message' => '❌ API Key를 복호화할 수 없습니다.',
                'debug' => $results,
                'recommendation' => '설정을 다시 저장해주세요. (설정 > Chat 설정)'
            ];
        }
        
        // 5. 모든 검증 통과
        return [
            'success' => true,
            'message' => '✅ 암호화 시스템이 정상 작동합니다.',
            'debug' => $results,
            'summary' => [
                'encryption' => $results['encryption_works'] ? 'OK' : 'FAIL',
                'decryption' => $results['decryption_works'] ? 'OK' : 'FAIL',
                'oauth_secret' => $results['oauth_secret_valid'] === true ? 'OK' : ($results['oauth_secret_valid'] === false ? 'FAIL' : 'NOT_SET'),
                'main_secret' => $results['main_secret_valid'] === true ? 'OK' : ($results['main_secret_valid'] === false ? 'FAIL' : 'NOT_SET'),
                'api_key' => $results['api_key_valid'] === true ? 'OK' : ($results['api_key_valid'] === false ? 'FAIL' : 'NOT_SET')
            ]
        ];
    }
    
    /**
     * 마이그레이션 테스트
     * 
     * @param string $old_encrypted_value 이전 암호화 값
     * @return array 마이그레이션 결과
     */
    public static function test_migration($old_encrypted_value) {
        if (empty($old_encrypted_value)) {
            return [
                'success' => false,
                'message' => '테스트할 값이 비어있습니다.'
            ];
        }
        
        require_once plugin_dir_path(__FILE__) . 'class-encryption-manager.php';
        $manager = Azure_AI_Chatbot_Encryption_Manager::get_instance();
        
        // 1. 이미 새 형식인지 확인
        if ($manager->is_encrypted($old_encrypted_value)) {
            return [
                'success' => true,
                'message' => '이미 새 형식입니다. 마이그레이션 불필요.',
                'format' => 'v2'
            ];
        }
        
        // 2. 마이그레이션 시도
        error_log('[Azure AI Chatbot Validator] Testing migration for value: ' . substr($old_encrypted_value, 0, 20) . '...');
        
        $migrated = $manager->migrate_encrypted_value($old_encrypted_value);
        
        if (empty($migrated)) {
            return [
                'success' => false,
                'message' => '마이그레이션 실패: 이전 형식을 인식할 수 없습니다.',
                'old_value_length' => strlen($old_encrypted_value)
            ];
        }
        
        // 3. 마이그레이션된 값 복호화 테스트
        $decrypted = $manager->decrypt($migrated);
        
        if (empty($decrypted)) {
            return [
                'success' => false,
                'message' => '마이그레이션은 되었으나 복호화 실패',
                'migrated_value' => substr($migrated, 0, 30) . '...'
            ];
        }
        
        return [
            'success' => true,
            'message' => '마이그레이션 성공',
            'old_format' => 'legacy',
            'new_format' => 'v2',
            'decrypted_length' => strlen($decrypted),
            'migrated_value' => substr($migrated, 0, 30) . '...'
        ];
    }
}
