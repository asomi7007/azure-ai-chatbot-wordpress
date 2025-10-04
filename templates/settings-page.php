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

// 현재 모드
$mode = $options['mode'] ?? 'chat';
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
                                <label>작동 모드 *</label>
                            </th>
                            <td>
                                <fieldset>
                                    <label style="display: block; margin-bottom: 10px;">
                                        <input type="radio" 
                                               name="azure_chatbot_settings[mode]" 
                                               value="chat" 
                                               <?php checked($mode, 'chat'); ?>
                                               class="mode-radio" />
                                        <strong>Chat 모드 (OpenAI 호환)</strong> - 간단한 대화형 챗봇 (API Key 인증)
                                    </label>
                                    <label style="display: block;">
                                        <input type="radio" 
                                               name="azure_chatbot_settings[mode]" 
                                               value="agent" 
                                               <?php checked($mode, 'agent'); ?>
                                               class="mode-radio" />
                                        <strong>Agent 모드 (Azure AI Foundry)</strong> - 고급 에이전트 기능 (Entra ID 인증 필수)
                                    </label>
                                </fieldset>
                                <p class="description">
                                    • Chat 모드: Azure OpenAI 또는 OpenAI 호환 API 사용 (API Key 인증)<br>
                                    • Agent 모드: Azure AI Foundry Assistants API 사용 (Entra ID 인증, threads/runs 지원)<br>
                                    <a href="admin.php?page=azure-ai-chatbot-guide" target="_blank">설정 가이드 보기</a>
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Chat 모드 필드 -->
                        <tr class="mode-field mode-chat">
                            <th scope="row">
                                <label for="chat_endpoint">Chat 엔드포인트 *</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="chat_endpoint" 
                                       name="azure_chatbot_settings[chat_endpoint]" 
                                       value="<?php echo esc_attr($options['chat_endpoint'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="https://your-resource.openai.azure.com" />
                                <p class="description">
                                    Azure OpenAI 엔드포인트<br>
                                    예: https://your-resource.openai.azure.com
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-chat">
                            <th scope="row">
                                <label for="deployment_name">배포 이름 *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="deployment_name" 
                                       name="azure_chatbot_settings[deployment_name]" 
                                       value="<?php echo esc_attr($options['deployment_name'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="gpt-4o" />
                                <p class="description">
                                    Azure OpenAI 배포 이름 (예: gpt-4o, gpt-35-turbo)
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-chat">
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
                        
                        <!-- Agent 모드 필드 -->
                        <tr class="mode-field mode-agent">
                            <th scope="row">
                                <label for="agent_endpoint">Agent 엔드포인트 *</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="agent_endpoint" 
                                       name="azure_chatbot_settings[agent_endpoint]" 
                                       value="<?php echo esc_attr($options['agent_endpoint'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="https://your-resource.services.ai.azure.com/api/projects/your-project" />
                                <p class="description">
                                    <strong>프로젝트 경로 필수:</strong><br>
                                    https://your-resource.services.ai.azure.com<strong>/api/projects/your-project</strong>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-agent">
                            <th scope="row">
                                <label for="agent_id">Agent ID *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="agent_id" 
                                       name="azure_chatbot_settings[agent_id]" 
                                       value="<?php echo esc_attr($options['agent_id'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="your-agent-id" />
                                <p class="description">
                                    Azure AI Foundry에서 생성한 Agent ID
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-agent">
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
                                    Service Principal의 Application (Client) ID
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-agent">
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
                        
                        <tr class="mode-field mode-agent">
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
                                    Azure Entra ID (Azure AD) Tenant ID
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
                    
                    <p style="display: flex; align-items: center; gap: 10px;">
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-saved"></span>
                            설정 저장
                        </button>
                        <button type="button" id="test-connection" class="button button-secondary">
                            <span class="dashicons dashicons-arrow-right-alt"></span>
                            연결 테스트
                        </button>
                        <span id="test-result"></span>
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
    
    // 모드에 따라 필드 표시/숨김
    function toggleAuthFields() {
        const mode = $('input[name="azure_chatbot_settings[mode]"]:checked').val();
        
        if (mode === 'chat') {
            // Chat 모드: API Key 인증
            $('.mode-chat').show();
            $('.mode-agent').hide();
            
            // Chat 필드는 필수, Agent 필드는 선택
            $('#chat_endpoint, #deployment_name, #api_key').prop('required', true);
            $('#agent_endpoint, #agent_id, #client_id, #client_secret, #tenant_id').prop('required', false);
        } else {
            // Agent 모드: Entra ID 인증
            $('.mode-chat').hide();
            $('.mode-agent').show();
            
            // Agent 필드는 필수, Chat 필드는 선택
            $('#chat_endpoint, #deployment_name, #api_key').prop('required', false);
            $('#agent_endpoint, #agent_id, #client_id, #client_secret, #tenant_id').prop('required', true);
        }
    }
    
    // 초기 로드 시 필드 표시
    toggleAuthFields();
    
    // 모드 변경 시
    $('.mode-radio').on('change', function() {
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
