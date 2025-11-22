<?php
/**
 * Plugin Name: Azure AI Chatbot
 * Plugin URI: https://github.com/asomi7007/azure-ai-chatbot-wordpress
 * Description: Integrate Azure AI Foundry agents and OpenAI-compatible chat models into WordPress with a modern chat widget
 * Version: 3.0.70
 * Author: Asomi AI
 * Author URI: https://www.eldensolution.kr
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: azure-ai-chatbot
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // 직접 접근 차단
}

// 플러그인 상수 정의
define('AZURE_CHATBOT_VERSION', '3.0.70');
define('AZURE_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AZURE_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AZURE_CHATBOT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// 클래스 로드
require_once AZURE_CHATBOT_PLUGIN_DIR . 'includes/class-encryption-manager.php';
require_once AZURE_CHATBOT_PLUGIN_DIR . 'includes/class-encryption-validator.php';
require_once AZURE_CHATBOT_PLUGIN_DIR . 'includes/class-azure-oauth.php';

/**
 * 메인 플러그인 클래스
 */
class Azure_AI_Chatbot {
    
    /**
     * 싱글톤 인스턴스
     */
    private static $instance = null;
    
    /**
     * 플러그인 옵션
     */
    private $options;
    
    /**
     * 암호화 매니저 인스턴스
     */
    private $encryption_manager;
    
    /**
     * 싱글톤 패턴
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 생성자
     */
    private function __construct() {
        // 암호화 매니저 초기화
        $this->encryption_manager = Azure_AI_Chatbot_Encryption_Manager::get_instance();
        
        $this->load_options();
        $this->init_hooks();
    }
    
    /**
     * 데이터 암호화 (외부 접근용 - 암호화 매니저로 위임)
     * 
     * @param string $data 암호화할 데이터
     * @return string 암호화된 데이터
     */
    public function encrypt($data) {
        return $this->encryption_manager->encrypt($data);
    }
    
    /**
     * 데이터 복호화 (외부 접근용 - 암호화 매니저로 위임)
     * 
     * @param string $data 복호화할 데이터
     * @return string 복호화된 데이터
     */
    public function decrypt($data) {
        return $this->encryption_manager->decrypt($data);
    }
    
    /**
     * 옵션 로드 (마이그레이션 포함)
     */
    private function load_options() {
        $stored_options = get_option('azure_chatbot_settings', []);
        $needs_update = false;
        
        // API Key 복호화 (마이그레이션 지원)
        $api_key = '';
        if (!empty($stored_options['api_key_encrypted'])) {
            $api_key = $this->encryption_manager->decrypt($stored_options['api_key_encrypted']);
            
            // 복호화 실패 시 마이그레이션 시도
            if (empty($api_key)) {
                error_log('[Azure AI Chatbot] API Key decryption failed, attempting migration');
                $migrated = $this->encryption_manager->migrate_encrypted_value($stored_options['api_key_encrypted']);
                if (!empty($migrated)) {
                    $api_key = $this->encryption_manager->decrypt($migrated);
                    if (!empty($api_key)) {
                        $stored_options['api_key_encrypted'] = $migrated;
                        $needs_update = true;
                        error_log('[Azure AI Chatbot] API Key migration successful');
                    }
                }
            }
        }
        
        // Client Secret 복호화 (마이그레이션 지원)
        $client_secret = '';
        if (!empty($stored_options['client_secret_encrypted'])) {
            $client_secret = $this->encryption_manager->decrypt($stored_options['client_secret_encrypted']);
            
            // 복호화 실패 시 마이그레이션 시도
            if (empty($client_secret)) {
                error_log('[Azure AI Chatbot] Client Secret decryption failed, attempting migration');
                $migrated = $this->encryption_manager->migrate_encrypted_value($stored_options['client_secret_encrypted']);
                if (!empty($migrated)) {
                    $client_secret = $this->encryption_manager->decrypt($migrated);
                    if (!empty($client_secret)) {
                        $stored_options['client_secret_encrypted'] = $migrated;
                        $needs_update = true;
                        error_log('[Azure AI Chatbot] Client Secret migration successful');
                    }
                }
            }
        }
        
        // 마이그레이션된 값 저장
        if ($needs_update) {
            update_option('azure_chatbot_settings', $stored_options);
            error_log('[Azure AI Chatbot] Settings updated with migrated encryption format');
        }
        
        $this->options = [
            'mode' => $stored_options['mode'] ?? 'agent', // 'agent' (Entra ID + Agent) or 'chat' (API Key + Chat Completion)
            
            // Agent 모드 (Entra ID)
            'client_id' => $stored_options['client_id'] ?? '',
            'client_secret' => $client_secret,
            'tenant_id' => $stored_options['tenant_id'] ?? '',
            'agent_endpoint' => $stored_options['agent_endpoint'] ?? '',
            'agent_id' => $stored_options['agent_id'] ?? '',
            
            // Chat 모드 (API Key)
            'api_key' => $api_key,
            'chat_endpoint' => $stored_options['chat_endpoint'] ?? '',
            'deployment_name' => $stored_options['deployment_name'] ?? '',
            
            // 공통 설정
            'enabled' => $stored_options['enabled'] ?? false,
            'public_access' => $stored_options['public_access'] ?? true, // 비로그인 사용자 접근 허용 (기본값: 허용)
            'widget_position' => $stored_options['widget_position'] ?? 'bottom-right',
            'primary_color' => $stored_options['primary_color'] ?? '#667eea',
            'secondary_color' => $stored_options['secondary_color'] ?? '#764ba2',
            'welcome_message' => $stored_options['welcome_message'] ?? __('Hello! How can I help you?', 'azure-ai-chatbot'),
            'chat_title' => $stored_options['chat_title'] ?? __('AI Assistant', 'azure-ai-chatbot')
        ];
    }
    
    /**
     * 훅 초기화
     */
    private function init_hooks() {
        // 언어 파일 로드
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        
        // 관리자 메뉴
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // 관리자 알림
        add_action('admin_notices', [$this, 'display_admin_notices']);
        
        // 프론트엔드 (위젯이 활성화된 경우에만)
        if ($this->is_widget_enabled()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
            add_action('wp_footer', [$this, 'render_widget']);
        }
        
        // REST API
        add_action('rest_api_init', [$this, 'register_api']);
        
        // AJAX 핸들러
        add_action('wp_ajax_azure_chatbot_test_connection', [$this, 'ajax_test_connection']);
        
        // 플러그인 활성화/비활성화
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // 관리자 스타일
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // 플러그인 설정 링크
        add_filter('plugin_action_links_' . AZURE_CHATBOT_PLUGIN_BASENAME, [$this, 'add_settings_link']);
    }
    
    /**
     * 언어 파일 로드
     */
    public function load_textdomain() {
        $domain = 'azure-ai-chatbot';
        
        // WordPress 표준 방식으로 언어 파일 로드
        load_plugin_textdomain(
            $domain,
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * 관리자 알림 표시
     */
    public function display_admin_notices() {
        // 보안 키 업데이트 성공
        $success = get_transient('azure_chatbot_security_keys_success');
        if ($success) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <strong>Azure AI Chatbot:</strong> 
                    WordPress 보안 키가 자동으로 생성되어 wp-config.php에 추가되었습니다! 🎉
                </p>
                <p>
                    백업 파일: <code><?php echo esc_html(basename($success['backup_path'])); ?></code><br>
                    업데이트 시간: <?php echo esc_html($success['updated_at']); ?>
                </p>
                <p>
                    <em>이제 API Key가 AES-256 암호화로 안전하게 보호됩니다.</em>
                </p>
            </div>
            <?php
            delete_transient('azure_chatbot_security_keys_success');
        }
        
        // 보안 키 경고
        $warning = get_transient('azure_chatbot_security_keys_warning');
        if ($warning) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>Azure AI Chatbot 보안 경고:</strong> 
                    WordPress 보안 키가 설정되지 않았거나 기본값입니다.
                </p>
                <p>
                    wp-config.php 파일에 쓰기 권한이 없어 자동 업데이트할 수 없습니다.<br>
                    파일 경로: <code><?php echo esc_html($warning['config_path']); ?></code>
                </p>
                <p>
                    <strong>수동으로 설정하는 방법:</strong>
                </p>
                <ol>
                    <li><a href="https://api.wordpress.org/secret-key/1.1/salt/" target="_blank">WordPress 보안 키 생성기</a>에서 새 키 생성</li>
                    <li>생성된 키를 복사</li>
                    <li><code>wp-config.php</code> 파일을 열어서 기존 보안 키 섹션 교체</li>
                    <li>파일 저장 후 플러그인 재활성화</li>
                </ol>
                <p>
                    <em>보안 키가 없으면 API Key 암호화가 제대로 작동하지 않을 수 있습니다.</em>
                </p>
            </div>
            <?php
            delete_transient('azure_chatbot_security_keys_warning');
        }
        
        // 보안 키 오류
        $error = get_transient('azure_chatbot_security_keys_error');
        if ($error) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong>Azure AI Chatbot 오류:</strong> 
                    보안 키 자동 업데이트에 실패했습니다.
                </p>
                <p>오류 메시지: <?php echo esc_html($error['message']); ?></p>
                <p>
                    <strong>수동 설정 방법:</strong><br>
                    <a href="https://api.wordpress.org/secret-key/1.1/salt/" target="_blank" class="button button-primary">
                        WordPress 보안 키 생성하기
                    </a>
                </p>
            </div>
            <?php
            delete_transient('azure_chatbot_security_keys_error');
        }
    }
    
    /**
     * 위젯 활성화 여부 확인
     */
    private function is_widget_enabled() {
        if (empty($this->options['enabled'])) {
            return false;
        }
        
        // public_access 옵션 확인 (비로그인 사용자 접근 허용 여부)
        // public_access가 false이고 사용자가 로그인하지 않은 경우 위젯 숨김
        if (empty($this->options['public_access']) && !is_user_logged_in()) {
            return false;
        }
        
        // 모드에 따라 필수 필드 검증
        if ($this->options['mode'] === 'agent') {
            // Agent 모드: Entra ID + Agent 필수
            return !empty($this->options['client_id']) && 
                   !empty($this->options['client_secret']) && 
                   !empty($this->options['tenant_id']) &&
                   !empty($this->options['agent_endpoint']) &&
                   !empty($this->options['agent_id']);
        } else {
            // Chat 모드: API Key + Deployment 필수
            return !empty($this->options['api_key']) &&
                   !empty($this->options['chat_endpoint']) &&
                   !empty($this->options['deployment_name']);
        }
    }
    
    /**
     * 플러그인 활성화
     */
    public function activate() {
        // 기본 옵션 설정
        if (!get_option('azure_chatbot_settings')) {
            add_option('azure_chatbot_settings', $this->options);
        }
        
        // WordPress 보안 키 확인 및 생성
        $this->check_and_update_security_keys();
    }
    
    /**
     * WordPress 보안 키 확인 및 자동 업데이트
     */
    private function check_and_update_security_keys() {
        // 필요한 보안 상수 목록
        $required_keys = [
            'AUTH_KEY',
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'AUTH_SALT',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT'
        ];
        
        // 누락되거나 기본값인 키 확인
        $missing_keys = [];
        foreach ($required_keys as $key) {
            if (!defined($key) || constant($key) === 'put your unique phrase here' || empty(constant($key))) {
                $missing_keys[] = $key;
            }
        }
        
        // 모든 키가 제대로 설정되어 있으면 종료
        if (empty($missing_keys)) {
            return;
        }
        
        // wp-config.php 파일 경로
        $config_path = ABSPATH . 'wp-config.php';
        
        // 파일 쓰기 권한 확인
        if (!is_writable($config_path)) {
            // 경고 메시지를 관리자 알림으로 저장
            set_transient('azure_chatbot_security_keys_warning', [
                'missing_keys' => $missing_keys,
                'config_path' => $config_path
            ], 3600);
            return;
        }
        
        try {
            // WordPress.org에서 보안 키 가져오기
            $response = wp_remote_get('https://api.wordpress.org/secret-key/1.1/salt/', [
                'timeout' => 15,
                'sslverify' => true
            ]);
            
            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            $new_keys = wp_remote_retrieve_body($response);
            
            if (empty($new_keys)) {
                throw new Exception('보안 키를 가져올 수 없습니다.');
            }
            
            // wp-config.php 파일 읽기
            $config_content = file_get_contents($config_path);
            
            if ($config_content === false) {
                throw new Exception('wp-config.php 파일을 읽을 수 없습니다.');
            }
            
            // 기존 보안 키 섹션 찾기
            $key_section_start = strpos($config_content, "define('AUTH_KEY'");
            
            if ($key_section_start === false) {
                // 보안 키 섹션이 없으면 추가할 위치 찾기
                $insert_position = strpos($config_content, "/* That's all");
                
                if ($insert_position === false) {
                    $insert_position = strpos($config_content, "/**#@-*/");
                }
                
                if ($insert_position !== false) {
                    // 새 보안 키 섹션 추가
                    $security_comment = "\n/**#@+\n * Authentication unique keys and salts.\n * @since " . date('Y-m-d H:i:s') . " (Azure AI Chatbot 플러그인이 자동 생성)\n */\n";
                    $new_section = $security_comment . $new_keys . "\n/**#@-*/\n\n";
                    
                    $config_content = substr_replace($config_content, $new_section, $insert_position, 0);
                }
            } else {
                // 기존 보안 키 섹션 찾아서 교체
                $key_section_end = strpos($config_content, "/**#@-*/", $key_section_start);
                
                if ($key_section_end !== false) {
                    // 주석 포함하여 교체
                    $old_section_start = strrpos(substr($config_content, 0, $key_section_start), "/**#@+");
                    
                    if ($old_section_start !== false) {
                        $key_section_end += strlen("/**#@-*/");
                        $old_section_length = $key_section_end - $old_section_start;
                        
                        $security_comment = "/**#@+\n * Authentication unique keys and salts.\n * @since " . date('Y-m-d H:i:s') . " (Azure AI Chatbot 플러그인이 업데이트)\n */\n";
                        $new_section = $security_comment . $new_keys . "\n/**#@-*/";
                        
                        $config_content = substr_replace($config_content, $new_section, $old_section_start, $old_section_length);
                    }
                }
            }
            
            // wp-config.php 백업 생성
            $backup_path = $config_path . '.backup-' . date('Y-m-d-His');
            copy($config_path, $backup_path);
            
            // 새 내용 저장
            if (file_put_contents($config_path, $config_content) === false) {
                throw new Exception('wp-config.php 파일을 저장할 수 없습니다.');
            }
            
            // 성공 메시지 저장
            set_transient('azure_chatbot_security_keys_success', [
                'backup_path' => $backup_path,
                'updated_at' => current_time('mysql')
            ], 3600);
            
        } catch (Exception $e) {
            // 오류 발생 시 경고 메시지 저장
            set_transient('azure_chatbot_security_keys_error', [
                'message' => $e->getMessage(),
                'missing_keys' => $missing_keys
            ], 3600);
            
            error_log('Azure AI Chatbot: 보안 키 자동 업데이트 실패 - ' . $e->getMessage());
        }
    }
    
    /**
     * 플러그인 비활성화
     */
    public function deactivate() {
        // 필요시 정리 작업
    }
    
    /**
     * 관리자 메뉴 추가
     */
    public function add_admin_menu() {
        add_menu_page(
            'Azure AI Chatbot',
            'AI Chatbot',
            'manage_options',
            'azure-ai-chatbot',
            [$this, 'render_settings_page'],
            'dashicons-format-chat',
            80
        );
        
        add_submenu_page(
            'azure-ai-chatbot',
            __('설정', 'azure-ai-chatbot'),
            __('설정', 'azure-ai-chatbot'),
            'manage_options',
            'azure-ai-chatbot',
            [$this, 'render_settings_page']
        );
        
        add_submenu_page(
            'azure-ai-chatbot',
            __('사용 가이드', 'azure-ai-chatbot'),
            __('사용 가이드', 'azure-ai-chatbot'),
            'manage_options',
            'azure-ai-chatbot-guide',
            [$this, 'render_guide_page']
        );
    }
    
    /**
     * 설정 링크 추가
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=azure-ai-chatbot">' . __('설정', 'azure-ai-chatbot') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * 설정 등록
     */
    public function register_settings() {
        register_setting('azure_chatbot_settings_group', 'azure_chatbot_settings', [$this, 'sanitize_settings']);
    }
    
    /**
     * 설정 값 검증 및 저장
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        $old_options = get_option('azure_chatbot_settings', []);
        
        // 모드 선택
        $sanitized['mode'] = sanitize_text_field($input['mode'] ?? 'agent');
        
        // Agent 모드 설정 (Entra ID) - OAuth에서 값이 없으면 기존 값 유지
        $sanitized['client_id'] = !empty($input['client_id']) ? sanitize_text_field($input['client_id']) : ($old_options['client_id'] ?? '');
        $sanitized['tenant_id'] = !empty($input['tenant_id']) ? sanitize_text_field($input['tenant_id']) : ($old_options['tenant_id'] ?? '');
        $sanitized['agent_endpoint'] = !empty($input['agent_endpoint']) ? sanitize_text_field($input['agent_endpoint']) : ($old_options['agent_endpoint'] ?? '');
        $sanitized['agent_id'] = !empty($input['agent_id']) ? sanitize_text_field($input['agent_id']) : ($old_options['agent_id'] ?? '');
        
        // Client Secret 암호화하여 저장
        if (!empty($input['client_secret'])) {
            $client_secret = sanitize_text_field($input['client_secret']);
            
            // 마스킹된 값 감지 (• 문자 포함 여부로 판단)
            if (strpos($client_secret, '•') !== false) {
                // 마스킹된 값이면 기존 암호화된 값 유지
                $sanitized['client_secret_encrypted'] = $old_options['client_secret_encrypted'] ?? '';
                error_log('[Azure AI Chatbot] Masked client secret detected, keeping existing value');
            } elseif (!empty($client_secret)) {
                // 새 값 암호화
                $encrypted = $this->encryption_manager->encrypt($client_secret);
                if (!empty($encrypted)) {
                    $sanitized['client_secret_encrypted'] = $encrypted;
                    error_log('[Azure AI Chatbot] Client secret encrypted successfully');
                } else {
                    error_log('[Azure AI Chatbot] Failed to encrypt client secret');
                    add_settings_error('azure_chatbot_settings', 'encryption_error', 
                        'Client Secret 암호화에 실패했습니다.', 'error');
                    $sanitized['client_secret_encrypted'] = '';
                }
            } else {
                $sanitized['client_secret_encrypted'] = '';
            }
        } elseif (!empty($input['client_secret_encrypted'])) {
            // OAuth 자동 설정에서 이미 암호화된 값이 전달된 경우
            $sanitized['client_secret_encrypted'] = sanitize_text_field($input['client_secret_encrypted']);
        } else {
            $sanitized['client_secret_encrypted'] = $old_options['client_secret_encrypted'] ?? '';
        }
        
        // Chat 모드 설정 (API Key) - OAuth에서 값이 없으면 기존 값 유지
        $sanitized['chat_provider'] = !empty($input['chat_provider']) ? sanitize_text_field($input['chat_provider']) : ($old_options['chat_provider'] ?? 'azure-openai');
        $sanitized['chat_endpoint'] = !empty($input['chat_endpoint']) ? rtrim(sanitize_text_field($input['chat_endpoint']), '/') : ($old_options['chat_endpoint'] ?? '');
        $sanitized['deployment_name'] = !empty($input['deployment_name']) ? sanitize_text_field($input['deployment_name']) : ($old_options['deployment_name'] ?? '');
        
        // API Key 암호화하여 저장
        if (!empty($input['api_key'])) {
            $api_key = sanitize_text_field($input['api_key']);
            
            // 마스킹된 값 감지 (• 문자 포함 여부로 판단)
            if (strpos($api_key, '•') !== false) {
                // 마스킹된 값이면 기존 암호화된 값 유지
                $sanitized['api_key_encrypted'] = $old_options['api_key_encrypted'] ?? '';
                error_log('[Azure AI Chatbot] Masked API key detected, keeping existing value');
            } elseif (!empty($api_key)) {
                // 새 값 암호화
                $encrypted = $this->encryption_manager->encrypt($api_key);
                if (!empty($encrypted)) {
                    $sanitized['api_key_encrypted'] = $encrypted;
                    error_log('[Azure AI Chatbot] API key encrypted successfully');
                } else {
                    error_log('[Azure AI Chatbot] Failed to encrypt API key');
                    add_settings_error('azure_chatbot_settings', 'encryption_error', 
                        'API Key 암호화에 실패했습니다.', 'error');
                    $sanitized['api_key_encrypted'] = '';
                }
            } else {
                $sanitized['api_key_encrypted'] = '';
            }
        } elseif (!empty($input['api_key_encrypted'])) {
            // OAuth 자동 설정에서 이미 암호화된 값이 전달된 경우
            $sanitized['api_key_encrypted'] = sanitize_text_field($input['api_key_encrypted']);
        } else {
            // 기존 값 유지
            $sanitized['api_key_encrypted'] = $old_options['api_key_encrypted'] ?? '';
        }
        
        // 공통 설정
        $sanitized['enabled'] = !empty($input['enabled']);
        $sanitized['public_access'] = !empty($input['public_access']); // 비로그인 사용자 접근 허용
        $sanitized['widget_position'] = sanitize_text_field($input['widget_position'] ?? 'bottom-right');
        $sanitized['primary_color'] = sanitize_hex_color($input['primary_color'] ?? '#667eea');
        $sanitized['secondary_color'] = sanitize_hex_color($input['secondary_color'] ?? '#764ba2');
        $sanitized['welcome_message'] = sanitize_textarea_field($input['welcome_message'] ?? __('Hello! How can I help you?', 'azure-ai-chatbot'));
        $sanitized['chat_title'] = sanitize_text_field($input['chat_title'] ?? __('AI Assistant', 'azure-ai-chatbot'));
        
        return $sanitized;
    }
    
    /**
     * 민감한 값 마스킹 헬퍼 함수 (통합)
     *
     * @param string $value 마스킹할 값
     * @return string 마스킹된 값
     */
    private function mask_sensitive_value($value) {
        if (empty($value)) {
            return '';
        }

        $length = strlen($value);

        if ($length <= 8) {
            return str_repeat('•', $length);
        }

        // 앞 4자리와 뒤 4자리만 표시, 나머지는 마스킹
        return substr($value, 0, 4) . str_repeat('•', $length - 8) . substr($value, -4);
    }

    /**
     * 설정 페이지용 API Key 마스킹
     */
    public function get_masked_api_key() {
        return $this->mask_sensitive_value($this->options['api_key'] ?? '');
    }

    /**
     * 설정 페이지용 Client Secret 마스킹
     */
    public function get_masked_client_secret() {
        return $this->mask_sensitive_value($this->options['client_secret'] ?? '');
    }
    
    /**
     * 관리자 에셋 로드
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'azure-ai-chatbot') === false) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_style(
            'azure-chatbot-admin-css',
            AZURE_CHATBOT_PLUGIN_URL . 'assets/admin.css',
            [],
            AZURE_CHATBOT_VERSION
        );
        
        wp_enqueue_script(
            'azure-chatbot-admin-js',
            AZURE_CHATBOT_PLUGIN_URL . 'assets/admin.js',
            ['jquery', 'wp-color-picker'],
            AZURE_CHATBOT_VERSION,
            true
        );
        
        // AJAX URL과 nonce를 JavaScript에 전달
        wp_localize_script('azure-chatbot-admin-js', 'azureChatbotAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('azure_chatbot_test'),
            'i18n' => [
                'testing' => __('테스트 중...', 'azure-ai-chatbot'),
                'testConnection' => __('연결 테스트', 'azure-ai-chatbot'),
                'connectionFailed' => __('연결 실패', 'azure-ai-chatbot'),
                'errorOccurred' => __('오류 발생', 'azure-ai-chatbot'),
                'testError' => __('연결 테스트 중 오류가 발생했습니다.', 'azure-ai-chatbot'),
                'details' => __('상세 정보', 'azure-ai-chatbot')
            ]
        ]);
    }
    
    /**
     * 프론트엔드 에셋 로드
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'azure-chatbot-css',
            AZURE_CHATBOT_PLUGIN_URL . 'assets/chatbot.css',
            [],
            AZURE_CHATBOT_VERSION
        );
        
        wp_enqueue_script(
            'azure-chatbot-js',
            AZURE_CHATBOT_PLUGIN_URL . 'assets/chatbot.js',
            ['jquery'],
            AZURE_CHATBOT_VERSION,
            true
        );
        
        // 스크립트에 설정 전달
        wp_localize_script('azure-chatbot-js', 'azureChatbot', [
            'apiUrl' => rest_url('azure-chatbot/v1/chat'),
            'nonce' => wp_create_nonce('wp_rest'), // WordPress REST API 표준 nonce
            'settings' => [
                'position' => $this->options['widget_position'],
                'primaryColor' => $this->options['primary_color'],
                'secondaryColor' => $this->options['secondary_color'],
                'welcomeMessage' => $this->options['welcome_message'],
                'chatTitle' => $this->options['chat_title']
            ]
        ]);
        
        // 인라인 CSS로 색상 적용
        $custom_css = "
            .chatbot-toggle {
                background: linear-gradient(135deg, {$this->options['primary_color']} 0%, {$this->options['secondary_color']} 100%);
            }
            .chatbot-header {
                background: linear-gradient(135deg, {$this->options['primary_color']} 0%, {$this->options['secondary_color']} 100%);
            }
            .user-message .message-text {
                background: {$this->options['primary_color']};
            }
            #azure-chatbot-send {
                background: {$this->options['primary_color']};
            }
        ";
        wp_add_inline_style('azure-chatbot-css', $custom_css);
    }
    
    /**
     * 위젯 렌더링
     */
    public function render_widget() {
        $position_class = 'position-' . $this->options['widget_position'];
        ?>
        <div id="azure-chatbot-widget" class="<?php echo esc_attr($position_class); ?>">
            <button id="azure-chatbot-toggle" class="chatbot-toggle" aria-label="<?php esc_attr_e('Open chat', 'azure-ai-chatbot'); ?>">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                </svg>
            </button>
            
            <div id="azure-chatbot-window" class="chatbot-window">
                <div class="chatbot-header">
                    <h3><?php echo esc_html($this->options['chat_title']); ?></h3>
                    <button id="azure-chatbot-close" aria-label="<?php esc_attr_e('Close chat', 'azure-ai-chatbot'); ?>">×</button>
                </div>
                
                <div id="azure-chatbot-messages" class="chatbot-messages">
                    <div class="message bot-message">
                        <div class="message-content">
                            <span class="message-avatar">🤖</span>
                            <div class="message-text"><?php echo esc_html($this->options['welcome_message']); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="chatbot-loading" style="display:none;">
                    <span><?php esc_html_e('Generating response', 'azure-ai-chatbot'); ?></span>
                    <div class="typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                
                <div class="chatbot-input">
                    <input type="text" id="azure-chatbot-input" placeholder="<?php esc_attr_e('Type your message...', 'azure-ai-chatbot'); ?>" aria-label="<?php esc_attr_e('Message input', 'azure-ai-chatbot'); ?>" />
                    <button id="azure-chatbot-send" aria-label="<?php esc_attr_e('Send message', 'azure-ai-chatbot'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * REST API 등록
     */
    public function register_api() {
        register_rest_route('azure-chatbot/v1', '/chat', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_chat'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * 채팅 요청 처리
     */
    public function handle_chat($request) {
        // Nonce 검증 (로그인 사용자만)
        $nonce = $request->get_header('X-WP-Nonce');
        
        if (is_user_logged_in()) {
            // 로그인 사용자는 wp_rest nonce 검증
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                error_log('[Azure Chat] Nonce verification failed for logged-in user');
                return new WP_Error('invalid_nonce', '보안 검증 실패', ['status' => 403]);
            }
        } else {
            // 비로그인 사용자는 public_access 옵션 확인
            if (!$this->options['public_access']) {
                error_log('[Azure Chat] Public access disabled for non-logged-in user');
                return new WP_Error('access_denied', '로그인이 필요합니다', ['status' => 401]);
            }
        }
        
        $message = sanitize_text_field($request->get_param('message'));
        $thread_id = sanitize_text_field($request->get_param('thread_id'));
        
        if (empty($message)) {
            return new WP_Error('empty_message', '메시지가 비어있습니다', ['status' => 400]);
        }
        
        // 설정 확인
        if (!$this->is_widget_enabled()) {
            return new WP_Error('config_error', '플러그인 설정이 완료되지 않았습니다', ['status' => 500]);
        }
        
        try {
            $mode = $this->options['mode'];
            error_log('[Azure Chat] Mode: ' . $mode);
            error_log('[Azure Chat] Message: ' . $message);
            error_log('[Azure Chat] Thread ID: ' . ($thread_id ?: 'null'));
            
            if ($mode === 'agent') {
                // Agent 모드: Entra ID + Assistants API (threads, messages, runs)
                error_log('[Azure Chat] Creating Agent API Handler...');
                error_log('[Azure Chat] Agent Endpoint: ' . $this->options['agent_endpoint']);
                error_log('[Azure Chat] Agent ID: ' . $this->options['agent_id']);
                
                $api_handler = new Azure_AI_API_Handler(
                    $this->options['agent_endpoint'],
                    $this->options['agent_id'],
                    'entra_id',
                    null,
                    $this->options['client_id'],
                    $this->options['client_secret'],
                    $this->options['tenant_id']
                );
                
                error_log('[Azure Chat] Sending message via Agent API...');
                $response = $api_handler->send_message($message, $thread_id);
                error_log('[Azure Chat] Response received: ' . json_encode($response));
                
                return new WP_REST_Response([
                    'success' => true,
                    'reply' => $response['message'],
                    'thread_id' => $response['thread_id']
                ], 200);
                
            } else {
                // Chat 모드: API Key + Chat Completions API (simple messages)
                $api_handler = new Azure_AI_API_Handler(
                    $this->options['chat_endpoint'],
                    $this->options['deployment_name'],
                    'api_key',
                    $this->options['api_key'],
                    null,
                    null,
                    null
                );
                
                $response = $api_handler->send_chat_message($message);
                
                return new WP_REST_Response([
                    'success' => true,
                    'reply' => $response['message']
                    // Chat 모드에서는 thread_id 없음
                ], 200);
            }
            
        } catch (Exception $e) {
            error_log('[Azure Chat] ERROR: ' . $e->getMessage());
            error_log('[Azure Chat] ERROR Trace: ' . $e->getTraceAsString());
            
            // 사용자에게 상세한 에러 메시지 반환 (디버깅용)
            return new WP_REST_Response([
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
                'debug_info' => [
                    'mode' => $mode ?? 'unknown',
                    'endpoint' => $mode === 'agent' ? ($this->options['agent_endpoint'] ?? 'N/A') : ($this->options['chat_endpoint'] ?? 'N/A'),
                    'thread_id' => $thread_id ?? 'null',
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
    
    /**
     * 설정 페이지 렌더링
     */
    public function render_settings_page() {
        include AZURE_CHATBOT_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    /**
     * 가이드 페이지 렌더링
     */
    public function render_guide_page() {
        include AZURE_CHATBOT_PLUGIN_DIR . 'templates/guide-page.php';
    }
    
    /**
     * 연결 테스트 AJAX 핸들러
     */
    public function ajax_test_connection() {
        check_ajax_referer('azure_chatbot_test', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => '권한이 없습니다.']);
            return;
        }
        
        // 연결 테스트는 위젯 활성화 여부와 무관하게 필드만 검증
        $mode = $this->options['mode'];
        $missing_fields = [];
        
        if ($mode === 'agent') {
            // Agent 모드 필수 필드 검증
            if (empty($this->options['agent_endpoint'])) {
                $missing_fields[] = '• Agent 엔드포인트';
            }
            if (empty($this->options['agent_id'])) {
                $missing_fields[] = '• Agent ID';
            }
            if (empty($this->options['client_id'])) {
                $missing_fields[] = '• Client ID (App ID)';
            }
            if (empty($this->options['client_secret'])) {
                $missing_fields[] = '• Client Secret';
            }
            if (empty($this->options['tenant_id'])) {
                $missing_fields[] = '• Tenant ID';
            }
            
            if (!empty($missing_fields)) {
                $error_msg = "❌ Agent 모드 설정이 완료되지 않았습니다.\n\n";
                $error_msg .= "다음 필드를 입력해주세요:\n" . implode("\n", $missing_fields);
                
                wp_send_json_error(['message' => $error_msg]);
                return;
            }
        } else {
            // Chat 모드 필수 필드 검증
            if (empty($this->options['chat_endpoint'])) {
                $missing_fields[] = '• Chat 엔드포인트';
            }
            if (empty($this->options['deployment_name'])) {
                $missing_fields[] = '• 배포 이름';
            }
            if (empty($this->options['api_key'])) {
                $missing_fields[] = '• API Key';
            }
            
            if (!empty($missing_fields)) {
                $error_msg = "❌ Chat 모드 설정이 완료되지 않았습니다.\n\n";
                $error_msg .= "다음 필드를 입력해주세요:\n" . implode("\n", $missing_fields);
                
                wp_send_json_error(['message' => $error_msg]);
                return;
            }
        }
        
        try {
            $mode = $this->options['mode'];
            
            if ($mode === 'agent') {
                // OAuth 설정 우선 사용 (자동 설정에서 저장된 값)
                $oauth_settings = get_option('azure_chatbot_oauth_settings', []);
                
                // Client ID, Tenant ID, Client Secret 결정
                $client_id = !empty($oauth_settings['client_id']) ? $oauth_settings['client_id'] : $this->options['client_id'];
                $tenant_id = !empty($oauth_settings['tenant_id']) ? $oauth_settings['tenant_id'] : $this->options['tenant_id'];
                
                // Client Secret 복호화
                $client_secret = '';
                if (!empty($oauth_settings['client_secret'])) {
                    $client_secret = $this->decrypt($oauth_settings['client_secret']);
                } else if (!empty($this->options['client_secret_encrypted'])) {
                    $client_secret = $this->decrypt($this->options['client_secret_encrypted']);
                }
                
                if (empty($client_secret)) {
                    throw new Exception('Client Secret을 복호화할 수 없습니다.');
                }
                
                // Agent 모드: Entra ID + Assistants API
                $api_handler = new Azure_AI_API_Handler(
                    $this->options['agent_endpoint'],
                    $this->options['agent_id'],
                    'entra_id',
                    null,
                    $client_id,
                    $client_secret,
                    $tenant_id
                );
                
                // 간단한 테스트 메시지 전송
                $response = $api_handler->send_message('Hello, this is a test message.', null);
                
                wp_send_json_success([
                    'message' => 'Agent 모드 연결에 성공했습니다! Azure AI Foundry 에이전트가 정상적으로 응답했습니다.'
                ]);
            } else {
                // Chat 모드: API Key + Chat Completions API
                $api_handler = new Azure_AI_API_Handler(
                    $this->options['chat_endpoint'],
                    $this->options['deployment_name'],
                    'api_key',
                    $this->options['api_key'],
                    null,
                    null,
                    null
                );
                
                // 간단한 테스트 메시지 전송 (Chat Completions 방식)
                $response = $api_handler->send_chat_message('Hello, this is a test message.');
                
                wp_send_json_success([
                    'message' => 'Chat 모드 연결에 성공했습니다! 챗봇이 정상적으로 응답했습니다.'
                ]);
            }
            
        } catch (Exception $e) {
            $mode = $this->options['mode'] ?? 'chat';
            $error_message = "❌ 연결 테스트 실패\n\n";
            
            // 모드 표시
            if ($mode === 'agent') {
                $error_message .= "📍 모드: Agent (Azure AI Foundry)\n\n";
            } else {
                $error_message .= "📍 모드: Chat (OpenAI 호환)\n\n";
            }
            
            // 에러 메시지 파싱 및 구조화
            $raw_error = $e->getMessage();
            
            // HTTP 상태 코드 추출
            if (preg_match('/HTTP (\d{3})/', $raw_error, $matches)) {
                $http_code = $matches[1];
                $error_message .= "🔴 오류 코드: HTTP {$http_code}\n\n";
            }
            
            // 서버 응답 메시지 추출
            if (preg_match('/상세 정보: (.+?)(\n|$)/s', $raw_error, $matches)) {
                $error_message .= "💬 서버 메시지:\n" . trim($matches[1]) . "\n\n";
            }
            
            // 원본 에러 메시지 (상세 정보 전체)
            $error_message .= "📋 상세 오류 내용:\n";
            $error_message .= str_replace('\n', "\n", $raw_error);
            
            wp_send_json_error([
                'message' => $error_message
            ]);
        }
    }
}

/**
 * Azure AI API 처리 클래스
 */
class Azure_AI_API_Handler {
    
    private $endpoint;
    private $agent_id;
    private $api_version;
    
    // 인증 관련
    private $auth_type; // 'api_key' or 'entra_id'
    private $api_key;
    private $client_id;
    private $client_secret;
    private $tenant_id;
    private $access_token;
    private $token_expiry;
    
    public function __construct($endpoint, $agent_id, $auth_type = 'api_key', $api_key = '', $client_id = '', $client_secret = '', $tenant_id = '') {
        $this->endpoint = rtrim($endpoint, '/');
        $this->agent_id = $agent_id;
        $this->auth_type = $auth_type;
        $this->api_key = $api_key;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->tenant_id = $tenant_id;
        
        // API 버전 설정
        if ($auth_type === 'entra_id') {
            // Agent 모드 (Entra ID): v1
            $this->api_version = 'v1';
        } else {
            // Chat 모드 (API Key): 날짜 형식
            $this->api_version = '2024-08-01-preview';
        }
        
        // Entra ID 인증이면 토큰 캐시 확인
        if ($this->auth_type === 'entra_id') {
            $this->load_cached_token();
        }
    }
    
    /**
     * 캐시된 액세스 토큰 로드
     */
    private function load_cached_token() {
        $cache_key = 'azure_chatbot_access_token_' . md5($this->client_id);
        $cached = get_transient($cache_key);
        
        if ($cached && !empty($cached['token']) && !empty($cached['expiry'])) {
            // 만료 5분 전까지 사용
            if (time() < ($cached['expiry'] - 300)) {
                $this->access_token = $cached['token'];
                $this->token_expiry = $cached['expiry'];
            }
        }
    }
    
    /**
     * 캐시된 액세스 토큰 저장
     */
    private function save_cached_token($token, $expires_in) {
        $cache_key = 'azure_chatbot_access_token_' . md5($this->client_id);
        $expiry = time() + $expires_in;
        
        set_transient($cache_key, [
            'token' => $token,
            'expiry' => $expiry
        ], $expires_in);
        
        $this->access_token = $token;
        $this->token_expiry = $expiry;
    }
    
    /**
     * Entra ID 액세스 토큰 획득
     */
    private function get_access_token() {
        // 캐시된 토큰이 유효하면 반환
        if (!empty($this->access_token) && time() < ($this->token_expiry - 300)) {
            return $this->access_token;
        }
        
        // OAuth 2.0 Client Credentials Flow
        $token_url = "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/v2.0/token";
        
        $response = wp_remote_post($token_url, [
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'scope' => 'https://ai.azure.com/.default'  // Azure AI Foundry Assistants API용 scope
            ],
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            throw new Exception('토큰 요청 실패: ' . $response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($http_code !== 200 || empty($data['access_token'])) {
            $error_msg = !empty($data['error_description']) ? $data['error_description'] : 'Unknown error';
            throw new Exception("Entra ID 인증 실패 (HTTP {$http_code}): {$error_msg}");
        }
        
        // 토큰 캐시 저장 (expires_in은 초 단위, 기본 3600초 = 1시간)
        $expires_in = !empty($data['expires_in']) ? intval($data['expires_in']) : 3600;
        $this->save_cached_token($data['access_token'], $expires_in);
        
        return $data['access_token'];
    }
    
    /**
     * 메시지 전송 및 응답 받기
     */
    public function send_message($message, $thread_id = null) {
        error_log('[Agent API] send_message() called');
        error_log('[Agent API] Input thread_id: ' . ($thread_id ?: 'null'));
        
        // 1. Thread 생성 또는 재사용
        if (empty($thread_id)) {
            error_log('[Agent API] Creating new thread...');
            $thread_id = $this->create_thread();
            error_log('[Agent API] New thread created: ' . $thread_id);
        } else {
            error_log('[Agent API] Reusing existing thread: ' . $thread_id);
        }
        
        // 2. 메시지 추가
        error_log('[Agent API] Adding message to thread...');
        $this->add_message($thread_id, $message);
        error_log('[Agent API] Message added successfully');
        
        // 3. Agent Run 실행
        error_log('[Agent API] Creating run...');
        $run_id = $this->create_run($thread_id);
        error_log('[Agent API] Run created: ' . $run_id);
        
        // 4. 완료 대기
        error_log('[Agent API] Waiting for completion...');
        $run_status = $this->wait_for_completion($thread_id, $run_id);
        error_log('[Agent API] Run status: ' . ($run_status['status'] ?? 'unknown'));
        
        // 5. Function calling 처리 (필요시)
        if ($run_status['status'] === 'requires_action') {
            error_log('[Agent API] Handling tool calls...');
            $this->handle_tool_calls($thread_id, $run_id, $run_status);
            $run_status = $this->wait_for_completion($thread_id, $run_id);
            error_log('[Agent API] After tool calls, status: ' . ($run_status['status'] ?? 'unknown'));
        }
        
        // 6. 응답 메시지 가져오기
        error_log('[Agent API] Retrieving latest message...');
        $response_message = $this->get_latest_message($thread_id);
        error_log('[Agent API] Response message length: ' . strlen($response_message));
        
        return [
            'message' => $response_message,
            'thread_id' => $thread_id
        ];
    }
    
    /**
     * Chat Completions API를 사용한 메시지 전송 (Chat 모드용)
     * 제공자별로 다른 API 형식 사용
     */
    public function send_chat_message($message) {
        $options = get_option('azure_chatbot_settings', []);
        $provider = $options['chat_provider'] ?? 'azure-openai';
        
        // 제공자별 API 경로 및 데이터 구성
        switch ($provider) {
            case 'azure-openai':
                // Azure OpenAI: /openai/deployments/{deployment}/chat/completions
                $path = "/openai/deployments/{$this->agent_id}/chat/completions";
                $data = [
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $message]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800
                ];
                break;
                
            case 'openai':
                // OpenAI: /v1/chat/completions
                $path = "/v1/chat/completions";
                $data = [
                    'model' => $this->agent_id, // OpenAI는 model 파라미터 사용
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $message]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800
                ];
                break;
                
            case 'gemini':
                // Google Gemini: /v1beta/models/{model}:generateContent
                $path = "/v1beta/models/{$this->agent_id}:generateContent";
                $data = [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $message]
                            ]
                        ]
                    ]
                ];
                break;
                
            case 'claude':
                // Anthropic Claude: /v1/messages
                $path = "/v1/messages";
                $data = [
                    'model' => $this->agent_id,
                    'messages' => [
                        ['role' => 'user', 'content' => $message]
                    ],
                    'max_tokens' => 800
                ];
                break;
                
            case 'grok':
                // xAI Grok: /v1/chat/completions (OpenAI 호환)
                $path = "/v1/chat/completions";
                $data = [
                    'model' => $this->agent_id,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $message]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800
                ];
                break;
                
            case 'other':
            default:
                // Other (OpenAI 호환): /v1/chat/completions
                $path = "/v1/chat/completions";
                $data = [
                    'model' => $this->agent_id,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $message]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800
                ];
                break;
        }
        
        $response = $this->api_request($path, 'POST', $data);
        
        // 제공자별 응답 파싱
        $content = $this->parse_chat_response($response, $provider);
        
        if (!empty($content)) {
            return ['message' => $content];
        }
        
        throw new Exception('Chat API 응답에서 메시지를 찾을 수 없습니다.');
    }
    
    /**
     * 제공자별 Chat API 응답 파싱
     */
    private function parse_chat_response($response, $provider) {
        switch ($provider) {
            case 'azure-openai':
            case 'openai':
            case 'grok':
            case 'other':
                // OpenAI 호환 형식: choices[0].message.content
                return $response['choices'][0]['message']['content'] ?? null;
                
            case 'gemini':
                // Gemini 형식: candidates[0].content.parts[0].text
                return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
            case 'claude':
                // Claude 형식: content[0].text
                return $response['content'][0]['text'] ?? null;
                
            default:
                return null;
        }
    }
    
    private function create_thread() {
        // endpoint가 이미 /api/projects/{project} 형식이므로 /threads만 추가
        $response = $this->api_request(
            '/threads',
            'POST',
            []
        );
        return $response['id'];
    }
    
    private function add_message($thread_id, $message) {
        $this->api_request(
            "/threads/{$thread_id}/messages",
            'POST',
            ['role' => 'user', 'content' => $message]
        );
    }
    
    private function create_run($thread_id) {
        $response = $this->api_request(
            "/threads/{$thread_id}/runs",
            'POST',
            ['assistant_id' => $this->agent_id]
        );
        return $response['id'];
    }
    
    private function wait_for_completion($thread_id, $run_id, $max_attempts = 30) {
        $sleep_ms = 250; // 적응형 폴링: 250ms → 1000ms
        
        for ($i = 0; $i < $max_attempts; $i++) {
            $status = $this->api_request(
                "/threads/{$thread_id}/runs/{$run_id}",
                'GET'
            );
            
            $current_status = $status['status'] ?? 'unknown';
            
            // 즉시 반환 조건
            if (in_array($current_status, ['completed', 'failed', 'cancelled', 'expired', 'requires_action'], true)) {
                return $status;
            }
            
            // 적응형 대기: 점진적으로 증가
            usleep($sleep_ms * 1000);
            if ($sleep_ms < 1000) {
                $sleep_ms = min(1000, $sleep_ms + 250);
            }
        }
        
        throw new Exception('Run timeout: 최대 대기 시간 초과');
    }
    
    private function handle_tool_calls($thread_id, $run_id, $run_status) {
        $tool_calls = $run_status['required_action']['submit_tool_outputs']['tool_calls'] ?? [];
        
        if (empty($tool_calls)) {
            return;
        }
        
        $tool_outputs = [];
        
        foreach ($tool_calls as $tool_call) {
            $call_id = $tool_call['id'] ?? '';
            $function_name = $tool_call['function']['name'] ?? '';
            $arguments_json = $tool_call['function']['arguments'] ?? '{}';
            
            $arguments = json_decode($arguments_json, true);
            if (!is_array($arguments)) {
                $arguments = [];
            }
            
            $output = $this->execute_function($function_name, $arguments);
            
            $tool_outputs[] = [
                'tool_call_id' => $call_id,
                'output' => $output
            ];
        }
        
        if (!empty($tool_outputs)) {
            $this->api_request(
                "/threads/{$thread_id}/runs/{$run_id}/submitToolOutputs",
                'POST',
                ['tool_outputs' => $tool_outputs]
            );
        }
    }
    
    private function execute_function($function_name, $arguments) {
        // Function calling 확장 포인트
        $result = apply_filters('azure_chatbot_function_call', null, $function_name, $arguments);
        
        if ($result !== null) {
            return json_encode($result);
        }
        
        // 기본 함수들
        switch ($function_name) {
            case 'get_current_time':
                return json_encode(['time' => current_time('Y-m-d H:i:s')]);
            
            default:
                return json_encode(['error' => 'Unknown function: ' . $function_name]);
        }
    }
    
    private function get_latest_message($thread_id) {
        $messages = $this->api_request(
            "/threads/{$thread_id}/messages?limit=20",
            'GET'
        );
        
        // Assistant의 첫 번째 메시지 찾기
        $assistant_text = '';
        $items = $messages['data'] ?? [];
        
        foreach ($items as $m) {
            if (($m['role'] ?? '') === 'assistant') {
                foreach (($m['content'] ?? []) as $p) {
                    // output_text 타입 처리
                    if (($p['type'] ?? '') === 'output_text') {
                        $assistant_text .= ($p['text'] ?? '');
                    }
                    // text 타입 처리
                    if (($p['type'] ?? '') === 'text' && isset($p['text']['value'])) {
                        $assistant_text .= $p['text']['value'];
                    }
                }
                
                // 첫 번째 assistant 메시지를 찾았으면 중단
                if ($assistant_text !== '') {
                    break;
                }
            }
        }
        
        return $assistant_text !== '' ? $assistant_text : '응답을 받지 못했습니다.';
    }
    
    private function api_request($path, $method, $data = null) {
        $url = $this->endpoint . $path;
        
        // 디버그 로그
        error_log('[API Request] Endpoint: ' . $this->endpoint);
        error_log('[API Request] Path: ' . $path);
        error_log('[API Request] Final URL: ' . $url);
        
        // 제공자 정보 가져오기
        $options = get_option('azure_chatbot_settings', []);
        $provider = $options['chat_provider'] ?? 'azure-openai';
        $mode = $options['mode'] ?? 'chat';
        
        // Azure OpenAI와 Agent 모드에서만 api-version 추가
        if ($provider === 'azure-openai' || $mode === 'agent') {
            if ($method === 'GET' && strpos($path, '?') === false) {
                $url .= '?api-version=' . $this->api_version;
            } elseif ($method === 'GET') {
                $url .= '&api-version=' . $this->api_version;
            } elseif (strpos($path, '?') === false) {
                $url .= '?api-version=' . $this->api_version;
            } else {
                $url .= '&api-version=' . $this->api_version;
            }
        }
        
        // 제공자별 인증 헤더 설정
        $headers = ['Content-Type' => 'application/json'];
        
        if ($this->auth_type === 'entra_id') {
            // Entra ID: Bearer 토큰 사용 (Agent 모드)
            $access_token = $this->get_access_token();
            $headers['Authorization'] = 'Bearer ' . $access_token;
        } else {
            // API Key 인증 (Chat 모드)
            switch ($provider) {
                case 'azure-openai':
                    // Azure OpenAI: api-key 헤더
                    $headers['api-key'] = $this->api_key;
                    break;
                    
                case 'openai':
                case 'grok':
                case 'other':
                    // OpenAI/Grok/Other: Authorization Bearer 헤더
                    $headers['Authorization'] = 'Bearer ' . $this->api_key;
                    break;
                    
                case 'gemini':
                    // Google Gemini: 쿼리 파라미터로 key 전달
                    $separator = strpos($url, '?') !== false ? '&' : '?';
                    $url .= $separator . 'key=' . $this->api_key;
                    break;
                    
                case 'claude':
                    // Anthropic Claude: x-api-key 헤더
                    $headers['x-api-key'] = $this->api_key;
                    $headers['anthropic-version'] = '2023-06-01';
                    break;
            }
        }
        
        $args = [
            'method' => $method,
            'headers' => $headers,
            'timeout' => 60
        ];
        
        if ($data !== null && in_array($method, ['POST', 'PUT'])) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            throw new Exception('네트워크 오류: ' . $response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $response_data = json_decode($body, true);
        
        if ($http_code >= 400) {
            error_log("Azure AI API Error: HTTP {$http_code} - {$body}");
            
            // 상세한 에러 메시지 구성
            $error_message = "HTTP {$http_code}";
            
            // HTTP 상태 코드별 상세 설명
            switch ($http_code) {
                case 401:
                    $error_message .= " - 인증 실패\n";
                    if ($this->auth_type === 'entra_id') {
                        $error_message .= "• Client ID, Client Secret, Tenant ID가 올바른지 확인해주세요\n";
                        $error_message .= "• Service Principal에 Cognitive Services User 권한이 있는지 확인해주세요";
                    } else {
                        $error_message .= "• API Key가 올바른지 확인해주세요\n";
                        $error_message .= "• Endpoint URL이 정확한지 확인해주세요";
                    }
                    break;
                case 403:
                    $error_message .= " - 권한 없음\n";
                    if ($this->auth_type === 'entra_id') {
                        $error_message .= "• Service Principal에 해당 리소스 접근 권한이 있는지 확인해주세요";
                    } else {
                        $error_message .= "• API Key에 해당 에이전트 접근 권한이 있는지 확인해주세요";
                    }
                    break;
                case 404:
                    $error_message .= " - 리소스를 찾을 수 없음\n";
                    $error_message .= "• Endpoint URL이 정확한지 확인해주세요\n";
                    if ($this->auth_type === 'entra_id') {
                        $error_message .= "• Agent ID가 올바른지 확인해주세요";
                    } else {
                        $error_message .= "• 배포 이름(Deployment Name)이 올바른지 확인해주세요";
                    }
                    break;
                case 429:
                    $error_message .= " - 요청 한도 초과\n";
                    $error_message .= "• 잠시 후 다시 시도해주세요";
                    break;
                case 500:
                case 502:
                case 503:
                    $error_message .= " - Azure 서버 오류\n";
                    $error_message .= "• Azure 서비스 상태를 확인해주세요\n";
                    $error_message .= "• 잠시 후 다시 시도해주세요";
                    break;
                default:
                    $error_message .= " - API 오류";
            }
            
            // Azure API가 반환한 상세 오류 메시지 추가
            if (!empty($response_data['error']['message'])) {
                $error_message .= "\n\n상세 정보: " . $response_data['error']['message'];
            } elseif (!empty($response_data['message'])) {
                $error_message .= "\n\n상세 정보: " . $response_data['message'];
            }
            
            // 요청 URL 정보 (디버깅용)
            $error_message .= "\n\n요청 URL: " . parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . $path;
            
            throw new Exception($error_message);
        }
        
        return $response_data;
    }
}

// 플러그인 초기화
function azure_ai_chatbot_init() {
    return Azure_AI_Chatbot::get_instance();
}
add_action('plugins_loaded', 'azure_ai_chatbot_init');
