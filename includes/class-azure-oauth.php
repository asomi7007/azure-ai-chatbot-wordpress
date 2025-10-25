<?php
/**
 * Azure OAuth Authentication Handler
 * 
 * WordPress ?ŒëŸ¬ê·¸ì¸?ì„œ Azure OAuth 2.0 ?¸ì¦??ì²˜ë¦¬?˜ëŠ” ?´ë˜??
 */

if (!defined('ABSPATH')) {
    exit;
}

class Azure_Chatbot_OAuth {
    
    /**
     * OAuth ?¤ì •
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
     * ?ì„±??
     */
    public function __construct() {
        $this->load_config();
        $this->init_hooks();
    }
    
    /**
     * OAuth ?¤ì • ë¡œë“œ
     */
    private function load_config() {
        // wp-config.php?ì„œ ?ìˆ˜ë¡??•ì˜??ê°??°ì„  ?¬ìš©
        $this->client_id = defined('AZURE_CHATBOT_OAUTH_CLIENT_ID') 
            ? AZURE_CHATBOT_OAUTH_CLIENT_ID 
            : get_option('azure_chatbot_oauth_client_id', '');
            
        $this->client_secret = defined('AZURE_CHATBOT_OAUTH_CLIENT_SECRET') 
            ? AZURE_CHATBOT_OAUTH_CLIENT_SECRET 
            : get_option('azure_chatbot_oauth_client_secret', '');
            
        $this->tenant_id = defined('AZURE_CHATBOT_OAUTH_TENANT_ID') 
            ? AZURE_CHATBOT_OAUTH_TENANT_ID 
            : get_option('azure_chatbot_oauth_tenant_id', '');
        
        // Redirect URI: WordPress ê´€ë¦¬ì ?¤ì • ?˜ì´ì§€
        $this->redirect_uri = admin_url('admin.php?page=azure-ai-chatbot&azure_callback=1');
        
        // Azure OAuth Endpoints
        $this->authority_url = "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/v2.0/authorize";
        $this->token_url = "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/v2.0/token";
    }
    
    /**
     * Hooks ì´ˆê¸°??
     */
    private function init_hooks() {
        // OAuth ì½œë°± ì²˜ë¦¬
        add_action('admin_init', array($this, 'handle_oauth_callback'));
        
        // AJAX ?¸ë“¤??
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
     * OAuth ?¸ì¦ URL ?ì„±
     */
    public function get_authorization_url() {
        // State ?ì„± (CSRF ë°©ì?)
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
     * OAuth ?¤ì •???„ë£Œ?˜ì—ˆ?”ì? ?•ì¸
     */
    public function is_configured() {
        return !empty($this->client_id) && 
               !empty($this->client_secret) && 
               !empty($this->tenant_id);
    }
    
    /**
     * OAuth ì½œë°± ì²˜ë¦¬
     */
    public function handle_oauth_callback() {
        // OAuth ì½œë°±?¸ì? ?•ì¸
        if (!isset($_GET['page']) || $_GET['page'] !== 'azure-ai-chatbot') {
            return;
        }
        
        if (!isset($_GET['azure_callback'])) {
            return;
        }
        
        // ê´€ë¦¬ì ê¶Œí•œ ?•ì¸
        if (!current_user_can('manage_options')) {
            wp_die(__('ê¶Œí•œ???†ìŠµ?ˆë‹¤.', 'azure-ai-chatbot'));
        }
        
        // ?ëŸ¬ ì²˜ë¦¬
        if (isset($_GET['error'])) {
            $error_description = isset($_GET['error_description']) ? $_GET['error_description'] : $_GET['error'];
            set_transient('azure_oauth_error', $error_description, 60);
            wp_redirect(admin_url('admin.php?page=azure-ai-chatbot&oauth_error=1'));
            exit;
        }
        
        // Authorization Code ?•ì¸
        if (!isset($_GET['code'])) {
            return;
        }
        
        // State ê²€ì¦?(CSRF ë°©ì?)
        $state = isset($_GET['state']) ? $_GET['state'] : '';
        $saved_state = get_transient('azure_oauth_state');
        
        if (empty($state) || $state !== $saved_state) {
            set_transient('azure_oauth_error', 'Invalid state parameter', 60);
            wp_redirect(admin_url('admin.php?page=azure-ai-chatbot&oauth_error=1'));
            exit;
        }
        
        // State ?? œ
        delete_transient('azure_oauth_state');
        
        // Authorization Codeë¡?Access Token ?”ì²­
        $code = sanitize_text_field($_GET['code']);
        $token_data = $this->request_access_token($code);
        
        if (is_wp_error($token_data)) {
            set_transient('azure_oauth_error', $token_data->get_error_message(), 60);
            wp_redirect(admin_url('admin.php?page=azure-ai-chatbot&oauth_error=1'));
            exit;
        }
        
        // Access Token???¸ì…˜???€??(ë³´ì•ˆ??DB???€?¥í•˜ì§€ ?ŠìŒ)
        $this->save_token_to_session($token_data);
        
        // ?±ê³µ ë¦¬ë‹¤?´ë ‰??
        wp_redirect(admin_url('admin.php?page=azure-ai-chatbot&oauth_success=1'));
        exit;
    }
    
    /**
     * Authorization Codeë¥?Access Token?¼ë¡œ êµí™˜
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
     * Token???¸ì…˜???€??
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
     * ?¸ì…˜?ì„œ Access Token ê°€?¸ì˜¤ê¸?
     */
    private function get_access_token() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['azure_access_token'])) {
            return new WP_Error('no_token', '?¸ì¦???„ìš”?©ë‹ˆ?? Azure ?ë™ ?¤ì • ë²„íŠ¼???´ë¦­?˜ì„¸??');
        }
        
        // ? í° ë§Œë£Œ ?•ì¸
        if (isset($_SESSION['azure_token_expires']) && $_SESSION['azure_token_expires'] < time()) {
            // Refresh Token???ˆìœ¼ë©?ê°±ì‹  ?œë„
            if (isset($_SESSION['azure_refresh_token'])) {
                $token_data = $this->refresh_access_token($_SESSION['azure_refresh_token']);
                if (!is_wp_error($token_data)) {
                    $this->save_token_to_session($token_data);
                    return $token_data['access_token'];
                }
            }
            
            // ê°±ì‹  ?¤íŒ¨ ???ëŸ¬ ë°˜í™˜
            $this->clear_session();
            return new WP_Error('token_expired', '?¸ì…˜??ë§Œë£Œ?˜ì—ˆ?µë‹ˆ?? ?¤ì‹œ ?¸ì¦?˜ì„¸??');
        }
        
        return $_SESSION['azure_access_token'];
    }
    
    /**
     * Refresh Token?¼ë¡œ Access Token ê°±ì‹ 
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
     * ?¸ì…˜ ?•ë¦¬
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
     * Azure Management API ?¸ì¶œ
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
                isset($data['error']['message']) ? $data['error']['message'] : 'API ?¸ì¶œ ?¤íŒ¨'
            );
        }
        
        return $data;
    }
    
    /**
     * AJAX: Subscription ëª©ë¡ ì¡°íšŒ
     */
    public function ajax_get_subscriptions() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'ê¶Œí•œ???†ìŠµ?ˆë‹¤.'));
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
     * AJAX: Resource Group ëª©ë¡ ì¡°íšŒ
     */
    public function ajax_get_resource_groups() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'ê¶Œí•œ???†ìŠµ?ˆë‹¤.'));
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : '';
        
        if (empty($subscription_id)) {
            wp_send_json_error(array('message' => 'Subscription IDê°€ ?„ìš”?©ë‹ˆ??'));
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
     * AJAX: AI Foundry / OpenAI ë¦¬ì†Œ??ëª©ë¡ ì¡°íšŒ
     */
    public function ajax_get_resources() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'ê¶Œí•œ???†ìŠµ?ˆë‹¤.'));
        }
        
        $subscription_id = isset($_POST['subscription_id']) ? sanitize_text_field($_POST['subscription_id']) : '';
        $resource_group = isset($_POST['resource_group']) ? sanitize_text_field($_POST['resource_group']) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'chat';
        
        if (empty($subscription_id) || empty($resource_group)) {
            wp_send_json_error(array('message' => 'Subscription ID?€ Resource Group???„ìš”?©ë‹ˆ??'));
        }
        
        // ëª¨ë“œ???°ë¼ ?¤ë¥¸ ë¦¬ì†Œ???€??ì¡°íšŒ
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
     * AJAX: Agent ID ëª©ë¡ ì¡°íšŒ (Agent ëª¨ë“œ ?„ìš©)
     */
    public function ajax_get_agents() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'ê¶Œí•œ???†ìŠµ?ˆë‹¤.'));
        }
        
        $resource_id = isset($_POST['resource_id']) ? sanitize_text_field($_POST['resource_id']) : '';
        
        if (empty($resource_id)) {
            wp_send_json_error(array('message' => 'Resource IDê°€ ?„ìš”?©ë‹ˆ??'));
        }
        
        // AI Foundry Project??Endpoint ì¡°íšŒ
        $resource_info = $this->call_azure_api($resource_id, '2023-05-01');
        
        if (is_wp_error($resource_info)) {
            wp_send_json_error(array('message' => $resource_info->get_error_message()));
        }
        
        // Discovery URL (Project Endpoint) ì¶”ì¶œ
        $discovery_url = isset($resource_info['properties']['discoveryUrl']) 
            ? $resource_info['properties']['discoveryUrl'] 
            : '';
            
        if (empty($discovery_url)) {
            wp_send_json_error(array('message' => 'Project Endpointë¥?ì°¾ì„ ???†ìŠµ?ˆë‹¤.'));
        }
        
        // Keys ì¡°íšŒ (Subscription Key ?„ìš”)
        $keys_endpoint = "{$resource_id}/listKeys";
        $keys_result = $this->call_azure_api($keys_endpoint, '2023-05-01');
        
        if (is_wp_error($keys_result)) {
            wp_send_json_error(array('message' => $keys_result->get_error_message()));
        }
        
        $subscription_key = isset($keys_result['key1']) ? $keys_result['key1'] : '';
        
        if (empty($subscription_key)) {
            wp_send_json_error(array('message' => 'Subscription Keyë¥?ì°¾ì„ ???†ìŠµ?ˆë‹¤.'));
        }
        
        // Agent ëª©ë¡ ì¡°íšŒ (AI Foundry Agents API)
        $agents_url = rtrim($discovery_url, '/') . '/agents';
        
        $response = wp_remote_get($agents_url, array(
            'headers' => array(
                'api-key' => $subscription_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Agent ëª©ë¡ ì¡°íšŒ ?¤íŒ¨: ' . $response->get_error_message()));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            wp_send_json_error(array('message' => 'Agent ëª©ë¡??ì°¾ì„ ???†ìŠµ?ˆë‹¤. Project??Agentê°€ ?ì„±?˜ì–´ ?ˆëŠ”ì§€ ?•ì¸?˜ì„¸??'));
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
     * AJAX: API Key ì¡°íšŒ
     */
    public function ajax_get_keys() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'ê¶Œí•œ???†ìŠµ?ˆë‹¤.'));
        }
        
        $resource_id = isset($_POST['resource_id']) ? sanitize_text_field($_POST['resource_id']) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'chat';
        
        if (empty($resource_id)) {
            wp_send_json_error(array('message' => 'Resource IDê°€ ?„ìš”?©ë‹ˆ??'));
        }
        
        // Keys ì¡°íšŒ
        $endpoint = "{$resource_id}/listKeys";
        $result = $this->call_azure_api($endpoint, '2023-05-01');
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        // Endpoint ?•ë³´ ì¡°íšŒ
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
     * AJAX: OAuth ?¤ì • ?€??
     */
    public function ajax_save_oauth_settings() {
        check_ajax_referer('azure_oauth_save', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'ê¶Œí•œ???†ìŠµ?ˆë‹¤.'));
        }
        
        $client_id = isset($_POST['client_id']) ? sanitize_text_field($_POST['client_id']) : '';
        $client_secret = isset($_POST['client_secret']) ? sanitize_text_field($_POST['client_secret']) : '';
        $tenant_id = isset($_POST['tenant_id']) ? sanitize_text_field($_POST['tenant_id']) : '';
        
        if (empty($client_id) || empty($client_secret) || empty($tenant_id)) {
            wp_send_json_error(array('message' => 'ëª¨ë“  ?„ë“œë¥??…ë ¥?˜ì„¸??'));
        }
        
        update_option('azure_chatbot_oauth_client_id', $client_id);
        update_option('azure_chatbot_oauth_client_secret', $client_secret);
        update_option('azure_chatbot_oauth_tenant_id', $tenant_id);
        
        wp_send_json_success(array('message' => 'OAuth ?¤ì •???€?¥ë˜?ˆìŠµ?ˆë‹¤.'));
    }
    
    /**
     * AJAX: ?¸ì…˜ ì´ˆê¸°??
     */
    public function ajax_clear_session() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'ê¶Œí•œ???†ìŠµ?ˆë‹¤.'));
        }
        
        $this->clear_session();
        
        wp_send_json_success(array('message' => '?¸ì…˜??ì´ˆê¸°?”ë˜?ˆìŠµ?ˆë‹¤.'));
    }
    
    /**
     * AJAX: OAuth ?¤ì • ì´ˆê¸°??
     */
    public function ajax_reset_config() {
        check_ajax_referer('azure_oauth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'ê¶Œí•œ???†ìŠµ?ˆë‹¤.'));
        }
        
        delete_option('azure_chatbot_oauth_client_id');
        delete_option('azure_chatbot_oauth_client_secret');
        delete_option('azure_chatbot_oauth_tenant_id');
        
        $this->clear_session();
        
        wp_send_json_success(array('message' => 'OAuth ?¤ì •??ì´ˆê¸°?”ë˜?ˆìŠµ?ˆë‹¤.'));
    }
}

// OAuth ?¸ë“¤??ì´ˆê¸°??
function azure_chatbot_oauth_init() {
    return new Azure_Chatbot_OAuth();
}
add_action('plugins_loaded', 'azure_chatbot_oauth_init');
