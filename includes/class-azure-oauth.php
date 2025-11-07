<?php
/**
 * Azure OAuth Authentication Handler
 * 
 * WordPress ?�러그인?�서 Azure OAuth 2.0 ?�증??처리?�는 ?�래??
 */

if (!defined('ABSPATH')) {
    exit;
}

class Azure_Chatbot_OAuth {
    
    /**
     * OAuth ?�정
     */
    private $client_id;
    private $client_secret;
    private $tenant_id;
    private $redirect_uri;
    
    /**
     * Azure OAuth Endpoints
     */
    private $authority_url;
    private $token_url;
    
    /**
     * ?�성??
     */
    public function __construct() {
        $this->load_config();
        $this->init_hooks();
    }
    
    /**
     * OAuth ?�정 로드
     */
    private function load_config() {
        // wp-config.php?�서 ?�수�??�의??�??�선 ?�용
        $this->client_id = defined('AZURE_CHATBOT_OAUTH_CLIENT_ID') 
            ? AZURE_CHATBOT_OAUTH_CLIENT_ID 
            : get_option('azure_chatbot_oauth_client_id', '');
            
        $this->client_secret = defined('AZURE_CHATBOT_OAUTH_CLIENT_SECRET') 
            ? AZURE_CHATBOT_OAUTH_CLIENT_SECRET 
            : get_option('azure_chatbot_oauth_client_secret', '');
            
        $this->tenant_id = defined('AZURE_CHATBOT_OAUTH_TENANT_ID') 
            ? AZURE_CHATBOT_OAUTH_TENANT_ID 
            : get_option('azure_chatbot_oauth_tenant_id', '');
        
        // Redirect URI: WordPress 관리자 ?�정 ?�이지
        $this->redirect_uri = admin_url('admin.php?page=azure-ai-chatbot&azure_callback=1');
        
        // Azure OAuth Endpoints
        $this->authority_url = "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/v2.0/authorize";
        $this->token_url = "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/v2.0/token";
    }
    
    /**
     * Hooks 초기??
     */
    private function init_hooks() {
        // OAuth 콜백 처리
        add_action('admin_init', array($this, 'handle_oauth_callback'));
        
        // AJAX ?�들??
        add_action('wp_ajax_azure_oauth_get_subscriptions', array($this, 'ajax_get_subscriptions'));
        add_action('wp_ajax_azure_oauth_get_resource_groups', array($this, 'ajax_get_resource_groups'));
        add_action('wp_ajax_azure_oauth_get_resources', array($this, 'ajax_get_resources'));
        add_action('wp_ajax_azure_oauth_get_agents', array($this, 'ajax_get_agents'));
        add_action('wp_ajax_azure_oauth_get_keys', array($this, 'ajax_get_keys'));
        add_action('wp_ajax_save_oauth_settings', array($this, 'ajax_save_oauth_settings'));
        add_action('wp_ajax_azure_oauth_clear_session', array($this, 'ajax_clear_session'));
        add_action('wp_ajax_azure_oauth_reset_config', array($this, 'ajax_reset_config'));
        
        // 리소스 생성 관련 AJAX
        add_action('wp_ajax_azure_oauth_get_available_locations', array($this, 'ajax_get_available_locations'));
        add_action('wp_ajax_azure_oauth_get_available_models', array($this, 'ajax_get_available_models'));
        add_action('wp_ajax_azure_oauth_create_resource_group', array($this, 'ajax_create_resource_group'));
        add_action('wp_ajax_azure_oauth_create_ai_resource', array($this, 'ajax_create_ai_resource'));
        add_action('wp_ajax_azure_oauth_save_final_config', array($this, 'ajax_save_final_config'));
        add_action('wp_ajax_azure_oauth_get_deployments', array($this, 'ajax_get_deployments'));
        add_action('wp_ajax_azure_oauth_save_existing_config', array($this, 'ajax_save_existing_config'));
    }
    
    /**
     * OAuth ?�증 URL ?�성
     */
    public function get_authorization_url() {
        // State ?�성 (CSRF 방�?)
        $state = wp_create_nonce('azure_oauth_state');
        set_transient('azure_oauth_state', $state, HOUR_IN_SECONDS);
        
        $params = array(
            'client_id' => $this->client_id,
            'response_type' => 'code',
            'redirect_uri' => $this->redirect_uri,
            'response_mode' => 'query',
            'scope' => 'https://management.azure.com/user_impersonation offline_access openid profile',
            'state' => $state
        );
        
        return $this->authority_url . '?' . http_build_query($params);
    }
    
    /**
     * OAuth ?�정???�료?�었?��? ?�인
     */
    public function is_configured() {
        return !empty($this->client_id) && 
               !empty($this->client_secret) && 
               !empty($this->tenant_id);
    }
    
    /**
     * OAuth 콜백 처리
     */
    public function handle_oauth_callback() {
        // OAuth 콜백?��? ?�인
        if (!isset($_GET['page']) || $_GET['page'] !== 'azure-ai-chatbot') {
            return;
        }
        
        if (!isset($_GET['azure_callback'])) {
            return;
        }
        
        // 관리자 권한 ?�인
        if (!current_user_can('manage_options')) {
            wp_die(__('권한???�습?�다.', 'azure-ai-chatbot'));
        }
        
        // ?�러 처리
        if (isset($_GET['error'])) {
            $error_description = isset($_GET['error_description']) ? $_GET['error_description'] : $_GET['error'];
            set_transient('azure_oauth_error', $error_description, 60);
            wp_redirect(admin_url('admin.php?page=azure-ai-chatbot&oauth_error=1'));
            exit;
        }
        
        // Authorization Code ?�인
        if (!isset($_GET['code'])) {
            return;
        }
        
        // State 검�?(CSRF 방�?)
        $state = isset($_GET['state']) ? $_GET['state'] : '';
        $saved_state = get_transient('azure_oauth_state');
        
        if (empty($state) || $state !== $saved_state) {
            set_transient('azure_oauth_error', 'Invalid state parameter', 60);
            wp_redirect(admin_url('admin.php?page=azure-ai-chatbot&oauth_error=1'));
            exit;
        }
        
        // State ??��
        delete_transient('azure_oauth_state');
        
        // Authorization Code�?Access Token ?�청
        $code = sanitize_text_field($_GET['code']);
        $token_data = $this->request_access_token($code);
        
        if (is_wp_error($token_data)) {
            set_transient('azure_oauth_error', $token_data->get_error_message(), 60);
            wp_redirect(admin_url('admin.php?page=azure-ai-chatbot&oauth_error=1'));
            exit;
        }
        
        // Access Token???�션???�??(보안??DB???�?�하지 ?�음)
        $this->save_token_to_session($token_data);
        
        // 팝업 창인지 확인하고 처리
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php esc_html_e('인증 완료', 'azure-ai-chatbot'); ?></title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background: #f0f0f1;
                }
                .success-message {
                    text-align: center;
                    padding: 40px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                .success-message h1 {
                    color: #2271b1;
                    margin-bottom: 10px;
                }
                .dashicons {
                    font-size: 48px;
                    color: #46b450;
                    margin-bottom: 20px;
                }
            </style>
            <link rel="stylesheet" href="<?php echo esc_url(admin_url('css/dashicons.min.css')); ?>">
        </head>
        <body>
            <div class="success-message">
                <div class="dashicons dashicons-yes-alt"></div>
                <h1><?php esc_html_e('Azure 인증 성공!', 'azure-ai-chatbot'); ?></h1>
                <p><?php esc_html_e('창이 자동으로 닫힙니다...', 'azure-ai-chatbot'); ?></p>
            </div>
            <script>
                // localStorage에 토큰 저장 완료 플래그 설정 (팝업에서 부모 창으로 전달)
                try {
                    localStorage.setItem('azure_oauth_token_saved', '1');
                    localStorage.setItem('azure_oauth_token_saved_time', Date.now().toString());
                    console.log('[OAuth] Token saved flag set in localStorage');
                } catch(e) {
                    console.warn('[OAuth] Cannot access localStorage:', e);
                }
                
                // 팝업 창이면 부모 창 새로고침 후 닫기
                if (window.opener) {
                    try {
                        // has_token=1 파라미터 추가하여 localStorage 기반 토큰 확인
                        // tab=oauth-auto-setup 추가하여 OAuth 자동 설정 탭으로 이동
                        var successUrl = <?php 
                            $url = add_query_arg(array(
                                'page' => 'azure-ai-chatbot',
                                'tab' => 'oauth-auto-setup',
                                'oauth_success' => '1',
                                'has_token' => '1'
                            ), admin_url('admin.php'));
                            echo json_encode($url); 
                        ?>;
                        console.log('[OAuth] Redirecting to:', successUrl);
                        window.opener.location.href = successUrl;
                        setTimeout(function() {
                            window.close();
                        }, 1000);
                    } catch(e) {
                        console.error('[OAuth] Error redirecting:', e);
                        // 크로스 오리진 문제 시 부모 창 새로고침만
                        window.opener.location.reload();
                        setTimeout(function() {
                            window.close();
                        }, 1000);
                    }
                } else {
                    // 팝업이 아니면 일반 리다이렉트
                    var successUrl = <?php 
                        $url = add_query_arg(array(
                            'page' => 'azure-ai-chatbot',
                            'tab' => 'oauth-auto-setup',
                            'oauth_success' => '1',
                            'has_token' => '1'
                        ), admin_url('admin.php'));
                        echo json_encode($url); 
                    ?>;
                    console.log('[OAuth] Redirecting to:', successUrl);
                    window.location.href = successUrl;
                }
            </script>
        </body>
        </html>
        <?php
        exit;
    }
    
    /**
     * Authorization Code�?Access Token?�로 교환
     */
    private function request_access_token($code) {
        $params = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'redirect_uri' => $this->redirect_uri,
            'grant_type' => 'authorization_code',
            'scope' => 'https://management.azure.com/user_impersonation'
        );
        
        $response = wp_remote_post($this->token_url, array(
            'body' => $params,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return new WP_Error(
                'token_error',
                isset($data['error_description']) ? $data['error_description'] : $data['error']
            );
        }
        
        return $data;
    }
    
    /**
     * Token???�션???�??
     */
    private function save_token_to_session($token_data) {
        if (!session_id()) {
            session_start();
        }
        
        $_SESSION['azure_access_token'] = $token_data['access_token'];
        $_SESSION['azure_token_expires'] = time() + intval($token_data['expires_in']);
        
        if (isset($token_data['refresh_token'])) {
            $_SESSION['azure_refresh_token'] = $token_data['refresh_token'];
        }
    }
    
    /**
     * ?�션?�서 Access Token 가?�오�?
     */
    private function get_access_token() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['azure_access_token'])) {
            return new WP_Error('no_token', '?�증???�요?�니?? Azure ?�동 ?�정 버튼???�릭?�세??');
        }
        
        // ?�큰 만료 ?�인
        if (isset($_SESSION['azure_token_expires']) && $_SESSION['azure_token_expires'] < time()) {
            // Refresh Token???�으�?갱신 ?�도
            if (isset($_SESSION['azure_refresh_token'])) {
                $token_data = $this->refresh_access_token($_SESSION['azure_refresh_token']);
                if (!is_wp_error($token_data)) {
                    $this->save_token_to_session($token_data);
                    return $token_data['access_token'];
                }
            }
            
            // 갱신 ?�패 ???�러 반환
            $this->clear_session();
            return new WP_Error('token_expired', '?�션??만료?�었?�니?? ?�시 ?�증?�세??');
        }
        
        return $_SESSION['azure_access_token'];
    }
    
    /**
     * Refresh Token?�로 Access Token 갱신
     */
    private function refresh_access_token($refresh_token) {
        $params = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
            'scope' => 'https://management.azure.com/user_impersonation'
        );
        
        $response = wp_remote_post($this->token_url, array(
            'body' => $params,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return new WP_Error(
                'refresh_error',
                isset($data['error_description']) ? $data['error_description'] : $data['error']
            );
        }
        
        return $data;
    }
    
    /**
     * ?�션 ?�리
     */
    public function clear_session() {
        if (!session_id()) {
            session_start();
        }
        
        unset($_SESSION['azure_access_token']);
        unset($_SESSION['azure_token_expires']);
        unset($_SESSION['azure_refresh_token']);
    }
    
    /**
     * Azure Management API ?�출
     */
    /**
     * Azure Management API ?�출
     * 
     * @param string $endpoint API 엔드포인트 (전체 URL 또는 경로)
     * @param string $api_version API 버전
     * @param string $method HTTP 메서드 (GET, POST, PUT, DELETE)
     * @param array $body 요청 본문 (POST/PUT용)
     * @param bool $is_path true면 endpoint를 경로로 처리, false면 전체 URL로 처리
     * @return array|WP_Error
     */
    private function call_azure_api($endpoint, $api_version = '2021-04-01', $method = 'GET', $body = null, $is_path = true) {
        $access_token = $this->get_access_token();
        
        if (is_wp_error($access_token)) {
            return $access_token;
        }
        
        // URL 생성
        if ($is_path) {
            // 경로가 주어진 경우
            $url = "https://management.azure.com{$endpoint}";
            if ($api_version) {
                $url .= (strpos($url, '?') !== false ? '&' : '?') . "api-version={$api_version}";
            }
        } else {
            // 전체 URL이 주어진 경우
            $url = $endpoint;
        }
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 60,
            'method' => strtoupper($method)
        );
        
        // POST/PUT 요청일 경우 body 추가
        if ($body !== null && in_array(strtoupper($method), array('POST', 'PUT', 'PATCH'))) {
            $args['body'] = json_encode($body);
        }
        
        // HTTP 요청
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // 204 No Content 처리
        if ($status_code === 204) {
            return array('success' => true);
        }
        
        // 응답이 비어있으면 에러
        if (empty($response_body)) {
            if ($status_code >= 200 && $status_code < 300) {
                return array('success' => true);
            }
            return new WP_Error('api_error', "HTTP {$status_code}: 응답이 비어있습니다.");
        }
        
        $data = json_decode($response_body, true);
        
        // JSON 디코딩 실패
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'JSON 파싱 실패: ' . json_last_error_msg());
        }
        
        // API 에러 처리
        if (isset($data['error'])) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'API 호출 실패';
            return new WP_Error('api_error', $error_message);
        }
        
        // HTTP 에러 코드 처리
        if ($status_code >= 400) {
            $error_message = isset($data['message']) ? $data['message'] : "HTTP {$status_code} 에러";
            return new WP_Error('http_error', $error_message);
        }
        
        return $data;
    }
    
    /**
     * AJAX: Subscription 목록 조회
     */
    public function ajax_get_subscriptions() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $result = $this->call_azure_api('/subscriptions', '2020-01-01');
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        $subscriptions = array();
        if (isset($result['value'])) {
            foreach ($result['value'] as $sub) {
                $subscriptions[] = array(
                    'id' => $sub['subscriptionId'],
                    'name' => $sub['displayName']
                );
            }
        }
        
        wp_send_json_success(array('subscriptions' => $subscriptions));
    }
    
    /**
     * AJAX: Resource Group 목록 조회
     */
    public function ajax_get_resource_groups() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : '';
        
        if (empty($subscription_id)) {
            wp_send_json_error(array('message' => 'Subscription ID가 ?�요?�니??'));
        }
        
        $result = $this->call_azure_api("/subscriptions/{$subscription_id}/resourcegroups", '2021-04-01');
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        $resource_groups = array();
        if (isset($result['value'])) {
            foreach ($result['value'] as $rg) {
                $resource_groups[] = array(
                    'name' => $rg['name'],
                    'location' => $rg['location']
                );
            }
        }
        
        wp_send_json_success(array('resource_groups' => $resource_groups));
    }
    
    /**
     * AJAX: AI Foundry / OpenAI 리소??목록 조회
     */
    public function ajax_get_resources() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : '';
        $resource_group = isset($_POST['resource_group']) ? sanitize_text_field($_POST['resource_group']) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'chat';
        
        if (empty($subscription_id) || empty($resource_group)) {
            wp_send_json_error(array('message' => 'Subscription ID?� Resource Group???�요?�니??'));
        }
        
        // 모드???�라 ?�른 리소???�??조회
        if ($mode === 'agent') {
            // AI Foundry Project
            $resource_type = 'Microsoft.MachineLearningServices/workspaces';
        } else {
            // Azure OpenAI / AI Services
            $resource_type = 'Microsoft.CognitiveServices/accounts';
        }
        
        $endpoint = "/subscriptions/{$subscription_id}/resourceGroups/{$resource_group}/providers/{$resource_type}";
        $result = $this->call_azure_api($endpoint, '2023-05-01');
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        $resources = array();
        if (isset($result['value'])) {
            foreach ($result['value'] as $resource) {
                $resources[] = array(
                    'name' => $resource['name'],
                    'id' => $resource['id'],
                    'location' => $resource['location']
                );
            }
        }
        
        wp_send_json_success(array('resources' => $resources));
    }
    
    /**
     * AJAX: Agent ID 목록 조회 (Agent 모드 ?�용)
     */
    public function ajax_get_agents() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $resource_id = isset($_POST['resource_id']) ? sanitize_text_field($_POST['resource_id']) : '';
        
        if (empty($resource_id)) {
            wp_send_json_error(array('message' => 'Resource ID가 ?�요?�니??'));
        }
        
        // AI Foundry Project??Endpoint 조회
        $resource_info = $this->call_azure_api($resource_id, '2023-05-01');
        
        if (is_wp_error($resource_info)) {
            wp_send_json_error(array('message' => $resource_info->get_error_message()));
        }
        
        // Discovery URL (Project Endpoint) 추출
        $discovery_url = isset($resource_info['properties']['discoveryUrl']) 
            ? $resource_info['properties']['discoveryUrl'] 
            : '';
            
        if (empty($discovery_url)) {
            wp_send_json_error(array('message' => 'Project Endpoint�?찾을 ???�습?�다.'));
        }
        
        // Keys 조회 (Subscription Key ?�요)
        $keys_endpoint = "{$resource_id}/listKeys";
        $keys_result = $this->call_azure_api($keys_endpoint, '2023-05-01');
        
        if (is_wp_error($keys_result)) {
            wp_send_json_error(array('message' => $keys_result->get_error_message()));
        }
        
        $subscription_key = isset($keys_result['key1']) ? $keys_result['key1'] : '';
        
        if (empty($subscription_key)) {
            wp_send_json_error(array('message' => 'Subscription Key�?찾을 ???�습?�다.'));
        }
        
        // Agent 목록 조회 (AI Foundry Agents API)
        $agents_url = rtrim($discovery_url, '/') . '/agents';
        
        $response = wp_remote_get($agents_url, array(
            'headers' => array(
                'api-key' => $subscription_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Agent 목록 조회 ?�패: ' . $response->get_error_message()));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            wp_send_json_error(array('message' => 'Agent 목록??찾을 ???�습?�다. Project??Agent가 ?�성?�어 ?�는지 ?�인?�세??'));
        }
        
        $agents = array();
        foreach ($data['data'] as $agent) {
            $agents[] = array(
                'id' => $agent['id'],
                'name' => isset($agent['name']) ? $agent['name'] : $agent['id'],
                'description' => isset($agent['description']) ? $agent['description'] : '',
                'created_at' => isset($agent['created_at']) ? $agent['created_at'] : ''
            );
        }
        
        wp_send_json_success(array('agents' => $agents));
    }
    
    /**
     * AJAX: API Key 조회
     */
    public function ajax_get_keys() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $resource_id = isset($_POST['resource_id']) ? sanitize_text_field($_POST['resource_id']) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'chat';
        
        if (empty($resource_id)) {
            wp_send_json_error(array('message' => 'Resource ID가 ?�요?�니??'));
        }
        
        // Keys 조회
        $endpoint = "{$resource_id}/listKeys";
        $result = $this->call_azure_api($endpoint, '2023-05-01');
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        // Endpoint ?�보 조회
        $resource_info = $this->call_azure_api($resource_id, '2023-05-01');
        
        if (is_wp_error($resource_info)) {
            wp_send_json_error(array('message' => $resource_info->get_error_message()));
        }
        
        $endpoint_url = '';
        if ($mode === 'agent') {
            // AI Foundry Project Endpoint
            $endpoint_url = isset($resource_info['properties']['discoveryUrl']) 
                ? $resource_info['properties']['discoveryUrl'] 
                : '';
        } else {
            // Azure OpenAI Endpoint
            $endpoint_url = isset($resource_info['properties']['endpoint']) 
                ? $resource_info['properties']['endpoint'] 
                : '';
        }
        
        $api_key = isset($result['key1']) ? $result['key1'] : '';
        
        wp_send_json_success(array(
            'endpoint' => $endpoint_url,
            'api_key' => $api_key
        ));
    }
    
    /**
     * AJAX: OAuth ?�정 ?�??
     */
    public function ajax_save_oauth_settings() {
        check_ajax_referer('azure_oauth_save', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $client_id = isset($_POST['client_id']) ? sanitize_text_field($_POST['client_id']) : '';
        $client_secret = isset($_POST['client_secret']) ? sanitize_text_field($_POST['client_secret']) : '';
        $tenant_id = isset($_POST['tenant_id']) ? sanitize_text_field($_POST['tenant_id']) : '';
        $save_to_agent_mode = isset($_POST['save_to_agent_mode']) && $_POST['save_to_agent_mode'];
        
        if (empty($client_id) || empty($client_secret) || empty($tenant_id)) {
            wp_send_json_error(array('message' => '모든 필드를 입력하세요.'));
        }
        
        // OAuth 설정 저장
        update_option('azure_chatbot_oauth_client_id', $client_id);
        update_option('azure_chatbot_oauth_client_secret', $client_secret);
        update_option('azure_chatbot_oauth_tenant_id', $tenant_id);
        
        // Agent Mode 설정에도 자동으로 저장
        if ($save_to_agent_mode) {
            update_option('azure_client_id', $client_id);
            update_option('azure_client_secret', $client_secret);
            update_option('azure_tenant_id', $tenant_id);
        }
        
        wp_send_json_success(array(
            'message' => 'OAuth 설정이 저장되었습니다.' . ($save_to_agent_mode ? ' (Agent Mode 설정도 업데이트됨)' : '')
        ));
    }
    
    /**
     * AJAX: ?�션 초기??
     */
    public function ajax_clear_session() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $this->clear_session();
        
        wp_send_json_success(array('message' => '?�션??초기?�되?�습?�다.'));
    }
    
    /**
     * AJAX: OAuth ?�정 초기??
     */
    public function ajax_reset_config() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        delete_option('azure_chatbot_oauth_client_id');
        delete_option('azure_chatbot_oauth_client_secret');
        delete_option('azure_chatbot_oauth_tenant_id');
        
        $this->clear_session();
        
        wp_send_json_success(array('message' => 'OAuth ?�정??초기?�되?�습?�다.'));
    }
    
    /**
     * AJAX: 사용 가능한 지역 조회
     */
    public function ajax_get_available_locations() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $subscription = isset($_POST['subscription']) ? sanitize_text_field($_POST['subscription']) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'chat';
        $resource_type = isset($_POST['resource_type']) ? sanitize_text_field($_POST['resource_type']) : '';
        
        if (empty($subscription)) {
            wp_send_json_error(array('message' => '구독 ID가 필요합니다.'));
        }
        
        // 모든 모드에서 AI Foundry (Machine Learning Services) 사용
        $provider = 'Microsoft.MachineLearningServices';
        
        // 구독의 사용 가능한 위치 조회
        $endpoint = "https://management.azure.com/subscriptions/{$subscription}/providers/{$provider}?api-version=2021-04-01";
        $result = $this->call_azure_api($endpoint, null, 'GET', null, false);
        
        if (is_wp_error($result)) {
            // 실패 시 기본 지역 목록 반환 (AI Foundry 지원 지역)
            $default_locations = array(
                array('name' => 'koreacentral', 'displayName' => 'Korea Central'),
                array('name' => 'eastus', 'displayName' => 'East US'),
                array('name' => 'eastus2', 'displayName' => 'East US 2'),
                array('name' => 'westus', 'displayName' => 'West US'),
                array('name' => 'westus2', 'displayName' => 'West US 2'),
                array('name' => 'westeurope', 'displayName' => 'West Europe'),
                array('name' => 'northeurope', 'displayName' => 'North Europe'),
                array('name' => 'japaneast', 'displayName' => 'Japan East'),
                array('name' => 'southeastasia', 'displayName' => 'Southeast Asia')
            );
            wp_send_json_success(array('locations' => $default_locations));
            return;
        }
        
        // AI Foundry workspaces의 사용 가능한 위치 추출
        $locations = array();
        if (isset($result['resourceTypes']) && is_array($result['resourceTypes'])) {
            foreach ($result['resourceTypes'] as $type) {
                // AI Foundry는 'workspaces' 타입
                if (isset($type['resourceType']) && $type['resourceType'] === 'workspaces') {
                    if (isset($type['locations']) && is_array($type['locations'])) {
                        foreach ($type['locations'] as $location) {
                            $location_name = strtolower(str_replace(' ', '', $location));
                            $locations[] = array(
                                'name' => $location_name,
                                'displayName' => $location
                            );
                        }
                    }
                    break;
                }
            }
        }
        
        // 중복 제거 및 정렬
        $unique_locations = array();
        $seen = array();
        foreach ($locations as $loc) {
            if (!in_array($loc['name'], $seen)) {
                $unique_locations[] = $loc;
                $seen[] = $loc['name'];
            }
        }
        
        // displayName으로 정렬
        usort($unique_locations, function($a, $b) {
            return strcmp($a['displayName'], $b['displayName']);
        });
        
        wp_send_json_success(array('locations' => $unique_locations));
    }
    
    /**
     * AJAX: 사용 가능한 모델 조회 (AI Foundry에서 사용 가능한 모델)
     */
    public function ajax_get_available_models() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $subscription = isset($_POST['subscription']) ? sanitize_text_field($_POST['subscription']) : '';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        
        if (empty($subscription) || empty($location)) {
            wp_send_json_error(array('message' => '구독 ID와 위치가 필요합니다.'));
        }
        
        // AI Foundry에서 사용 가능한 기본 모델 목록
        // 실제로는 Azure AI Model Catalog API를 호출해야 하지만,
        // 현재는 일반적으로 사용 가능한 모델 목록을 반환
        $default_models = array(
            array('name' => 'gpt-4o', 'version' => '2024-08-06', 'displayName' => 'GPT-4o (2024-08-06)', 'publisher' => 'Azure OpenAI'),
            array('name' => 'gpt-4o-mini', 'version' => '2024-07-18', 'displayName' => 'GPT-4o mini (2024-07-18)', 'publisher' => 'Azure OpenAI'),
            array('name' => 'gpt-4', 'version' => '0613', 'displayName' => 'GPT-4 (0613)', 'publisher' => 'Azure OpenAI'),
            array('name' => 'gpt-4-turbo', 'version' => '2024-04-09', 'displayName' => 'GPT-4 Turbo (2024-04-09)', 'publisher' => 'Azure OpenAI'),
            array('name' => 'gpt-35-turbo', 'version' => '0613', 'displayName' => 'GPT-3.5 Turbo (0613)', 'publisher' => 'Azure OpenAI')
        );
        
        wp_send_json_success(array('models' => $default_models));
    }
    
    /**
     * AJAX: 리소스 그룹 생성
     */
    public function ajax_create_resource_group() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        $subscription = isset($_POST['subscription']) ? sanitize_text_field($_POST['subscription']) : '';
        
        if (empty($name) || empty($location) || empty($subscription)) {
            wp_send_json_error(array('message' => '모든 필드가 필요합니다.'));
        }
        
        // 세션에서 토큰 가져오기
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['azure_access_token'])) {
            error_log('[Azure OAuth] Resource Group 생성 실패: 토큰 없음');
            wp_send_json_error(array(
                'message' => '인증 토큰이 없습니다. 다시 로그인하세요.',
                'code' => 401
            ));
        }
        
        // 리소스 그룹 생성
        $endpoint = "https://management.azure.com/subscriptions/{$subscription}/resourcegroups/{$name}?api-version=2021-04-01";
        $body = array(
            'location' => $location,
            'tags' => array(
                'created-by' => 'azure-ai-chatbot-plugin',
                'created-at' => date('Y-m-d')
            )
        );
        
        error_log('[Azure OAuth] Resource Group 생성 요청: ' . $name . ' @ ' . $location);
        
        $result = $this->call_azure_api($endpoint, null, 'PUT', $body, false);
        
        if (is_wp_error($result)) {
            $error_message = $result->get_error_message();
            $error_code = $result->get_error_code();
            
            error_log('[Azure OAuth] Resource Group 생성 실패: ' . $error_message);
            
            // 토큰 만료 체크
            if ($error_code === 401 || strpos($error_message, 'Unauthorized') !== false) {
                unset($_SESSION['azure_access_token']);
                wp_send_json_error(array(
                    'message' => '인증이 만료되었습니다. 다시 로그인하세요.',
                    'code' => 401
                ));
            }
            
            wp_send_json_error(array(
                'message' => $error_message,
                'code' => $error_code
            ));
        }
        
        $resource_id = isset($result['id']) ? $result['id'] : '';
        
        error_log('[Azure OAuth] Resource Group 생성 성공: ' . $resource_id);
        
        wp_send_json_success(array(
            'message' => '리소스 그룹이 생성되었습니다.',
            'resource_id' => $resource_id,
            'name' => $name
        ));
    }
    
    /**
     * AJAX: AI 리소스 생성 (OpenAI 또는 AI Foundry)
     */
    public function ajax_create_ai_resource() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한???�습?�다.'));
        }
        
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $sku = isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : 'S0';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        $resource_group = isset($_POST['resource_group']) ? sanitize_text_field($_POST['resource_group']) : '';
        $subscription = isset($_POST['subscription']) ? sanitize_text_field($_POST['subscription']) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'chat';
        
        // Chat 모드 전용 파라미터
        $model = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : '';
        $deployment_name = isset($_POST['deployment_name']) ? sanitize_text_field($_POST['deployment_name']) : '';
        $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 10;
        
        if (empty($name) || empty($location) || empty($resource_group) || empty($subscription)) {
            wp_send_json_error(array('message' => '필수 필드가 누락되었습니다.'));
        }
        
        // 모든 모드에서 AI Foundry Project 생성
        $result = $this->create_ai_foundry_project($subscription, $resource_group, $name, $location);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        // Chat 모드일 경우 모델 배포 추가
        if ($mode === 'chat' && !empty($model) && !empty($deployment_name)) {
            sleep(15); // AI Foundry Project 생성 완료 대기
            
            $deploy_result = $this->deploy_model_to_ai_foundry(
                $subscription, 
                $resource_group, 
                $name, 
                $deployment_name, 
                $model, 
                $capacity
            );
            
            if (is_wp_error($deploy_result)) {
                wp_send_json_error(array(
                    'message' => 'AI Foundry Project는 생성되었으나 모델 배포에 실패했습니다: ' . $deploy_result->get_error_message()
                ));
            }
            
            // Chat 모드 설정 정보 구성
            $chat_endpoint = "https://{$name}.{$location}.inference.ml.azure.com";
            
            wp_send_json_success(array(
                'message' => 'AI Foundry Project와 모델이 성공적으로 생성되었습니다.',
                'resource_id' => $result['id'],
                'mode' => 'chat',
                'config' => array(
                    'endpoint' => $chat_endpoint,
                    'deployment_name' => $deployment_name,
                    'model' => $model,
                    'location' => $location,
                    'resource_name' => $name
                )
            ));
        } else {
            // Agent 모드 설정 정보 구성
            $agent_endpoint = "https://{$name}.{$location}.services.ai.azure.com/api/projects/{$name}";
            $client_id = get_option('azure_chatbot_oauth_client_id', '');
            $tenant_id = get_option('azure_chatbot_oauth_tenant_id', '');
            
            wp_send_json_success(array(
                'message' => 'AI Foundry Project가 성공적으로 생성되었습니다.',
                'resource_id' => $result['id'],
                'mode' => 'agent',
                'config' => array(
                    'endpoint' => $agent_endpoint,
                    'project_name' => $name,
                    'location' => $location,
                    'client_id' => $client_id,
                    'tenant_id' => $tenant_id
                )
            ));
        }
    }
    
    /**
     * AI Foundry에 모델 배포 (Chat 모드용)
     */
    private function deploy_model_to_ai_foundry($subscription, $resource_group, $project_name, $deployment_name, $model, $capacity) {
        // AI Foundry의 Online Endpoint를 통한 모델 배포
        $endpoint = "https://management.azure.com/subscriptions/{$subscription}/resourceGroups/{$resource_group}/providers/Microsoft.MachineLearningServices/workspaces/{$project_name}/onlineEndpoints/{$deployment_name}?api-version=2024-04-01";
        
        $body = array(
            'location' => '', // Project의 location을 상속
            'properties' => array(
                'authMode' => 'Key',
                'description' => 'Model deployment created by Azure AI Chatbot Plugin'
            ),
            'tags' => array(
                'created-by' => 'azure-ai-chatbot-plugin',
                'model' => $model
            )
        );
        
        $endpoint_result = $this->call_azure_api($endpoint, null, 'PUT', $body, false);
        
        if (is_wp_error($endpoint_result)) {
            return $endpoint_result;
        }
        
        // Endpoint 생성 대기
        sleep(10);
        
        // Deployment 생성
        $deployment_endpoint = "https://management.azure.com/subscriptions/{$subscription}/resourceGroups/{$resource_group}/providers/Microsoft.MachineLearningServices/workspaces/{$project_name}/onlineEndpoints/{$deployment_name}/deployments/{$deployment_name}-deployment?api-version=2024-04-01";
        
        $deployment_body = array(
            'properties' => array(
                'model' => $model,
                'scaleSettings' => array(
                    'scaleType' => 'Standard',
                    'capacity' => $capacity
                )
            )
        );
        
        return $this->call_azure_api($deployment_endpoint, null, 'PUT', $deployment_body, false);
    }
    
    /**
     * AI Foundry Project 생성
     */
    private function create_ai_foundry_project($subscription, $resource_group, $name, $location) {
        // AI Foundry Hub가 먼저 필요함
        $hub_name = $name . '-hub';
        $hub_endpoint = "https://management.azure.com/subscriptions/{$subscription}/resourceGroups/{$resource_group}/providers/Microsoft.MachineLearningServices/workspaces/{$hub_name}?api-version=2024-04-01";
        
        $hub_body = array(
            'location' => $location,
            'kind' => 'hub',
            'properties' => array(
                'friendlyName' => $hub_name,
                'description' => 'AI Foundry Hub created by Azure AI Chatbot Plugin'
            ),
            'tags' => array(
                'created-by' => 'azure-ai-chatbot-plugin',
                'created-at' => date('Y-m-d')
            )
        );
        
        $hub_result = $this->call_azure_api($hub_endpoint, null, 'PUT', $hub_body, false);
        
        if (is_wp_error($hub_result)) {
            return $hub_result;
        }
        
        // Hub 생성 완료 대기
        sleep(30);
        
        // AI Foundry Project 생성
        $project_endpoint = "https://management.azure.com/subscriptions/{$subscription}/resourceGroups/{$resource_group}/providers/Microsoft.MachineLearningServices/workspaces/{$name}?api-version=2024-04-01";
        
        $project_body = array(
            'location' => $location,
            'kind' => 'project',
            'properties' => array(
                'friendlyName' => $name,
                'description' => 'AI Foundry Project created by Azure AI Chatbot Plugin',
                'hubResourceId' => $hub_result['id']
            ),
            'tags' => array(
                'created-by' => 'azure-ai-chatbot-plugin',
                'created-at' => date('Y-m-d')
            )
        );
        
        return $this->call_azure_api($project_endpoint, null, 'PUT', $project_body, false);
    }
    
    /**
     * AJAX: 최종 설정 저장 (자동 설정 완료 후)
     */
    public function ajax_save_final_config() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : '';
        $config = isset($_POST['config']) ? $_POST['config'] : array();
        
        if (empty($mode) || empty($config)) {
            wp_send_json_error(array('message' => '필수 파라미터가 누락되었습니다.'));
        }
        
        // 현재 설정 가져오기
        $settings = get_option('azure_chatbot_settings', array());
        
        // 모드 설정
        $settings['mode'] = $mode;
        
        // Chat 모드 설정 저장
        if ($mode === 'chat' && isset($config['endpoint'])) {
            $settings['provider'] = 'azure-openai';
            $settings['chat_endpoint'] = sanitize_text_field($config['endpoint']);
            $settings['deployment_name'] = isset($config['deployment_name']) ? sanitize_text_field($config['deployment_name']) : '';
            
            // API Key는 나중에 수동으로 입력하도록 메시지 표시
            // (보안상 Azure API에서 자동으로 가져올 수 없음)
        }
        
        // Agent 모드 설정 저장
        if ($mode === 'agent' && isset($config['endpoint'])) {
            $settings['agent_endpoint'] = sanitize_text_field($config['endpoint']);
            $settings['agent_id'] = isset($config['agent_id']) ? sanitize_text_field($config['agent_id']) : '';
            
            // Client ID, Secret, Tenant ID는 OAuth 설정에서 가져오기
            $client_id = isset($config['client_id']) ? sanitize_text_field($config['client_id']) : get_option('azure_chatbot_oauth_client_id', '');
            $tenant_id = isset($config['tenant_id']) ? sanitize_text_field($config['tenant_id']) : get_option('azure_chatbot_oauth_tenant_id', '');
            
            $settings['client_id'] = $client_id;
            $settings['tenant_id'] = $tenant_id;
            
            // Client Secret은 암호화하여 저장
            $client_secret = get_option('azure_chatbot_oauth_client_secret', '');
            if (!empty($client_secret)) {
                $plugin = Azure_AI_Chatbot::get_instance();
                $settings['client_secret_encrypted'] = $plugin->encrypt($client_secret);
            }
        }
        
        // 설정 저장
        update_option('azure_chatbot_settings', $settings);
        
        $message = $mode === 'chat' 
            ? '설정이 저장되었습니다! API Key는 수동으로 입력해주세요.' 
            : '설정이 저장되었습니다!';
        
        wp_send_json_success(array(
            'message' => $message,
            'settings' => $settings
        ));
    }
    
    /**
     * AJAX: AI Foundry Project의 배포 목록 조회
     */
    public function ajax_get_deployments() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $resource_id = isset($_POST['resource_id']) ? sanitize_text_field($_POST['resource_id']) : '';
        $subscription_id = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : '';
        $resource_group = isset($_POST['resource_group']) ? sanitize_text_field($_POST['resource_group']) : '';
        
        if (empty($resource_id) || empty($subscription_id) || empty($resource_group)) {
            wp_send_json_error(array('message' => '필수 파라미터가 누락되었습니다.'));
        }
        
        $access_token = $this->get_access_token();
        if (!$access_token) {
            wp_send_json_error(array('message' => 'Azure 토큰을 가져올 수 없습니다.'));
        }
        
        // 리소스 타입 판별
        $is_cognitive_services = strpos($resource_id, '/Microsoft.CognitiveServices/accounts/') !== false;
        $is_ai_foundry = strpos($resource_id, '/Microsoft.MachineLearningServices/workspaces/') !== false;
        
        $deployments = array();
        
        if ($is_cognitive_services) {
            // Cognitive Services: 배포 목록 API
            $api_url = "https://management.azure.com" . $resource_id . "/deployments?api-version=2023-05-01";
            
            $response = wp_remote_get($api_url, array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                wp_send_json_error(array('message' => '배포 목록 조회 실패: ' . $response->get_error_message()));
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            $status_code = wp_remote_retrieve_response_code($response);
            
            if ($status_code !== 200) {
                error_log('[Azure OAuth] Cognitive Services 배포 조회 실패 - HTTP ' . $status_code);
                error_log('[Azure OAuth] Response: ' . $body);
                wp_send_json_error(array('message' => '배포 목록 조회 실패: HTTP ' . $status_code . ' - ' . substr($body, 0, 200)));
            }
            
            if (isset($data['value']) && is_array($data['value'])) {
                foreach ($data['value'] as $deployment) {
                    if (isset($deployment['name']) && isset($deployment['properties'])) {
                        $deployments[] = array(
                            'name' => $deployment['name'],
                            'model' => isset($deployment['properties']['model']['name']) ? $deployment['properties']['model']['name'] : '',
                            'version' => isset($deployment['properties']['model']['version']) ? $deployment['properties']['model']['version'] : '',
                            'status' => isset($deployment['properties']['provisioningState']) ? $deployment['properties']['provisioningState'] : '',
                            'capacity' => isset($deployment['sku']['capacity']) ? $deployment['sku']['capacity'] : ''
                        );
                    }
                }
            }
            
        } else if ($is_ai_foundry) {
            // AI Foundry Project: 배포 목록 API
            $api_url = "https://management.azure.com" . $resource_id . "/deployments?api-version=2024-05-01-preview";
            
            $response = wp_remote_get($api_url, array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                wp_send_json_error(array('message' => '배포 목록 조회 실패: ' . $response->get_error_message()));
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            $status_code = wp_remote_retrieve_response_code($response);
            
            if ($status_code !== 200) {
                error_log('[Azure OAuth] AI Foundry 배포 조회 실패 - HTTP ' . $status_code);
                error_log('[Azure OAuth] Response: ' . $body);
                wp_send_json_error(array('message' => '배포 목록 조회 실패: HTTP ' . $status_code . ' - ' . substr($body, 0, 200)));
            }
            
            if (isset($data['value']) && is_array($data['value'])) {
                foreach ($data['value'] as $deployment) {
                    if (isset($deployment['name']) && isset($deployment['properties'])) {
                        $deployments[] = array(
                            'name' => $deployment['name'],
                            'model' => isset($deployment['properties']['model']['name']) ? $deployment['properties']['model']['name'] : '',
                            'version' => isset($deployment['properties']['model']['version']) ? $deployment['properties']['model']['version'] : '',
                            'status' => isset($deployment['properties']['provisioningState']) ? $deployment['properties']['provisioningState'] : ''
                        );
                    }
                }
            }
        } else {
            wp_send_json_error(array('message' => '지원하지 않는 리소스 타입입니다.'));
        }
        
        wp_send_json_success(array(
            'deployments' => $deployments,
            'count' => count($deployments)
        ));
    }
    
    /**
     * AJAX: 기존 리소스 설정 저장 (API Key 포함)
     */
    public function ajax_save_existing_config() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $settings_data = isset($_POST['settings']) ? $_POST['settings'] : array();
        
        if (empty($settings_data)) {
            wp_send_json_error(array('message' => '설정 데이터가 누락되었습니다.'));
        }
        
        // 현재 설정 가져오기
        $settings = get_option('azure_chatbot_settings', array());
        
        // 전달받은 설정 병합
        if (isset($settings_data['mode'])) {
            $settings['mode'] = sanitize_text_field($settings_data['mode']);
        }
        if (isset($settings_data['provider'])) {
            $settings['provider'] = sanitize_text_field($settings_data['provider']);
        }
        if (isset($settings_data['chat_endpoint'])) {
            $settings['chat_endpoint'] = sanitize_text_field($settings_data['chat_endpoint']);
        }
        if (isset($settings_data['deployment_name'])) {
            $settings['deployment_name'] = sanitize_text_field($settings_data['deployment_name']);
        }
        if (isset($settings_data['api_key'])) {
            // API Key는 암호화하여 저장
            $plugin = Azure_AI_Chatbot::get_instance();
            $settings['api_key_encrypted'] = $plugin->encrypt($settings_data['api_key']);
        }
        
        // 설정 저장
        update_option('azure_chatbot_settings', $settings);
        
        wp_send_json_success(array(
            'message' => '설정이 저장되었습니다! (API Key 포함)',
            'settings' => $settings
        ));
    }
}

// OAuth ?�들??초기??
function azure_chatbot_oauth_init() {
    return new Azure_Chatbot_OAuth();
}
add_action('plugins_loaded', 'azure_chatbot_oauth_init');
