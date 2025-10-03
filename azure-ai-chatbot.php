<?php
/**
 * Plugin Name: Azure AI Chatbot
 * Plugin URI: https://edueldensolution.kr
 * Description: Azure AI Foundry 에이전트를 WordPress에 통합하는 채팅 위젯
 * Version: 2.0.0
 * Author: 허석 (Heo Seok)
 * Author URI: mailto:admin@edueldensolution.kr
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: azure-ai-chatbot
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // 직접 접근 차단
}

// 플러그인 상수 정의
define('AZURE_CHATBOT_VERSION', '2.0.0');
define('AZURE_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AZURE_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AZURE_CHATBOT_PLUGIN_BASENAME', plugin_basename(__FILE__));

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
     * 암호화 키 (WordPress 보안 키 기반)
     */
    private $encryption_key;
    
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
        $this->init_encryption_key();
        $this->load_options();
        $this->init_hooks();
    }
    
    /**
     * 암호화 키 초기화
     * WordPress의 보안 상수를 조합하여 생성
     */
    private function init_encryption_key() {
        // WordPress 보안 상수들을 조합하여 암호화 키 생성
        $key_parts = [
            defined('AUTH_KEY') ? AUTH_KEY : '',
            defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : '',
            defined('LOGGED_IN_KEY') ? LOGGED_IN_KEY : '',
            defined('NONCE_KEY') ? NONCE_KEY : ''
        ];
        
        $combined_key = implode('', $key_parts);
        
        // 키가 비어있다면 기본값 사용 (보안 경고 로그)
        if (empty($combined_key)) {
            error_log('Azure AI Chatbot: WordPress 보안 키가 설정되지 않았습니다. wp-config.php에 보안 키를 추가하세요.');
            $combined_key = 'default-insecure-key-' . get_site_url();
        }
        
        // SHA-256으로 고정 길이 키 생성 (32바이트)
        $this->encryption_key = hash('sha256', $combined_key, true);
    }
    
    /**
     * 데이터 암호화
     */
    private function encrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        // OpenSSL 사용 가능 여부 확인
        if (!function_exists('openssl_encrypt')) {
            error_log('Azure AI Chatbot: OpenSSL이 설치되지 않았습니다. API 키가 암호화되지 않고 저장됩니다.');
            return base64_encode($data); // 폴백: base64만 사용 (경고)
        }
        
        $method = 'aes-256-cbc';
        $iv_length = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        $encrypted = openssl_encrypt(
            $data,
            $method,
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // IV와 암호화된 데이터를 함께 저장
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * 데이터 복호화
     */
    private function decrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        // OpenSSL 사용 불가능한 경우
        if (!function_exists('openssl_decrypt')) {
            return base64_decode($data); // 폴백: base64 디코딩만
        }
        
        $method = 'aes-256-cbc';
        $iv_length = openssl_cipher_iv_length($method);
        
        $decoded = base64_decode($data);
        
        // 데이터 길이 검증
        if (strlen($decoded) < $iv_length) {
            error_log('Azure AI Chatbot: 암호화된 데이터 형식이 올바르지 않습니다.');
            return '';
        }
        
        $iv = substr($decoded, 0, $iv_length);
        $encrypted = substr($decoded, $iv_length);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            $method,
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return $decrypted !== false ? $decrypted : '';
    }
    
    /**
     * 옵션 로드
     */
    private function load_options() {
        $stored_options = get_option('azure_chatbot_settings', []);
        
        // API Key 복호화
        $api_key = '';
        if (!empty($stored_options['api_key_encrypted'])) {
            $api_key = $this->decrypt($stored_options['api_key_encrypted']);
        }
        
        $this->options = [
            'api_key' => $api_key,
            'endpoint' => $stored_options['endpoint'] ?? '',
            'agent_id' => $stored_options['agent_id'] ?? '',
            'enabled' => $stored_options['enabled'] ?? false,
            'widget_position' => $stored_options['widget_position'] ?? 'bottom-right',
            'primary_color' => $stored_options['primary_color'] ?? '#667eea',
            'secondary_color' => $stored_options['secondary_color'] ?? '#764ba2',
            'welcome_message' => $stored_options['welcome_message'] ?? '안녕하세요! 무엇을 도와드릴까요?',
            'chat_title' => $stored_options['chat_title'] ?? 'AI 도우미'
        ];
    }
    
    /**
     * 훅 초기화
     */
    private function init_hooks() {
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
        return !empty($this->options['enabled']) && 
               !empty($this->options['api_key']) && 
               !empty($this->options['endpoint']) && 
               !empty($this->options['agent_id']);
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
            '설정',
            '설정',
            'manage_options',
            'azure-ai-chatbot',
            [$this, 'render_settings_page']
        );
        
        add_submenu_page(
            'azure-ai-chatbot',
            '사용 가이드',
            '사용 가이드',
            'manage_options',
            'azure-ai-chatbot-guide',
            [$this, 'render_guide_page']
        );
    }
    
    /**
     * 설정 링크 추가
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=azure-ai-chatbot">설정</a>';
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
        
        // API Key는 암호화하여 저장
        if (!empty($input['api_key'])) {
            $api_key = sanitize_text_field($input['api_key']);
            
            // 기존 값과 다른 경우에만 암호화 (마스킹된 값 처리)
            if (strpos($api_key, '••••') === false) {
                $sanitized['api_key_encrypted'] = $this->encrypt($api_key);
            } else {
                // 마스킹된 값이면 기존 암호화 값 유지
                $old_options = get_option('azure_chatbot_settings', []);
                $sanitized['api_key_encrypted'] = $old_options['api_key_encrypted'] ?? '';
            }
        }
        
        // 나머지 값들은 일반적으로 저장 (민감하지 않은 정보)
        $sanitized['endpoint'] = esc_url_raw($input['endpoint'] ?? '');
        $sanitized['agent_id'] = sanitize_text_field($input['agent_id'] ?? '');
        $sanitized['enabled'] = !empty($input['enabled']);
        $sanitized['widget_position'] = sanitize_text_field($input['widget_position'] ?? 'bottom-right');
        $sanitized['primary_color'] = sanitize_hex_color($input['primary_color'] ?? '#667eea');
        $sanitized['secondary_color'] = sanitize_hex_color($input['secondary_color'] ?? '#764ba2');
        $sanitized['welcome_message'] = sanitize_textarea_field($input['welcome_message'] ?? '안녕하세요! 무엇을 도와드릴까요?');
        $sanitized['chat_title'] = sanitize_text_field($input['chat_title'] ?? 'AI 도우미');
        
        return $sanitized;
    }
    
    /**
     * 설정 페이지용 API Key 마스킹
     */
    public function get_masked_api_key() {
        if (empty($this->options['api_key'])) {
            return '';
        }
        
        $key = $this->options['api_key'];
        $key_length = strlen($key);
        
        if ($key_length <= 8) {
            return str_repeat('•', $key_length);
        }
        
        // 앞 4자리와 뒤 4자리만 표시, 나머지는 마스킹
        return substr($key, 0, 4) . str_repeat('•', $key_length - 8) . substr($key, -4);
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
            'nonce' => wp_create_nonce('azure_chatbot_nonce'),
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
            <button id="azure-chatbot-toggle" class="chatbot-toggle" aria-label="채팅 열기">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                </svg>
            </button>
            
            <div id="azure-chatbot-window" class="chatbot-window">
                <div class="chatbot-header">
                    <h3><?php echo esc_html($this->options['chat_title']); ?></h3>
                    <button id="azure-chatbot-close" aria-label="채팅 닫기">×</button>
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
                    <span>답변 생성 중</span>
                    <div class="typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                
                <div class="chatbot-input">
                    <input type="text" id="azure-chatbot-input" placeholder="메시지를 입력하세요..." aria-label="메시지 입력" />
                    <button id="azure-chatbot-send" aria-label="메시지 전송">
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
        // Nonce 검증
        if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'azure_chatbot_nonce')) {
            return new WP_Error('invalid_nonce', '보안 검증 실패', ['status' => 403]);
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
            // Azure AI API 호출
            $api_handler = new Azure_AI_API_Handler(
                $this->options['endpoint'],
                $this->options['api_key'],
                $this->options['agent_id']
            );
            
            $response = $api_handler->send_message($message, $thread_id);
            
            return new WP_REST_Response([
                'success' => true,
                'reply' => $response['message'],
                'thread_id' => $response['thread_id']
            ], 200);
            
        } catch (Exception $e) {
            error_log('Azure AI Chatbot Error: ' . $e->getMessage());
            return new WP_Error('api_error', '일시적인 오류가 발생했습니다', ['status' => 500]);
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
        
        if (!$this->is_widget_enabled()) {
            wp_send_json_error(['message' => '설정이 완료되지 않았습니다. API Key, 엔드포인트, 에이전트 ID를 모두 입력해주세요.']);
            return;
        }
        
        try {
            $api_handler = new Azure_AI_API_Handler(
                $this->options['endpoint'],
                $this->options['api_key'],
                $this->options['agent_id']
            );
            
            // 간단한 테스트 메시지 전송
            $response = $api_handler->send_message('Hello, this is a test message.', null);
            
            wp_send_json_success([
                'message' => 'Azure AI 연결에 성공했습니다! 에이전트가 정상적으로 응답했습니다.'
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Azure AI 연결에 실패했습니다: ' . $e->getMessage()
            ]);
        }
    }
}

/**
 * Azure AI API 처리 클래스
 */
class Azure_AI_API_Handler {
    
    private $endpoint;
    private $api_key;
    private $agent_id;
    private $api_version = '2024-12-01-preview';
    
    public function __construct($endpoint, $api_key, $agent_id) {
        $this->endpoint = rtrim($endpoint, '/');
        $this->api_key = $api_key;
        $this->agent_id = $agent_id;
    }
    
    /**
     * 메시지 전송 및 응답 받기
     */
    public function send_message($message, $thread_id = null) {
        // 1. Thread 생성 또는 재사용
        if (empty($thread_id)) {
            $thread_id = $this->create_thread();
        }
        
        // 2. 메시지 추가
        $this->add_message($thread_id, $message);
        
        // 3. Agent Run 실행
        $run_id = $this->create_run($thread_id);
        
        // 4. 완료 대기
        $run_status = $this->wait_for_completion($thread_id, $run_id);
        
        // 5. Function calling 처리 (필요시)
        if ($run_status['status'] === 'requires_action') {
            $this->handle_tool_calls($thread_id, $run_id, $run_status);
            $run_status = $this->wait_for_completion($thread_id, $run_id);
        }
        
        // 6. 응답 메시지 가져오기
        $response_message = $this->get_latest_message($thread_id);
        
        return [
            'message' => $response_message,
            'thread_id' => $thread_id
        ];
    }
    
    private function create_thread() {
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
        for ($i = 0; $i < $max_attempts; $i++) {
            $status = $this->api_request(
                "/threads/{$thread_id}/runs/{$run_id}",
                'GET'
            );
            
            if (in_array($status['status'], ['completed', 'failed', 'cancelled', 'requires_action'])) {
                return $status;
            }
            
            sleep(1);
        }
        
        throw new Exception('Run timeout');
    }
    
    private function handle_tool_calls($thread_id, $run_id, $run_status) {
        $tool_outputs = [];
        
        foreach ($run_status['required_action']['submit_tool_outputs']['tool_calls'] as $tool_call) {
            $function_name = $tool_call['function']['name'];
            $arguments = json_decode($tool_call['function']['arguments'], true);
            
            $output = $this->execute_function($function_name, $arguments);
            
            $tool_outputs[] = [
                'tool_call_id' => $tool_call['id'],
                'output' => $output
            ];
        }
        
        $this->api_request(
            "/threads/{$thread_id}/runs/{$run_id}/submit_tool_outputs",
            'POST',
            ['tool_outputs' => $tool_outputs]
        );
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
            "/threads/{$thread_id}/messages?order=desc&limit=1",
            'GET'
        );
        
        if (!empty($messages['data'][0]['content'][0]['text']['value'])) {
            return $messages['data'][0]['content'][0]['text']['value'];
        }
        
        return '응답을 받지 못했습니다.';
    }
    
    private function api_request($path, $method, $data = null) {
        $url = $this->endpoint . $path;
        
        if ($method === 'GET' && strpos($path, '?') === false) {
            $url .= '?api-version=' . $this->api_version;
        } elseif ($method === 'GET') {
            $url .= '&api-version=' . $this->api_version;
        } else {
            $url .= '?api-version=' . $this->api_version;
        }
        
        $args = [
            'method' => $method,
            'headers' => [
                'api-key' => $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 60
        ];
        
        if ($data !== null && in_array($method, ['POST', 'PUT'])) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($http_code >= 400) {
            error_log("Azure AI API Error: HTTP {$http_code} - {$body}");
            throw new Exception("API Error: HTTP {$http_code}");
        }
        
        return json_decode($body, true);
    }
}

// 플러그인 초기화
function azure_ai_chatbot_init() {
    return Azure_AI_Chatbot::get_instance();
}
add_action('plugins_loaded', 'azure_ai_chatbot_init');
