<?php
/**
 * Azure OAuth Authentication Handler
 * 
 * WordPress 플러그인에서 Azure OAuth 2.0 인증을 처리하는 클래스
 */

if (!defined('ABSPATH')) {
    exit;
}

class Azure_Chatbot_OAuth {
    
    /**
     * OAuth 설정
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
     * 생성자
     */
    public function __construct() {
        $this->load_config();
        $this->init_hooks();
    }
    
    /**
     * OAuth 설정 로드
     */
    private function load_config() {
        // wp-config.php에서 상수로 정의된 값 우선 사용
        $this->client_id = defined('AZURE_CHATBOT_OAUTH_CLIENT_ID') 
            ? AZURE_CHATBOT_OAUTH_CLIENT_ID 
            : get_option('azure_chatbot_oauth_client_id', '');
            
        $this->client_secret = defined('AZURE_CHATBOT_OAUTH_CLIENT_SECRET') 
            ? AZURE_CHATBOT_OAUTH_CLIENT_SECRET 
            : get_option('azure_chatbot_oauth_client_secret', '');
            
        $this->tenant_id = defined('AZURE_CHATBOT_OAUTH_TENANT_ID') 
            ? AZURE_CHATBOT_OAUTH_TENANT_ID 
            : get_option('azure_chatbot_oauth_tenant_id', '');
        
        // Redirect URI: WordPress 관리자 설정 페이지
        $this->redirect_uri = admin_url('admin.php?page=azure-chatbot-settings&azure_callback=1');
        
        // Azure OAuth Endpoints
        $this->authority_url = "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/v2.0/authorize";
        $this->token_url = "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/v2.0/token";
    }
    
    /**
     * Hooks 초기화
     */
    private function init_hooks() {
        // OAuth 콜백 처리
        add_action('admin_init', array($this, 'handle_oauth_callback'));
        
        // AJAX 핸들러
        add_action('wp_ajax_azure_oauth_get_subscriptions', array($this, 'ajax_get_subscriptions'));
        add_action('wp_ajax_azure_oauth_get_resource_groups', array($this, 'ajax_get_resource_groups'));
        add_action('wp_ajax_azure_oauth_get_resources', array($this, 'ajax_get_resources'));
        add_action('wp_ajax_azure_oauth_get_agents', array($this, 'ajax_get_agents'));
        add_action('wp_ajax_azure_oauth_get_keys', array($this, 'ajax_get_keys'));
        add_action('wp_ajax_save_oauth_settings', array($this, 'ajax_save_oauth_settings'));
        add_action('wp_ajax_azure_oauth_clear_session', array($this, 'ajax_clear_session'));
        add_action('wp_ajax_azure_oauth_reset_config', array($this, 'ajax_reset_config'));
    }
    
    /**
     * OAuth 인증 URL 생성
     */
    public function get_authorization_url() {
        // State 생성 (CSRF 방지)
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
     * OAuth 설정이 완료되었는지 확인
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
        // OAuth 콜백인지 확인
        if (!isset($_GET['page']) || $_GET['page'] !== 'azure-chatbot-settings') {
            return;
        }
        
        if (!isset($_GET['azure_callback'])) {
            return;
        }
        
        // 관리자 권한 확인
        if (!current_user_can('manage_options')) {
            wp_die(__('권한이 없습니다.', 'azure-ai-chatbot'));
        }
        
        // 에러 처리
        if (isset($_GET['error'])) {
            $error_description = isset($_GET['error_description']) ? $_GET['error_description'] : $_GET['error'];
            set_transient('azure_oauth_error', $error_description, 60);
            wp_redirect(admin_url('admin.php?page=azure-chatbot-settings&oauth_error=1'));
            exit;
        }
        
        // Authorization Code 확인
        if (!isset($_GET['code'])) {
            return;
        }
        
        // State 검증 (CSRF 방지)
        $state = isset($_GET['state']) ? $_GET['state'] : '';
        $saved_state = get_transient('azure_oauth_state');
        
        if (empty($state) || $state !== $saved_state) {
            set_transient('azure_oauth_error', 'Invalid state parameter', 60);
            wp_redirect(admin_url('admin.php?page=azure-chatbot-settings&oauth_error=1'));
            exit;
        }
        
        // State 삭제
        delete_transient('azure_oauth_state');
        
        // Authorization Code로 Access Token 요청
        $code = sanitize_text_field($_GET['code']);
        $token_data = $this->request_access_token($code);
        
        if (is_wp_error($token_data)) {
            set_transient('azure_oauth_error', $token_data->get_error_message(), 60);
            wp_redirect(admin_url('admin.php?page=azure-chatbot-settings&oauth_error=1'));
            exit;
        }
        
        // Access Token을 세션에 저장 (보안상 DB에 저장하지 않음)
        $this->save_token_to_session($token_data);
        
        // 성공 리다이렉트
        wp_redirect(admin_url('admin.php?page=azure-chatbot-settings&oauth_success=1'));
        exit;
    }
    
    /**
     * Authorization Code를 Access Token으로 교환
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
     * Token을 세션에 저장
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
     * 세션에서 Access Token 가져오기
     */
    private function get_access_token() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['azure_access_token'])) {
            return new WP_Error('no_token', '인증이 필요합니다. Azure 자동 설정 버튼을 클릭하세요.');
        }
        
        // 토큰 만료 확인
        if (isset($_SESSION['azure_token_expires']) && $_SESSION['azure_token_expires'] < time()) {
            // Refresh Token이 있으면 갱신 시도
            if (isset($_SESSION['azure_refresh_token'])) {
                $token_data = $this->refresh_access_token($_SESSION['azure_refresh_token']);
                if (!is_wp_error($token_data)) {
                    $this->save_token_to_session($token_data);
                    return $token_data['access_token'];
                }
            }
            
            // 갱신 실패 시 에러 반환
            $this->clear_session();
            return new WP_Error('token_expired', '세션이 만료되었습니다. 다시 인증하세요.');
        }
        
        return $_SESSION['azure_access_token'];
    }
    
    /**
     * Refresh Token으로 Access Token 갱신
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
     * 세션 정리
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
     * Azure Management API 호출
     */
    private function call_azure_api($endpoint, $api_version = '2021-04-01') {
        $access_token = $this->get_access_token();
        
        if (is_wp_error($access_token)) {
            return $access_token;
        }
        
        $url = "https://management.azure.com{$endpoint}?api-version={$api_version}";
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
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
                'api_error',
                isset($data['error']['message']) ? $data['error']['message'] : 'API 호출 실패'
            );
        }
        
        return $data;
    }
    
    /**
     * AJAX: Subscription 목록 조회
     */
    public function ajax_get_subscriptions() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
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
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : '';
        
        if (empty($subscription_id)) {
            wp_send_json_error(array('message' => 'Subscription ID가 필요합니다.'));
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
     * AJAX: AI Foundry / OpenAI 리소스 목록 조회
     */
    public function ajax_get_resources() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : '';
        $resource_group = isset($_POST['resource_group']) ? sanitize_text_field($_POST['resource_group']) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'chat';
        
        if (empty($subscription_id) || empty($resource_group)) {
            wp_send_json_error(array('message' => 'Subscription ID와 Resource Group이 필요합니다.'));
        }
        
        // 모드에 따라 다른 리소스 타입 조회
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
     * AJAX: Agent ID 목록 조회 (Agent 모드 전용)
     */
    public function ajax_get_agents() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $resource_id = isset($_POST['resource_id']) ? sanitize_text_field($_POST['resource_id']) : '';
        
        if (empty($resource_id)) {
            wp_send_json_error(array('message' => 'Resource ID가 필요합니다.'));
        }
        
        // AI Foundry Project의 Endpoint 조회
        $resource_info = $this->call_azure_api($resource_id, '2023-05-01');
        
        if (is_wp_error($resource_info)) {
            wp_send_json_error(array('message' => $resource_info->get_error_message()));
        }
        
        // Discovery URL (Project Endpoint) 추출
        $discovery_url = isset($resource_info['properties']['discoveryUrl']) 
            ? $resource_info['properties']['discoveryUrl'] 
            : '';
            
        if (empty($discovery_url)) {
            wp_send_json_error(array('message' => 'Project Endpoint를 찾을 수 없습니다.'));
        }
        
        // Keys 조회 (Subscription Key 필요)
        $keys_endpoint = "{$resource_id}/listKeys";
        $keys_result = $this->call_azure_api($keys_endpoint, '2023-05-01');
        
        if (is_wp_error($keys_result)) {
            wp_send_json_error(array('message' => $keys_result->get_error_message()));
        }
        
        $subscription_key = isset($keys_result['key1']) ? $keys_result['key1'] : '';
        
        if (empty($subscription_key)) {
            wp_send_json_error(array('message' => 'Subscription Key를 찾을 수 없습니다.'));
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
            wp_send_json_error(array('message' => 'Agent 목록 조회 실패: ' . $response->get_error_message()));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            wp_send_json_error(array('message' => 'Agent 목록을 찾을 수 없습니다. Project에 Agent가 생성되어 있는지 확인하세요.'));
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
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $resource_id = isset($_POST['resource_id']) ? sanitize_text_field($_POST['resource_id']) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'chat';
        
        if (empty($resource_id)) {
            wp_send_json_error(array('message' => 'Resource ID가 필요합니다.'));
        }
        
        // Keys 조회
        $endpoint = "{$resource_id}/listKeys";
        $result = $this->call_azure_api($endpoint, '2023-05-01');
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        // Endpoint 정보 조회
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
     * AJAX: OAuth 설정 저장
     */
    public function ajax_save_oauth_settings() {
        check_ajax_referer('azure_oauth_save', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $client_id = isset($_POST['client_id']) ? sanitize_text_field($_POST['client_id']) : '';
        $client_secret = isset($_POST['client_secret']) ? sanitize_text_field($_POST['client_secret']) : '';
        $tenant_id = isset($_POST['tenant_id']) ? sanitize_text_field($_POST['tenant_id']) : '';
        
        if (empty($client_id) || empty($client_secret) || empty($tenant_id)) {
            wp_send_json_error(array('message' => '모든 필드를 입력하세요.'));
        }
        
        update_option('azure_chatbot_oauth_client_id', $client_id);
        update_option('azure_chatbot_oauth_client_secret', $client_secret);
        update_option('azure_chatbot_oauth_tenant_id', $tenant_id);
        
        wp_send_json_success(array('message' => 'OAuth 설정이 저장되었습니다.'));
    }
    
    /**
     * AJAX: 세션 초기화
     */
    public function ajax_clear_session() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        $this->clear_session();
        
        wp_send_json_success(array('message' => '세션이 초기화되었습니다.'));
    }
    
    /**
     * AJAX: OAuth 설정 초기화
     */
    public function ajax_reset_config() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
        }
        
        delete_option('azure_chatbot_oauth_client_id');
        delete_option('azure_chatbot_oauth_client_secret');
        delete_option('azure_chatbot_oauth_tenant_id');
        
        $this->clear_session();
        
        wp_send_json_success(array('message' => 'OAuth 설정이 초기화되었습니다.'));
    }
}

// OAuth 핸들러 초기화
function azure_chatbot_oauth_init() {
    return new Azure_Chatbot_OAuth();
}
add_action('plugins_loaded', 'azure_chatbot_oauth_init');
