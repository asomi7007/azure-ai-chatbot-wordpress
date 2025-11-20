# Azure AI Chatbot WordPress – Project Brief

## Project Goal
- Deliver a WordPress plugin that connects Azure AI Foundry Agents and Azure OpenAI chat deployments through a zero-code OAuth 2.0 auto-setup flow.
- Support both Chat (API-key based) and Agent (Assistants API + Entra ID) modes so site owners can toggle between lightweight Q&A and advanced function-calling experiences.
- Make onboarding approachable for Korean and English administrators with inline guidance, cascading resource selectors, and secure credential handling (AES-256 at rest, short-lived OAuth tokens in session).

## Recent Worklog (Nov 2025)
1. **Inline OAuth UI overhaul** – replaced popup-only resource pickers with cascading dropdown cards inside `templates/oauth-auto-setup.php`, including live logs and summaries.
2. **Auto-setup button resurrection** – restored the familiar "Azure 자동 설정 시작" popup trigger, ensuring the popup recenters and auto-closes after the callback completes.
3. **OAuth session reset** – reintroduced the "인증 초기화" control that clears the stored token/nonce via `azure_oauth_clear_session`, with spinner feedback and log entries.
4. **AI Foundry project filtering** – added `azure_oauth_get_ai_projects` plus `get_ai_service_access_token()` so Agent mode now lists true AI Foundry projects (via data-plane endpoint) before fetching agents.
5. **Agent auto-fill fixes** – Agent endpoints now follow `/api/projects/{project}` convention, ensuring values saved to settings align with what the Assistants API handler expects.
6. **Packaging script validation** – refreshed `create-zip-new.ps1` usage to auto-detect plugin version (3.0.58), purge temp artifacts, compress with Bandizip when available, and verify critical files post-build.
7. **AI Foundry Project Listing Fix (v2)** – Further refined `ajax_get_ai_projects` to directly treat non-Hub `Microsoft.MachineLearningServices/workspaces` as Projects. This ensures projects are listed even if the Hub API lookup fails or isn't applicable, following Azure Best Practices for resource discovery.

## Key Files & Directories
| Path | Description |
| --- | --- |
| `azure-ai-chatbot.php` | Main plugin bootstrap: registers admin pages, AJAX handlers, REST endpoints, and links Chat/Agent runtime to WordPress options. |
| `templates/oauth-auto-setup.php` | Admin UI for OAuth-based onboarding; hosts cascading dropdown workflow, inline logs, and popup trigger. |
| `includes/class-azure-oauth.php` | Server-side OAuth manager; handles callbacks, Azure REST calls, AI Foundry project listing, key retrieval, and AJAX endpoints. |
| `assets/admin.js`, `assets/admin.css` | Admin-only scripts and styles powering live previews, AJAX helpers, and UI polish. |
| `docs/` | Markdown/HTML guides (setup, logs, user manual) referenced from the admin help links. |
| `languages/` | PO/MO translation files plus helper scripts for compiling (`compile-po-to-mo.php`, `.py`). |
| `scripts/` | Shell automation such as `setup-oauth-app.sh` for Cloud Shell provisioning of App Registrations. |
| `templates/settings-page.php` | Manual configuration screen for Chat/Agent parameters, including validation and connection tests. |
| `create-zip-new.ps1` | PowerShell packager: copies whitelisted folders, builds `azure-ai-chatbot-wordpress-<version>.zip`, and verifies file hashes/patterns. |

## Build: Packaging ZIP
1. Open Windows PowerShell (7.x recommended) and switch to the repo root.
2. Run the packaging helper. It auto-detects the plugin version from `azure-ai-chatbot.php`, wipes old ZIPs/temp dirs, copies whitelisted assets, and compresses with Bandizip (level 9) or .NET fallback. The script then inspects contents, hashes critical files, and copies the final path to the clipboard.

```powershell
Set-Location -Path "c:\Users\asomi\OneDrive - 엘던솔루션\작업용\AzureAI\azure-ai-chatbot-wordpress"
.\create-zip-new.ps1
```

_Output_: `azure-ai-chatbot-wordpress-3.0.58.zip` in the repository root.

If the verification phase flags missing regex patterns (because templates changed), review the warning, confirm the file content, and update the regex in `create-zip-new.ps1` if needed before re-running.

## Deployment Workflow (WordPress)
1. **Upload the ZIP**: In WordPress admin, go to `Plugins → Add New → Upload Plugin`, choose the generated ZIP, and activate it. For upgrades, deactivate the old version first, upload the new ZIP, then activate.
2. **Initial configuration**: Navigate to `AI Chatbot → OAuth Auto-Setup`. Click **Azure 자동 설정 시작**, complete Microsoft login, and wait for the popup to close automatically.
3. **Resource selection**: Use the cascading dropdowns to choose Subscription → Resource Group → (AI Foundry Project or OpenAI deployment). Agent mode now shows actual AI Foundry projects and agents; Chat mode lists Azure OpenAI deployments. Logs and summary boxes confirm each step.
4. **Auto-save check**: Once the status panel shows "설정 완료", switch to the **Manual Settings** tab to confirm the fields are populated (Chat endpoint/deployment/API key or Agent endpoint/ID/Entra credentials).
5. **Smoke test**: Use the built-in "연결 테스트" button on the settings page to ensure API reachability. For Agent mode, confirm the Assistants API can create threads and runs.
6. **Production rollout**: Embed the chatbot widget (shortcode or block) on target pages. Monitor Azure usage via the `docs/AZURE_LOGS_GUIDE.md` references and WordPress debug logs as needed.

_Optional_: Maintain a release note in `release-notes-3.0.58.md` or increment the version header in `azure-ai-chatbot.php` when shipping new builds.
