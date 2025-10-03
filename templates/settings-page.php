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
                                <label for="endpoint">엔드포인트 URL *</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="endpoint" 
                                       name="azure_chatbot_settings[endpoint]" 
                                       value="<?php echo esc_attr($options['endpoint'] ?? ''); ?>" 
                                       class="regular-text"
                                       placeholder="https://your-project.cognitiveservices.azure.com/"
                                       required />
                                <p class="description">
                                    Azure AI Foundry 프로젝트의 엔드포인트 URL을 입력하세요.
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="api_key">API Key *</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="api_key" 
                                       name="azure_chatbot_settings[api_key]" 
                                       value="<?php echo esc_attr($masked_api_key); ?>" 
                                       class="regular-text"
                                       placeholder="API Key를 입력하세요"
                                       required />
                                <button type="button" id="toggle-api-key" class="button">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    API Key는 암호화되어 안전하게 저장됩니다. (AES-256 암호화)
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
    console.log('jQuery available:', typeof $ !== 'undefined');
    console.log('Admin data:', typeof azureChatbotAdmin !== 'undefined' ? azureChatbotAdmin : 'NOT DEFINED');
    console.log('Test button exists:', $('#test-connection').length);
    console.log('Toggle button exists:', $('#toggle-api-key').length);
    console.log('Color pickers:', $('.color-picker').length);
});
</script>
