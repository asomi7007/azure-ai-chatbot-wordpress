<?php
/**
 * Azure OAuth Auto Setup UI Template
 */

if (!defined('ABSPATH')) exit;

$oauth = new Azure_Chatbot_OAuth();
$is_configured = $oauth->is_configured();

// 세션에 토큰이 있는지 확인
if (!session_id()) {
    session_start();
}
$has_token = isset($_SESSION['azure_access_token']) && !empty($_SESSION['azure_access_token']);

// OAuth 성공/실패 메시지 표시
if (isset($_GET['oauth_success'])) {
    if ($has_token) {
        echo '<div class="notice notice-success is-dismissible"><p>';
        esc_html_e('Azure 인증에 성공했습니다! 자동으로 리소스를 생성합니다...', 'azure-ai-chatbot');
        echo '</p></div>';
        
        // Operation Mode 확인
        $operation_mode = get_option('azure_ai_chatbot_operation_mode', 'chat');
        
        // 자동으로 리소스 생성 프로세스 시작
        echo '<script>
        jQuery(document).ready(function($) {
            // 성공 메시지 표시 후 자동으로 리소스 생성 시작
            setTimeout(function() {
                console.log("[Auto Setup] OAuth 인증 완료, 자동 설정 시작");
                console.log("[Auto Setup] Operation Mode: ' . esc_js($operation_mode) . '");
                
                // 리소스 선택 섹션으로 스크롤
                $("html, body").animate({
                    scrollTop: $(".oauth-step-2").offset().top - 100
                }, 500);
                
                // 1초 후 자동으로 Subscription 로드
                setTimeout(function() {
                    console.log("[Auto Setup] Subscription 로드 시작");
                    loadSubscriptions();
                }, 1000);
            }, 500);
        });
        </script>';
    } else {
        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo esc_html__('인증은 완료되었지만 세션 정보가 없습니다. 페이지를 새로고침 하거나 다시 인증하세요.', 'azure-ai-chatbot');
        echo ' <a href="' . esc_url(admin_url('admin.php?page=azure-ai-chatbot')) . '" class="button button-small">';
        echo esc_html__('새로고침', 'azure-ai-chatbot');
        echo '</a></p></div>';
    }
}

if (isset($_GET['oauth_error'])) {
    $error_msg = get_transient('azure_oauth_error');
    delete_transient('azure_oauth_error');
    echo '<div class="notice notice-error is-dismissible"><p>';
    echo esc_html__('인증 실패: ', 'azure-ai-chatbot') . esc_html($error_msg ?: '알 수 없는 오류');
    echo '</p></div>';
}
?>

<div class="postbox azure-oauth-section">
    <h2 class="hndle">
        <span class="dashicons dashicons-admin-network"></span>
        <?php esc_html_e('Azure 자동 설정 (OAuth)', 'azure-ai-chatbot'); ?>
    </h2>
    <div class="inside">
        <?php if (!$is_configured): ?>
            <div class="notice notice-warning inline">
                <p>
                    <strong><?php esc_html_e('자동 설정을 사용하려면 OAuth 설정이 필요합니다.', 'azure-ai-chatbot'); ?></strong><br>
                    <?php esc_html_e('Azure Portal에서 App Registration을 생성하거나 아래 자동 설정 스크립트를 사용하세요.', 'azure-ai-chatbot'); ?>
                </p>
            </div>
            
            <!-- App Registration 자동 설정 안내 -->
            <div class="oauth-setup-guide" style="background: #f0f6fc; border-left: 4px solid #0078d4; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0;">
                    <span class="dashicons dashicons-info"></span>
                    <?php esc_html_e('Azure App Registration 자동 설정', 'azure-ai-chatbot'); ?>
                </h3>
                
                <p><strong><?php esc_html_e('방법 1: Azure Cloud Shell 사용 (추천)', 'azure-ai-chatbot'); ?></strong></p>
                <ol>
                    <li>
                        <a href="https://shell.azure.com" target="_blank" class="button button-primary">
                            <span class="dashicons dashicons-cloud" style="margin-top: 3px;"></span>
                            <?php esc_html_e('Azure Cloud Shell 열기', 'azure-ai-chatbot'); ?>
                        </a>
                    </li>
                    <li>
                        <?php esc_html_e('아래 명령어를 복사해서 Cloud Shell에 붙여넣으세요:', 'azure-ai-chatbot'); ?>
                        <?php
                        $site_url = get_site_url();
                        $bash_command = "bash <(curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/scripts/setup-oauth-app.sh) " . esc_url($site_url);
                        $pwsh_command = "curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/scripts/setup-oauth-app.sh | bash -s " . esc_url($site_url);
                        ?>
                        
                        <p style="margin: 10px 0 5px 0;"><strong>Bash 모드 (권장):</strong></p>
                        <div style="background: #2d2d2d; color: #f8f8f8; padding: 10px; margin: 5px 0; border-radius: 4px; font-family: monospace; position: relative;">
                            <code id="oauth-setup-command-bash"><?php echo esc_html($bash_command); ?></code>
                            <button type="button" class="button button-small" onclick="copyOAuthCommandBash()" style="position: absolute; right: 10px; top: 10px;">
                                <?php esc_html_e('복사', 'azure-ai-chatbot'); ?>
                            </button>
                        </div>
                        
                        <p style="margin: 10px 0 5px 0;"><strong>PowerShell 모드:</strong></p>
                        <div style="background: #2d2d2d; color: #f8f8f8; padding: 10px; margin: 5px 0; border-radius: 4px; font-family: monospace; position: relative;">
                            <code id="oauth-setup-command-pwsh"><?php echo esc_html($pwsh_command); ?></code>
                            <button type="button" class="button button-small" onclick="copyOAuthCommandPwsh()" style="position: absolute; right: 10px; top: 10px;">
                                <?php esc_html_e('복사', 'azure-ai-chatbot'); ?>
                            </button>
                        </div>
                        <p style="margin: 5px 0; font-size: 12px; color: #666;">
                            💡 Cloud Shell이 PowerShell 모드로 시작되면 PowerShell 명령어를 사용하세요.
                        </p>
                    </li>
                    <li><?php esc_html_e('생성된 Client ID, Client Secret, Tenant ID를 복사', 'azure-ai-chatbot'); ?></li>
                    <li><?php esc_html_e('Azure Portal에서 Admin Consent 부여', 'azure-ai-chatbot'); ?></li>
                </ol>
                
                <p><strong><?php esc_html_e('방법 2: Azure Portal에서 수동 설정', 'azure-ai-chatbot'); ?></strong></p>
                <ol>
                    <li>
                        <?php
                        $site_url = get_site_url();
                        $redirect_uri = admin_url('admin.php?page=azure-ai-chatbot&azure_callback=1');
                        $app_reg_url = 'https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/CreateApplicationBlade';
                        ?>
                        <a href="<?php echo esc_url($app_reg_url); ?>" target="_blank" class="button">
                            <?php esc_html_e('Azure Portal에서 App Registration 생성', 'azure-ai-chatbot'); ?>
                        </a>
                    </li>
                    <li>
                        <?php esc_html_e('Redirect URI 설정:', 'azure-ai-chatbot'); ?>
                        <div style="background: #fff; border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 4px; position: relative;">
                            <code id="redirect-uri"><?php echo esc_html($redirect_uri); ?></code>
                            <button type="button" class="button button-small" onclick="copyRedirectUri()" style="position: absolute; right: 10px; top: 10px;">
                                <?php esc_html_e('복사', 'azure-ai-chatbot'); ?>
                            </button>
                        </div>
                    </li>
                    <li><?php esc_html_e('API 권한 추가: Microsoft Graph (User.Read), Azure Service Management (user_impersonation)', 'azure-ai-chatbot'); ?></li>
                    <li><?php esc_html_e('Client Secret 생성', 'azure-ai-chatbot'); ?></li>
                    <li><?php esc_html_e('Admin Consent 부여', 'azure-ai-chatbot'); ?></li>
                </ol>
                
                <p>
                    <a href="<?php echo esc_url(AZURE_CHATBOT_PLUGIN_URL . 'docs/AZURE_AUTO_SETUP.md'); ?>" target="_blank">
                        <span class="dashicons dashicons-book"></span>
                        <?php esc_html_e('자세한 설정 가이드 보기', 'azure-ai-chatbot'); ?>
                    </a>
                </p>
            </div>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="oauth_client_id"><?php esc_html_e('Client ID', 'azure-ai-chatbot'); ?> *</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="oauth_client_id" 
                               name="azure_chatbot_oauth_client_id" 
                               value="<?php echo esc_attr(get_option('azure_chatbot_oauth_client_id', '')); ?>" 
                               class="regular-text" 
                               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />
                        <p class="description">
                            <?php esc_html_e('Azure App Registration의 Application (client) ID', 'azure-ai-chatbot'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="oauth_client_secret"><?php esc_html_e('Client Secret', 'azure-ai-chatbot'); ?> *</label>
                    </th>
                    <td>
                        <input type="password" 
                               id="oauth_client_secret" 
                               name="azure_chatbot_oauth_client_secret" 
                               value="<?php echo esc_attr(get_option('azure_chatbot_oauth_client_secret', '')); ?>" 
                               class="regular-text" 
                               placeholder="비밀번호는 저장 후 마스킹됩니다" />
                        <p class="description">
                            <?php esc_html_e('Azure App Registration에서 생성한 Client Secret', 'azure-ai-chatbot'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="oauth_tenant_id"><?php esc_html_e('Tenant ID', 'azure-ai-chatbot'); ?> *</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="oauth_tenant_id" 
                               name="azure_chatbot_oauth_tenant_id" 
                               value="<?php echo esc_attr(get_option('azure_chatbot_oauth_tenant_id', '')); ?>" 
                               class="regular-text" 
                               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />
                        <p class="description">
                            <?php esc_html_e('Azure AD의 Directory (tenant) ID', 'azure-ai-chatbot'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="button" class="button" onclick="saveOAuthSettings()">
                    <?php esc_html_e('OAuth 설정 저장', 'azure-ai-chatbot'); ?>
                </button>
            </p>
            
        <?php else: ?>
            
            <?php if (!$has_token): ?>
                <!-- Step 1: Azure 인증 -->
                <div class="oauth-step oauth-step-1">
                    <h3><?php esc_html_e('1단계: Admin Consent 승인 (필수)', 'azure-ai-chatbot'); ?></h3>
                    <div class="notice notice-warning inline" style="margin: 10px 0; padding: 12px;">
                        <p style="margin: 0 0 10px 0;">
                            <strong><?php esc_html_e('⚠️ 중요: Azure 자동 설정을 시작하기 전에 Admin Consent를 먼저 승인해야 합니다!', 'azure-ai-chatbot'); ?></strong>
                        </p>
                        <p style="margin: 0 0 10px 0;">
                            <?php esc_html_e('다음 링크를 클릭하여 브라우저에서 관리자 동의를 승인하세요:', 'azure-ai-chatbot'); ?>
                        </p>
                        <?php 
                        $client_id = get_option('azure_ai_chatbot_client_id');
                        $tenant_id = get_option('azure_ai_chatbot_tenant_id');
                        if ($client_id && $tenant_id):
                            $consent_url = "https://login.microsoftonline.com/{$tenant_id}/adminconsent?client_id={$client_id}";
                        ?>
                        <p style="margin: 0;">
                            <a href="<?php echo esc_url($consent_url); ?>" 
                               class="button button-secondary"
                               target="_blank"
                               style="background: #2271b1; color: white; border-color: #2271b1;">
                                <span class="dashicons dashicons-yes" style="margin-top: 3px;"></span>
                                <?php esc_html_e('Admin Consent 승인하기', 'azure-ai-chatbot'); ?>
                            </a>
                        </p>
                        <?php else: ?>
                        <p style="margin: 0; color: #d63638;">
                            <?php esc_html_e('❌ Client ID와 Tenant ID를 먼저 입력하고 저장하세요.', 'azure-ai-chatbot'); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <h3><?php esc_html_e('2단계: Azure 인증', 'azure-ai-chatbot'); ?></h3>
                    <p>
                        <?php esc_html_e('Admin Consent 승인 후, Azure에 로그인하여 리소스 접근 권한을 부여하세요.', 'azure-ai-chatbot'); ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url($oauth->get_authorization_url()); ?>" 
                           class="button button-primary button-hero"
                           target="_blank"
                           onclick="return openOAuthPopup(this.href);">
                            <span class="dashicons dashicons-lock" style="margin-top: 3px;"></span>
                            <?php esc_html_e('Azure 자동 설정 시작', 'azure-ai-chatbot'); ?>
                        </a>
                    </p>
                    <p class="description">
                        <?php esc_html_e('Microsoft 계정으로 로그인 후 권한을 승인하면 자동으로 돌아옵니다.', 'azure-ai-chatbot'); ?>
                    </p>
                </div>
            <?php else: ?>
                <!-- Step 2: 리소스 선택 -->
                <div class="oauth-step oauth-step-2">
                    <h3><?php esc_html_e('2단계: Azure 리소스 선택', 'azure-ai-chatbot'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="oauth_subscription"><?php esc_html_e('Subscription', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <select id="oauth_subscription" class="regular-text">
                                    <option value=""><?php esc_html_e('로딩 중...', 'azure-ai-chatbot'); ?></option>
                                </select>
                                <button type="button" class="button" onclick="loadSubscriptions()">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e('새로고침', 'azure-ai-chatbot'); ?>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="oauth_resource_group"><?php esc_html_e('Resource Group', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <select id="oauth_resource_group" class="regular-text" disabled>
                                    <option value=""><?php esc_html_e('먼저 Subscription을 선택하세요', 'azure-ai-chatbot'); ?></option>
                                    <option value="__CREATE_NEW__"><?php esc_html_e('➕ 새 Resource Group 만들기', 'azure-ai-chatbot'); ?></option>
                                </select>
                                <button type="button" class="button" onclick="loadResourceGroups()" style="display:none;" id="refresh-rg-btn">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e('새로고침', 'azure-ai-chatbot'); ?>
                                </button>
                                
                                <!-- 새 Resource Group 생성 폼 -->
                                <div id="new-rg-form" style="display:none; margin-top:10px; padding:15px; background:#f0f6fc; border-left:4px solid #0078d4;">
                                    <p><strong><?php esc_html_e('새 Resource Group 만들기', 'azure-ai-chatbot'); ?></strong></p>
                                    
                                    <p style="margin:10px 0;">
                                        <label>
                                            <input type="radio" name="rg_name_mode" value="auto" checked onchange="toggleRgNameInput()">
                                            <?php esc_html_e('자동 생성 이름 사용 (권장)', 'azure-ai-chatbot'); ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="radio" name="rg_name_mode" value="manual" onchange="toggleRgNameInput()">
                                            <?php esc_html_e('직접 입력', 'azure-ai-chatbot'); ?>
                                        </label>
                                    </p>
                                    
                                    <div id="auto-rg-name" style="margin:10px 0;">
                                        <input type="text" id="new_rg_name_auto" class="regular-text" 
                                               value="" readonly 
                                               placeholder="rg-aichatbot-prod-koreacentral"
                                               style="background:#f5f5f5;">
                                        <p class="description">
                                            💡 <?php esc_html_e('Azure 명명 규칙: rg-{워크로드}-{환경}-{지역}', 'azure-ai-chatbot'); ?>
                                        </p>
                                    </div>
                                    
                                    <div id="manual-rg-name" style="margin:10px 0; display:none;">
                                        <input type="text" id="new_rg_name_manual" class="regular-text" 
                                               placeholder="my-resource-group"
                                               pattern="[a-z0-9-]{3,24}">
                                        <p class="description">
                                            <?php esc_html_e('소문자, 숫자, 하이픈만 사용 (3-24자)', 'azure-ai-chatbot'); ?>
                                        </p>
                                    </div>
                                    
                                    <p style="margin:10px 0;">
                                        <label for="new_rg_location"><?php esc_html_e('위치 (Region)', 'azure-ai-chatbot'); ?> *</label><br>
                                        <select id="new_rg_location" class="regular-text">
                                            <option value=""><?php esc_html_e('로딩 중...', 'azure-ai-chatbot'); ?></option>
                                        </select>
                                        <button type="button" class="button button-small" onclick="loadAvailableLocations()" style="margin-left:5px;">
                                            <span class="dashicons dashicons-update"></span>
                                            <?php esc_html_e('새로고침', 'azure-ai-chatbot'); ?>
                                        </button>
                                        <p class="description">
                                            💡 <?php esc_html_e('AI Foundry 사용 가능 지역만 표시됩니다', 'azure-ai-chatbot'); ?>
                                        </p>
                                    </p>
                                    
                                    <p style="margin:10px 0;">
                                        <button type="button" class="button button-primary" onclick="createResourceGroup()">
                                            <span class="dashicons dashicons-plus"></span>
                                            <?php esc_html_e('Resource Group 생성', 'azure-ai-chatbot'); ?>
                                        </button>
                                        <button type="button" class="button" onclick="cancelNewResourceGroup()">
                                            <?php esc_html_e('취소', 'azure-ai-chatbot'); ?>
                                        </button>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('모드', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="radio" name="oauth_mode" value="chat" checked />
                                    <?php esc_html_e('Chat 모드 (Azure OpenAI)', 'azure-ai-chatbot'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="radio" name="oauth_mode" value="agent" />
                                    <?php esc_html_e('Agent 모드 (AI Foundry)', 'azure-ai-chatbot'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="oauth_resource"><?php esc_html_e('AI 리소스', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <select id="oauth_resource" class="regular-text" disabled>
                                    <option value=""><?php esc_html_e('먼저 Resource Group을 선택하세요', 'azure-ai-chatbot'); ?></option>
                                    <option value="__CREATE_NEW__"><?php esc_html_e('➕ 새 AI 리소스 만들기', 'azure-ai-chatbot'); ?></option>
                                </select>
                                
                                <!-- 새 AI 리소스 생성 폼 -->
                                <div id="new-ai-resource-form" style="display:none; margin-top:10px; padding:15px; background:#f0f6fc; border-left:4px solid #0078d4;">
                                    <p><strong><?php esc_html_e('새 AI Foundry Project 만들기', 'azure-ai-chatbot'); ?></strong></p>
                                    
                                    <p style="margin:10px 0;">
                                        <label>
                                            <input type="radio" name="ai_name_mode" value="auto" checked onchange="toggleAiNameInput()">
                                            <?php esc_html_e('자동 생성 이름 사용 (권장)', 'azure-ai-chatbot'); ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="radio" name="ai_name_mode" value="manual" onchange="toggleAiNameInput()">
                                            <?php esc_html_e('직접 입력', 'azure-ai-chatbot'); ?>
                                        </label>
                                    </p>
                                    
                                    <div id="auto-ai-name" style="margin:10px 0;">
                                        <input type="text" id="new_ai_name_auto" class="regular-text" 
                                               value="" readonly 
                                               placeholder="ai-chatbot-prod"
                                               style="background:#f5f5f5;">
                                        <p class="description">
                                            💡 <?php esc_html_e('Azure 명명 규칙: ai-{워크로드}-{환경}', 'azure-ai-chatbot'); ?>
                                        </p>
                                    </div>
                                    
                                    <div id="manual-ai-name" style="margin:10px 0; display:none;">
                                        <input type="text" id="new_ai_name_manual" class="regular-text" 
                                               placeholder="my-ai-resource"
                                               pattern="[a-z0-9-]{3,24}">
                                        <p class="description">
                                            <?php esc_html_e('소문자, 숫자, 하이픈만 사용 (3-24자)', 'azure-ai-chatbot'); ?>
                                        </p>
                                    </div>
                                    
                                    <p style="margin:10px 0;">
                                        <label for="new_ai_sku"><?php esc_html_e('가격 계층 (SKU)', 'azure-ai-chatbot'); ?> *</label><br>
                                        <select id="new_ai_sku" class="regular-text">
                                            <option value="S0">S0 - Standard (프로덕션 권장)</option>
                                            <option value="F0">F0 - Free (테스트용, 제한적)</option>
                                        </select>
                                    </p>
                                    
                                    <p style="margin:10px 0;" id="ai-location-container">
                                        <label for="new_ai_location"><?php esc_html_e('위치 (Region)', 'azure-ai-chatbot'); ?></label><br>
                                        <input type="text" id="new_ai_location" class="regular-text" readonly 
                                               value="" 
                                               style="background:#f5f5f5;">
                                        <span class="description">
                                            <?php esc_html_e('(Resource Group과 동일한 위치 사용)', 'azure-ai-chatbot'); ?>
                                        </span>
                                    </p>
                                    
                                    <!-- Chat 모드 전용: 모델 선택 -->
                                    <div id="chat-model-selection" style="display:none;">
                                        <p style="margin:10px 0;">
                                            <label for="new_ai_model"><?php esc_html_e('배포할 모델', 'azure-ai-chatbot'); ?> *</label><br>
                                            <select id="new_ai_model" class="regular-text">
                                                <option value=""><?php esc_html_e('지역을 선택하면 사용 가능한 모델이 표시됩니다', 'azure-ai-chatbot'); ?></option>
                                            </select>
                                            <button type="button" class="button button-small" onclick="loadAvailableModels()" style="margin-left:5px;">
                                                <span class="dashicons dashicons-update"></span>
                                            </button>
                                        </p>
                                        
                                        <p style="margin:10px 0;">
                                            <label for="new_ai_deployment_name"><?php esc_html_e('배포 이름 (Deployment Name)', 'azure-ai-chatbot'); ?></label><br>
                                            <input type="text" id="new_ai_deployment_name" class="regular-text" 
                                                   value="" readonly 
                                                   style="background:#f5f5f5;">
                                            <p class="description">
                                                💡 <?php esc_html_e('자동 생성: {model-name}-deployment', 'azure-ai-chatbot'); ?>
                                            </p>
                                        </p>
                                        
                                        <p style="margin:10px 0;">
                                            <label for="new_ai_capacity"><?php esc_html_e('용량 (Capacity)', 'azure-ai-chatbot'); ?></label><br>
                                            <select id="new_ai_capacity" class="regular-text">
                                                <option value="10">10K TPM (테스트용)</option>
                                                <option value="30" selected>30K TPM (권장)</option>
                                                <option value="50">50K TPM</option>
                                                <option value="100">100K TPM</option>
                                                <option value="240">240K TPM (최대)</option>
                                            </select>
                                            <p class="description">
                                                TPM = Tokens Per Minute (분당 토큰 수)
                                            </p>
                                        </p>
                                    </div>
                                    
                                    <p style="margin:10px 0;">
                                        <button type="button" class="button button-primary" onclick="createAIResource()">
                                            <span class="dashicons dashicons-plus"></span>
                                            <span id="create-ai-btn-text"><?php esc_html_e('AI Foundry Project 생성', 'azure-ai-chatbot'); ?></span>
                                        </button>
                                        <button type="button" class="button" onclick="cancelNewAIResource()">
                                            <?php esc_html_e('취소', 'azure-ai-chatbot'); ?>
                                        </button>
                                    </p>
                                    
                                    <p class="description" style="margin-top:10px; font-size:12px; color:#666;">
                                        ⏱️ <span id="creation-time-estimate"><?php esc_html_e('리소스 생성은 1-2분 정도 소요됩니다.', 'azure-ai-chatbot'); ?></span>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Agent 모드 전용: Agent 선택 -->
                        <tr id="agent_selection_row" style="display: none;">
                            <th scope="row">
                                <label for="oauth_agent"><?php esc_html_e('Agent', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <select id="oauth_agent" class="regular-text" disabled>
                                    <option value=""><?php esc_html_e('먼저 리소스를 선택하세요', 'azure-ai-chatbot'); ?></option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('AI Foundry Project에서 생성된 Agent를 선택하세요.', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="button" 
                                class="button button-primary" 
                                id="btn-fetch-keys" 
                                onclick="fetchKeys()" 
                                disabled>
                            <?php esc_html_e('값 자동 추출', 'azure-ai-chatbot'); ?>
                        </button>
                        <button type="button" 
                                class="button" 
                                onclick="clearOAuthSession()">
                            <?php esc_html_e('인증 초기화', 'azure-ai-chatbot'); ?>
                        </button>
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- OAuth 설정 재구성 -->
            <p style="margin-top: 20px;">
                <button type="button" id="reset-oauth-button" class="button">
                    <span class="dashicons dashicons-admin-generic" style="margin-top: 3px;"></span>
                    <?php esc_html_e('OAuth 설정 변경', 'azure-ai-chatbot'); ?>
                </button>
                <span class="description" style="margin-left: 10px;">
                    <?php esc_html_e('Client ID, Secret, Tenant ID를 변경하려면 클릭하세요', 'azure-ai-chatbot'); ?>
                </span>
            </p>
            
            <script type="text/javascript">
            (function($) {
                console.log('Script loaded, looking for button...');
                var button = $('#reset-oauth-button');
                console.log('Button found:', button.length);
                
                $('#reset-oauth-button').on('click', function(e) {
                    e.preventDefault();
                    console.log('Reset OAuth button clicked!');
                    
                    if (!confirm('<?php esc_html_e('OAuth 설정을 초기화하시겠습니까? 저장된 Client ID, Client Secret, Tenant ID가 모두 삭제됩니다.', 'azure-ai-chatbot'); ?>')) {
                        console.log('User cancelled');
                        return false;
                    }
                    
                    var btn = $(this);
                    var originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: rotation 2s infinite linear;"></span> <?php esc_html_e('초기화 중...', 'azure-ai-chatbot'); ?>');
                    
                    $.post(ajaxurl, {
                        action: 'azure_oauth_reset_config',
                        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>'
                    })
                    .done(function(response) {
                        console.log('Response:', response);
                        if (response.success) {
                            var resetSuccessMsg = <?php echo json_encode(__('OAuth 설정이 초기화되었습니다. 페이지를 새로고침합니다.', 'azure-ai-chatbot')); ?>;
                            alert(resetSuccessMsg);
                            location.reload();
                        } else {
                            var resetFailMsg = <?php echo json_encode(__('초기화 실패:', 'azure-ai-chatbot')); ?>;
                            var unknownErrorMsg = <?php echo json_encode(__('알 수 없는 오류', 'azure-ai-chatbot')); ?>;
                            alert(resetFailMsg + ' ' + (response.data && response.data.message ? response.data.message : unknownErrorMsg));
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    })
                    .fail(function(xhr, status, error) {
                        console.error('AJAX Error:', xhr, status, error);
                        alert('<?php esc_html_e('AJAX 오류:', 'azure-ai-chatbot'); ?> ' + error);
                        btn.prop('disabled', false).html(originalHtml);
                    });
                    
                    return false;
                });
            })(jQuery);
            </script>
            
        <?php endif; ?>
    </div>
</div>

<style>
.azure-oauth-section .inside {
    padding: 20px;
}
.oauth-step {
    background: #f9f9f9;
    border-left: 4px solid #2271b1;
    padding: 15px;
    margin: 15px 0;
}
.oauth-step h3 {
    margin-top: 0;
}
</style>

<script>
// 자동 설정 모드 플래그
var autoSetupMode = <?php echo isset($_GET['oauth_success']) && $has_token ? 'true' : 'false'; ?>;
var operationMode = '<?php echo esc_js(get_option('azure_ai_chatbot_operation_mode', 'chat')); ?>';

console.log('[Auto Setup] Auto mode:', autoSetupMode);
console.log('[Auto Setup] Operation mode:', operationMode);

function openOAuthPopup(url) {
    var width = 600;
    var height = 700;
    var left = (screen.width - width) / 2;
    var top = (screen.height - height) / 2;
    
    window.open(
        url,
        'AzureOAuth',
        'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left + ',toolbar=no,menubar=no,scrollbars=yes,resizable=yes'
    );
    
    return false; // 기본 링크 동작 방지
}

function copyToClipboard(elementId, successMessage) {
    var textToCopy = document.getElementById(elementId).textContent;
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(function() {
            alert(successMessage);
        }).catch(function(err) {
            console.error('Clipboard write failed: ', err);
            // Fallback for older browsers
            var textArea = document.createElement("textarea");
            textArea.value = textToCopy;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                alert(successMessage);
            } catch (e) {
                console.error('Fallback copy failed: ', e);
                alert('<?php esc_html_e('복사에 실패했습니다.', 'azure-ai-chatbot'); ?>');
            }
            document.body.removeChild(textArea);
        });
    } else {
        // Fallback for non-secure contexts or old browsers
        var textArea = document.createElement("textarea");
        textArea.value = textToCopy;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            alert(successMessage);
        } catch (e) {
            console.error('Fallback copy failed: ', e);
            alert('<?php esc_html_e('복사에 실패했습니다.', 'azure-ai-chatbot'); ?>');
        }
        document.body.removeChild(textArea);
    }
}

function copyOAuthCommandBash() {
    copyToClipboard('oauth-setup-command-bash', '<?php esc_html_e('Bash 명령어가 클립보드에 복사되었습니다!', 'azure-ai-chatbot'); ?>');
}

function copyOAuthCommandPwsh() {
    copyToClipboard('oauth-setup-command-pwsh', '<?php esc_html_e('PowerShell 명령어가 클립보드에 복사되었습니다!', 'azure-ai-chatbot'); ?>');
}

function copyRedirectUri() {
    copyToClipboard('redirect-uri', '<?php esc_html_e('Redirect URI가 클립보드에 복사되었습니다!', 'azure-ai-chatbot'); ?>');
}

// 하위 호환성을 위해 유지
function copyOAuthCommand() {
    copyOAuthCommandBash();
}

jQuery(document).ready(function($) {
    // 인증 성공 시 자동으로 Subscription 로드
    <?php if ($has_token): ?>
    loadSubscriptions();
    <?php endif; ?>
    
    // Subscription 변경 시 Resource Group 로드
    $('#oauth_subscription').on('change', function() {
        loadResourceGroups();
    });
    
    // Resource Group 변경 시 리소스 로드 및 새 리소스 그룹 폼 처리
    $('#oauth_resource_group').on('change', function() {
        var value = $(this).val();
        if (value === '__CREATE_NEW__') {
            $('#new-rg-form').slideDown(300);
            // 위치 정보가 로드되지 않았으면 로드
            if ($('#new_rg_location option').length <= 1) {
                loadAvailableLocations();
            } else {
                generateResourceGroupName();
            }
        } else {
            $('#new-rg-form').slideUp(300);
            if (value) {
                var selectedOption = $(this).find('option:selected');
                var location = selectedOption.text().match(/\(([^)]+)\)/);
                if (location && location[1]) {
                    $('#new_ai_location').val(location[1]);
                }
            }
            loadResources();
        }
    });

    // AI 리소스 선택 시 새 리소스 폼 처리
    $('#oauth_resource').on('change', function() {
        var value = $(this).val();
        if (value === '__CREATE_NEW__') {
            $('#new-ai-resource-form').slideDown(300);
            generateAIResourceName();
            
            var rgLocation = $('#new_ai_location').val();
            if (!rgLocation) {
                var selectedRg = $('#oauth_resource_group option:selected');
                var location = selectedRg.text().match(/\(([^)]+)\)/);
                 if (location && location[1]) {
                    $('#new_ai_location').val(location[1]);
                }
            }
        } else {
            $('#new-ai-resource-form').slideUp(300);
        }
        
        var mode = $('input[name="oauth_mode"]:checked').val();
        if (mode === 'agent' && value && value !== '__CREATE_NEW__') {
            loadAgents(value);
        }
        updateFetchButton();
    });
    
    // 모드 변경 시 리소스 다시 로드 및 UI 업데이트
    $('input[name="oauth_mode"]').on('change', function() {
        var mode = $(this).val();
        
        if (mode === 'agent') {
            $('#agent_selection_row').show();
        } else {
            $('#agent_selection_row').hide();
            $('#oauth_agent').val('').prop('disabled', true);
        }
        
        if ($('#oauth_resource_group').val() && $('#oauth_resource_group').val() !== '__CREATE_NEW__') {
            loadResources();
        }
        
        if ($('#new-ai-resource-form').is(':visible')) {
            generateAIResourceName();
        }
    });
    
    // Agent 선택 시 버튼 활성화
    $('#oauth_agent').on('change', function() {
        updateFetchButton();
    });

    // Location 변경 시 Resource Group 이름 재생성
    $('#new_rg_location').on('change', function() {
        if ($('input[name="rg_name_mode"]:checked').val() === 'auto') {
            generateResourceGroupName();
        }
    });

    // 모델 선택 시 배포 이름 자동 생성
    $('#new_ai_model').on('change', function() {
        var modelName = $(this).val();
        if (modelName) {
            var deploymentName = modelName.replace(/[^a-zA-Z0-9]/g, '-') + '-deployment';
            $('#new_ai_deployment_name').val(deploymentName);
        }
    });
});

function updateFetchButton() {
    var mode = jQuery('input[name="oauth_mode"]:checked').val();
    var resourceSelected = jQuery('#oauth_resource').val();
    var canFetch = false;
    
    if (mode === 'chat') {
        // Chat 모드: 리소스만 선택되면 OK
        canFetch = !!resourceSelected;
    } else {
        // Agent 모드: 리소스 + Agent 선택되어야 함
        var agentSelected = jQuery('#oauth_agent').val();
        canFetch = !!resourceSelected && !!agentSelected;
    }
    
    jQuery('#btn-fetch-keys').prop('disabled', !canFetch);
}

function saveOAuthSettings() {
    var data = {
        action: 'save_oauth_settings',
        nonce: '<?php echo wp_create_nonce("azure_oauth_save"); ?>',
        client_id: jQuery('#oauth_client_id').val(),
        client_secret: jQuery('#oauth_client_secret').val(),
        tenant_id: jQuery('#oauth_tenant_id').val()
    };
    
    jQuery.post(ajaxurl, data, function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert('저장 실패: ' + response.data.message);
        }
    });
}

function loadSubscriptions() {
    var $select = jQuery('#oauth_subscription');
    $select.html('<option value="">로딩 중...</option>').prop('disabled', true);
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_get_subscriptions',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>'
    }, function(response) {
        $select.prop('disabled', false);
        
        if (response.success) {
            $select.html('<option value="">선택하세요</option>');
            response.data.subscriptions.forEach(function(sub) {
                $select.append('<option value="' + sub.id + '">' + sub.name + '</option>');
            });
            
            // 자동 설정 모드: 첫 번째 Subscription 자동 선택
            if (autoSetupMode && response.data.subscriptions.length > 0) {
                var firstSubscription = response.data.subscriptions[0];
                console.log('[Auto Setup] 첫 번째 Subscription 자동 선택:', firstSubscription.name);
                $select.val(firstSubscription.id);
                
                // Subscription 선택 이벤트 트리거
                $select.trigger('change');
                
                // 1초 후 자동으로 리소스 생성 시작
                setTimeout(function() {
                    console.log('[Auto Setup] 리소스 자동 생성 시작');
                    startAutoResourceCreation(firstSubscription.id);
                }, 1000);
            }
        } else {
            $select.html('<option value="">오류: ' + response.data.message + '</option>');
            console.error('[Auto Setup] Subscription 로드 실패:', response.data.message);
        }
    });
}

function loadResourceGroups() {
    var subscriptionId = jQuery('#oauth_subscription').val();
    if (!subscriptionId) return;
    
    var $select = jQuery('#oauth_resource_group');
    $select.html('<option value="">로딩 중...</option>').prop('disabled', true);
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_get_resource_groups',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        subscription_id: subscriptionId
    }, function(response) {
        $select.prop('disabled', false);
        
        if (response.success) {
            $select.html('<option value="">선택하세요</option>');
            response.data.resource_groups.forEach(function(rg) {
                $select.append('<option value="' + rg.name + '">' + rg.name + ' (' + rg.location + ')</option>');
            });
        } else {
            $select.html('<option value="">오류: ' + response.data.message + '</option>');
        }
    });
}

function loadResources() {
    var subscriptionId = jQuery('#oauth_subscription').val();
    var resourceGroup = jQuery('#oauth_resource_group').val();
    var mode = jQuery('input[name="oauth_mode"]:checked').val();
    
    if (!subscriptionId || !resourceGroup) return;
    
    var $select = jQuery('#oauth_resource');
    $select.html('<option value="">로딩 중...</option>').prop('disabled', true);
    
    // Agent 선택 초기화
    jQuery('#oauth_agent').html('<option value="">먼저 리소스를 선택하세요</option>').prop('disabled', true);
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_get_resources',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        subscription_id: subscriptionId,
        resource_group: resourceGroup,
        mode: mode
    }, function(response) {
        $select.prop('disabled', false);
        
        if (response.success) {
            $select.html('<option value="">선택하세요</option>');
            response.data.resources.forEach(function(res) {
                $select.append('<option value="' + res.id + '">' + res.name + ' (' + res.location + ')</option>');
            });
        } else {
            $select.html('<option value="">오류: ' + response.data.message + '</option>');
        }
    });
}

function loadAgents(resourceId) {
    if (!resourceId) return;
    
    var $select = jQuery('#oauth_agent');
    $select.html('<option value="">로딩 중...</option>').prop('disabled', true);
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_get_agents',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        resource_id: resourceId
    }, function(response) {
        $select.prop('disabled', false);
        
        if (response.success) {
            if (response.data.agents.length === 0) {
                $select.html('<option value="">Agent가 없습니다. AI Foundry에서 Agent를 생성하세요.</option>');
            } else {
                $select.html('<option value="">선택하세요</option>');
                response.data.agents.forEach(function(agent) {
                    var label = agent.name;
                    if (agent.description) {
                        label += ' - ' + agent.description;
                    }
                    $select.append('<option value="' + agent.id + '">' + label + '</option>');
                });
            }
        } else {
            $select.html('<option value="">오류: ' + response.data.message + '</option>');
        }
    });
}

function fetchKeys() {
    var resourceId = jQuery('#oauth_resource').val();
    var mode = jQuery('input[name="oauth_mode"]:checked').val();
    var agentId = mode === 'agent' ? jQuery('#oauth_agent').val() : '';
    
    if (!resourceId) {
        alert('<?php esc_html_e('먼저 리소스를 선택하세요.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    if (mode === 'agent' && !agentId) {
        alert('<?php esc_html_e('Agent 모드에서는 에이전트를 선택해야 합니다.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    jQuery('#btn-fetch-keys').prop('disabled', true).text('<?php esc_html_e('추출 중...', 'azure-ai-chatbot'); ?>');
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_get_keys',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        resource_id: resourceId,
        mode: mode
    }, function(response) {
        jQuery('#btn-fetch-keys').prop('disabled', false).text('<?php esc_html_e('값 가져오기', 'azure-ai-chatbot'); ?>');
        
        if (response.success) {
            // 모드에 따라 필드에 값 자동 입력
            if (mode === 'chat') {
                jQuery('#chat_endpoint').val(response.data.endpoint);
                jQuery('#api_key').val(response.data.api_key);
                // Chat 모드 라디오 버튼 선택
                jQuery('input[name="azure_chatbot_settings[mode]"][value="chat"]').prop('checked', true).trigger('change');
                
                var chatModeMsg = <?php echo json_encode(__('Chat 모드 값이 자동으로 입력되었습니다.', 'azure-ai-chatbot')); ?>;
                var endpointLabel = <?php echo json_encode(__('Endpoint:', 'azure-ai-chatbot')); ?>;
                var saveSettingsMsg = <?php echo json_encode(__('설정을 저장하세요.', 'azure-ai-chatbot')); ?>;
                alert(chatModeMsg + '\n\n' + endpointLabel + ' ' + response.data.endpoint + '\n\n' + saveSettingsMsg);
            } else {
                jQuery('#agent_endpoint').val(response.data.endpoint);
                jQuery('#subscription_key').val(response.data.api_key);
                jQuery('#agent_id').val(agentId);
                // Agent 모드 라디오 버튼 선택
                jQuery('input[name="azure_chatbot_settings[mode]"][value="agent"]').prop('checked', true).trigger('change');
                
                var agentModeMsg = <?php echo json_encode(__('Agent 모드 값이 자동으로 입력되었습니다.', 'azure-ai-chatbot')); ?>;
                var projectEndpointLabel = <?php echo json_encode(__('Project Endpoint:', 'azure-ai-chatbot')); ?>;
                var agentIdLabel = <?php echo json_encode(__('Agent ID:', 'azure-ai-chatbot')); ?>;
                var saveSettingsMsg2 = <?php echo json_encode(__('설정을 저장하세요.', 'azure-ai-chatbot')); ?>;
                alert(agentModeMsg + '\n\n' + projectEndpointLabel + ' ' + response.data.endpoint + '\n' + agentIdLabel + ' ' + agentId + '\n\n' + saveSettingsMsg2);
            }
            
            // Auto Setting 섹션 닫기
            jQuery('#oauth-auto-setup-section').slideUp(300);
            jQuery('#toggle-auto-setup .dashicons').attr('class', 'dashicons dashicons-admin-network');
            
            // API 설정 섹션으로 스크롤
            jQuery('html, body').animate({
                scrollTop: jQuery('.postbox').eq(1).offset().top - 50
            }, 500);
        } else {
            alert('<?php esc_html_e('키 추출 실패:', 'azure-ai-chatbot'); ?> ' + response.data.message);
        }
    });
}

function clearOAuthSession() {
    if (!confirm('인증 세션을 초기화하시겠습니까?')) return;
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_clear_session',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>'
    }, function() {
        location.reload();
    });
}

// Resource Group 생성 관련 함수들
function toggleRgNameInput() {
    var mode = jQuery('input[name="rg_name_mode"]:checked').val();
    if (mode === 'auto') {
        jQuery('#auto-rg-name').show();
        jQuery('#manual-rg-name').hide();
        generateResourceGroupName();
    } else {
        jQuery('#auto-rg-name').hide();
        jQuery('#manual-rg-name').show();
    }
}

// 사용 가능한 Azure 지역 로드
function loadAvailableLocations() {
    var subscription = jQuery('#oauth_subscription').val();
    var mode = jQuery('input[name="oauth_mode"]:checked').val();
    
    if (!subscription) {
        alert('<?php esc_html_e('먼저 Subscription을 선택하세요.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    jQuery('#new_rg_location').html('<option value=""><?php esc_html_e('로딩 중...', 'azure-ai-chatbot'); ?></option>');
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_get_available_locations',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        subscription: subscription,
        mode: mode,
        resource_type: mode === 'chat' ? 'Microsoft.CognitiveServices/accounts' : 'Microsoft.MachineLearningServices/workspaces'
    }, function(response) {
        if (response.success && response.data.locations) {
            var html = '';
            response.data.locations.forEach(function(location) {
                html += '<option value="' + location.name + '">' + location.displayName + '</option>';
            });
            jQuery('#new_rg_location').html(html);
            
            // 첫 번째 지역 선택 시 RG 이름 자동 생성
            if (jQuery('input[name="rg_name_mode"]:checked').val() === 'auto') {
                generateResourceGroupName();
            }
        } else {
            // 실패 시 기본 지역 목록 표시
            var defaultLocations = [
                {name: 'koreacentral', display: 'Korea Central (한국 중부)'},
                {name: 'eastus', display: 'East US (미국 동부)'},
                {name: 'eastus2', display: 'East US 2 (미국 동부 2)'},
                {name: 'westus', display: 'West US (미국 서부)'},
                {name: 'westus2', display: 'West US 2 (미국 서부 2)'},
                {name: 'westeurope', display: 'West Europe (서유럽)'},
                {name: 'northeurope', display: 'North Europe (북유럽)'},
                {name: 'southeastasia', display: 'Southeast Asia (동남아시아)'},
                {name: 'japaneast', display: 'Japan East (일본 동부)'}
            ];
            
            var html = '';
            defaultLocations.forEach(function(location) {
                html += '<option value="' + location.name + '">' + location.display + '</option>';
            });
            jQuery('#new_rg_location').html(html);
        }
    });
}

// 사용 가능한 OpenAI 모델 로드 (Chat 모드 전용)
function loadAvailableModels() {
    var location = jQuery('#new_ai_location').val() || jQuery('#new_rg_location').val();
    
    if (!location) {
        alert('<?php esc_html_e('먼저 위치를 선택하세요.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    jQuery('#new_ai_model').html('<option value=""><?php esc_html_e('로딩 중...', 'azure-ai-chatbot'); ?></option>');
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_get_available_models',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        location: location
    }, function(response) {
        if (response.success && response.data.models) {
            var html = '<option value=""><?php esc_html_e('모델을 선택하세요', 'azure-ai-chatbot'); ?></option>';
            response.data.models.forEach(function(model) {
                html += '<option value="' + model.name + '">' + model.displayName + ' (' + model.version + ')</option>';
            });
            jQuery('#new_ai_model').html(html);
        } else {
            // 실패 시 기본 모델 목록
            var defaultModels = [
                {name: 'gpt-4o', display: 'GPT-4o', version: '2024-08-06'},
                {name: 'gpt-4o-mini', display: 'GPT-4o Mini', version: '2024-07-18'},
                {name: 'gpt-4', display: 'GPT-4 Turbo', version: '0125-Preview'},
                {name: 'gpt-35-turbo', display: 'GPT-3.5 Turbo', version: '0125'}
            ];
            
            var html = '<option value=""><?php esc_html_e('모델을 선택하세요', 'azure-ai-chatbot'); ?></option>';
            defaultModels.forEach(function(model) {
                html += '<option value="' + model.name + '">' + model.display + ' (' + model.version + ')</option>';
            });
            jQuery('#new_ai_model').html(html);
        }
    });
}

function generateResourceGroupName() {
    var location = jQuery('#new_rg_location').val();
    var timestamp = new Date().toISOString().slice(0,10).replace(/-/g, '');
    var name = 'rg-aichatbot-prod-' + location;
    jQuery('#new_rg_name_auto').val(name);
}

function createResourceGroup() {
    var nameMode = jQuery('input[name="rg_name_mode"]:checked').val();
    var name = nameMode === 'auto' ? 
        jQuery('#new_rg_name_auto').val() : 
        jQuery('#new_rg_name_manual').val();
    var location = jQuery('#new_rg_location').val();
    var subscription = jQuery('#oauth_subscription').val();
    
    if (!name || !location) {
        alert('<?php esc_html_e('모든 필드를 입력하세요.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    // 이름 유효성 검사
    if (!/^[a-z0-9-]{3,24}$/.test(name)) {
        alert('<?php esc_html_e('리소스 그룹 이름은 소문자, 숫자, 하이픈만 사용하며 3-24자여야 합니다.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    jQuery('#new-rg-form button').prop('disabled', true);
    jQuery('#new-rg-form').prepend('<p class="notice notice-info inline"><span class="dashicons dashicons-update spin"></span> <?php esc_html_e('리소스 그룹 생성 중...', 'azure-ai-chatbot'); ?></p>');
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_create_resource_group',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        name: name,
        location: location,
        subscription: subscription
    }, function(response) {
        jQuery('#new-rg-form .notice').remove();
        jQuery('#new-rg-form button').prop('disabled', false);
        
        if (response.success) {
            alert('<?php esc_html_e('리소스 그룹이 성공적으로 생성되었습니다!', 'azure-ai-chatbot'); ?>');
            
            // 폼 숨기기
            jQuery('#new-rg-form').hide();
            jQuery('#oauth_resource_group').val('');
            
            // 리소스 그룹 목록 새로고침
            loadResourceGroups();
        } else {
            alert('<?php esc_html_e('생성 실패:', 'azure-ai-chatbot'); ?> ' + response.data.message);
        }
    });
}

function cancelNewResourceGroup() {
    jQuery('#new-rg-form').hide();
    jQuery('#oauth_resource_group').val('');
}

// AI 리소스 생성 관련 함수들
function toggleAiNameInput() {
    var mode = jQuery('input[name="ai_name_mode"]:checked').val();
    if (mode === 'auto') {
        jQuery('#auto-ai-name').show();
        jQuery('#manual-ai-name').hide();
        generateAIResourceName();
    } else {
        jQuery('#auto-ai-name').hide();
        jQuery('#manual-ai-name').show();
    }
}

function generateAIResourceName() {
    var chatMode = jQuery('input[name="oauth_mode"]:checked').val();
    var timestamp = new Date().toISOString().slice(0,10).replace(/-/g, '');
    var name;
    
    if (chatMode === 'chat') {
        name = 'ai-chatbot-prod';
        jQuery('#create-ai-btn-text').text('<?php esc_html_e('Project 생성 및 모델 배포', 'azure-ai-chatbot'); ?>');
        jQuery('#chat-model-selection').show();
        jQuery('#creation-time-estimate').text('<?php esc_html_e('AI Foundry Project 생성 및 모델 배포는 2-3분 정도 소요됩니다.', 'azure-ai-chatbot'); ?>');
        
        // 지역이 선택되어 있으면 모델 목록 로드
        var location = jQuery('#new_ai_location').val() || jQuery('#new_rg_location').val();
        if (location) {
            loadAvailableModels();
        }
    } else {
        name = 'ai-chatbot-prod';
        jQuery('#create-ai-btn-text').text('<?php esc_html_e('AI Foundry Project 생성', 'azure-ai-chatbot'); ?>');
        jQuery('#chat-model-selection').hide();
        jQuery('#creation-time-estimate').text('<?php esc_html_e('AI Foundry Project 생성은 1-2분 정도 소요됩니다.', 'azure-ai-chatbot'); ?>');
    }
    
    jQuery('#new_ai_name_auto').val(name);
}

// 모델 선택 시 배포 이름 자동 생성
jQuery(document).ready(function($) {
    $('#new_ai_model').on('change', function() {
        var modelName = $(this).val();
        if (modelName) {
            var deploymentName = modelName + '-deployment';
            $('#new_ai_deployment_name').val(deploymentName);
        }
    });
});

// 자동 리소스 생성 함수
function startAutoResourceCreation(subscriptionId) {
    console.log('[Auto Setup] 자동 리소스 생성 시작');
    console.log('[Auto Setup] Subscription ID:', subscriptionId);
    console.log('[Auto Setup] Operation Mode:', operationMode);
    
    // Resource Group 이름 자동 생성
    var timestamp = new Date().getTime();
    var rgName = 'rg-ai-chatbot-' + timestamp;
    var aiResourceName = 'ai-chatbot-' + timestamp;
    var location = 'koreacentral';
    
    // 1단계: Resource Group 생성
    console.log('[Auto Setup] Resource Group 생성:', rgName);
    createResourceGroup(subscriptionId, rgName, location, function(success) {
        if (!success) {
            console.error('[Auto Setup] Resource Group 생성 실패');
            alert('<?php esc_html_e('Resource Group 생성에 실패했습니다.', 'azure-ai-chatbot'); ?>');
            return;
        }
        
        console.log('[Auto Setup] Resource Group 생성 완료');
        
        // 2단계: AI Resource 생성
        console.log('[Auto Setup] AI Resource 생성:', aiResourceName);
        
        // 기본값 설정
        var sku = 'S0';
        var model = operationMode === 'chat' ? 'gpt-4o' : '';
        var deploymentName = operationMode === 'chat' ? 'gpt-4o' : '';
        var capacity = operationMode === 'chat' ? '10' : '';
        
        jQuery.post(ajaxurl, {
            action: 'azure_oauth_create_ai_resource',
            nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
            name: aiResourceName,
            sku: sku,
            location: location,
            resource_group: rgName,
            subscription: subscriptionId,
            mode: operationMode,
            model: model,
            deployment_name: deploymentName,
            capacity: capacity
        }, function(response) {
            if (response.success) {
                console.log('[Auto Setup] AI Resource 생성 완료');
                
                // 성공 메시지
                var chatSuccessMsg = <?php echo json_encode(__('Chat 모드 설정이 완료되었습니다!', 'azure-ai-chatbot')); ?>;
                var agentSuccessMsg = <?php echo json_encode(__('Agent 모드 설정이 완료되었습니다!', 'azure-ai-chatbot')); ?>;
                var successMsg = operationMode === 'chat' ? chatSuccessMsg : agentSuccessMsg;
                
                alert(successMsg + '\n\n' + <?php echo json_encode(__('설정 페이지로 이동하여 자동으로 입력된 값을 확인하세요.', 'azure-ai-chatbot')); ?>);
                
                // 설정 페이지로 리다이렉트
                window.location.href = '<?php echo admin_url("admin.php?page=azure-ai-chatbot"); ?>';
            } else {
                console.error('[Auto Setup] AI Resource 생성 실패:', response.data.message);
                alert('<?php esc_html_e('AI Resource 생성 실패:', 'azure-ai-chatbot'); ?> ' + response.data.message);
            }
        });
    });
}

// Resource Group 생성 함수
function createResourceGroup(subscriptionId, name, location, callback) {
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_create_resource_group',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        subscription_id: subscriptionId,
        name: name,
        location: location
    }, function(response) {
        callback(response.success);
    });
}

function createAIResource() {
    var nameMode = jQuery('input[name="ai_name_mode"]:checked').val();
    var name = nameMode === 'auto' ? 
        jQuery('#new_ai_name_auto').val() : 
        jQuery('#new_ai_name_manual').val();
    var sku = jQuery('#new_ai_sku').val();
    var location = jQuery('#new_ai_location').val();
    var resourceGroup = jQuery('#oauth_resource_group').val();
    var subscription = jQuery('#oauth_subscription').val();
    var mode = jQuery('input[name="oauth_mode"]:checked').val();
    
    // Chat 모드일 때는 모델 정보도 필요
    var model = mode === 'chat' ? jQuery('#new_ai_model').val() : '';
    var deploymentName = mode === 'chat' ? jQuery('#new_ai_deployment_name').val() : '';
    var capacity = mode === 'chat' ? jQuery('#new_ai_capacity').val() : '';
    
    if (!name || !sku || !location || !resourceGroup) {
        alert('<?php esc_html_e('모든 필드를 입력하세요.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    // Chat 모드 추가 검증
    if (mode === 'chat' && (!model || !deploymentName)) {
        alert('<?php esc_html_e('모델과 배포 이름을 선택하세요.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    // 이름 유효성 검사
    if (!/^[a-z0-9-]{3,64}$/.test(name)) {
        alert('<?php esc_html_e('리소스 이름은 소문자, 숫자, 하이픈만 사용하며 3-64자여야 합니다.', 'azure-ai-chatbot'); ?>');
        return;
    }
    
    var chatProgressMsg = <?php echo json_encode(__('AI Foundry Project 생성 및 모델 배포 중... (2-3분 소요)', 'azure-ai-chatbot')); ?>;
    var agentProgressMsg = <?php echo json_encode(__('AI Foundry Project 생성 중... (1-2분 소요)', 'azure-ai-chatbot')); ?>;
    var progressMsg = mode === 'chat' ? chatProgressMsg : agentProgressMsg;
    
    jQuery('#new-ai-resource-form button').prop('disabled', true);
    jQuery('#new-ai-resource-form').prepend('<p class="notice notice-info inline"><span class="dashicons dashicons-update spin"></span> ' + progressMsg + '</p>');
    
    jQuery.post(ajaxurl, {
        action: 'azure_oauth_create_ai_resource',
        nonce: '<?php echo wp_create_nonce("azure_oauth_nonce"); ?>',
        name: name,
        sku: sku,
        location: location,
        resource_group: resourceGroup,
        subscription: subscription,
        mode: mode,
        model: model,
        deployment_name: deploymentName,
        capacity: capacity
    }, function(response) {
        jQuery('#new-ai-resource-form .notice').remove();
        jQuery('#new-ai-resource-form button').prop('disabled', false);
        
        if (response.success) {
            var chatSuccessMsg = <?php echo json_encode(__('AI Foundry Project와 모델이 성공적으로 배포되었습니다!', 'azure-ai-chatbot')); ?>;
            var agentSuccessMsg = <?php echo json_encode(__('AI Foundry Project가 성공적으로 생성되었습니다!', 'azure-ai-chatbot')); ?>;
            var successMsg = mode === 'chat' ? chatSuccessMsg : agentSuccessMsg;
            
            alert(successMsg);
            
            // 폼 숨기기
            jQuery('#new-ai-resource-form').hide();
            jQuery('#oauth_resource').val('');
            
            // 리소스 목록 새로고침
            loadResources();
        } else {
            alert('<?php esc_html_e('생성 실패:', 'azure-ai-chatbot'); ?> ' + response.data.message);
        }
    });
}

function cancelNewAIResource() {
    jQuery('#new-ai-resource-form').hide();
    jQuery('#oauth_resource').val('');
}

// Resource Group 선택 이벤트 처리 수정
jQuery(document).ready(function($) {
    $('#oauth_resource_group').on('change', function() {
        var value = $(this).val();
        
        if (value === '__CREATE_NEW__') {
            $('#new-rg-form').slideDown(300);
            generateResourceGroupName();
        } else {
            $('#new-rg-form').slideUp(300);
            
            if (value) {
                // 선택된 Resource Group의 location 가져오기
                var selectedOption = $(this).find('option:selected');
                var location = selectedOption.data('location');
                if (location) {
                    $('#new_ai_location').val(location);
                }
            }
            
            loadResources();
        }
    });
    
    // AI 리소스 선택 이벤트 처리
    $('#oauth_resource').on('change', function() {
        var value = $(this).val();
        
        if (value === '__CREATE_NEW__') {
            $('#new-ai-resource-form').slideDown(300);
            generateAIResourceName();
            
            // Resource Group의 location 설정
            var rgLocation = $('#new_ai_location').val();
            if (!rgLocation) {
                var selectedRg = $('#oauth_resource_group option:selected');
                $('#new_ai_location').val(selectedRg.data('location') || 'koreacentral');
            }
        } else {
            $('#new-ai-resource-form').slideUp(300);
        }
    });
    
    // 모드 변경 시 AI 리소스 이름 재생성
    $('input[name="oauth_mode"]').on('change', function() {
        generateAIResourceName();
    });
    
    // Location 변경 시 Resource Group 이름 재생성
    $('#new_rg_location').on('change', function() {
        if ($('input[name="rg_name_mode"]:checked').val() === 'auto') {
            generateResourceGroupName();
        }
    });
});
</script>

