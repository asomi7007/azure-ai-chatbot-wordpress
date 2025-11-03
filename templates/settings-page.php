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

// 엔드포인트에서 trailing slash 제거 (기존 저장된 값 정리)
if (!empty($options['chat_endpoint'])) {
    $options['chat_endpoint'] = rtrim($options['chat_endpoint'], '/');
}
if (!empty($options['agent_endpoint'])) {
    $options['agent_endpoint'] = rtrim($options['agent_endpoint'], '/');
}
?>

<div class="wrap azure-chatbot-settings">
    <h1>
        <span class="dashicons dashicons-admin-generic"></span>
        <?php esc_html_e('Azure AI Chatbot 설정', 'azure-ai-chatbot'); ?>
    </h1>
    
    <form method="post" action="options.php" id="azure-chatbot-settings-form">
        <?php settings_fields('azure_chatbot_settings_group'); ?>
        
        <div class="settings-container">
            <!-- API 설정 -->
            <div class="postbox">
                <h2 class="hndle">
                    <span class="dashicons dashicons-cloud"></span>
                    <?php esc_html_e('Azure AI Connection Settings', 'azure-ai-chatbot'); ?>
                    <button type="button" id="toggle-auto-setup" class="button button-secondary" style="float: right; margin-top: -3px;">
                        <span class="dashicons dashicons-admin-network" style="margin-top: 3px;"></span>
                        <?php esc_html_e('Auto Setting', 'azure-ai-chatbot'); ?>
                    </button>
                </h2>
                <div class="inside">
                    
                    <!-- Azure OAuth 자동 설정 (기본 숨김) -->
                    <div id="oauth-auto-setup-section" style="display: none; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                        <?php include AZURE_CHATBOT_PLUGIN_DIR . 'templates/oauth-auto-setup.php'; ?>
                    </div>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Operation Mode', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <fieldset>
                                    <label style="display: block; margin-bottom: 10px;">
                                        <input type="radio" 
                                               name="azure_chatbot_settings[mode]" 
                                               value="chat" 
                                               <?php checked($mode, 'chat'); ?>
                                               class="mode-radio" />
                                        <strong><?php esc_html_e('Chat 모드 (OpenAI 호환)', 'azure-ai-chatbot'); ?></strong> - <?php esc_html_e('간단한 대화형 챗봇 (API Key 인증)', 'azure-ai-chatbot'); ?>
                                    </label>
                                    <label style="display: block;">
                                        <input type="radio" 
                                               name="azure_chatbot_settings[mode]" 
                                               value="agent" 
                                               <?php checked($mode, 'agent'); ?>
                                               class="mode-radio" />
                                        <strong><?php esc_html_e('Agent 모드 (Azure AI Foundry)', 'azure-ai-chatbot'); ?></strong> - <?php esc_html_e('고급 에이전트 기능 (Entra ID 인증 필수)', 'azure-ai-chatbot'); ?>
                                    </label>
                                </fieldset>
                                <p class="description">
                                    • <?php esc_html_e('Chat 모드: Azure OpenAI 또는 OpenAI 호환 API 사용 (API Key 인증)', 'azure-ai-chatbot'); ?><br>
                                    • <?php esc_html_e('Agent 모드: Azure AI Foundry Assistants API 사용 (Entra ID 인증, threads/runs 지원)', 'azure-ai-chatbot'); ?><br>
                                    <a href="admin.php?page=azure-ai-chatbot-guide" target="_blank"><?php esc_html_e('설정 가이드 보기', 'azure-ai-chatbot'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Chat 모드 필드 -->
                        <tr class="mode-field mode-chat">
                            <th scope="row">
                                <label for="chat_provider"><?php esc_html_e('AI 제공자', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <select id="chat_provider" 
                                        name="azure_chatbot_settings[chat_provider]" 
                                        class="regular-text">
                                    <option value="azure-openai" <?php selected($options['chat_provider'] ?? 'azure-openai', 'azure-openai'); ?>>
                                        Azure OpenAI
                                    </option>
                                    <option value="openai" <?php selected($options['chat_provider'] ?? '', 'openai'); ?>>
                                        OpenAI
                                    </option>
                                    <option value="gemini" <?php selected($options['chat_provider'] ?? '', 'gemini'); ?>>
                                        Google Gemini
                                    </option>
                                    <option value="claude" <?php selected($options['chat_provider'] ?? '', 'claude'); ?>>
                                        Anthropic Claude
                                    </option>
                                    <option value="grok" <?php selected($options['chat_provider'] ?? '', 'grok'); ?>>
                                        xAI Grok
                                    </option>
                                    <option value="other" <?php selected($options['chat_provider'] ?? '', 'other'); ?>>
                                        <?php esc_html_e('기타 (OpenAI 호환)', 'azure-ai-chatbot'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('사용할 AI 제공자를 선택하세요', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-chat">
                            <th scope="row">
                                <label for="chat_endpoint"><?php esc_html_e('엔드포인트', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="chat_endpoint" 
                                       name="azure_chatbot_settings[chat_endpoint]" 
                                       value="<?php echo esc_attr($options['chat_endpoint'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="https://your-resource.openai.azure.com" />
                                <p class="description" id="endpoint-description">
                                    <?php esc_html_e('Azure OpenAI 엔드포인트', 'azure-ai-chatbot'); ?><br>
                                    <?php esc_html_e('예: https://your-resource.openai.azure.com', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-chat chat-field-deployment">
                            <th scope="row">
                                <label for="deployment_name"><?php esc_html_e('배포/모델 이름', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="deployment_name" 
                                       name="azure_chatbot_settings[deployment_name]" 
                                       value="<?php echo esc_attr($options['deployment_name'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="gpt-4o" />
                                <p class="description" id="deployment-description">
                                    <?php esc_html_e('Azure OpenAI 배포 이름 (예: gpt-4o, gpt-35-turbo)', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-chat">
                            <th scope="row">
                                <label for="api_key"><span id="api-key-label">API Key</span> *</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="api_key" 
                                       name="azure_chatbot_settings[api_key]" 
                                       value="<?php echo esc_attr($masked_api_key); ?>" 
                                       class="regular-text"
                                       placeholder="<?php esc_attr_e('API Key를 입력하세요', 'azure-ai-chatbot'); ?>" />
                                <button type="button" id="toggle-api-key" class="button">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description" id="api-key-description">
                                    <?php esc_html_e('API Key는 암호화되어 안전하게 저장됩니다. (AES-256 암호화)', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-chat chat-field-region" style="display: none;">
                            <th scope="row">
                                <label for="chat_region"><?php esc_html_e('리전', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="chat_region" 
                                       name="azure_chatbot_settings[chat_region]" 
                                       value="<?php echo esc_attr($options['chat_region'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="us-west-2" />
                                <p class="description" id="region-description">
                                    <?php esc_html_e('선택사항: 일부 제공자는 리전 지정이 필요할 수 있습니다', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Agent 모드 필드 -->
                        <tr class="mode-field mode-agent">
                            <th scope="row">
                                <label for="agent_endpoint"><?php esc_html_e('Agent 엔드포인트', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="agent_endpoint" 
                                       name="azure_chatbot_settings[agent_endpoint]" 
                                       value="<?php echo esc_attr($options['agent_endpoint'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="https://your-resource.services.ai.azure.com/api/projects/your-project" />
                                <p class="description">
                                    <strong><?php esc_html_e('프로젝트 경로 필수:', 'azure-ai-chatbot'); ?></strong><br>
                                    https://your-resource.services.ai.azure.com<strong>/api/projects/your-project</strong>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-agent">
                            <th scope="row">
                                <label for="agent_id"><?php esc_html_e('Agent ID', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="agent_id" 
                                       name="azure_chatbot_settings[agent_id]" 
                                       value="<?php echo esc_attr($options['agent_id'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="your-agent-id" />
                                <p class="description">
                                    <?php esc_html_e('Azure AI Foundry에서 생성한 Agent ID', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-agent">
                            <th scope="row">
                                <label for="client_id"><?php esc_html_e('Client ID (App ID)', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="client_id" 
                                       name="azure_chatbot_settings[client_id]" 
                                       value="<?php echo esc_attr($options['client_id'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />
                                <p class="description">
                                    <?php esc_html_e('Service Principal의 Application (Client) ID', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-agent">
                            <th scope="row">
                                <label for="client_secret"><?php esc_html_e('Client Secret', 'azure-ai-chatbot'); ?> *</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="client_secret" 
                                       name="azure_chatbot_settings[client_secret]" 
                                       value="<?php echo esc_attr($masked_client_secret); ?>" 
                                       class="regular-text"
                                       placeholder="<?php esc_attr_e('Client Secret을 입력하세요', 'azure-ai-chatbot'); ?>" />
                                <button type="button" id="toggle-client-secret" class="button">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    <?php esc_html_e('Client Secret은 암호화되어 안전하게 저장됩니다. (AES-256 암호화)', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="mode-field mode-agent">
                            <th scope="row">
                                <label for="tenant_id"><?php esc_html_e('Tenant ID', 'azure-ai-chatbot'); ?> *</label>
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
                                <label for="enabled"><?php esc_html_e('위젯 활성화', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="enabled" 
                                           name="azure_chatbot_settings[enabled]" 
                                           value="1" 
                                           <?php checked(!empty($options['enabled'])); ?> />
                                    <?php esc_html_e('채팅 위젯을 사이트에 표시합니다', 'azure-ai-chatbot'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="public_access"><?php esc_html_e('비로그인 사용자 접근 허용', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="public_access" 
                                           name="azure_chatbot_settings[public_access]" 
                                           value="1" 
                                           <?php checked(!empty($options['public_access']) || !isset($options['public_access'])); ?> />
                                    <?php esc_html_e('로그인하지 않은 사용자도 챗봇을 사용할 수 있습니다', 'azure-ai-chatbot'); ?>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('체크 해제 시 WordPress에 로그인한 사용자만 챗봇을 사용할 수 있습니다.', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p style="display: flex; align-items: center; gap: 10px;">
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-saved"></span>
                            <?php esc_html_e('설정 저장', 'azure-ai-chatbot'); ?>
                        </button>
                        <button type="button" id="test-connection" class="button button-secondary">
                            <span class="dashicons dashicons-arrow-right-alt"></span>
                            <?php esc_html_e('연결 테스트', 'azure-ai-chatbot'); ?>
                        </button>
                    </p>
                    
                    <!-- 테스트 결과 표시 영역 -->
                    <div id="test-result" style="margin-top: 15px;"></div>
                </div>
            </div>
            
            <!-- 외관 설정 -->
            <div class="postbox">
                <h2 class="hndle">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <?php esc_html_e('외관 설정', 'azure-ai-chatbot'); ?>
                </h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="chat_title"><?php esc_html_e('채팅 제목', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="chat_title" 
                                       name="azure_chatbot_settings[chat_title]" 
                                       value="<?php echo esc_attr($options['chat_title'] ?? __('AI 도우미', 'azure-ai-chatbot')); ?>" 
                                       class="regular-text" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="welcome_message"><?php esc_html_e('환영 메시지', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
                                <textarea id="welcome_message" 
                                          name="azure_chatbot_settings[welcome_message]" 
                                          rows="3" 
                                          class="large-text"><?php echo esc_textarea($options['welcome_message'] ?? __('안녕하세요! 무엇을 도와드릴까요?', 'azure-ai-chatbot')); ?></textarea>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="widget_position"><?php esc_html_e('위젯 위치', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
                                <select id="widget_position" name="azure_chatbot_settings[widget_position]">
                                    <option value="bottom-right" <?php selected($options['widget_position'] ?? 'bottom-right', 'bottom-right'); ?>>
                                        <?php esc_html_e('오른쪽 하단', 'azure-ai-chatbot'); ?>
                                    </option>
                                    <option value="bottom-left" <?php selected($options['widget_position'] ?? '', 'bottom-left'); ?>>
                                        <?php esc_html_e('왼쪽 하단', 'azure-ai-chatbot'); ?>
                                    </option>
                                    <option value="top-right" <?php selected($options['widget_position'] ?? '', 'top-right'); ?>>
                                        <?php esc_html_e('오른쪽 상단', 'azure-ai-chatbot'); ?>
                                    </option>
                                    <option value="top-left" <?php selected($options['widget_position'] ?? '', 'top-left'); ?>>
                                        <?php esc_html_e('왼쪽 상단', 'azure-ai-chatbot'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="primary_color"><?php esc_html_e('메인 색상', 'azure-ai-chatbot'); ?></label>
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
                                <label for="secondary_color"><?php esc_html_e('보조 색상', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="secondary_color" 
                                       name="azure_chatbot_settings[secondary_color]" 
                                       value="<?php echo esc_attr($options['secondary_color'] ?? '#764ba2'); ?>" 
                                       class="color-picker" />
                                <p class="description">
                                    <?php esc_html_e('메인 색상과 보조 색상으로 그라데이션이 적용됩니다.', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('미리보기', 'azure-ai-chatbot'); ?></label>
                            </th>
                            <td>
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
                                    <?php esc_html_e('설정을 변경하면 실시간으로 미리보기가 업데이트됩니다.', 'azure-ai-chatbot'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <?php submit_button(__('설정 저장', 'azure-ai-chatbot'), 'primary button-large', 'submit', false); ?>
            <a href="admin.php?page=azure-ai-chatbot-guide" class="button button-secondary button-large" style="margin-left: 10px;">
                <span class="dashicons dashicons-book"></span>
                <?php esc_html_e('사용 가이드 보기', 'azure-ai-chatbot'); ?>
            </a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('Settings page loaded');
    
    // Auto Setting 버튼 토글
    $('#toggle-auto-setup').on('click', function() {
        const section = $('#oauth-auto-setup-section');
        section.slideToggle(300);
        
        // 버튼 텍스트 변경
        const isVisible = section.is(':visible');
        const icon = isVisible ? 'dashicons-arrow-up-alt2' : 'dashicons-admin-network';
        $(this).find('.dashicons').attr('class', 'dashicons ' + icon);
    });
    
    // AI 제공자별 설정 정보 (PHP에서 번역된 텍스트 전달)
    const providerConfig = {
        'azure-openai': {
            endpointPlaceholder: 'https://your-resource.openai.azure.com',
            endpointDescription: '<strong><?php echo esc_js(__('Azure OpenAI 엔드포인트', 'azure-ai-chatbot')); ?></strong><br><?php echo esc_js(__('예: https://your-resource.openai.azure.com', 'azure-ai-chatbot')); ?>',
            deploymentLabel: '<?php echo esc_js(__('배포 이름', 'azure-ai-chatbot')); ?>',
            deploymentPlaceholder: 'gpt-4o',
            deploymentDescription: '<?php echo esc_js(__('Azure OpenAI 배포 이름 (예: gpt-4o, gpt-35-turbo)', 'azure-ai-chatbot')); ?>',
            apiKeyLabel: 'API Key',
            apiKeyDescription: '<?php echo esc_js(__('API Key는 암호화되어 안전하게 저장됩니다. (AES-256 암호화)', 'azure-ai-chatbot')); ?>',
            showDeployment: true,
            showRegion: false
        },
        'openai': {
            endpointPlaceholder: 'https://api.openai.com',
            endpointDescription: '<strong><?php echo esc_js(__('OpenAI API 엔드포인트', 'azure-ai-chatbot')); ?></strong><br><?php echo esc_js(__('기본값: https://api.openai.com (변경 불필요)', 'azure-ai-chatbot')); ?>',
            deploymentLabel: '<?php echo esc_js(__('모델 이름', 'azure-ai-chatbot')); ?>',
            deploymentPlaceholder: 'gpt-4-turbo',
            deploymentDescription: '<?php echo esc_js(__('OpenAI 모델 이름 (예: gpt-4-turbo, gpt-3.5-turbo, gpt-4o)', 'azure-ai-chatbot')); ?>',
            apiKeyLabel: 'API Key',
            apiKeyDescription: '<?php echo esc_js(__('OpenAI API Key (sk-로 시작)', 'azure-ai-chatbot')); ?><br><?php echo esc_js(__('암호화되어 안전하게 저장됩니다.', 'azure-ai-chatbot')); ?>',
            showDeployment: true,
            showRegion: false
        },
        'gemini': {
            endpointPlaceholder: 'https://generativelanguage.googleapis.com',
            endpointDescription: '<strong><?php echo esc_js(__('Google Gemini API 엔드포인트', 'azure-ai-chatbot')); ?></strong><br><?php echo esc_js(__('기본값: https://generativelanguage.googleapis.com', 'azure-ai-chatbot')); ?>',
            deploymentLabel: '<?php echo esc_js(__('모델 이름', 'azure-ai-chatbot')); ?>',
            deploymentPlaceholder: 'gemini-2.0-flash-exp',
            deploymentDescription: '<?php echo esc_js(__('Gemini 모델 이름 (예: gemini-2.0-flash-exp, gemini-1.5-pro, gemini-1.5-flash)', 'azure-ai-chatbot')); ?>',
            apiKeyLabel: 'API Key',
            apiKeyDescription: '<?php echo esc_js(__('Google AI Studio에서 발급받은 API Key', 'azure-ai-chatbot')); ?><br><?php echo esc_js(__('암호화되어 안전하게 저장됩니다.', 'azure-ai-chatbot')); ?>',
            showDeployment: true,
            showRegion: false
        },
        'claude': {
            endpointPlaceholder: 'https://api.anthropic.com',
            endpointDescription: '<strong><?php echo esc_js(__('Anthropic Claude API 엔드포인트', 'azure-ai-chatbot')); ?></strong><br><?php echo esc_js(__('기본값: https://api.anthropic.com', 'azure-ai-chatbot')); ?>',
            deploymentLabel: '<?php echo esc_js(__('모델 이름', 'azure-ai-chatbot')); ?>',
            deploymentPlaceholder: 'claude-3-5-sonnet-20241022',
            deploymentDescription: '<?php echo esc_js(__('Claude 모델 이름 (예: claude-3-5-sonnet-20241022, claude-3-opus-20240229)', 'azure-ai-chatbot')); ?>',
            apiKeyLabel: 'API Key',
            apiKeyDescription: '<?php echo esc_js(__('Anthropic API Key (sk-ant-로 시작)', 'azure-ai-chatbot')); ?><br><?php echo esc_js(__('암호화되어 안전하게 저장됩니다.', 'azure-ai-chatbot')); ?>',
            showDeployment: true,
            showRegion: false
        },
        'grok': {
            endpointPlaceholder: 'https://api.x.ai',
            endpointDescription: '<strong><?php echo esc_js(__('xAI Grok API 엔드포인트', 'azure-ai-chatbot')); ?></strong><br><?php echo esc_js(__('기본값: https://api.x.ai', 'azure-ai-chatbot')); ?>',
            deploymentLabel: '<?php echo esc_js(__('모델 이름', 'azure-ai-chatbot')); ?>',
            deploymentPlaceholder: 'grok-beta',
            deploymentDescription: '<?php echo esc_js(__('Grok 모델 이름 (예: grok-beta)', 'azure-ai-chatbot')); ?>',
            apiKeyLabel: 'API Key',
            apiKeyDescription: '<?php echo esc_js(__('xAI API Key', 'azure-ai-chatbot')); ?><br><?php echo esc_js(__('암호화되어 안전하게 저장됩니다.', 'azure-ai-chatbot')); ?>',
            showDeployment: true,
            showRegion: false
        },
        'other': {
            endpointPlaceholder: 'https://your-api-endpoint.com',
            endpointDescription: '<strong><?php echo esc_js(__('API 엔드포인트', 'azure-ai-chatbot')); ?></strong><br><?php echo esc_js(__('OpenAI 호환 API 엔드포인트를 입력하세요', 'azure-ai-chatbot')); ?>',
            deploymentLabel: '<?php echo esc_js(__('모델 이름', 'azure-ai-chatbot')); ?>',
            deploymentPlaceholder: 'model-name',
            deploymentDescription: '<?php echo esc_js(__('사용할 모델 이름을 입력하세요', 'azure-ai-chatbot')); ?>',
            apiKeyLabel: 'API Key',
            apiKeyDescription: '<?php echo esc_js(__('API Key는 암호화되어 안전하게 저장됩니다. (AES-256 암호화)', 'azure-ai-chatbot')); ?>',
            showDeployment: true,
            showRegion: false
        }
    };
    
    // 제공자에 따라 필드 업데이트
    function updateProviderFields() {
        const provider = $('#chat_provider').val();
        const config = providerConfig[provider] || providerConfig['azure-openai'];
        
        // 엔드포인트 필드 업데이트
        $('#chat_endpoint').attr('placeholder', config.endpointPlaceholder);
        $('#endpoint-description').html(config.endpointDescription);
        
        // 배포/모델 이름 필드 업데이트
        $('label[for="deployment_name"]').text(config.deploymentLabel + ' *');
        $('#deployment_name').attr('placeholder', config.deploymentPlaceholder);
        $('#deployment-description').html(config.deploymentDescription);
        
        // API Key 필드 업데이트
        $('#api-key-label').text(config.apiKeyLabel);
        $('#api-key-description').html(config.apiKeyDescription);
        
        // 필드 표시/숨김
        if (config.showDeployment) {
            $('.chat-field-deployment').show();
        } else {
            $('.chat-field-deployment').hide();
        }
        
        if (config.showRegion) {
            $('.chat-field-region').show();
        } else {
            $('.chat-field-region').hide();
        }
    }
    
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
            
            // 제공자별 필드 업데이트
            updateProviderFields();
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
    
    // 제공자 변경 시
    $('#chat_provider').on('change', function() {
        updateProviderFields();
    });
    
    // 엔드포인트 입력 시 trailing slash 자동 제거
    $('#chat_endpoint, #agent_endpoint').on('blur', function() {
        const $input = $(this);
        let value = $input.val().trim();
        
        // Trailing slash 제거
        if (value.endsWith('/')) {
            value = value.slice(0, -1);
            $input.val(value);
            
            // 사용자에게 알림
            const fieldName = $input.attr('id') === 'chat_endpoint' ? 'Chat 엔드포인트' : 'Agent 엔드포인트';
            console.log(`${fieldName}: 끝의 슬래시(/)가 자동으로 제거되었습니다.`);
        }
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
