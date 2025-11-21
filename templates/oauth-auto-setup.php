<?php
/**
 * Azure OAuth Auto Setup UI Template (cascading dropdown edition)
 */

if (!defined('ABSPATH')) {
	exit;
}

$oauth          = new Azure_Chatbot_OAuth();
$is_configured  = $oauth->is_configured();

// 세션 토큰 확인
if (!session_id() && !headers_sent()) {
	session_start();
}

$session_has_token = !empty($_SESSION['azure_access_token']);
$url_has_token     = isset($_GET['has_token']) && $_GET['has_token'] === '1';
$has_token         = $session_has_token || $url_has_token;

$settings       = get_option('azure_chatbot_settings', array());
$operation_mode = isset($settings['mode']) ? $settings['mode'] : 'chat';
$nonce          = wp_create_nonce('azure_oauth_nonce');

?>

<div class="postbox azure-oauth-section">
	<h2 class="hndle">
		<span class="dashicons dashicons-admin-network"></span>
		<?php esc_html_e('Azure 자동 설정 (OAuth)', 'azure-ai-chatbot'); ?>
	</h2>
	<div class="inside">
		<div class="notice notice-info inline" style="margin: 15px 0; padding: 15px; background: #f5f5f5; border-left: 4px solid #2271b1;">
			<h3 style="margin: 0 0 10px;">📍 <?php esc_html_e('선택된 모드', 'azure-ai-chatbot'); ?></h3>
			<p style="margin: 0; color: #333;">
				<strong><?php echo esc_html($operation_mode === 'agent' ? __('Agent 모드', 'azure-ai-chatbot') : __('Chat 모드', 'azure-ai-chatbot')); ?></strong>
				<span style="color:#777; margin-left:6px;">
					<?php echo esc_html($operation_mode === 'agent'
						? __('AI Foundry Agent (Assistants API)', 'azure-ai-chatbot')
						: __('Azure OpenAI (GPT 계열)', 'azure-ai-chatbot'));
					?>
				</span>
			</p>
			<p class="description" style="margin-top: 8px;">
				ℹ️ <?php esc_html_e('모드는 Manual Settings 탭에서 변경할 수 있습니다.', 'azure-ai-chatbot'); ?>
			</p>
		</div>

		<?php if (isset($_GET['oauth_success']) && $has_token): ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e('Azure 인증이 완료되었습니다. 아래 드롭다운에서 리소스를 차례대로 선택하세요.', 'azure-ai-chatbot'); ?></p>
			</div>
		<?php endif; ?>

		<?php if (isset($_GET['oauth_error'])): ?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo esc_html__('인증 실패: ', 'azure-ai-chatbot') . esc_html(get_transient('azure_oauth_error') ?: __('알 수 없는 오류', 'azure-ai-chatbot')); ?></p>
			</div>
			<?php delete_transient('azure_oauth_error'); ?>
		<?php endif; ?>

		<?php if (!$is_configured): ?>
			<div class="notice notice-warning inline" style="margin-bottom: 20px;">
				<p>
					<strong><?php esc_html_e('OAuth 자격 증명이 필요합니다.', 'azure-ai-chatbot'); ?></strong><br>
					<?php esc_html_e('Azure Portal에서 App Registration을 생성하거나 제공된 스크립트로 자동 설정을 완료하세요.', 'azure-ai-chatbot'); ?>
				</p>
			</div>
			<div style="background: #f0f6fc; border-left: 4px solid #2563eb; padding: 15px; border-radius: 4px;">
				<h3 style="margin-top: 0;">
					<span class="dashicons dashicons-info"></span>
					<?php esc_html_e('빠른 시작', 'azure-ai-chatbot'); ?>
				</h3>
				<?php
				$site_url      = get_site_url();
				$redirect_uri  = admin_url('admin.php?page=azure-ai-chatbot&azure_callback=1');
				$shell_command = "bash <(curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/scripts/setup-oauth-app.sh) " . esc_url($site_url);
				?>
				<ol style="margin-left: 20px;">
					<li>
						<?php esc_html_e('Azure Cloud Shell을 연 뒤 아래 명령을 실행하세요.', 'azure-ai-chatbot'); ?>
						<a href="https://shell.azure.com" target="_blank" class="button button-small" style="margin-left: 10px; vertical-align: middle;">
							<span class="dashicons dashicons-external"></span> <?php esc_html_e('Cloud Shell 열기', 'azure-ai-chatbot'); ?>
						</a>
					</li>
					<li style="margin-top: 10px;">
						<div style="position: relative;">
							<code id="setup-cmd" style="display:block; background:#111827; color:#f8fafc; padding:15px; padding-right: 80px; border-radius:4px; overflow-x: auto; font-family: Consolas, Monaco, 'Andale Mono', monospace;">
								<?php echo esc_html($shell_command); ?>
							</code>
							<button type="button" class="button button-small" onclick="copyToClipboard('setup-cmd')" style="position: absolute; top: 10px; right: 10px;">
								<span class="dashicons dashicons-clipboard" style="margin-top: 2px;"></span> <?php esc_html_e('복사', 'azure-ai-chatbot'); ?>
							</button>
						</div>
					</li>
					<li>
						<?php esc_html_e('생성된 Client ID / Client Secret / Tenant ID를 입력하고 저장합니다.', 'azure-ai-chatbot'); ?>
						<div style="margin-top: 15px; background: #fff; padding: 15px; border: 1px solid #dcdcde; border-radius: 4px;">
							<table class="form-table" role="presentation" style="margin-top: 0;">
								<tbody>
									<tr>
										<th scope="row" style="padding: 10px 0; width: 120px;"><label for="quick_client_id">Client ID</label></th>
										<td style="padding: 10px 0;">
											<input name="quick_client_id" type="text" id="quick_client_id" value="" class="regular-text" style="width: 100%;" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
										</td>
									</tr>
									<tr>
										<th scope="row" style="padding: 10px 0;"><label for="quick_client_secret">Client Secret</label></th>
										<td style="padding: 10px 0;">
											<input name="quick_client_secret" type="password" id="quick_client_secret" value="" class="regular-text" style="width: 100%;" placeholder="Value (not Secret ID)">
										</td>
									</tr>
									<tr>
										<th scope="row" style="padding: 10px 0;"><label for="quick_tenant_id">Tenant ID</label></th>
										<td style="padding: 10px 0;">
											<input name="quick_tenant_id" type="text" id="quick_tenant_id" value="" class="regular-text" style="width: 100%;" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
										</td>
									</tr>
								</tbody>
							</table>
							<p class="submit" style="padding: 10px 0 0; margin: 0;">
								<button type="button" id="save-quick-credentials" class="button button-primary">
									<?php esc_html_e('OAuth 설정 저장', 'azure-ai-chatbot'); ?>
								</button>
								<span id="quick-save-spinner" class="spinner" style="float: none; margin-top: 4px;"></span>
							</p>
						</div>
					</li>
					<li style="margin-top: 15px;">
						<?php esc_html_e('Redirect URI', 'azure-ai-chatbot'); ?>:
						<code><?php echo esc_html($redirect_uri); ?></code>
					</li>
				</ol>
				<p class="description" style="margin-top:10px;">
					📘 <a href="<?php echo esc_url(AZURE_CHATBOT_PLUGIN_URL . 'docs/AZURE_AUTO_SETUP.md'); ?>" target="_blank">
						<?php esc_html_e('상세 가이드 보기', 'azure-ai-chatbot'); ?>
					</a>
				</p>
			</div>
		<?php else: ?>
			<?php if (!$has_token): ?>
				<div class="oauth-step" style="margin:30px 0;">
					<h3>1. <?php esc_html_e('Azure 자동 설정 시작', 'azure-ai-chatbot'); ?></h3>
					<p><?php esc_html_e('버튼을 누르면 중앙에 OAuth 팝업이 열리고 인증이 끝나면 자동으로 닫힙니다.', 'azure-ai-chatbot'); ?></p>
					<p class="description" style="margin-bottom:12px;">
						<?php esc_html_e('브라우저 팝업 차단이 켜져 있다면 허용해 주세요. 창이 닫힌 뒤에는 이 화면이 자동으로 새로고침됩니다.', 'azure-ai-chatbot'); ?>
					</p>
					<p>
						<a href="<?php echo esc_url($oauth->get_authorization_url()); ?>"
						   class="button button-primary button-hero"
						   onclick="return openOAuthPopup(this.href);">
							<span class="dashicons dashicons-controls-play" style="margin-top:4px;"></span>
							<?php esc_html_e('Azure 자동 설정 시작', 'azure-ai-chatbot'); ?>
						</a>
					</p>
				</div>
			<?php endif; ?>

			<div class="oauth-step cascade-step" <?php if (!$has_token) echo 'style="opacity:0.4; pointer-events:none;"'; ?>>
				<h3>2. <?php esc_html_e('드롭다운으로 리소스 연결', 'azure-ai-chatbot'); ?></h3>
				<p class="description" style="margin-bottom: 20px;">
					<?php esc_html_e('아래 순서대로 값을 선택하면 Chat/Agent 설정이 자동으로 채워집니다.', 'azure-ai-chatbot'); ?>
				</p>

				<div class="azure-cascade-card" style="flex: 0 0 100%; max-width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; margin-bottom: 16px;">
					<div class="cascade-actions" style="justify-content: space-between; margin: 0;">
						<div style="display: flex; align-items: center; gap: 10px;">
							<span class="dashicons dashicons-info" style="color: #64748b;"></span>
							<span style="font-size: 13px; color: #475569;">
								<?php esc_html_e('설정에 문제가 있거나 처음부터 다시 설정하려면 초기화를 진행하세요.', 'azure-ai-chatbot'); ?>
							</span>
						</div>
						<div style="display: flex; gap: 10px;">
							<button type="button" id="reset-oauth-config" class="button button-link-delete" style="text-decoration: none;">
								<span class="dashicons dashicons-trash" style="margin-top: 3px;"></span>
								<?php esc_html_e('설정 초기화', 'azure-ai-chatbot'); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="azure-cascade-grid">
					<div class="azure-cascade-card">
						<h4>① <?php esc_html_e('Subscription', 'azure-ai-chatbot'); ?></h4>
						<select id="oauth_subscription" class="widefat" <?php if (!$has_token) echo 'disabled'; ?>>
							<option value=""><?php esc_html_e('OAuth 인증 후 사용할 수 있습니다.', 'azure-ai-chatbot'); ?></option>
						</select>
						<button type="button" class="button" id="refresh-subscriptions" style="margin-top:10px;">
							<span class="dashicons dashicons-update"></span>
							<?php esc_html_e('새로고침', 'azure-ai-chatbot'); ?>
						</button>
					</div>

					<div class="azure-cascade-card">
						<h4>② <?php esc_html_e('Azure Resource Group', 'azure-ai-chatbot'); ?></h4>
						<select id="oauth_resource_group" class="widefat" disabled>
							<option value=""><?php esc_html_e('먼저 Subscription을 선택하세요.', 'azure-ai-chatbot'); ?></option>
						</select>
					</div>

					<div class="azure-cascade-card">
						<h4>③ <?php esc_html_e('AI Foundry 프로젝트', 'azure-ai-chatbot'); ?></h4>
						<select id="foundry_project" class="widefat" disabled>
							<option value=""><?php esc_html_e('Resource Group을 먼저 선택하세요.', 'azure-ai-chatbot'); ?></option>
						</select>
						<p class="description" style="margin-top:8px;">
							<?php esc_html_e('Azure OpenAI 또는 AI Foundry 리소스가 자동으로 필터링됩니다.', 'azure-ai-chatbot'); ?>
						</p>
					</div>

					<div class="azure-cascade-card" id="chat-deployment-card">
						<h4>④ <?php esc_html_e('모델 배포 선택 (Chat)', 'azure-ai-chatbot'); ?></h4>
						<select id="chat_deployment" class="widefat" disabled>
							<option value=""><?php esc_html_e('프로젝트를 선택하면 배포 목록이 표시됩니다.', 'azure-ai-chatbot'); ?></option>
						</select>
						<p class="description" style="margin-top:8px;">
							<?php esc_html_e('선택 시 Endpoint / API Key가 자동 저장됩니다.', 'azure-ai-chatbot'); ?>
						</p>
					</div>

					<div class="azure-cascade-card" id="agent-card">
						<h4>④ <?php esc_html_e('Agent 선택 (Agent 모드)', 'azure-ai-chatbot'); ?></h4>
						<select id="agent_selector" class="widefat" disabled>
							<option value=""><?php esc_html_e('AI Foundry 프로젝트를 선택하면 Agent 목록이 표시됩니다.', 'azure-ai-chatbot'); ?></option>
						</select>
						<p class="description" style="margin-top:8px;">
							<?php esc_html_e('선택한 Agent의 Endpoint / ID가 자동 저장됩니다.', 'azure-ai-chatbot'); ?>
						</p>
					</div>
				</div>

				<div class="auto-setup-status">
					<div id="auto-setup-summary" data-status="idle">
						<strong><?php esc_html_e('상태: 대기 중', 'azure-ai-chatbot'); ?></strong>
						<p><?php esc_html_e('드롭다운을 순서대로 선택하면 진행 상황이 여기에 표시됩니다.', 'azure-ai-chatbot'); ?></p>
					</div>
					<pre id="auto-setup-log" class="auto-setup-log">[Log] Ready.</pre>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<style>
	.azure-cascade-grid {
		display: flex;
		flex-wrap: wrap;
		gap: 16px;
	}
	.azure-cascade-card {
		flex: 1 1 260px;
		background: #fff;
		border: 1px solid #dcdcde;
		border-radius: 6px;
		padding: 16px;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
	}
	.cascade-actions {
		display: flex;
		align-items: center;
		gap: 12px;
		margin: 0 0 16px;
	}
	.cascade-actions .description {
		margin: 0;
		color: #555;
	}
	.azure-cascade-card h4 {
		margin: 0 0 10px;
		font-size: 14px;
		color: #111;
	}
	.auto-setup-status {
		margin-top: 24px;
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
		gap: 16px;
	}
	#auto-setup-summary {
		border-left: 4px solid #2271b1;
		padding: 14px;
		background: #f8fbff;
		border-radius: 4px;
		min-height: 96px;
	}
	#auto-setup-summary[data-status="success"] {
		border-color: #22c55e;
		background: #f0fdf4;
	}
	#auto-setup-summary[data-status="error"] {
		border-color: #ef4444;
		background: #fef2f2;
	}
	.auto-setup-log {
		background: #0f172a;
		color: #e2e8f0;
		padding: 14px;
		border-radius: 4px;
		min-height: 120px;
		max-height: 220px;
		overflow-y: auto;
		font-family: Consolas, Monaco, monospace;
		font-size: 12px;
	}
	.auto-setup-log::-webkit-scrollbar {
		width: 6px;
	}
	.auto-setup-log::-webkit-scrollbar-thumb {
		background: rgba(148, 163, 184, 0.6);
		border-radius: 3px;
	}
	@keyframes rotation {
		from { transform: rotate(0deg); }
		to { transform: rotate(360deg); }
	}
</style>

<script>
(function($) {
	const autoSetup = {
		state: {
			hasToken: <?php echo $has_token ? 'true' : 'false'; ?>,
			isConfigured: <?php echo $is_configured ? 'true' : 'false'; ?>,
			mode: '<?php echo esc_js($operation_mode); ?>',
			nonce: '<?php echo esc_js($nonce); ?>',
			subscriptionId: null,
			resourceGroup: null,
			selectedResourceId: null,
			selectedResource: null,
			projectName: null,
			projectEndpoint: null,
			hubId: null,
			hubEndpoint: null,
			deploymentName: null,
			agentId: null,
			resourcesByRg: {},
			deploymentsByResource: {},
			agentsByResource: {}
		},
		ui: {},

		init() {
			this.cacheDom();
			this.bindEvents();
		},



		cacheDom() {
			this.ui.subscription   = $('#oauth_subscription');
			this.ui.resourceGroup  = $('#oauth_resource_group');
			this.ui.project        = $('#foundry_project');
			this.ui.deployment     = $('#chat_deployment');
			this.ui.agent          = $('#agent_selector');
			this.ui.summary        = $('#auto-setup-summary');
			this.ui.log            = $('#auto-setup-log');
			this.ui.refreshSubBtn  = $('#refresh-subscriptions');
			this.ui.chatCard       = $('#chat-deployment-card');
			this.ui.agentCard      = $('#agent-card');
			this.ui.resetConfigBtn = $('#reset-oauth-config');
		},

		bindEvents() {
			this.ui.refreshSubBtn.on('click', () => this.loadSubscriptions(true));
			this.ui.subscription.on('change', () => this.handleSubscriptionChange());
			this.ui.resourceGroup.on('change', () => this.handleResourceGroupChange());
			this.ui.project.on('change', () => this.handleProjectChange());
			this.ui.deployment.on('change', () => this.handleDeploymentChange());
			this.ui.agent.on('change', () => this.handleAgentChange());

			if (this.ui.resetConfigBtn.length) {
				this.ui.resetConfigBtn.on('click', () => this.handleConfigReset());
			}
			$('#save-quick-credentials').on('click', () => this.handleQuickSave());
		},

		payload(action, extra = {}) {
			return Object.assign({ action, nonce: this.state.nonce }, extra);
		},

		ajax(action, data) {
			return $.post(window.ajaxurl || '<?php echo esc_js(admin_url('admin-ajax.php')); ?>', this.payload(action, data));
		},

		setSelectLoading($select, placeholder) {
			$select.prop('disabled', true)
				.empty()
				.append($('<option>', { value: '', text: placeholder || '<?php echo esc_js(__('로딩 중...', 'azure-ai-chatbot')); ?>' }));
		},

		appendLog(message, meta) {
			const timestamp = new Date().toISOString();
			const line = `[${timestamp}] ${message}`;
			this.ui.log.text((this.ui.log.text() + '\n' + line).trim());
			if (meta) {
				console.log('[Auto Setup]', message, meta);
			} else {
				console.log('[Auto Setup]', message);
			}
		},

		updateSummary(status, title, description) {
			this.ui.summary.attr('data-status', status || 'idle');
			this.ui.summary.html(
				`<strong>${title || '<?php echo esc_js(__('상태 업데이트', 'azure-ai-chatbot')); ?>'}</strong>` +
				`<p style="margin:6px 0 0;">${description || ''}</p>`
			);
		},

		maskSecret(value) {
			if (!value || typeof value !== 'string') {
				return '';
			}
			const length = value.length;
			if (length <= 8) {
				return '•'.repeat(length);
			}
			return `${value.slice(0, 4)}${'•'.repeat(Math.max(0, length - 8))}${value.slice(-4)}`;
		},

		reflectManualFields(mode, payload = {}) {
			if (mode === 'chat') {
				if (payload.endpoint) {
					$('#chat_endpoint').val(payload.endpoint).trigger('change');
				}
				if (payload.deployment) {
					$('#deployment_name').val(payload.deployment).trigger('change');
				}
				if (payload.apiKeyMasked) {
					$('#api_key')
						.val(payload.apiKeyMasked)
						.attr('placeholder', '<?php echo esc_js(__('자동 저장됨', 'azure-ai-chatbot')); ?>')
						.trigger('change');
				}
			} else if (mode === 'agent') {
				if (payload.endpoint) {
					$('#agent_endpoint').val(payload.endpoint).trigger('change');
				}
				if (payload.agentId) {
					$('#agent_id').val(payload.agentId).trigger('change');
				}
			}
		},



		handleConfigReset() {
			if (!this.state.isConfigured || this.ui.resetConfigBtn.prop('disabled')) {
				return;
			}

			if (!window.confirm('<?php echo esc_js(__('정말로 모든 설정을 초기화하시겠습니까? Client ID, Secret, Tenant ID 및 모든 연결 정보가 삭제됩니다.', 'azure-ai-chatbot')); ?>')) {
				return;
			}

			this.appendLog('Resetting OAuth configuration...');
			const originalHtml = this.ui.resetConfigBtn.html();
			this.ui.resetConfigBtn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="margin-top:3px; animation: rotation 1.2s linear infinite;"></span> <?php echo esc_js(__('초기화 중...', 'azure-ai-chatbot')); ?>');

			this.ajax('azure_oauth_reset_config').done(() => {
				this.appendLog('Configuration reset complete. Reloading page.');
				window.location.reload();
			}).fail((xhr, status, error) => {
				this.appendLog('Failed to reset configuration', { status, error, response: xhr && xhr.responseText });
				this.ui.resetConfigBtn.prop('disabled', false).html(originalHtml);
				window.alert('<?php echo esc_js(__('설정 초기화에 실패했습니다.', 'azure-ai-chatbot')); ?>');
			});
		},

		handleQuickSave() {
			const clientId = $('#quick_client_id').val().trim();
			const clientSecret = $('#quick_client_secret').val().trim();
			const tenantId = $('#quick_tenant_id').val().trim();

			if (!clientId || !clientSecret || !tenantId) {
				alert('<?php echo esc_js(__('모든 필드(Client ID, Client Secret, Tenant ID)를 입력해주세요.', 'azure-ai-chatbot')); ?>');
				return;
			}

			const $btn = $('#save-quick-credentials');
			const $spinner = $('#quick-save-spinner');

			$btn.prop('disabled', true);
			$spinner.addClass('is-active');

			this.ajax('azure_oauth_save_credentials', {
				client_id: clientId,
				client_secret: clientSecret,
				tenant_id: tenantId
			}).done((response) => {
				if (response.success) {
					alert(response.data.message);
					window.location.reload();
				} else {
					alert(response.data.message || '<?php echo esc_js(__('저장 실패', 'azure-ai-chatbot')); ?>');
					$btn.prop('disabled', false);
					$spinner.removeClass('is-active');
				}
			}).fail((xhr, status, error) => {
				alert('<?php echo esc_js(__('서버 통신 오류가 발생했습니다.', 'azure-ai-chatbot')); ?>');
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
		},


		resetFrom(level) {
			switch (level) {
				case 'subscription':
					this.state.subscriptionId = null;
					this.state.resourceGroup = null;
					this.state.selectedResourceId = null;
					this.state.selectedResource = null;
					this.state.projectName = null;
					this.state.projectEndpoint = null;
					this.state.hubId = null;
					this.state.hubEndpoint = null;
					this.state.deploymentName = null;
					this.state.agentId = null;
					this.setSelectLoading(this.ui.resourceGroup, '<?php echo esc_js(__('Subscription을 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					this.setSelectLoading(this.ui.project, '<?php echo esc_js(__('Resource Group을 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					this.setSelectLoading(this.ui.deployment, '<?php echo esc_js(__('프로젝트를 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					this.setSelectLoading(this.ui.agent, '<?php echo esc_js(__('프로젝트를 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					break;
				case 'resourceGroup':
					this.state.resourceGroup = null;
					this.state.selectedResourceId = null;
					this.state.selectedResource = null;
					this.state.projectName = null;
					this.state.projectEndpoint = null;
					this.state.hubId = null;
					this.state.hubEndpoint = null;
					this.state.deploymentName = null;
					this.state.agentId = null;
					this.setSelectLoading(this.ui.project, '<?php echo esc_js(__('Resource Group을 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					this.setSelectLoading(this.ui.deployment, '<?php echo esc_js(__('프로젝트를 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					this.setSelectLoading(this.ui.agent, '<?php echo esc_js(__('프로젝트를 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					break;
				case 'project':
					this.state.selectedResourceId = null;
					this.state.selectedResource = null;
					this.state.projectName = null;
					this.state.projectEndpoint = null;
					this.state.hubId = null;
					this.state.hubEndpoint = null;
					this.state.deploymentName = null;
					this.state.agentId = null;
					this.setSelectLoading(this.ui.deployment, '<?php echo esc_js(__('프로젝트를 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					this.setSelectLoading(this.ui.agent, '<?php echo esc_js(__('프로젝트를 먼저 선택하세요.', 'azure-ai-chatbot')); ?>');
					break;
				case 'deployment':
					this.state.deploymentName = null;
					break;
				case 'agent':
					this.state.agentId = null;
					break;
			}
		},

		loadSubscriptions(force) {
			this.setSelectLoading(this.ui.subscription, '<?php echo esc_js(__('구독 목록을 불러오는 중...', 'azure-ai-chatbot')); ?>');
			this.resetFrom('subscription');
			this.updateSummary('loading', '<?php esc_html_e('구독 조회 중', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('Azure API에서 Subscription 정보를 가져오는 중입니다.', 'azure-ai-chatbot'); ?>');
			this.appendLog('Requesting subscription list...');

			this.ajax('azure_oauth_get_subscriptions').done((response) => {
				if (!response || !response.success) {
					throw new Error(response && response.data ? response.data.message : 'Unknown error');
				}

				const subs = response.data.subscriptions || [];
				this.ui.subscription.empty();
				this.ui.subscription.append($('<option>', { value: '', text: '<?php echo esc_js(__('Subscription을 선택하세요.', 'azure-ai-chatbot')); ?>' }));
				subs.forEach((sub) => {
					this.ui.subscription.append($('<option>', { value: sub.id, text: `${sub.name} (${sub.id})` }));
				});
				this.ui.subscription.prop('disabled', subs.length === 0);

				this.updateSummary('idle', '<?php esc_html_e('구독 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('사용할 Subscription을 선택하세요.', 'azure-ai-chatbot'); ?>');
				this.appendLog(`Loaded ${subs.length} subscription(s).`);

				if (subs.length === 1 && force !== true) {
					this.ui.subscription.val(subs[0].id).trigger('change');
				}
			}).fail((xhr, status, error) => {
				this.updateSummary('error', '<?php esc_html_e('구독 조회 실패', 'azure-ai-chatbot'); ?>', error || status);
				this.appendLog('Failed to load subscriptions', { status, error, response: xhr && xhr.responseText });
			});
		},

		loadResourceGroups(subscriptionId) {
			this.setSelectLoading(this.ui.resourceGroup, '<?php echo esc_js(__('Resource Group을 조회 중...', 'azure-ai-chatbot')); ?>');
			this.resetFrom('resourceGroup');
			this.updateSummary('loading', '<?php esc_html_e('Resource Group 조회', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('선택한 Subscription 안의 Resource Group을 확인합니다.', 'azure-ai-chatbot'); ?>');
			this.appendLog(`Loading resource groups for subscription ${subscriptionId}...`);

			this.ajax('azure_oauth_get_resource_groups', {
				subscription_id: subscriptionId
			}).done((response) => {
				if (!response || !response.success) {
					throw new Error(response && response.data ? response.data.message : 'Unknown error');
				}

				const groups = response.data.resource_groups || [];
				this.ui.resourceGroup.empty();
				this.ui.resourceGroup.append($('<option>', { value: '', text: '<?php echo esc_js(__('Resource Group을 선택하세요.', 'azure-ai-chatbot')); ?>' }));
				groups.forEach((rg) => {
					const label = `${rg.name} (${rg.location})`;
					this.ui.resourceGroup.append(
						$('<option>', { value: rg.name, text: label, 'data-location': rg.location })
					);
				});
				this.ui.resourceGroup.prop('disabled', groups.length === 0);
				this.appendLog(`Loaded ${groups.length} resource group(s).`);

				if (groups.length === 1) {
					this.ui.resourceGroup.val(groups[0].name).trigger('change');
				} else {
					this.updateSummary('idle', '<?php esc_html_e('Resource Group 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('사용할 Resource Group을 선택하세요.', 'azure-ai-chatbot'); ?>');
				}
			}).fail((xhr, status, error) => {
				this.updateSummary('error', '<?php esc_html_e('Resource Group 조회 실패', 'azure-ai-chatbot'); ?>', error || status);
				this.appendLog('Failed to load resource groups', { status, error, response: xhr && xhr.responseText });
			});
		},

		loadProjects() {
			if (!this.state.subscriptionId || !this.state.resourceGroup) {
				return;
			}

			if (this.state.mode === 'agent') {
				this.loadAgentProjects();
				return;
			}

			this.setSelectLoading(this.ui.project, '<?php echo esc_js(__('AI Foundry 프로젝트를 조회 중...', 'azure-ai-chatbot')); ?>');
			this.resetFrom('project');
			this.updateSummary('loading', '<?php esc_html_e('AI Foundry 프로젝트 조회', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('선택한 Resource Group 안의 프로젝트를 가져옵니다.', 'azure-ai-chatbot'); ?>');
			this.appendLog(`Loading projects for RG ${this.state.resourceGroup}...`);

			this.ajax('azure_oauth_get_resources', {
				subscription_id: this.state.subscriptionId,
				resource_group: this.state.resourceGroup,
				mode: this.state.mode
			}).done((response) => {
				if (!response || !response.success) {
					throw new Error(response && response.data ? response.data.message : 'Unknown error');
				}

				const resources = response.data.resources || [];
				this.state.resourcesByRg[this.state.resourceGroup] = resources;
				this.ui.project.empty();
				this.ui.project.append($('<option>', { value: '', text: '<?php echo esc_js(__('프로젝트를 선택하세요.', 'azure-ai-chatbot')); ?>' }));

				resources.forEach((resource) => {
					const label = `${resource.name} (${resource.location})`;
					this.ui.project.append(
						$('<option>', {
							value: resource.id,
							text: label,
							'data-kind': resource.kind || '',
							'data-endpoint': resource.endpoint || ''
						})
					);
				});

				this.ui.project.prop('disabled', resources.length === 0);
				this.appendLog(`Loaded ${resources.length} project/resource item(s).`);

				if (resources.length === 1) {
					this.ui.project.val(resources[0].id).trigger('change');
				} else {
					this.updateSummary('idle', '<?php esc_html_e('프로젝트 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('사용할 AI Foundry 프로젝트를 선택하세요.', 'azure-ai-chatbot'); ?>');
				}
			}).fail((xhr, status, error) => {
				this.updateSummary('error', '<?php esc_html_e('프로젝트 조회 실패', 'azure-ai-chatbot'); ?>', error || status);
				this.appendLog('Failed to load projects', { status, error, response: xhr && xhr.responseText });
			});
		},

		loadAgentProjects() {
			this.setSelectLoading(this.ui.project, '<?php echo esc_js(__('AI 프로젝트 목록을 조회 중...', 'azure-ai-chatbot')); ?>');
			this.resetFrom('project');
			this.updateSummary('loading', '<?php esc_html_e('AI Foundry 프로젝트 조회', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('허브에서 생성된 프로젝트를 불러옵니다.', 'azure-ai-chatbot'); ?>');
			this.appendLog(`Loading AI projects for RG ${this.state.resourceGroup}...`);

			this.ajax('azure_oauth_get_ai_projects', {
				subscription_id: this.state.subscriptionId,
				resource_group: this.state.resourceGroup
			}).done((response) => {
				if (!response || !response.success) {
					throw new Error(response && response.data ? response.data.message : 'Unknown error');
				}

				const projects = response.data.projects || [];
				this.state.resourcesByRg[this.state.resourceGroup] = projects;
				this.ui.project.empty();
				this.ui.project.append($('<option>', { value: '', text: '<?php echo esc_js(__('프로젝트를 선택하세요.', 'azure-ai-chatbot')); ?>' }));

				projects.forEach((project) => {
					const label = `${project.display_name || project.name} (${project.hub_name || project.location || '<?php echo esc_js(__('AI Hub', 'azure-ai-chatbot')); ?>'})`;
					this.ui.project.append(
						$('<option>', {
							value: project.id,
							text: label,
							'data-project': project.name,
							'data-hub-id': project.hub_id,
							'data-hub-endpoint': project.hub_endpoint,
							'data-project-endpoint': project.project_endpoint || project.hub_endpoint || '',
							'data-description': project.description || ''
						})
					);
				});

				this.ui.project.prop('disabled', projects.length === 0);
				this.appendLog(`Loaded ${projects.length} AI project(s).`);

				if (projects.length === 1) {
					this.ui.project.val(projects[0].id).trigger('change');
				} else if (projects.length === 0) {
					this.updateSummary('error', '<?php esc_html_e('프로젝트 없음', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('선택한 허브에 프로젝트가 없습니다. Azure AI Foundry에서 프로젝트를 생성하세요.', 'azure-ai-chatbot'); ?>');
				} else {
					this.updateSummary('idle', '<?php esc_html_e('프로젝트 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('사용할 프로젝트를 선택하세요.', 'azure-ai-chatbot'); ?>');
				}
			}).fail((xhr, status, error) => {
				this.updateSummary('error', '<?php esc_html_e('프로젝트 조회 실패', 'azure-ai-chatbot'); ?>', error || status);
				this.appendLog('Failed to load AI projects', { status, error, response: xhr && xhr.responseText });
			});
		},

		loadDeployments(resourceId) {
			this.setSelectLoading(this.ui.deployment, '<?php echo esc_js(__('배포 목록을 조회 중...', 'azure-ai-chatbot')); ?>');
			this.resetFrom('deployment');
			this.appendLog(`Loading deployments for resource ${resourceId}...`);

			this.ajax('azure_oauth_get_deployments', {
				resource_id: resourceId,
				subscription_id: this.state.subscriptionId,
				resource_group: this.state.resourceGroup
			}).done((response) => {
				if (!response || !response.success) {
					throw new Error(response && response.data ? response.data.message : 'Unknown error');
				}

				const deployments = response.data.deployments || [];
				this.state.deploymentsByResource[resourceId] = deployments;

				this.ui.deployment.empty();
				this.ui.deployment.append($('<option>', { value: '', text: '<?php echo esc_js(__('모델 배포를 선택하세요.', 'azure-ai-chatbot')); ?>' }));
				deployments.forEach((deployment) => {
					const label = `${deployment.name} (${deployment.model || 'model'})`;
					this.ui.deployment.append(
						$('<option>', { value: deployment.name, text: label, 'data-model': deployment.model || '' })
					);
				});
				this.ui.deployment.prop('disabled', deployments.length === 0);
				this.appendLog(`Loaded ${deployments.length} deployment(s).`);

				if (deployments.length === 1) {
					this.ui.deployment.val(deployments[0].name).trigger('change');
				} else if (deployments.length === 0) {
					this.updateSummary('error', '<?php esc_html_e('배포 없음', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('선택한 리소스에 배포된 모델이 없습니다.', 'azure-ai-chatbot'); ?>');
				} else {
					this.updateSummary('idle', '<?php esc_html_e('배포 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('사용할 모델 배포를 선택하세요.', 'azure-ai-chatbot'); ?>');
				}
			}).fail((xhr, status, error) => {
				this.updateSummary('error', '<?php esc_html_e('배포 조회 실패', 'azure-ai-chatbot'); ?>', error || status);
				this.appendLog('Failed to load deployments', { status, error, response: xhr && xhr.responseText });
			});
		},

		loadAgents(context) {
			this.setSelectLoading(this.ui.agent, '<?php echo esc_js(__('Agent 목록을 조회 중...', 'azure-ai-chatbot')); ?>');
			this.resetFrom('agent');

			const payload = {};
			let cacheKey = this.state.selectedResourceId;
			if (context && typeof context === 'object') {
				const projectId = context.projectId || this.state.selectedResourceId;
				cacheKey = context.projectCacheKey || projectId || context.hubId;
				payload.resource_id = projectId || context.hubId;
				if (context.hubId) {
					payload.hub_resource_id = context.hubId;
				}
				if (context.projectName) {
					payload.project_name = context.projectName;
				}
				if (context.hubEndpoint) {
					payload.hub_endpoint = context.hubEndpoint;
				}
				if (context.projectEndpoint) {
					payload.project_endpoint = context.projectEndpoint;
				}
				payload.project_resource_id = projectId;
				this.appendLog(`Loading agents for project ${context.projectName || projectId} (hub ${context.hubId || 'n/a'})...`);
			} else {
				payload.resource_id = context || this.state.selectedResourceId;
				cacheKey = payload.resource_id;
				this.appendLog(`Loading agents for resource ${payload.resource_id}...`);
			}

			if (!payload.resource_id) {
				this.updateSummary('error', '<?php esc_html_e('Agent 조회 실패', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('선택된 프로젝트 정보가 없습니다.', 'azure-ai-chatbot'); ?>');
				return;
			}

			this.ajax('azure_oauth_get_agents', payload).done((response) => {
				if (!response || !response.success) {
					throw new Error(response && response.data ? response.data.message : 'Unknown error');
				}

				const agents = response.data.agents || [];
				if (cacheKey) {
					this.state.agentsByResource[cacheKey] = agents;
				}

				this.ui.agent.empty();
				this.ui.agent.append($('<option>', { value: '', text: '<?php echo esc_js(__('Agent를 선택하세요.', 'azure-ai-chatbot')); ?>' }));
				agents.forEach((agent) => {
					const label = `${agent.name} (${agent.id || agent.name})`;
					this.ui.agent.append($('<option>', { value: agent.id || agent.name, text: label }));
				});
				this.ui.agent.prop('disabled', agents.length === 0);
				this.appendLog(`Loaded ${agents.length} agent(s).`);

				if (agents.length === 1) {
					this.ui.agent.val(agents[0].id || agents[0].name).trigger('change');
				} else if (agents.length === 0) {
					const message = response.data && response.data.message ? response.data.message : '<?php echo esc_js(__('Agent를 찾을 수 없습니다.', 'azure-ai-chatbot')); ?>';
					this.updateSummary('error', '<?php esc_html_e('Agent 없음', 'azure-ai-chatbot'); ?>', message);
				} else {
					this.updateSummary('idle', '<?php esc_html_e('Agent 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('사용할 Agent를 선택하세요.', 'azure-ai-chatbot'); ?>');
				}
			}).fail((xhr, status, error) => {
				this.updateSummary('error', '<?php esc_html_e('Agent 조회 실패', 'azure-ai-chatbot'); ?>', error || status);
				this.appendLog('Failed to load agents', { status, error, response: xhr && xhr.responseText });
			});
		},

		handleSubscriptionChange() {
			const value = this.ui.subscription.val();
			this.resetFrom('subscription');

			if (!value) {
				this.updateSummary('idle', '<?php esc_html_e('구독 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('Subscription을 선택하세요.', 'azure-ai-chatbot'); ?>');
				return;
			}

			this.state.subscriptionId = value;
			this.appendLog(`Subscription selected: ${value}`);
			this.loadResourceGroups(value);
		},

		handleResourceGroupChange() {
			const value = this.ui.resourceGroup.val();
			this.resetFrom('resourceGroup');

			if (!value) {
				this.updateSummary('idle', '<?php esc_html_e('Resource Group 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('Resource Group을 선택하세요.', 'azure-ai-chatbot'); ?>');
				return;
			}

			this.state.resourceGroup = value;
			this.appendLog(`Resource Group selected: ${value}`);
			this.loadProjects();
		},

		handleProjectChange() {
			const resourceId = this.ui.project.val();
			this.resetFrom('project');

			if (!resourceId) {
				this.updateSummary('idle', '<?php esc_html_e('프로젝트 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('프로젝트를 선택하세요.', 'azure-ai-chatbot'); ?>');
				return;
			}

			const resources = this.state.resourcesByRg[this.state.resourceGroup] || [];
			const selected = resources.find((res) => res.id === resourceId);
			this.state.selectedResourceId = resourceId;
			this.state.selectedResource = selected || null;
			const $selectedOption = this.ui.project.find('option:selected');

			this.appendLog('Project selected', selected);
			this.updateSummary('loading', '<?php esc_html_e('세부 정보 로드', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('선택한 프로젝트의 정보를 가져오는 중입니다.', 'azure-ai-chatbot'); ?>');

			if (this.state.mode === 'chat') {
				this.loadDeployments(resourceId);
			} else {
				this.state.projectName = (selected && (selected.name || selected.display_name)) || $selectedOption.data('project') || resourceId;
				this.state.hubId = (selected && selected.hub_id) || $selectedOption.data('hub-id') || resourceId;
				this.state.hubEndpoint = (selected && selected.hub_endpoint) || $selectedOption.data('hub-endpoint') || '';
				this.state.projectEndpoint = (selected && selected.project_endpoint) || $selectedOption.data('project-endpoint') || '';

				if (!this.state.projectName || !this.state.hubId) {
					this.updateSummary('error', '<?php esc_html_e('프로젝트 정보 누락', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('프로젝트 이름 또는 허브 정보를 찾을 수 없습니다.', 'azure-ai-chatbot'); ?>');
					return;
				}

				this.loadAgents({
					hubId: this.state.hubId,
					projectId: this.state.selectedResourceId,
					projectName: this.state.projectName,
					hubEndpoint: this.state.hubEndpoint,
					projectEndpoint: this.state.projectEndpoint,
					projectCacheKey: this.state.selectedResourceId
				});
			}
		},

		handleDeploymentChange() {
			const deployment = this.ui.deployment.val();
			if (!deployment) {
				this.state.deploymentName = null;
				this.updateSummary('idle', '<?php esc_html_e('배포 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('모델 배포를 선택하세요.', 'azure-ai-chatbot'); ?>');
				return;
			}

			this.state.deploymentName = deployment;
			this.appendLog(`Deployment selected: ${deployment}`);
			this.autoFillChatSettings();
		},

		handleAgentChange() {
			const agentId = this.ui.agent.val();
			if (!agentId) {
				this.state.agentId = null;
				this.updateSummary('idle', '<?php esc_html_e('Agent 선택 대기', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('Agent를 선택하세요.', 'azure-ai-chatbot'); ?>');
				return;
			}

			this.state.agentId = agentId;
			const cacheKey = this.state.selectedResourceId || this.state.hubId;
			const agents = this.state.agentsByResource[cacheKey] || [];
			const agent = agents.find((item) => (item.id || item.name) === agentId) || null;
			this.appendLog('Agent selected', agent);
			this.autoFillAgentSettings(agent);
		},

		autoFillChatSettings() {
			if (!this.state.selectedResource || !this.state.deploymentName) {
				return;
			}

			this.updateSummary('loading', '<?php esc_html_e('API Key 조회 중', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('선택한 리소스의 Endpoint와 Key를 불러옵니다.', 'azure-ai-chatbot'); ?>');
			this.appendLog('Fetching API key for chat mode...');

			this.ajax('azure_oauth_get_keys', {
				resource_id: this.state.selectedResourceId,
				subscription_id: this.state.subscriptionId,
				resource_group: this.state.resourceGroup
			}).done((response) => {
				if (!response || !response.success || !response.data || !response.data.key) {
					throw new Error(response && response.data ? (response.data.message || 'API key missing') : 'Unknown error');
				}

				const endpoint = response.data.endpoint || (this.state.selectedResource && this.state.selectedResource.endpoint)
					|| `https://${this.state.selectedResource.name}.openai.azure.com`;

				const settings = {
					mode: 'chat',
					chat_endpoint: endpoint,
					deployment_name: this.state.deploymentName,
					api_key: response.data.key
				};

				this.appendLog('Saving chat settings...', settings);
				this.ajax('azure_oauth_save_existing_config', { settings })
					.done((saveResponse) => {
						if (!saveResponse || !saveResponse.success) {
							throw new Error(saveResponse && saveResponse.data ? saveResponse.data.message : 'Unknown error');
						}

						this.updateSummary('success', '<?php esc_html_e('Chat 설정 완료', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('Endpoint, Deployment, API Key가 자동 저장되었습니다.', 'azure-ai-chatbot'); ?>');
						this.reflectManualFields('chat', {
							endpoint,
							deployment: this.state.deploymentName,
							apiKeyMasked: this.maskSecret(response.data.key)
						});
						this.appendLog('Chat configuration saved successfully.', saveResponse.data);
					})
					.fail((xhr, status, error) => {
						this.updateSummary('error', '<?php esc_html_e('Chat 설정 저장 실패', 'azure-ai-chatbot'); ?>', error || status);
						this.appendLog('Failed to save chat settings', { status, error, response: xhr && xhr.responseText });
					});
			}).fail((xhr, status, error) => {
				this.updateSummary('error', '<?php esc_html_e('API Key 조회 실패', 'azure-ai-chatbot'); ?>', error || status);
				this.appendLog('Failed to fetch API key', { status, error, response: xhr && xhr.responseText });
			});
		},

		autoFillAgentSettings(agent) {
			if (!this.state.selectedResource || !agent || !this.state.agentId) {
				return;
			}

			const resource = this.state.selectedResource;
			const projectBase = (this.state.projectEndpoint || '').replace(/\/$/, '');
			const hubBase = (this.state.hubEndpoint || '').replace(/\/$/, '');
			const resourceBase = (resource && resource.endpoint ? resource.endpoint : '').replace(/\/$/, '');
			const baseEndpoint = projectBase || hubBase || resourceBase || `https://${resource.name}.${resource.location}.services.ai.azure.com`;
			const projectSegment = this.state.projectName || resource.name;
			const agentEndpoint = baseEndpoint.includes('/api/projects/')
				? baseEndpoint
				: `${baseEndpoint}/api/projects/${projectSegment}`;

			const settings = {
				mode: 'agent',
				agent_endpoint: agentEndpoint,
				agent_id: agent.id || agent.name
			};

			this.updateSummary('loading', '<?php esc_html_e('Agent 설정 저장 중', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('선택한 Agent 정보를 저장합니다.', 'azure-ai-chatbot'); ?>');
			this.appendLog('Saving agent settings...', settings);

			this.ajax('azure_oauth_save_existing_config', { settings })
				.done((saveResponse) => {
					if (!saveResponse || !saveResponse.success) {
						throw new Error(saveResponse && saveResponse.data ? saveResponse.data.message : 'Unknown error');
					}

					this.updateSummary('success', '<?php esc_html_e('Agent 설정 완료', 'azure-ai-chatbot'); ?>', '<?php esc_html_e('Project / Agent ID / Endpoint가 자동 저장되었습니다.', 'azure-ai-chatbot'); ?>');
					this.reflectManualFields('agent', {
						endpoint: agentEndpoint,
						agentId: this.state.agentId
					});
					this.appendLog('Agent configuration saved successfully.', saveResponse.data);
				})
				.fail((xhr, status, error) => {
					this.updateSummary('error', '<?php esc_html_e('Agent 설정 저장 실패', 'azure-ai-chatbot'); ?>', error || status);
					this.appendLog('Failed to save agent settings', { status, error, response: xhr && xhr.responseText });
				});
		}
	};

	$(function() {
		autoSetup.init();
	});
})(jQuery);
</script>

<script>
function copyToClipboard(elementId) {
	var node = document.getElementById(elementId);
	var copyText = node.textContent || node.innerText;
	copyText = copyText.trim();

	if (navigator.clipboard && window.isSecureContext) {
		navigator.clipboard.writeText(copyText).then(function() {
			alert('<?php echo esc_js(__('명령어가 클립보드에 복사되었습니다.', 'azure-ai-chatbot')); ?>');
		}, function(err) {
			console.error('Async: Could not copy text: ', err);
			fallbackCopyTextToClipboard(copyText);
		});
	} else {
		fallbackCopyTextToClipboard(copyText);
	}
}

function fallbackCopyTextToClipboard(text) {
	var textArea = document.createElement("textarea");
	textArea.value = text;
	textArea.style.position = "fixed";  // Avoid scrolling to bottom
	document.body.appendChild(textArea);
	textArea.focus();
	textArea.select();

	try {
		var successful = document.execCommand('copy');
		if (successful) {
			alert('<?php echo esc_js(__('명령어가 클립보드에 복사되었습니다.', 'azure-ai-chatbot')); ?>');
		} else {
			console.error('Fallback: Oops, unable to copy');
		}
	} catch (err) {
		console.error('Fallback: Oops, unable to copy', err);
	}

	document.body.removeChild(textArea);
}

function openOAuthPopup(url) {
	var width = 720;
	var height = 840;
	var left = (window.screenX || window.screenLeft || 0) + (window.innerWidth - width) / 2;
	var top = (window.screenY || window.screenTop || 0) + (window.innerHeight - height) / 2;
	var popup = window.open(url, 'azure-oauth', 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',resizable=yes,scrollbars=yes');

	if (!popup) {
		alert('<?php echo esc_js(__('팝업이 차단되었습니다. 팝업을 허용한 뒤 다시 시도하세요.', 'azure-ai-chatbot')); ?>');
		return false;
	}

	popup.focus();
	return false;
}
</script>
