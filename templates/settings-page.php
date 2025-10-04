<?php
if (!defined('ABSPATH')) exit;

$plugin = Azure_AI_Chatbot::get_instance();
$options = get_option('azure_chatbot_settings', []);

// API Key 마스킹
$masked_api_key = '';
if (!empty($options['api_key_encrypted'])) {
    $decrypted_key = $plugin->decrypt($options['api_key_encrypted']);
    if ($decrypted_key && strlen($decrypted_key) > 8) {
        $masked_api_key = substr($decrypted_key, 0, 4) . str_repeat('•', strlen($decrypted_key) - 8) . substr($decrypted_key, -4);
    } else {
        $masked_api_key = str_repeat('•', strlen($decrypted_key));
    }
}

// Client Secret 마스킹
$masked_client_secret = '';
if (!empty($options['client_secret_encrypted'])) {
    $decrypted_secret = $plugin->decrypt($options['client_secret_encrypted']);
    if ($decrypted_secret && strlen($decrypted_secret) > 8) {
        $masked_client_secret = substr($decrypted_secret, 0, 4) . str_repeat('•', strlen($decrypted_secret) - 8) . substr($decrypted_secret, -4);
    } else {
        $masked_client_secret = str_repeat('•', strlen($decrypted_secret));
    }
}

// 현재 인증 방식
$auth_type = $options['auth_type'] ?? 'api_key';
?>

<div class="wrap azure-chatbot-settings">
    <h1>
        <span class="dashicons dashicons-admin-generic"></span>
        Azure AI Chatbot 설정
    </h1>
    
    <form method="post" action="options.php" id="azure-chatbot-settings-form">
        <?php settings_fields('azure_chatbot_settings_group'); ?>
        
        <div class="settings-container">
            <!-- API 설정 -->
            <div class="postbox">
                <h2 class="hndle">
                    <span class="dashicons dashicons-cloud"></span>
                    Azure AI 연결 설정
                </h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label>인증 방식 *</label>
                            </th>
                            <td>
                                <fieldset>
                                    <label style="display: block; margin-bottom: 10px;">
                                        <input type="radio" 
                                               name="azure_chatbot_settings[auth_type]" 
                                               value="api_key" 
                                               <?php checked($auth_type, 'api_key'); ?>
                                               class="auth-type-radio" />
                                        <strong>API Key 인증</strong> - 간단한 설정 (계정 수준 접근)
                                    </label>
                                    <label style="display: block;">
                                        <input type="radio" 
                                               name="azure_chatbot_settings[auth_type]" 
                                               value="entra_id" 
                                               <?php checked($auth_type, 'entra_id'); ?>
                                               class="auth-type-radio" />
                                        <strong>Entra ID (Service Principal)</strong> - 보안 강화, 프로젝트 수준 접근 (권장)
                                    </label>
                                </fieldset>
                                <p class="description">
                                    Azure AI Foundry Project API 사용 시 Entra ID 인증이 필요합니다.
                                    <a href="admin.php?page=azure-ai-chatbot-guide" target="_blank">설정 가이드 보기</a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="endpoint">엔드포인트 URL *</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="endpoint" 
                                       name="azure_chatbot_settings[endpoint]" 
                                       value="<?php echo esc_attr($options['endpoint'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="https://your-resource.services.ai.azure.com/api/projects/your-project"
                                       required />
                                <p class="description" id="endpoint-hint-apikey" style="display: none;">
                                    예: https://your-resource.cognitiveservices.azure.com/
                                </p>
                                <p class="description" id="endpoint-hint-entraid" style="display: none;">
                                    <strong>Entra ID 인증 시 프로젝트 경로 필수:</strong><br>
                                    https://your-resource.services.ai.azure.com<strong>/api/projects/your-project</strong>
                                </p>
                            </td>
                        </tr>
                        
                        <!-- API Key 인증 필드 -->
                        <tr class="auth-field auth-apikey">
                            <th scope="row">
                                <label for="api_key">API Key *</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="api_key" 
                                       name="azure_chatbot_settings[api_key]" 
                                       value="<?php echo esc_attr($masked_api_key); ?>" 
                                       class="regular-text"
                                       placeholder="API Key를 입력하세요" />
                                <button type="button" id="toggle-api-key" class="button">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    API Key는 암호화되어 안전하게 저장됩니다. (AES-256 암호화)
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Entra ID 인증 필드 -->
                        <tr class="auth-field auth-entraid">
                            <th scope="row">
                                <label for="client_id">Client ID (App ID) *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="client_id" 
                                       name="azure_chatbot_settings[client_id]" 
                                       value="<?php echo esc_attr($options['client_id'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />
                                <p class="description">
                                    Service Principal의 Application (Client) ID를 입력하세요.
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="auth-field auth-entraid">
                            <th scope="row">
                                <label for="client_secret">Client Secret *</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="client_secret" 
                                       name="azure_chatbot_settings[client_secret]" 
                                       value="<?php echo esc_attr($masked_client_secret); ?>" 
                                       class="regular-text"
                                       placeholder="Client Secret을 입력하세요" />
                                <button type="button" id="toggle-client-secret" class="button">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    Client Secret은 암호화되어 안전하게 저장됩니다. (AES-256 암호화)
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="auth-field auth-entraid">
                            <th scope="row">
                                <label for="tenant_id">Tenant ID *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="tenant_id" 
                                       name="azure_chatbot_settings[tenant_id]" 
                                       value="<?php echo esc_attr($options['tenant_id'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />
                                <p class="description">
                                    Azure Entra ID (Azure AD) Tenant ID를 입력하세요.
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="agent_id">에이전트 ID *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="agent_id" 
                                       name="azure_chatbot_settings[agent_id]" 
                                       value="<?php echo esc_attr($options['agent_id'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="your-agent-id"
                                       required />
                                <p class="description">
                                    Azure AI Foundry에서 생성한 에이전트의 ID를 입력하세요.
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="enabled">위젯 활성화</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="enabled" 
                                           name="azure_chatbot_settings[enabled]" 
                                           value="1" 
                                           <?php checked(!empty($options['enabled'])); ?> />
                                    채팅 위젯을 사이트에 표시합니다
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <p>
                        <button type="button" id="test-connection" class="button button-secondary">
                            <span class="dashicons dashicons-arrow-right-alt"></span>
                            연결 테스트
                        </button>
                        <span id="test-result" style="margin-left: 10px;"></span>
                    </p>
                </div>
            </div>
            
            <!-- 외관 설정 -->
            <div class="postbox">
                <h2 class="hndle">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    외관 설정
                </h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="chat_title">채팅 제목</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="chat_title" 
                                       name="azure_chatbot_settings[chat_title]" 
                                       value="<?php echo esc_attr($options['chat_title'] ?? 'AI 도우미'); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="welcome_message">환영 메시지</label>
                            </th>
                            <td>
                                <textarea id="welcome_message" 
                                          name="azure_chatbot_settings[welcome_message]" 
                                          rows="3" 
                                          class="large-text"><?php echo esc_textarea($options['welcome_message'] ?? '안녕하세요! 무엇을 도와드릴까요?'); ?></textarea>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="widget_position">위젯 위치</label>
                            </th>
                            <td>
                                <select id="widget_position" name="azure_chatbot_settings[widget_position]">
                                    <option value="bottom-right" <?php selected($options['widget_position'] ?? 'bottom-right', 'bottom-right'); ?>>
                                        오른쪽 하단
                                    </option>
                                    <option value="bottom-left" <?php selected($options['widget_position'] ?? '', 'bottom-left'); ?>>
                                        왼쪽 하단
                                    </option>
                                    <option value="top-right" <?php selected($options['widget_position'] ?? '', 'top-right'); ?>>
                                        오른쪽 상단
                                    </option>
                                    <option value="top-left" <?php selected($options['widget_position'] ?? '', 'top-left'); ?>>
                                        왼쪽 상단
                                    </option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="primary_color">메인 색상</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="primary_color" 
                                       name="azure_chatbot_settings[primary_color]" 
                                       value="<?php echo esc_attr($options['primary_color'] ?? '#667eea'); ?>" 
                                       class="color-picker" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="secondary_color">보조 색상</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="secondary_color" 
                                       name="azure_chatbot_settings[secondary_color]" 
                                       value="<?php echo esc_attr($options['secondary_color'] ?? '#764ba2'); ?>" 
                                       class="color-picker" />
                                <p class="description">
                                    메인 색상과 보조 색상으로 그라데이션이 적용됩니다.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- 미리보기 -->
            <div class="postbox">
                <h2 class="hndle">
                    <span class="dashicons dashicons-visibility"></span>
                    미리보기
                </h2>
                <div class="inside">
                    <div id="widget-preview" style="position: relative; height: 200px; background: #f0f0f0; border-radius: 8px; overflow: hidden;">
                        <div class="preview-widget" style="position: absolute; bottom: 20px; right: 20px;">
                            <button class="preview-toggle" style="width: 60px; height: 60px; border-radius: 50%; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
                                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="description">
                        설정을 변경하면 실시간으로 미리보기가 업데이트됩니다.
                    </p>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary button-large">
                <span class="dashicons dashicons-saved"></span>
                변경사항 저장
            </button>
            <a href="admin.php?page=azure-ai-chatbot-guide" class="button button-secondary button-large">
                <span class="dashicons dashicons-book"></span>
                사용 가이드 보기
            </a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('Settings page loaded');
    
    // 인증 방식에 따라 필드 표시/숨김
    function toggleAuthFields() {
        const authType = $('input[name="azure_chatbot_settings[auth_type]"]:checked').val();
        
        if (authType === 'api_key') {
            $('.auth-apikey').show();
            $('.auth-entraid').hide();
            $('#endpoint-hint-apikey').show();
            $('#endpoint-hint-entraid').hide();
            
            // API Key는 필수, Entra ID 필드는 선택
            $('#api_key').prop('required', true);
            $('#client_id, #client_secret, #tenant_id').prop('required', false);
        } else {
            $('.auth-apikey').hide();
            $('.auth-entraid').show();
            $('#endpoint-hint-apikey').hide();
            $('#endpoint-hint-entraid').show();
            
            // Entra ID 필드는 필수, API Key는 선택
            $('#api_key').prop('required', false);
            $('#client_id, #client_secret, #tenant_id').prop('required', true);
        }
    }
    
    // 초기 로드 시 필드 표시
    toggleAuthFields();
    
    // 인증 방식 변경 시
    $('.auth-type-radio').on('change', function() {
        toggleAuthFields();
    });
    
    // Client Secret 토글 버튼
    $('#toggle-client-secret').on('click', function(e) {
        e.preventDefault();
        const $input = $('#client_secret');
        const $icon = $(this).find('.dashicons');
        
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });
});
</script>
