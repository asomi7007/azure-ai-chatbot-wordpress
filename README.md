# Azure AI Chatbot for WordPress# Azure AI Chatbot for WordPress# Azure AI Chatbot for WordPress# Azure AI Chatbot for WordPress# Azure AI Chatbot for WordPress# Azure AI Chatbot for WordPress



**[English](#) | [한국어](README-ko.md)**



A modern WordPress plugin that brings Azure AI Foundry agents and OpenAI-compatible chat models to your website with full Assistants API integration.**[English](#) | [한국어](README-ko.md)**



[![Version](https://img.shields.io/badge/version-2.2.4-blue.svg)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)

[![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)A modern WordPress plugin that brings Azure AI Foundry agents and OpenAI-compatible chat models to your website. The first WordPress plugin to support Azure AI Foundry's Agent mode with full Assistants API integration.**[English](#) | [한국어](README-ko.md)**

[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)



---

[![Version](https://img.shields.io/badge/version-2.2.4-blue.svg)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

## Why This Plugin?

[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)

This plugin provides **comprehensive Azure AI Foundry Agent mode integration** for WordPress. While other plugins support basic chat APIs, this plugin uniquely offers:

[![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)Easily integrate powerful AI agents from Azure AI Foundry and OpenAI-compatible chat models into your WordPress website with a modern, customizable chat interface.**[English](#) | [한국어](README-ko.md)**

- **Azure AI Foundry Agents**: Full Assistants API support with Function Calling, RAG, and file uploads

- **Dual-Mode Architecture**: Choose between simple Chat mode or advanced Agent mode[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)

- **Enterprise Authentication**: Entra ID OAuth 2.0 support for enterprise deployments

- **Zero-Code Setup**: Automated scripts handle all Azure configuration



Built to bring Azure AI Foundry's powerful agent capabilities to WordPress users.---



---[![Version](https://img.shields.io/badge/version-2.2.4-blue.svg)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)



## Table of Contents## Why This Plugin?



- [Features](#features)[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)

- [Quick Start](#quick-start)

- [Installation](#installation)This is the **first and only WordPress plugin** that enables Azure AI Foundry Agent mode integration. While other plugins support basic chat APIs, this plugin uniquely provides:

- [Configuration](#configuration)

- [Security](#security)[![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)Easily integrate powerful AI agents from Azure AI Foundry and OpenAI-compatible chat models into your WordPress website.**[English](#) | [한국어](README-ko.md)**Azure AI Foundry의 강력한 AI 에이전트를 WordPress 웹사이트에 쉽게 통합하는 플러그인입니다.

- [Customization](#customization)

- [File Structure](#file-structure)- **Azure AI Foundry Agents**: Full support for Assistants API with Function Calling, RAG, and file uploads

- [Troubleshooting](#troubleshooting)

- [Version History](#version-history)- **Dual Mode Architecture**: Choose between simple Chat mode or advanced Agent mode[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)



---- **Enterprise Authentication**: Entra ID OAuth 2.0 support for enterprise deployments



## Features- **Zero Code Setup**: Automated scripts handle all Azure configuration



### Dual Mode Support



**Chat Mode** - Simple and VersatileBuilt because no existing WordPress plugin supported Azure AI Foundry's powerful agent capabilities.---

- Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok

- API Key authentication

- Real-time streaming responses

- Multi-provider support---[![Version](https://img.shields.io/badge/version-2.2.4-blue.svg)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)



**Agent Mode** - Advanced Capabilities

- Azure AI Foundry Assistants API v1

- Entra ID OAuth 2.0 authentication## Table of Contents## Table of Contents

- Function Calling and tool integration

- RAG (Retrieval Augmented Generation)

- File upload support

- Persistent conversation threads- [Features](#features)[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)



### Core Features- [Quick Start](#quick-start)



- **Zero-Code Setup**: Complete configuration from WordPress admin panel- [Installation](#installation)- [Features](#features)

- **Enterprise Security**: AES-256 encryption for credentials

- **Fully Customizable**: Colors, position, messages, styling- [Configuration](#configuration)

- **Responsive Design**: Perfect on desktop and mobile

- **Connection Testing**: Verify API connections before deployment- [Security](#security)- [Installation](#installation)[![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)Easily integrate powerful AI agents from Azure AI Foundry into your WordPress website.[![Version](https://img.shields.io/badge/version-2.2.4-blue.svg)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

- **Multilingual Support**: Korean and English with auto-detection

- [Customization](#customization)

---

- [File Structure](#file-structure)- [Quick Start](#quick-start)

## Quick Start

- [Troubleshooting](#troubleshooting)

### Setting Up Chat Mode

- [Version History](#version-history)  - [Chat Mode Setup](#chat-mode-setup)[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)

**Step 1: Get Configuration Values**



Run this script in [Azure Cloud Shell](https://shell.azure.com):

---  - [Agent Mode Setup](#agent-mode-setup)

```bash

curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash

```

## Features- [Configuration](#configuration)[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)

What the script does:

1. Lists your Azure subscriptions

2. Finds Azure OpenAI resources

3. Shows deployed models### Dual Mode Support- [Customization](#customization)

4. Retrieves endpoint and API key

5. Tests the connection

6. Outputs WordPress configuration values

**Chat Mode** - Simple & Versatile- [Troubleshooting](#troubleshooting)---

**Example Output:**

```- Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok

✅ Chat Mode Connection Successful!

- API Key authentication- [Development](#development)

WordPress Configuration:

• Mode: Chat Mode- Real-time streaming responses

• Endpoint: https://your-resource.openai.azure.com

• Deployment Name: gpt-4o- Multi-provider support- [FAQ](#faq)[![Version](https://img.shields.io/badge/version-2.2.4-blue.svg)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)[![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)

• API Key: abc123...xyz789

```



**Step 2: Configure WordPress****Agent Mode** - Advanced & Powerful  - [Contributing](#contributing)



1. Navigate to **AI Chatbot** → **Settings**- Azure AI Foundry Assistants API v1

2. Select **Chat Mode**

3. Choose **Azure OpenAI**- Entra ID OAuth 2.0 authentication- [License](#license)## Features

4. Enter the values from script output

5. Click **Save Changes** → **Test Connection**- Function Calling and tool integration



---- RAG (Retrieval Augmented Generation)



### Setting Up Agent Mode- File upload support



**Step 1: Get Configuration Values**- Persistent conversation threads---[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)



Run this script in [Azure Cloud Shell](https://shell.azure.com):



```bash### Core Capabilities

curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash

```



What the script does:- **Zero Code Configuration**: Complete setup from WordPress admin panel## Features- **Dual Mode Support**

1. Finds AI Foundry resources

2. Creates or uses existing Service Principal- **Enterprise Security**: AES-256 encryption for credentials

3. Generates Client Secret

4. Lists or helps create agents- **Fully Customizable**: Colors, position, messages, and styling

5. Tests complete connection

6. Outputs all WordPress configuration values- **Responsive Design**: Perfect on desktop and mobile



**Example Output:**- **Connection Testing**: Verify API connections before deployment### Dual Mode Support  - **Chat Mode**: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok (API Key auth)[![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)[![GitHub Release](https://img.shields.io/github/v/release/asomi7007/azure-ai-chatbot-wordpress)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

```

✅ Agent Mode Connection Successful!- **Multi-language**: Korean and English with auto-detection



WordPress Configuration:- **Chat Mode**: Direct integration with Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, and any OpenAI-compatible API

• Mode: Agent Mode

• Endpoint: https://your-resource.services.ai.azure.com/api/projects/your-project---

• Agent ID: asst_abc123xyz789

• Client ID: 12345678-1234-1234-1234-123456789012  - Simple API Key authentication  - **Agent Mode**: Azure AI Foundry Assistants API (Entra ID OAuth 2.0 auth)

• Client Secret: def456...uvw789

• Tenant ID: 87654321-4321-4321-4321-210987654321## Quick Start

```

  - Multi-provider support

**Step 2: Configure WordPress**

### Chat Mode Setup

1. Navigate to **AI Chatbot** → **Settings**

2. Select **Agent Mode**  - Real-time streaming responses  [![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)

3. Enter the values from script output

4. Click **Save Changes** → **Test Connection****Step 1: Get Your Configuration**



---  



## InstallationRun this script in [Azure Cloud Shell](https://shell.azure.com):



### Method 1: ZIP Upload (Recommended)- **Agent Mode**: Azure AI Foundry Assistants API- **Easy Configuration**: No need to edit `wp-config.php`, configure everything from admin page



1. Download `azure-ai-chatbot-wordpress.zip` from [Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)```bash

2. WordPress Admin → **Plugins** → **Add New** → **Upload Plugin**

3. Choose the ZIP file and click **Install Now**curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash  - Entra ID OAuth 2.0 authentication

4. Click **Activate Plugin**

```

### Method 2: Git Clone

  - Advanced features: Function Calling, RAG, file uploads[![GitHub Release](https://img.shields.io/github/v/release/asomi7007/azure-ai-chatbot-wordpress)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)## 🎉 최신 버전

```bash

git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.gitThe script will:

# Upload to /wp-content/plugins/

# Activate in WordPress admin1. List your Azure subscriptions  - Persistent conversation threads

```

2. Find Azure OpenAI resources

---

3. Show deployed models  - Tool integration support- **Security**: API Keys and Client Secrets are encrypted with AES-256

## Configuration

4. Retrieve endpoint and API key

### Chat Mode Providers

5. Test the connection

| Provider | Endpoint | Model Examples | Authentication |

|----------|----------|---------------|----------------|6. Output WordPress configuration values

| **Azure OpenAI** | `https://{resource}.openai.azure.com` | `gpt-4o` | API Key |

| **OpenAI** | `https://api.openai.com` | `gpt-4-turbo` | API Key (sk-) |### Key Highlights

| **Google Gemini** | `https://generativelanguage.googleapis.com` | `gemini-2.0-flash-exp` | API Key |

| **Anthropic Claude** | `https://api.anthropic.com` | `claude-3-5-sonnet-20241022` | API Key (sk-ant-) |**Example Output:**

| **xAI Grok** | `https://api.x.ai` | `grok-beta` | API Key |

| **Other** | Custom endpoint | OpenAI-compatible models | API Key |```- **Zero Code Configuration**: Set everything up from the WordPress admin panel



### Agent Mode Requirements✅ Chat Mode Connection Successful!



- **Agent ID**: Created in Azure AI Foundry (starts with `asst_`)- **Enterprise Security**: AES-256 encryption for API Keys and Client Secrets- **Customizable UI**: Change chatbot colors, position, title, and welcome message

- **Tenant ID**: Microsoft Entra tenant ID

- **Client ID**: Service Principal application IDWordPress Settings:

- **Client Secret**: Generated secret (created by script)

- **Project Path**: Full Azure resource path• Mode: Chat Mode- **Fully Customizable**: Colors, position, messages, and styling



**Project Path Format:**• Endpoint: https://your-resource.openai.azure.com

```

subscriptions/{subscription-id}/resourceGroups/{resource-group}/providers/Microsoft.MachineLearningServices/workspaces/{project-name}• Deployment: gpt-4o- **Responsive Design**: Perfect display on desktop and mobile## 🎉 Latest Release### v2.2.4 (2025-10-05) - 🐛 Chat 모드 HTTP 404 오류 수정

```

• API Key: abc123...xyz789

---

```- **Built-in Testing**: Test API connections before going live

## Security



### Credential Encryption

**Step 2: Configure WordPress**- **Multi-language**: Automatic language detection (Korean/English)- **Responsive Design**: Works perfectly on desktop and mobile devices

All sensitive data is encrypted before storage:



- **Algorithm**: AES-256-CBC

- **Key Storage**: WordPress authentication keys and salts1. Go to **AI Chatbot** → **Settings**- **No External Dependencies**: Works with standard WordPress installation

- **Encrypted Fields**: API Keys, Client Secrets

- **Decryption**: Only when needed for API calls2. Select **Chat Mode**



### Security Best Practices3. Choose **Azure OpenAI****수정 사항:**



1. **Use HTTPS**: Always run WordPress over HTTPS4. Enter values from script output

2. **Regular Updates**: Keep WordPress and PHP up to date

3. **Restrict Admin Access**: Limit who can access plugin settings5. Click **Save Changes** → **Test Connection**---

4. **Rotate Secrets**: Regenerate API keys and secrets periodically

5. **Monitor Logs**: Check debug logs for unauthorized access



### Network Security---- **Connection Testing**: Test your API connection directly from settings page



- Configure Azure firewall rules to allow WordPress server IP

- Use Service Principal with minimum required permissions

- Enable Azure Monitor for API usage tracking### Agent Mode Setup## Installation



---



## Customization**Step 1: Get Your Configuration**### v2.2.4 (2025-10-05) - 🐛 Chat Mode HTTP 404 Fix- Chat 모드에서 발생하던 HTTP 404 오류 완전 수정



### Visual Customization



Configure in **AI Chatbot** → **Settings**:Run this script in [Azure Cloud Shell](https://shell.azure.com):### Method 1: Install via ZIP (Recommended)



- **Chatbot Title**: Header text

- **Welcome Message**: Initial greeting

- **Button Color**: Hex color code (e.g., `#667eea`)```bash- **Multi-language**: Korean and English support with automatic language detection

- **Button Position**: Bottom right or bottom left

curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash

### Advanced CSS

```1. Download the latest `azure-ai-chatbot-wordpress.zip` from [GitHub Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

Add to **Appearance** → **Customize** → **Additional CSS**:



```css

/* Change chatbot window size */The script will:2. Go to WordPress admin: **Plugins** → **Add New** → **Upload Plugin****Fixed:**- API 버전 초기화 로직 개선 (Agent 모드: v1, Chat 모드: 2024-08-01-preview)

.azure-chatbot-window {

    width: 450px !important;1. Find AI Foundry resources

    height: 700px !important;

}2. Create or use existing Service Principal3. Select the downloaded ZIP file and click **Install Now**



/* Custom message colors */3. Generate Client Secret

.azure-chatbot-message.user {

    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;4. List agents or help create one4. Click **Activate Plugin**---

}

5. Test complete connection

/* Custom send button */

.azure-chatbot-send-btn {6. Output all WordPress configuration values

    background: #667eea !important;

}

```

**Example Output:**### Method 2: Manual Installation- Completely resolved HTTP 404 errors in Chat mode- 다중 제공자별 API 엔드포인트 및 인증 방식 최적화

### JavaScript Hooks

```

```javascript

// Chatbot event listeners✅ Agent Mode Connection Successful!

document.addEventListener('azure-chatbot-opened', function() {

    console.log('Chatbot opened');

});

WordPress Settings:```bash## Installation

document.addEventListener('azure-chatbot-message-sent', function(event) {

    console.log('Message sent:', event.detail.message);• Mode: Agent Mode

});

```• Endpoint: https://your-resource.services.ai.azure.com/api/projects/your-project# Clone the repository



---• Agent ID: asst_abc123xyz789



## File Structure• Client ID: 12345678-1234-1234-1234-123456789012git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git- Improved API version initialization logic (Agent mode: v1, Chat mode: 2024-08-01-preview)



```• Client Secret: def456...uvw789

azure-ai-chatbot-wordpress/

├── azure-ai-chatbot.php       # Main plugin file• Tenant ID: 87654321-4321-4321-4321-210987654321

├── assets/

│   ├── admin.css              # Admin styles```

│   ├── admin.js               # Admin scripts

│   ├── chatbot.css            # Widget styles# Upload to WordPress plugins directory### Method 1: Install via ZIP (Recommended)

│   └── chatbot.js             # Widget scripts

├── templates/**Step 2: Configure WordPress**

│   ├── settings-page.php      # Settings UI

│   └── guide-page.php         # User guide# /wp-content/plugins/azure-ai-chatbot-wordpress/

├── languages/

│   ├── *.po                   # Translation sources1. Go to **AI Chatbot** → **Settings**

│   ├── *.mo                   # Compiled translations

│   └── compile-po-to-mo.py    # Compiler script2. Select **Agent Mode**- Optimized API endpoints and authentication methods for multiple providers[릴리즈 노트](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.4) | [다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/download/v2.2.4/azure-ai-chatbot-wordpress.zip)

├── docs/

│   ├── AZURE_SETUP.md         # Detailed setup guide3. Enter values from script output

│   └── USER_GUIDE.md          # User documentation

├── test-chat-mode.sh          # Chat mode test script4. Click **Save Changes** → **Test Connection**# Activate from WordPress admin → Plugins

├── test-agent-mode.sh         # Agent mode test script

├── README.md                  # This file (English)

├── README-ko.md               # Korean version

├── CHANGELOG.md               # Complete version history---```1. Download the latest `azure-ai-chatbot-wordpress.zip` from [GitHub Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

└── LICENSE                    # GPL-2.0+

```



---## Installation



## Troubleshooting



### HTTP 404 Error (Chat Mode)### Method 1: Install via ZIP (Recommended)---2. Go to WordPress admin: **Plugins** → **Add New** → **Upload Plugin**



**Issue**: Getting 404 errors when testing



**Solutions**:1. Download `azure-ai-chatbot-wordpress.zip` from [Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

1. Remove trailing slash from endpoint URL

2. Verify deployment name (case-sensitive)2. WordPress admin → **Plugins** → **Add New** → **Upload Plugin**

3. Check API key validity

4. Try different API version3. Select ZIP and click **Install Now**## Quick Start3. Select the downloaded ZIP file and click **Install Now**



**Manual Test**:4. Click **Activate Plugin**

```bash

curl -X POST "https://YOUR-RESOURCE.openai.azure.com/openai/deployments/YOUR-DEPLOYMENT/chat/completions?api-version=2024-08-01-preview" \

  -H "api-key: YOUR-KEY" \

  -H "Content-Type: application/json" \### Method 2: Git Clone

  -d '{"messages":[{"role":"user","content":"Hello"}]}'

```### Chat Mode Setup4. Click **Activate Plugin**[Release Notes](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.4) | [Download](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/download/v2.2.4/azure-ai-chatbot-wordpress.zip)---



### Agent Mode Connection Failed```bash



**Issue**: Can't connect to Azure AI Foundrygit clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git



**Solutions**:# Upload to /wp-content/plugins/

1. Verify Service Principal has "Cognitive Services User" role

2. Check project path format# Activate from WordPress admin**Step 1: Get Azure OpenAI Configuration**

3. Confirm Client Secret is valid

4. Review network firewall rules```



**Check Permissions**:

```bash

az role assignment list --assignee YOUR-CLIENT-ID---

```

Use this script in [Azure Cloud Shell](https://shell.azure.com) to automatically retrieve all required values:### Method 2: Manual Installation

### Chatbot Not Appearing

## Configuration

**Issue**: Widget not showing on website



**Solutions**:

1. Clear WordPress cache### Chat Mode Providers

2. Verify plugin is activated

3. Check browser console (F12) for JavaScript errors```bash

4. Temporarily disable other plugins

5. Switch to default theme to test| Provider | Endpoint | Model Example | Auth |



### Enable Debug Logging|----------|----------|---------------|------|# Copy and paste this entire script into Azure Cloud Shell



Add to `wp-config.php`:| **Azure OpenAI** | `https://{resource}.openai.azure.com` | `gpt-4o` | API Key |

```php

define('WP_DEBUG', true);| **OpenAI** | `https://api.openai.com` | `gpt-4-turbo` | API Key (sk-) |curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash1. Clone this repository:---### v2.2.3 (2025-10-05) - 📖 버전 기록 상세화

define('WP_DEBUG_LOG', true);

define('WP_DEBUG_DISPLAY', false);| **Google Gemini** | `https://generativelanguage.googleapis.com` | `gemini-2.0-flash-exp` | API Key |

```

| **Anthropic Claude** | `https://api.anthropic.com` | `claude-3-5-sonnet-20241022` | API Key (sk-ant-) |```

Log location: `/wp-content/debug.log`

| **xAI Grok** | `https://api.x.ai` | `grok-beta` | API Key |

---

| **Other** | Custom endpoint | Any OpenAI-compatible | API Key |   ```bash

## Version History



### Latest Release: v2.2.4 (2025-10-05)

### Agent Mode Requirements**What this script does:**

**Fixes:**

- Fixed HTTP 404 errors in Chat mode

- Improved API version initialization logic

- Enhanced endpoint handling for multiple providers- **Agent ID**: From Azure AI Foundry (starts with `asst_`)1. Lists your Azure subscriptions   git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git**개선 사항:**



[Download v2.2.4](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.4)- **Tenant ID**: Microsoft Entra tenant ID



### Recent Updates- **Client ID**: Service Principal application ID2. Finds your Azure OpenAI resources



**v2.2.3** - Documentation and FAQ improvements  - **Client Secret**: Generated secret (created by script)

**v2.2.2** - GitHub badges and changelog additions  

**v2.2.1** - Endpoint slash handling fix  - **Project Path**: Full Azure resource path3. Shows deployed models   ```

**v2.2.0** - Multi-provider support (6 AI services)  

**v2.1.0** - Dual mode introduction (Chat + Agent)  

**v2.0.0** - Complete plugin redesign  

**v1.0.0** - Initial release**Project Path Format:**4. Retrieves endpoint and API key



[Full Changelog](CHANGELOG.md)```



---subscriptions/{subscription-id}/resourceGroups/{resource-group}/providers/Microsoft.MachineLearningServices/workspaces/{project-name}5. Tests the connection2. Upload the `azure-ai-chatbot-wordpress` folder to `/wp-content/plugins/`## ✨ Key Features- README.md 버전 기록을 상세하게 업데이트



## FAQ```



**Q: Which AI services can I use?**  6. Provides WordPress configuration values

A: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, and any OpenAI-compatible API.

---

**Q: Does this plugin support Azure AI Foundry agents?**  

A: Yes, this plugin provides comprehensive Azure AI Foundry Agent mode integration with full Assistants API support.3. Activate the plugin from WordPress admin



**Q: Do I need coding skills?**  ## Security

A: No. Azure setup uses automated scripts, and all configuration is done via WordPress admin panel.

**Script Output Example:**

**Q: Is it secure?**  

A: Yes. All credentials are encrypted with AES-256. Use HTTPS for production.### Credential Encryption



**Q: What's the difference between Chat and Agent mode?**  ```- 각 버전별 주요 기능 및 수정 사항 명시

A: Chat mode is simple API calls. Agent mode uses Azure AI Foundry for advanced features like Function Calling and RAG.

All sensitive data is encrypted before storage:

**Q: Can I use it on multiple sites?**  

A: Yes, but each WordPress installation needs separate configuration.========================================



**Q: Does it work with WordPress Multisite?**  - **Algorithm**: AES-256-CBC

A: Yes. Each site can have independent configuration.

- **Key Storage**: WordPress authentication keys and salts✅ Chat Mode Connection Successful!---

---

- **Encrypted Fields**: API Keys, Client Secrets

## Contributing

- **Decryption**: Only when needed for API calls========================================

Contributions are welcome!



1. Fork the repository

2. Create a feature branch (`git checkout -b feature/name`)### Best Practices### 🎯 Dual Mode Support- FAQ 섹션 대폭 강화 (AI 서비스 지원, 모드 차이, 보안 등)

3. Commit your changes (`git commit -m 'Add feature'`)

4. Push to the branch (`git push origin feature/name`)

5. Open a Pull Request

1. **Use HTTPS**: Always run WordPress on HTTPSWordPress Plugin Settings:

Please follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/).

2. **Regular Updates**: Keep WordPress and PHP updated

---

3. **Limit Admin Access**: Restrict plugin settings access• Mode: Chat Mode (OpenAI Compatible)## Configuration

## Support

4. **Rotate Secrets**: Periodically regenerate API keys and secrets

- **Documentation**: [docs/](docs/)

- **Issues**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)5. **Monitor Logs**: Check debug logs for unauthorized access• Chat Endpoint: https://your-resource.openai.azure.com

- **Discussions**: [GitHub Discussions](https://github.com/asomi7007/azure-ai-chatbot-wordpress/discussions)

- **Releases**: [Latest Version](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)



---### Network Security• Deployment Name: gpt-4o- **Chat Mode**: Support for Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok (API Key authentication)- 향후 계획 현실화



## License



GPL-2.0+ License - see [LICENSE](LICENSE) file for details- Configure Azure firewall rules to allow your WordPress server IP• API Key: abc123...xyz789



Free to use, modify, and distribute.- Use Service Principal with minimum required permissions



---- Enable Azure Monitor for API usage tracking### Chat Mode Setup



## Acknowledgments



Built for WordPress and Azure AI Foundry users who need enterprise-grade AI chat capabilities.---📌 Reference:



**Made with ❤️ for WordPress & Azure AI**


## Customization• Resource Name: your-resource- **Agent Mode**: Azure AI Foundry Assistants API (Entra ID OAuth 2.0 authentication)



### Visual Customization• Resource Group: your-rg



Configure in **AI Chatbot** → **Settings**:```1. Go to **AI Chatbot** → **Settings** in WordPress admin



- **Chatbot Title**: Header text

- **Welcome Message**: Initial greeting  

- **Button Color**: Hex color code (e.g., `#667eea`)**Step 2: Configure WordPress**2. Select **Chat Mode**[릴리즈 노트](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.3) | [다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/download/v2.2.3/azure-ai-chatbot-wordpress.zip)

- **Button Position**: Bottom-right or Bottom-left



### Advanced CSS

1. Go to **AI Chatbot** → **Settings** in WordPress admin3. Choose your AI provider (Azure OpenAI, OpenAI, Gemini, Claude, Grok, or Other)

Add to **Appearance** → **Customize** → **Additional CSS**:

2. Select **Chat Mode**

```css

/* Resize chatbot window */3. Choose **Azure OpenAI** as provider4. Enter your API endpoint and API Key### 🚀 Features

.azure-chatbot-window {

    width: 450px !important;4. Enter the values from the script output:

    height: 700px !important;

}   - **Endpoint**: The Chat Endpoint value5. Enter deployment/model name



/* Custom message colors */   - **Deployment Name**: The model deployment name

.azure-chatbot-message.user {

    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;   - **API Key**: Your API key6. Click **Save Changes**- ✅ **Easy Setup**: Configure everything from admin page, no need to edit wp-config.php---

}

5. Click **Save Changes**

/* Custom send button */

.azure-chatbot-send-btn {6. Click **Test Connection** to verify7. Click **Test Connection** to verify

    background: #667eea !important;

}

```

**Alternative: Manual Configuration**- 🎨 **Full Customization**: Freely change colors, position, and messages

### JavaScript Hooks



```javascript

// Listen to chatbot eventsIf you prefer manual setup:**Example (Azure OpenAI):**

document.addEventListener('azure-chatbot-opened', function() {

    console.log('Chatbot opened');

});

1. Go to [Azure Portal](https://portal.azure.com)- Endpoint: `https://your-resource.openai.azure.com`- 🤖 **Complete Azure AI Support**: Function Calling, RAG, file upload, etc.### v2.2.2 (2025-10-05) - 📚 문서 개선

document.addEventListener('azure-chatbot-message-sent', function(event) {

    console.log('Message:', event.detail.message);2. Navigate to your Azure OpenAI resource

});

```3. Go to **Keys and Endpoint** section- Deployment Name: `gpt-4o`



---4. Copy:



## File Structure   - **Endpoint**: `https://your-resource.openai.azure.com`- API Key: Your Azure OpenAI API key- 📱 **Responsive Design**: Perfect support for desktop and mobile**변경 사항:**



```   - **Key**: Either Key 1 or Key 2

azure-ai-chatbot-wordpress/

├── azure-ai-chatbot.php       # Main plugin file5. Go to **Model deployments** section

├── assets/

│   ├── admin.css              # Admin styles6. Note your deployment name (e.g., `gpt-4o`, `gpt-35-turbo`)

│   ├── admin.js               # Admin scripts

│   ├── chatbot.css            # Widget styles### Agent Mode Setup- 🔒 **Security**: API Keys/Client Secrets are encrypted with AES-256 and stored on server- Plugin URI를 GitHub 저장소 링크로 업데이트

│   └── chatbot.js             # Widget scripts

├── templates/---

│   ├── settings-page.php      # Settings UI

│   └── guide-page.php         # User guide

├── languages/

│   ├── *.po                   # Translation source### Agent Mode Setup

│   ├── *.mo                   # Compiled translations

│   └── compile-po-to-mo.py    # Compiler script1. Select **Agent Mode** in settings- 📖 **Markdown Guide**: Editable detailed guide provided- README.md에 버전 배지 및 릴리즈 링크 추가

├── docs/

│   ├── AZURE_SETUP.md         # Detailed setup guide**Step 1: Get AI Foundry Configuration**

│   └── USER_GUIDE.md          # User documentation

├── test-chat-mode.sh          # Chat mode test script2. Enter Agent ID from Azure AI Foundry

├── test-agent-mode.sh         # Agent mode test script

├── README.md                   # This fileUse this script in [Azure Cloud Shell](https://shell.azure.com) to automatically set up Agent mode:

├── README-ko.md               # Korean version

├── CHANGELOG.md               # Full version history3. Enter Service Principal credentials:- 🧪 **Connection Test**: Test immediately from settings page- readme.txt에 전체 변경 이력 추가

└── LICENSE                    # GPL-2.0+

``````bash



---# Copy and paste this entire script into Azure Cloud Shell   - Tenant ID



## Troubleshootingcurl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash



### HTTP 404 Error (Chat Mode)```   - Client ID- 🛠️ **Test Scripts**: Automatic test tools for Chat/Agent modes included



**Problem**: Getting 404 errors when testing



**Solutions**:**What this script does:**   - Client Secret

1. Remove trailing slash from endpoint URL

2. Verify deployment name (case-sensitive)1. Lists your Azure subscriptions

3. Check API key validity

4. Try different API version2. Finds AI Foundry/AI Services resources4. Enter Project Path: `subscriptions/{subscription-id}/resourceGroups/{resource-group}/providers/Microsoft.MachineLearningServices/workspaces/{project-name}`- 🌍 **Internationalization**: Multi-language support (Korean, English)[릴리즈 노트](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.2) | [다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/download/v2.2.2/azure-ai-chatbot-wordpress.zip)



**Test manually**:3. Creates or finds Service Principal

```bash

curl -X POST "https://YOUR-RESOURCE.openai.azure.com/openai/deployments/YOUR-DEPLOYMENT/chat/completions?api-version=2024-08-01-preview" \4. Generates Client Secret5. Click **Save Changes** and **Test Connection**

  -H "api-key: YOUR-KEY" \

  -H "Content-Type: application/json" \5. Lists available agents or helps create one

  -d '{"messages":[{"role":"user","content":"Hello"}]}'

```6. Tests the complete connection



### Agent Mode Connection Failed7. Provides all WordPress configuration values



**Problem**: Cannot connect to Azure AI FoundryFor detailed setup instructions, see:



**Solutions**:**Script Output Example:**

1. Verify Service Principal has "Cognitive Services User" role

2. Check project path format```- [Azure Setup Guide](docs/AZURE_SETUP.md)## 📦 Installation---

3. Confirm Client Secret is valid

4. Check network firewall rules=========================================



**Verify permissions**:✅ Agent Mode Connection Successful!- [Entra ID Configuration](ENTRA_ID_SETUP.md)

```bash

az role assignment list --assignee YOUR-CLIENT-ID=========================================

```



### Chatbot Not Appearing

WordPress Plugin Settings:

**Problem**: Widget doesn't show on website

• Mode: Agent Mode (Assistants API v1)---

**Solutions**:

1. Clear WordPress cache• Agent Endpoint: https://your-resource.services.ai.azure.com/api/projects/your-project

2. Check plugin is activated

3. Open browser console (F12) for JavaScript errors• Agent ID: asst_abc123xyz789### Install via ZIP File (Recommended) ⭐### v2.2.1 (2025-10-05) - 🐛 Hotfix

4. Temporarily disable other plugins

5. Switch to default theme to test• Client ID: 12345678-1234-1234-1234-123456789012



### Enable Debug Logging• Client Secret: def456...uvw789## Customization



Add to `wp-config.php`:• Tenant ID: 87654321-4321-4321-4321-210987654321

```php

define('WP_DEBUG', true);**수정 사항:**

define('WP_DEBUG_LOG', true);

define('WP_DEBUG_DISPLAY', false);📌 Reference:

```

• Resource Name: your-resource### Chatbot Appearance

Check logs at: `/wp-content/debug.log`

• Resource Group: your-rg

---

• Project Name: your-projectThe easiest and fastest installation method!- 엔드포인트 입력 시 trailing slash 자동 제거 (blur 이벤트)

## Version History

• Service Principal: azure-ai-chatbot-wp-your-resource

### Latest Release: v2.2.4 (2025-10-05)

```Customize from **AI Chatbot** → **Settings**:

**Fixed:**

- HTTP 404 errors in Chat mode

- API version initialization logic

- Multi-provider endpoint handling**Step 2: Configure WordPress**- 실시간 입력 검증으로 404 에러 사전 방지



[Download v2.2.4](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.4)



### Recent Updates1. Go to **AI Chatbot** → **Settings** in WordPress admin- **Chatbot Title**: Change the header text



**v2.2.3** - Enhanced documentation and FAQ  2. Select **Agent Mode**

**v2.2.2** - Added GitHub badges and changelog  

**v2.2.1** - Fixed endpoint trailing slash issue  3. Enter the values from the script output:- **Welcome Message**: Customize the greeting1. **Download the latest `azure-ai-chatbot-wordpress.zip` from [GitHub Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)**

**v2.2.0** - Multi-provider support (6 AI services)  

**v2.1.0** - Introduced dual mode (Chat + Agent)     - **Agent Endpoint**: The API endpoint for your project

**v2.0.0** - Complete plugin redesign  

**v1.0.0** - Initial release   - **Agent ID**: Your assistant ID (starts with `asst_`)- **Button Color**: Set the chat button color (hex code)



[Full Changelog](CHANGELOG.md)   - **Tenant ID**: Your Microsoft Entra tenant ID



---   - **Client ID**: Service Principal application ID- **Button Position**: Choose between bottom-right or bottom-left2. Access WordPress admin page**문제 해결:** Azure Portal에서 복사한 엔드포인트 끝의 `/` 자동 제거



## FAQ   - **Client Secret**: Generated secret



**Q: Which AI services work with this plugin?**     - **Project Path**: Full resource path

A: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, and any OpenAI-compatible API.

4. Click **Save Changes**

**Q: Is this the only plugin supporting Azure AI Foundry agents?**  

A: Yes. This is currently the only WordPress plugin with full Azure AI Foundry Agent mode integration.5. Click **Test Connection** to verify### Advanced Customization3. Click **Plugins** → **Add New** → **Upload Plugin**



**Q: Do I need coding skills?**  

A: No. Use the automated scripts for Azure setup, then configure via WordPress admin panel.

**Project Path Format:**

**Q: Is it secure?**  

A: Yes. All credentials are encrypted with AES-256. Use HTTPS for production.```



**Q: What's the difference between Chat and Agent modes?**  subscriptions/{subscription-id}/resourceGroups/{resource-group}/providers/Microsoft.MachineLearningServices/workspaces/{project-name}Add custom CSS in WordPress **Appearance** → **Customize** → **Additional CSS**:4. Select the downloaded ZIP file[릴리즈 노트](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.1) | [다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/download/v2.2.1/azure-ai-chatbot-wordpress.zip)

A: Chat mode is simple API calls. Agent mode uses Azure AI Foundry with advanced features like function calling and RAG.

```

**Q: Can I use multiple sites?**  

A: Yes. Each WordPress installation needs separate configuration.



**Q: Does it work with WordPress multisite?**  **Alternative: Manual Configuration**

A: Yes. Each site can have independent settings.

```css5. Click **Install Now**

---

1. Create agent in [Azure AI Foundry](https://ai.azure.com)

## Contributing

2. Copy Agent ID from the agent details page/* Example: Change chatbot window size */

Contributions welcome! Please:

3. Create Service Principal:

1. Fork the repository

2. Create feature branch (`git checkout -b feature/name`)   ```bash.azure-chatbot-window {6. Click **Activate Plugin** after installation completes---

3. Commit changes (`git commit -m 'Add feature'`)

4. Push to branch (`git push origin feature/name`)   az ad sp create-for-rbac --name "azure-ai-chatbot-wp" \

5. Open Pull Request

     --role "Cognitive Services User" \    width: 450px !important;

Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/).

     --scopes "/subscriptions/{subscription-id}/resourceGroups/{rg}/providers/Microsoft.CognitiveServices/accounts/{resource}"

---

   ```    height: 700px !important;

## Support

4. Note Client ID, Client Secret, and Tenant ID

- **Documentation**: [docs/](docs/)

- **Issues**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)}

- **Discussions**: [GitHub Discussions](https://github.com/asomi7007/azure-ai-chatbot-wordpress/discussions)

- **Releases**: [Latest Version](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)For detailed manual setup, see:



---- [Azure Setup Guide](docs/AZURE_SETUP.md)> 💡 **Tip**: No need to extract the ZIP file! Upload it as is.### v2.2.0 (2025-10-05) - 🌐 다중 AI 제공자 지원



## License- [Entra ID Configuration](ENTRA_ID_SETUP.md)



GPL-2.0+ License - See [LICENSE](LICENSE) file/* Example: Custom message bubble colors */



Free to use, modify, and distribute.---



---.azure-chatbot-message.user {**새로운 기능:**



## Acknowledgments## Configuration



Built for WordPress and Azure AI Foundry users who need enterprise-grade AI chat capabilities.    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;



**Made with ❤️ for WordPress & Azure AI**### Chatbot Appearance


}### Manual Installation (For Developers)- ✨ 6개 AI 제공자 지원: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, 기타 (OpenAI 호환)

Customize from **AI Chatbot** → **Settings**:

```

| Setting | Description | Example |

|---------|-------------|---------|- 🎨 동적 UI: 제공자 선택 시 엔드포인트/모델명/API Key 설명 자동 변경

| **Chatbot Title** | Header text | "AI Assistant", "Help" |

| **Welcome Message** | Initial greeting | "Hello! How can I help you?" |---

| **Button Color** | Chat button color | `#667eea`, `#ff6b6b` |

| **Button Position** | Placement on page | Bottom-right or Bottom-left |If you want to edit source code directly:- 🧪 Agent 모드 테스트 스크립트 (Service Principal 자동 생성 포함)



### Provider-Specific Settings## Usage



#### Azure OpenAI- 💬 모드별 오류 메시지 (Chat/Agent 구분)

- **Endpoint**: `https://{resource-name}.openai.azure.com`

- **Deployment Name**: Your model deployment (e.g., `gpt-4o`)### For Website Visitors

- **API Key**: From Azure Portal → Keys and Endpoint

1. Download or clone this repository

#### OpenAI

- **Endpoint**: `https://api.openai.com` (default)1. Click the chat button in the bottom corner of any page

- **Model Name**: `gpt-4-turbo`, `gpt-3.5-turbo`, `gpt-4o`

- **API Key**: Starts with `sk-`2. Type your message in the input field   ```bash**수정 사항:**



#### Google Gemini3. Press Enter or click the send button

- **Endpoint**: `https://generativelanguage.googleapis.com`

- **Model Name**: `gemini-2.0-flash-exp`, `gemini-1.5-pro`4. The AI will respond in real-time   git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git- 🐛 Trailing slash 3중 제거 (로드/저장/생성자)

- **API Key**: From Google AI Studio



#### Anthropic Claude

- **Endpoint**: `https://api.anthropic.com`### For Administrators   ```- 🎨 설정 UI 개선 (테스트 결과 위치, 미리보기 통합, 저장 버튼)

- **Model Name**: `claude-3-5-sonnet-20241022`, `claude-3-opus-20240229`

- **API Key**: Starts with `sk-ant-`



#### xAI Grok**Test Connection:**2. Upload the folder to `/wp-content/plugins/` directory

- **Endpoint**: `https://api.x.ai`

- **Model Name**: `grok-beta`- Go to **AI Chatbot** → **Settings**

- **API Key**: From xAI platform

- Click **Test Connection** button3. Activate the plugin from WordPress admin page[릴리즈 노트](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.0) | [다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/download/v2.2.0/azure-ai-chatbot-wordpress.zip)

---

- Check if API connection is successful

## Customization



### CSS Customization

**View Logs:**

Add custom styles in **Appearance** → **Customize** → **Additional CSS**:

- Check WordPress debug log at `/wp-content/debug.log`### Install from WordPress.org (Coming Soon)---

```css

/* Change chatbot window size */- Enable debugging in `wp-config.php` if needed

.azure-chatbot-window {

    width: 450px !important;

    height: 700px !important;

}**Monitor Usage:**



/* Custom message bubble colors */- Track API usage in Azure Portal (Chat Mode)1. WordPress admin page → **Plugins** → **Add New**### v2.1.0 (2025-10-05) - 🎯 듀얼 모드 지원

.azure-chatbot-message.user {

    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;- Monitor agent performance in Azure AI Foundry (Agent Mode)

}

2. Search "Azure AI Chatbot"**새로운 기능:**

.azure-chatbot-message.assistant {

    background: #f0f2f5 !important;---

    color: #1e1e1e !important;

}3. **Install Now** → **Activate**- ✨ Chat 모드 + Agent 모드 듀얼 지원



/* Customize send button */## Troubleshooting

.azure-chatbot-send-btn {

    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;- 🤖 Azure AI Foundry Assistants API v1 완벽 통합

}

### Common Issues

/* Change chat button position */

.azure-chatbot-toggle {## 🚀 Quick Start- 🔐 Entra ID OAuth 2.0 Client Credentials 인증

    bottom: 30px !important;

    right: 30px !important;**HTTP 404 Error in Chat Mode:**

}

```- Ensure API endpoint doesn't end with trailing slash- 🧵 Thread 관리 및 대화 컨텍스트 자동 유지



### JavaScript Hooks- Verify deployment name is correct



```javascript- Check API key is valid### Method 1: Chat Mode (Simple - Recommended) ⭐- ⚡ 적응형 폴링 (250ms → 1000ms)

// Custom event listeners

document.addEventListener('azure-chatbot-opened', function() {

    console.log('Chatbot opened');

});**Agent Mode Connection Failed:**- 🧪 연결 테스트 및 자동 설정 스크립트



document.addEventListener('azure-chatbot-closed', function() {- Verify Service Principal has correct permissions

    console.log('Chatbot closed');

});- Check project path format is correctChat mode is available immediately with just an API Key!- 🔒 AES-256 Client Secret 암호화



document.addEventListener('azure-chatbot-message-sent', function(event) {- Ensure Entra ID app registration is configured properly

    console.log('Message sent:', event.detail.message);

});- 📝 API Key/Client Secret 표시/숨김 토글

```

**Chatbot Not Appearing:**

---

- Clear WordPress cache#### 📋 Interactive Test in Azure Cloud Shell (Recommended) ⭐

## Troubleshooting

- Check if plugin is activated

### Common Issues

- Verify no JavaScript errors in browser console**테스트 도구:**

#### HTTP 404 Error (Chat Mode)



**Problem**: Getting 404 errors when testing connection

For more help, see our [detailed troubleshooting guide](docs/AZURE_SETUP.md#troubleshooting).Run with **one-line command** in Azure Cloud Shell ([shell.azure.com](https://shell.azure.com)):- `test-chat-mode.sh`: Chat 모드 Azure CLI 테스트

**Solutions**:

1. Ensure endpoint doesn't end with trailing slash `/`

2. Verify deployment name matches exactly (case-sensitive)

3. Check API key is valid and not expired---- `test-agent-mode.sh`: Agent 모드 SP 자동 생성 및 테스트

4. Try different API version in test



**Test command**:

```bash## Development```bash

curl -X POST "https://YOUR-RESOURCE.openai.azure.com/openai/deployments/YOUR-DEPLOYMENT/chat/completions?api-version=2024-08-01-preview" \

  -H "Content-Type: application/json" \

  -H "api-key: YOUR-API-KEY" \

  -d '{"messages":[{"role":"user","content":"Hello"}],"max_tokens":10}'### Testingcurl -sL https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash[릴리즈 노트](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.1.0) | [다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/download/v2.1.0/azure-ai-chatbot-wordpress.zip)

```



#### Agent Mode Connection Failed

Use included test scripts to verify your configuration:```

**Problem**: Cannot connect to Azure AI Foundry



**Solutions**:

1. Verify Service Principal has "Cognitive Services User" role**Chat Mode Test:**---

2. Check project path format is correct

3. Ensure Entra ID app registration is configured```bash

4. Verify Client Secret hasn't expired

5. Check network access (firewall rules)bash test-chat-mode.sh**Interactive process:**



**Verify permissions**:```

```bash

az role assignment list --assignee YOUR-CLIENT-ID \1. 📋 Select Azure subscription (if multiple)### v2.0.0 (2025-10-04) - 🎨 완전한 리뉴얼

  --scope "/subscriptions/{sub}/resourceGroups/{rg}/providers/Microsoft.CognitiveServices/accounts/{resource}"

```**Agent Mode Test:**



#### Chatbot Not Appearing```bash2. 📦 Select Azure OpenAI resource**새로운 기능:**



**Problem**: Chat widget doesn't show on websitetest-agent-mode.sh



**Solutions**:```3. 📊 Select deployed model- 관리자 페이지에서 모든 설정 가능

1. Clear WordPress cache

2. Check plugin is activated

3. Open browser console (F12) and look for JavaScript errors

4. Verify no JavaScript conflicts with theme### File Structure4. 🔐 Automatically retrieve endpoint/API Key- AES-256 API Key 암호화

5. Try disabling other plugins temporarily



#### API Key Not Saving

```5. 🧪 Run connection test- 색상 및 위젯 위치 커스터마이징

**Problem**: Settings don't persist after saving

azure-ai-chatbot-wordpress/

**Solutions**:

1. Check PHP `max_input_vars` setting (should be >1000)├── azure-ai-chatbot.php      # Main plugin file6. ✅ Output WordPress settings values- Azure AI 연결 테스트 기능

2. Verify WordPress database connection

3. Check file permissions on `/wp-content/uploads/`├── assets/                    # CSS and JavaScript files

4. Look for PHP errors in `/wp-content/debug.log`

│   ├── admin.css- 편집 가능한 마크다운 가이드

Enable debugging in `wp-config.php`:

```php│   ├── admin.js

define('WP_DEBUG', true);

define('WP_DEBUG_LOG', true);│   ├── chatbot.css#### WordPress Plugin Settings- 실시간 위젯 미리보기

define('WP_DEBUG_DISPLAY', false);

```│   └── chatbot.js



---├── templates/                 # Admin page templates



## Development│   ├── settings-page.php



### Testing Scripts│   └── guide-page.phpEnter the values from script output into WordPress admin page:---



Test your configuration before deploying:├── languages/                 # Translation files



**Chat Mode Test**:│   ├── azure-ai-chatbot-ko_KR.po

```bash

bash test-chat-mode.sh│   ├── azure-ai-chatbot-en_US.po

```

│   └── compile-po-to-mo.py1. WordPress admin → **Azure AI Chatbot** → **Settings**### v1.0.0 (2025-10-03) - 🎉 초기 릴리즈

**Agent Mode Test**:

```bash├── docs/                      # Documentation

bash test-agent-mode.sh

```│   ├── AZURE_SETUP.md2. **Operation Mode**: Select `Chat Mode (OpenAI Compatible)`**기본 기능:**



### File Structure│   └── USER_GUIDE.md



```├── README.md                  # This file (English)3. Enter values from script output- Azure AI Foundry 에이전트 통합

azure-ai-chatbot-wordpress/

├── azure-ai-chatbot.php       # Main plugin file├── README-ko.md               # Korean documentation

├── assets/                     # Frontend assets

│   ├── admin.css              # Admin panel styles└── LICENSE                    # GPL-2.0+ license4. Click **Save** button- 기본 채팅 위젯

│   ├── admin.js               # Admin panel scripts

│   ├── chatbot.css            # Chatbot widget styles```

│   └── chatbot.js             # Chatbot widget scripts

├── templates/                  # PHP templates5. Verify with **Connection Test** button- wp-config.php 기반 설정

│   ├── settings-page.php      # Settings interface

│   └── guide-page.php         # User guide page---

├── languages/                  # Translations

│   ├── azure-ai-chatbot-ko_KR.po

│   ├── azure-ai-chatbot-en_US.po

│   ├── *.mo                   # Compiled translations## FAQ

│   └── compile-po-to-mo.py    # Translation compiler

├── docs/                       # Documentation---## ✨ 주요 기능

│   ├── AZURE_SETUP.md         # Detailed Azure setup

│   └── USER_GUIDE.md          # User guide**Q: Which AI services are supported?**  

├── test-chat-mode.sh          # Chat mode test script

├── test-agent-mode.sh         # Agent mode test scriptA: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, and any OpenAI-compatible API.

├── README.md                   # This file

├── README-ko.md               # Korean documentation

├── CHANGELOG.md               # Version history

└── LICENSE                    # GPL-2.0+ license**Q: Is it secure?**  ### Method 2: Agent Mode (Advanced Features) 🤖### 🎯 듀얼 모드 지원

```

A: Yes. API Keys and Client Secrets are encrypted with AES-256 before storage.

### Local Development

- **Chat 모드**: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok 지원 (API Key 인증)

```bash

# Clone repository**Q: Can I use it on multiple websites?**  

git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git

cd azure-ai-chatbot-wordpressA: Yes, install and configure separately on each WordPress site.Agent mode uses Azure AI Foundry's **Assistants API v1** to provide advanced features:- **Agent 모드**: Azure AI Foundry Assistants API (Entra ID OAuth 2.0 인증)



# Create symbolic link to WordPress plugins directory

ln -s $(pwd) /path/to/wordpress/wp-content/plugins/azure-ai-chatbot-wordpress

**Q: What's the difference between Chat Mode and Agent Mode?**  

# Compile translations

cd languagesA: Chat Mode uses simple API Key authentication and supports multiple providers. Agent Mode uses Azure AI Foundry with Entra ID OAuth 2.0 and supports advanced features like function calling, RAG, and file uploads.

python compile-po-to-mo.py

```**✨ Agent Mode Key Features:**### 🚀 기능



### Adding Translations**Q: Do I need coding skills?**  



1. Edit `.po` files in `languages/` directoryA: No, everything can be configured from the WordPress admin interface.- 🧵 **Thread Management**: Automatic conversation context maintenance (remembers previous conversations on revisit)- ✅ **쉬운 설정**: wp-config.php 수정 없이 관리자 페이지에서 모든 설정

2. Compile to `.mo` format:

   ```bash

   python languages/compile-po-to-mo.py

   ```---- 🛠️ **Function Calling**: Extensible with external API calls, database queries, etc.- 🎨 **완전한 커스터마이징**: 색상, 위치, 메시지 자유롭게 변경

3. Test in WordPress with different language settings



---

## Contributing- 📎 **File Upload**: Document analysis and RAG (Retrieval-Augmented Generation)- 🤖 **Azure AI 완벽 지원**: Function Calling, RAG, 파일 업로드 등

## FAQ



### General Questions

Contributions are welcome! Please feel free to submit a Pull Request.- 🔄 **Async Run**: Support for long-running tasks- 📱 **반응형 디자인**: 데스크톱과 모바일 완벽 지원

**Q: Which AI services are supported?**  

A: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, and any OpenAI-compatible API endpoint.



**Q: Do I need coding skills to use this plugin?**  1. Fork the repository- 📊 **Status Tracking**: Real-time Run status monitoring (queued → in_progress → completed)- 🔒 **보안**: API Key/Client Secret은 AES-256으로 암호화되어 서버에 저장

A: No. Everything can be configured through the WordPress admin interface. The setup scripts handle all Azure configuration automatically.

2. Create your feature branch (`git checkout -b feature/AmazingFeature`)

**Q: Is it secure to store API keys in WordPress?**  

A: Yes. All API Keys and Client Secrets are encrypted with AES-256 before storage in the WordPress database. They are decrypted only when needed for API calls.3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)- 📖 **마크다운 가이드**: 편집 가능한 상세 가이드 제공



**Q: Can I use this on multiple WordPress sites?**  4. Push to the branch (`git push origin feature/AmazingFeature`)

A: Yes. Install and configure the plugin separately on each site. Each installation will have its own encrypted credentials.

5. Open a Pull Request**⚠️ Important: API Version**- 🧪 **연결 테스트**: 설정 페이지에서 즉시 테스트 가능

**Q: Does it work with multisite WordPress?**  

A: Yes. Each site in a multisite network can have independent chatbot configuration.



### Feature Questions---- Azure AI Foundry Assistants API **only supports `api-version=v1`**- 🛠️ **테스트 스크립트**: Chat/Agent 모드 자동 테스트 도구 포함



**Q: What's the difference between Chat Mode and Agent Mode?**  

A: 

- **Chat Mode**: Direct API calls with simple key authentication. Best for straightforward Q&A chatbots.## License- Date-based versions like `2024-12-01-preview` don't work in some regions like Sweden Central

- **Agent Mode**: Uses Azure AI Foundry with OAuth 2.0. Supports advanced features like function calling, RAG (Retrieval Augmented Generation), and file uploads.



**Q: Can I customize the chatbot appearance?**  

A: Yes. You can change colors, position, title, welcome message, and add custom CSS for complete design control.This project is licensed under the GPL-2.0+ License - see the [LICENSE](LICENSE) file for details.- This plugin uses `v1` for compatibility with all regions## 📦 설치 방법



**Q: Does it support streaming responses?**  

A: Yes. Both Chat Mode and Agent Mode support real-time streaming responses for better user experience.

---

**Q: Can I use my own Azure OpenAI deployment?**  

A: Yes. The plugin works with any Azure OpenAI deployment you create. Use the test script to get the exact configuration values.



**Q: Does it remember conversation history?**  ## Support#### 📋 Interactive Test in Azure Cloud Shell (Recommended) ⭐### ZIP 파일로 설치 (권장) ⭐

A: Yes. Agent Mode maintains persistent threads. Chat Mode keeps history in the user's browser session.



### Troubleshooting Questions

- **Documentation**: [docs/](docs/)

**Q: Why am I getting 404 errors in Chat Mode?**  

A: Most commonly caused by:- **Issues**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)

1. Trailing slash in endpoint URL

2. Incorrect deployment name- **Releases**: [GitHub Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases)Run with **one-line command** in Azure Cloud Shell ([shell.azure.com](https://shell.azure.com)):가장 쉽고 빠른 설치 방법입니다!

3. Wrong API version

Run the test script to automatically detect the correct configuration.



**Q: My Agent Mode connection fails. What should I check?**  ---

A: The test script automatically handles most issues. If manual setup:

1. Verify Service Principal permissions

2. Check project path format

3. Confirm Client Secret is valid## Changelog```bash1. **[GitHub Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)에서 최신 `azure-ai-chatbot-wordpress.zip` 다운로드**

4. Check network/firewall settings



**Q: Can I test the connection before going live?**  

A: Yes. Use the **Test Connection** button in settings, or run the provided test scripts in Azure Cloud Shell.### v2.2.4 (2025-10-05)curl -sL https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash2. WordPress 관리자 페이지 접속



**Q: The chatbot appears but doesn't respond. What's wrong?**  - Fixed HTTP 404 errors in Chat mode

A: Check browser console (F12) for errors. Common causes:

1. Invalid API key- Improved API version initialization logic```3. **플러그인** → **새로 추가** → **플러그인 업로드** 클릭

2. Rate limiting

3. Network restrictions- Optimized multi-provider API endpoints and authentication

4. Incorrect endpoint configuration

4. 다운로드한 ZIP 파일 선택

---

### v2.2.3 (2025-10-05)

## Contributing

- Enhanced README with detailed version history**Automatically handled:**5. **지금 설치** 클릭

Contributions are welcome! Here's how you can help:

- Strengthened FAQ section

### Reporting Issues

- Updated future roadmap1. 📋 Select Azure subscription (if multiple)6. 설치 완료 후 **플러그인 활성화** 클릭

1. Check [existing issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)

2. Create a new issue with:

   - Clear description

   - Steps to reproduce[View full changelog](CHANGELOG.md)2. 🏢 Select AI Foundry resource

   - Expected vs actual behavior

   - WordPress version

   - PHP version

   - Error messages/logs---3. 🔐 **Automatic Service Principal creation/verification**> 💡 **Tip**: ZIP 파일 압축을 풀 필요 없습니다! 그대로 업로드하세요.



### Submitting Code



1. Fork the repository**Made with ❤️ for WordPress & Azure AI**   - Reuse existing SP if available

2. Create a feature branch:

   ```bash

   git checkout -b feature/amazing-feature   - Create new if not exists### 수동 설치 (개발자용)

   ```

3. Make your changes   - **Automatic Client Secret generation**

4. Test thoroughly:

   - Test both Chat and Agent modes   - **Automatic permission grant** (Cognitive Services User)소스 코드를 직접 편집하려는 경우:

   - Check on different WordPress versions

   - Verify translations work4. 🤖 Automatic Agent selection or creation

5. Commit with clear messages:

   ```bash5. 🧪 Complete connection test (Thread → Message → Run)1. 이 저장소를 다운로드하거나 복제

   git commit -m "feat: add amazing feature"

   ```6. ✅ **Output all 5 WordPress settings**   ```bash

6. Push to your fork:

   ```bash   git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git

   git push origin feature/amazing-feature

   ```---   ```

7. Open a Pull Request

2. 폴더를 `/wp-content/plugins/` 디렉토리에 업로드

### Code Style

## ⚙️ Configuration Options3. WordPress 관리자 페이지에서 플러그인 활성화

- Follow WordPress [Coding Standards](https://developer.wordpress.org/coding-standards/)

- Use meaningful variable names

- Comment complex logic

- Keep functions focused and small### Azure Connection### WordPress.org에서 설치 (향후 지원 예정)



---



## License| Setting | Description | Required |1. WordPress 관리자 페이지 → **플러그인** → **새로 추가**



This project is licensed under the **GPL-2.0+ License**.|---------|-------------|----------|2. "Azure AI Chatbot" 검색



This means you can:| API Key | Azure AI API key | ✅ |3. **지금 설치** → **활성화**

- Use the plugin for free

- Modify the source code| Project Endpoint | Azure AI Project URL | ✅ |

- Distribute your modifications

- Use it commercially| Agent ID | Agent ID to use | ✅ |## 🚀 빠른 시작



See [LICENSE](LICENSE) file for full details.



---### Widget Settings### 방법 1: Chat 모드 (간단 - 권장) ⭐



## Support



### Documentation| Setting | Description | Default |Chat 모드는 API Key만으로 즉시 사용 가능합니다!

- [Azure Setup Guide](docs/AZURE_SETUP.md) - Detailed Azure configuration

- [Entra ID Setup](ENTRA_ID_SETUP.md) - Service Principal configuration|---------|-------------|---------|

- [User Guide](docs/USER_GUIDE.md) - Complete user documentation

| Widget Active | Whether to show chat widget | Disabled |#### 📋 Azure Cloud Shell에서 대화형 테스트 (권장) ⭐

### Get Help

- **Issues**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)| Widget Position | Button position (right/left bottom) | Right bottom |

- **Discussions**: [GitHub Discussions](https://github.com/asomi7007/azure-ai-chatbot-wordpress/discussions)

| Chat Title | Chat window title | "AI Assistant" |Azure Cloud Shell ([shell.azure.com](https://shell.azure.com))에서 **한 줄 명령**으로 실행:

### Links

- **Website**: [Plugin Homepage](https://github.com/asomi7007/azure-ai-chatbot-wordpress)| Welcome Message | First message | "Hello! How can I help you?" |

- **Releases**: [Download Latest](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

- **Changelog**: [View Changes](CHANGELOG.md)```bash



---### Designcurl -sL https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash



## Changelog```



### v2.2.4 (2025-10-05)| Setting | Description | Default |

- Fixed HTTP 404 errors in Chat mode

- Improved API version initialization logic|---------|-------------|---------|**대화형으로 진행됩니다:**

- Optimized multi-provider API endpoints and authentication

| Primary Color | Button and user message color | #667eea |1. 📋 Azure 구독 선택 (여러 개인 경우)

### v2.2.3 (2025-10-05)

- Enhanced README with detailed version history| Secondary Color | Gradient second color | #764ba2 |2. 📦 Azure OpenAI 리소스 선택

- Strengthened FAQ section

- Updated roadmap3. 📊 배포된 모델 선택



### v2.2.0 (2025-10-05)## 🎨 Customization4. 🔐 자동으로 엔드포인트/API Key 가져오기

- Added support for 6 AI providers

- Dynamic UI based on provider selection5. 🧪 연결 테스트 실행

- Improved error messages for both modes

### CSS Customization6. ✅ WordPress 설정값 출력

### v2.1.0 (2025-10-05)

- Introduced dual mode support (Chat + Agent)

- Integrated Azure AI Foundry Assistants API v1

- Added Entra ID OAuth 2.0 authenticationAdd to your theme's `style.css`:---

- Implemented thread management



[View full changelog](CHANGELOG.md)

```css#### 📝 수동으로 설정값 확인 (선택사항)

---

/* Chat button size */

**Made with ❤️ for WordPress & Azure AI**

.chatbot-toggle {Azure Cloud Shell에서 직접 명령어 실행:

    width: 70px !important;

    height: 70px !important;```bash

}# 구독 목록 보기

az account list --query "[].{Name:name, ID:id}" -o table

/* Chat window size */

.chatbot-window {# 특정 구독 선택

    width: 400px !important;az account set --subscription "your-subscription-name"

    height: 650px !important;

}# OpenAI 리소스 목록 보기

```az cognitiveservices account list --query "[?kind=='OpenAI'].{Name:name, RG:resourceGroup, Location:location}" -o table



### Adding Function Calling# 엔드포인트 확인

az cognitiveservices account show \

Add to `functions.php`:  --name "your-resource-name" \

  --resource-group "your-rg" \

```php  --query "properties.endpoint" -o tsv

add_filter('azure_chatbot_function_call', function($result, $function_name, $arguments) {

    if ($function_name === 'my_custom_function') {# API Key 확인

        // Custom logicaz cognitiveservices account keys list \

        return ['result' => 'success'];  --name "your-resource-name" \

    }  --resource-group "your-rg" \

    return $result;  --query "key1" -o tsv

}, 10, 3);

```# 배포된 모델 확인

az cognitiveservices account deployment list \

## 🔧 Developer Guide  --name "your-resource-name" \

  --resource-group "your-rg" \

### Hooks  --query "[].{Name:name, Model:properties.model.name}" -o table

```

**Filters:**

- `azure_chatbot_function_call` - Handle function calling#### WordPress 플러그인 설정

- `azure_chatbot_before_send` - Before message send

- `azure_chatbot_response_format` - Change response format위 스크립트 결과에서 나온 값을 그대로 WordPress 관리자 페이지에 입력하세요:



**Actions:**1. WordPress 관리자 → **Azure AI Chatbot** → **설정**

- `azure_chatbot_after_response` - After receiving response2. **작동 모드**: `Chat 모드 (OpenAI 호환)` 선택

- `azure_chatbot_widget_loaded` - Widget load complete3. 스크립트 결과에서 나온 값 입력

4. **저장** 버튼 클릭

### API Endpoints5. **연결 테스트** 버튼으로 확인



```---

POST /wp-json/azure-chatbot/v1/chat

```### 방법 2: Agent 모드 (고급 기능) 🤖



**Request Body:**Agent 모드는 Azure AI Foundry의 **Assistants API v1**을 사용하여 다음 고급 기능을 제공합니다:

```json

{**✨ Agent 모드 주요 기능:**

    "message": "User message",- 🧵 **Thread 관리**: 대화 컨텍스트 자동 유지 (재방문 시 이전 대화 기억)

    "thread_id": "thread_xxxxx" (optional)- 🛠️ **Function Calling**: 외부 API 호출, 데이터베이스 조회 등 확장 가능

}- 📎 **파일 업로드**: 문서 분석 및 RAG (Retrieval-Augmented Generation)

```- 🔄 **비동기 Run**: 장시간 실행 작업 지원

- 📊 **상태 추적**: 실시간 Run 상태 모니터링 (queued → in_progress → completed)

**Response:**

```json**⚠️ 중요: API 버전**

{- Azure AI Foundry Assistants API는 **`api-version=v1`만 지원**됩니다

    "success": true,- `2024-12-01-preview` 등 날짜 기반 버전은 Sweden Central 등 일부 리전에서 작동하지 않습니다

    "reply": "AI response",- 본 플러그인은 `v1`을 사용하여 모든 리전에서 호환됩니다

    "thread_id": "thread_xxxxx"

}#### 📋 Azure Cloud Shell에서 대화형 테스트 (권장) ⭐

```

Azure Cloud Shell ([shell.azure.com](https://shell.azure.com))에서 **한 줄 명령**으로 실행:

## 📊 System Requirements

```bash

- **WordPress**: 6.0 or highercurl -sL https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash

- **PHP**: 7.4 or higher```

- **Azure Subscription**: Active Azure subscription

- **Memory**: Minimum 128MB PHP memory limit**자동으로 처리됩니다:**

- **SSL**: HTTPS recommended (API security)1. 📋 Azure 구독 선택 (여러 개인 경우)

2. 🏢 AI Foundry 리소스 선택

## 🐛 Troubleshooting3. 🔐 **Service Principal 자동 생성/확인**

   - 기존 SP 있으면 재사용

### Chat button not visible   - 없으면 새로 생성

   - **Client Secret 자동 생성**

1. Check **Settings** → **Widget Active**   - **권한 자동 부여** (Cognitive Services User)

2. Verify API Key, endpoint, and Agent ID are all entered4. 🤖 Agent 자동 선택 또는 생성

3. Clear browser cache and refresh5. 🧪 완전한 연결 테스트 (Thread → Message → Run)

6. ✅ **WordPress 설정값 5개 모두 출력**:

### API Error   - Agent 엔드포인트

   - Agent ID

1. Recheck API Key in Azure Portal   - Client ID

2. Diagnose with **Connection Test** button   - Client Secret

3. Check `/wp-content/debug.log`   - Tenant ID



### Enable Debug Mode---



Add to `wp-config.php`:#### 📝 수동 설정 스크립트 (선택사항)



```php더 세밀한 제어가 필요한 경우:

define('WP_DEBUG', true);

define('WP_DEBUG_LOG', true);**⚡ 복사 → 붙여넣기 → 실행:**

define('WP_DEBUG_DISPLAY', false);

``````bash

cat > setup_azure_agent.sh << 'EOFSCRIPT'

## 💰 Pricing Guide#!/bin/bash

set -e

### Azure AI Foundry Pricing (As of 2025)

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**GPT-4o Model:**# 🚀 Azure AI Chatbot WordPress - Agent 모드 자동 설정

- Input: $2.50 per 1M tokens# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

- Output: $10.00 per 1M tokens

echo ""

**Estimated Costs:**echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

- 1,000 conversations/month (avg 500 tokens) ≈ $3-5echo "📋 Azure AI 프로젝트 정보 입력"

- 10,000 conversations/month ≈ $30-50echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo ""

Detailed pricing: [Azure Pricing Calculator](https://azure.microsoft.com/pricing/calculator/)

# 사용자 입력

## 🔐 Security Considerationsread -p "Resource Group 이름: " RESOURCE_GROUP

read -p "AI Foundry 리소스 이름: " ACCOUNT_NAME

### Encrypted API Key Storageread -p "프로젝트 이름 (리소스와 동일하면 엔터): " PROJECT_NAME

PROJECT_NAME=${PROJECT_NAME:-$ACCOUNT_NAME}

**Important**: This plugin does NOT store API Keys in plain text!read -p "Service Principal 이름 (기본: azure-ai-chatbot-wp): " SP_NAME

SP_NAME=${SP_NAME:-"azure-ai-chatbot-wp"}

#### Encryption Details

- **Algorithm**: AES-256-CBCecho ""

- **Key Generation**: Combination of WordPress security constants (SHA-256 hash)echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

- **IV**: Randomly generated (different each time)echo "� Azure 구독 정보 확인 중..."

- **Requirements**: OpenSSL PHP extension (installed by default on most servers)echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"



### Security FeaturesSUBSCRIPTION_ID=$(az account show --query "id" -o tsv)

TENANT_ID=$(az account show --query "tenantId" -o tsv)

- ✅ **AES-256 Encryption**: API Key database encryptionRESOURCE_ID="/subscriptions/$SUBSCRIPTION_ID/resourceGroups/$RESOURCE_GROUP/providers/Microsoft.CognitiveServices/accounts/$ACCOUNT_NAME"

- ✅ **API Key Masking**: Hide full key on settings page (e.g., ab12••••••••xy89)

- ✅ **Server-side Processing**: API Key used only on server, no client exposureecho "✅ Subscription ID: $SUBSCRIPTION_ID"

- ✅ **WordPress Nonce**: CSRF attack protectionecho "✅ Tenant ID: $TENANT_ID"

- ✅ **Input Validation**: All input sanitizationecho ""

- ✅ **Permission Check**: Only admins can change settings

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

### Security Recommendationsecho "🔐 Service Principal 생성 중..."

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

1. **Use HTTPS**: SSL certificate required

2. **Update WordPress**: Keep latest version# Service Principal 생성 시도

3. **Strong Passwords**: Secure admin accountsSP_OUTPUT=$(az ad sp create-for-rbac \

4. **Enable 2FA**: Use two-factor authentication  --name "$SP_NAME" \

5. **Regular Backups**: Backup database and files  --role "Cognitive Services User" \

6. **Security Plugins**: Use Wordfence, iThemes Security, etc.  --scopes "$RESOURCE_ID" \

  2>&1)

## 📈 Performance Optimization

if echo "$SP_OUTPUT" | grep -q "appId"; then

### Recommended Settings    echo "✅ Service Principal 생성 완료!"

    CLIENT_ID=$(echo $SP_OUTPUT | jq -r '.appId')

1. **Use Caching Plugin**: WP Rocket, W3 Total Cache    CLIENT_SECRET=$(echo $SP_OUTPUT | jq -r '.password')

2. **Use CDN**: Cloudflare, Amazon CloudFrontelse

3. **Image Optimization**: Imagify, ShortPixel    echo "⚠️  이미 존재하는 Service Principal입니다."

4. **Database Optimization**: WP-Optimize    echo "   새 Client Secret을 생성합니다..."

    

### Speed Improvement Tips    APP_ID=$(az ad sp list --display-name "$SP_NAME" --query "[0].appId" -o tsv)

    if [ -z "$APP_ID" ]; then

- Save Thread ID to local storage to prevent unnecessary creation        echo "❌ Service Principal을 찾을 수 없습니다."

- Keep agent prompts concise        echo "   다른 이름으로 다시 시도하거나 Azure Portal에서 수동으로 생성하세요."

- Consider caching function calling responses        exit 1

    fi

## 🌍 Internationalization    

    SP_OUTPUT=$(az ad app credential reset --id "$APP_ID" --append --years 1)

Currently available in Korean and English. Additional languages planned for future updates.    CLIENT_ID=$APP_ID

    CLIENT_SECRET=$(echo $SP_OUTPUT | jq -r '.password')

### Translation Contribution    echo "✅ Client Secret 재생성 완료!"

fi

Want to contribute translations?

- `.pot` file: `languages/azure-ai-chatbot.pot`AGENT_ENDPOINT="https://${ACCOUNT_NAME}.services.ai.azure.com/api/projects/${PROJECT_NAME}"

- Contact: admin@eldensolution.kr

echo ""

## 🤝 Contributingecho "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "🤖 AI Agent 확인 중..."

### Bug Reportsecho "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"



Please email with following information:# Token 생성

- WordPress versionTOKEN=$(curl -s -X POST "https://login.microsoftonline.com/$TENANT_ID/oauth2/v2.0/token" \

- PHP version  -H "Content-Type: application/x-www-form-urlencoded" \

- Plugin version  -d "client_id=$CLIENT_ID" \

- Error message  -d "client_secret=$CLIENT_SECRET" \

- Reproduction steps  -d "scope=https://ai.azure.com/.default" \

  -d "grant_type=client_credentials" | jq -r '.access_token')

**Contact**: admin@eldensolution.kr

if [ "$TOKEN" == "null" ] || [ -z "$TOKEN" ]; then

### Feature Requests    echo "⚠️  토큰 생성 실패. 권한 설정 중일 수 있습니다."

    echo "   1-2분 후 다시 시도하거나 Agent ID를 수동으로 입력하세요."

Have an idea for a new feature?    AGENT_ID="[AI Foundry에서 확인 필요]"

- Email: admin@eldensolution.krelse

- Subject: [Feature Request] Feature Title    # Assistants 목록 조회 (v1 API 사용)

    ASSISTANTS=$(curl -s \

### Code Contribution      "${AGENT_ENDPOINT}/assistants?api-version=v1" \

      -H "Authorization: Bearer $TOKEN")

1. Fork this repository    

2. Create your feature branch: `git checkout -b feature/AmazingFeature`    AGENT_COUNT=$(echo $ASSISTANTS | jq -r '.data | length' 2>/dev/null || echo "0")

3. Commit your changes: `git commit -m 'Add some AmazingFeature'`    

4. Push to the branch: `git push origin feature/AmazingFeature`    if [ "$AGENT_COUNT" == "0" ]; then

5. Open a Pull Request        echo ""

        echo "❌ Agent가 존재하지 않습니다!"

## 📝 Changelog        echo ""

        echo "다음 방법 중 하나를 선택하세요:"

### v2.2.4 (2025-10-05) - 🐛 Chat Mode HTTP 404 Fix        echo ""

- Completely resolved HTTP 404 errors in Chat mode        echo "1️⃣  AI Foundry Portal에서 생성 (권장)"

- Improved API version initialization logic        echo "   https://ai.azure.com → Agents → Create"

- Optimized API endpoints and authentication methods        echo ""

        echo "2️⃣  Azure Cloud Shell에서 생성:"

### v2.2.3 (2025-10-05) - 📖 Detailed Version History        echo ""

- Detailed update of version history in README.md        read -p "   지금 생성하시겠습니까? (y/n): " CREATE_AGENT

- Specified key features and fixes for each version        

- Significantly enhanced FAQ section        if [ "$CREATE_AGENT" == "y" ]; then

            read -p "   Agent 이름: " AGENT_NAME

### v2.2.2 (2025-10-05) - 📚 Documentation Improvements            read -p "   Agent 설명 (옵션): " AGENT_DESC

- Updated Plugin URI to GitHub repository link            read -p "   사용할 모델 (기본: gpt-4o): " AGENT_MODEL

- Added version badges and release links to README.md            AGENT_MODEL=${AGENT_MODEL:-"gpt-4o"}

- Added complete changelog to readme.txt            

            NEW_AGENT=$(curl -s -X POST \

### v2.2.1 (2025-10-05) - 🐛 Hotfix              "${AGENT_ENDPOINT}/assistants?api-version=v1" \

- Automatic trailing slash removal on endpoint input              -H "Authorization: Bearer $TOKEN" \

- Real-time input validation              -H "Content-Type: application/json" \

              -d "{\"model\":\"$AGENT_MODEL\",\"name\":\"$AGENT_NAME\",\"description\":\"$AGENT_DESC\",\"instructions\":\"당신은 친절한 AI 도우미입니다.\"}")

### v2.2.0 (2025-10-05) - 🌐 Multi AI Provider Support            

- Support for 6 AI providers            AGENT_ID=$(echo $NEW_AGENT | jq -r '.id')

- Dynamic UI based on provider selection            echo "✅ Agent 생성 완료: $AGENT_ID"

- Agent mode test script        else

- Mode-specific error messages            AGENT_ID="[나중에 AI Foundry에서 생성 후 입력]"

        fi

### v2.1.0 (2025-10-05) - 🎯 Dual Mode Support    else

- Chat mode + Agent mode dual support        echo "✅ $AGENT_COUNT 개의 Agent 발견!"

- Azure AI Foundry Assistants API v1 integration        echo ""

- Entra ID OAuth 2.0 authentication        echo "사용 가능한 Agents:"

- Thread management        echo $ASSISTANTS | jq -r '.data[] | "  - \(.id): \(.name // "Unnamed")"'

- Adaptive polling        echo ""

        

### v2.0.0 (2025-10-04) - 🎨 Complete Renewal        if [ "$AGENT_COUNT" == "1" ]; then

- Admin page configuration            AGENT_ID=$(echo $ASSISTANTS | jq -r '.data[0].id')

- AES-256 API Key encryption            AGENT_NAME=$(echo $ASSISTANTS | jq -r '.data[0].name // "Unnamed"')

- Customization options            echo "✅ 자동 선택: $AGENT_ID ($AGENT_NAME)"

- Connection test feature        else

- Editable markdown guide            read -p "사용할 Agent ID를 입력하세요: " AGENT_ID

        fi

### v1.0.0 (2025-10-03) - 🎉 Initial Release    fi

- Azure AI Foundry agent integrationfi

- Basic chat widget

- wp-config.php based settingsecho ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

## 📚 Additional Resourcesecho "✅ 설정 완료!"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

### Official Documentationecho ""

- [Azure AI Foundry](https://learn.microsoft.com/azure/ai-foundry/)echo "📋 WordPress에 아래 값을 복사하여 입력하세요:"

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

### Tutorialsecho "Agent 엔드포인트:"

- [Creating Azure AI Agent](https://ai.azure.com)echo "$AGENT_ENDPOINT"

- [Function Calling Guide](https://learn.microsoft.com/azure/ai-foundry/agents/)echo ""

echo "Agent ID:"

### Communityecho "$AGENT_ID"

- **Email Support**: admin@eldensolution.krecho ""

- **Website**: https://www.eldensolution.krecho "Client ID:"

echo "$CLIENT_ID"

## 📄 Licenseecho ""

echo "Client Secret:"

This project is distributed under the GPL-2.0+ license.echo "$CLIENT_SECRET"

echo ""

```echo "Tenant ID:"

Copyright (C) 2025 Elden Solutionecho "$TENANT_ID"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

This program is free software; you can redistribute it and/or modifyecho ""

it under the terms of the GNU General Public License as published byecho "⚠️  중요: Client Secret은 지금만 표시됩니다!"

the Free Software Foundation; either version 2 of the License, orecho "         안전한 곳에 즉시 저장하세요!"

(at your option) any later version.echo ""

```EOFSCRIPT



## 👤 Authorchmod +x setup_azure_agent.sh

./setup_azure_agent.sh

**Elden Solution**```

- Website: https://www.eldensolution.kr

- Email: admin@eldensolution.kr**� 사용 방법:**

- Location: South Korea1. Azure Cloud Shell (https://shell.azure.com) 접속

2. 위 전체 코드 블록 복사

## 🙏 Acknowledgments3. Cloud Shell에 붙여넣기

4. 프롬프트에 따라 정보 입력

Thanks to everyone who helped create this plugin:5. 출력된 값들을 WordPress 설정에 입력

- Microsoft Azure AI Team

- WordPress Community**📋 출력 예시:**

- All beta testers

```

Developed with ❤️ by [Elden Solution](https://www.eldensolution.kr)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Agent 엔드포인트:

## 💡 Roadmaphttps://your-resource.services.ai.azure.com/api/projects/your-project



### v2.3.0 (Planned)Agent ID:

- [ ] Real-time streaming responses (Server-Sent Events)asst_xxxxxxxxxxxxxxxxxxxxxx

- [ ] Conversation history dashboard

- [ ] File upload support (images, PDFs)Client ID:

- [ ] Voice input/output (Speech-to-Text, Text-to-Speech)xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx



### v2.4.0 (Planned)Client Secret:

- [ ] Complete multi-language support (English, Japanese, Chinese, etc.)xxx~xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

- [ ] Advanced analytics dashboard (conversation stats, user patterns)

- [ ] A/B testing featureTenant ID:

- [ ] White-label optionsxxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

### v3.0.0 (Long-term)```

- [ ] AI training data management (Fine-tuning)

- [ ] Multisite support#### 개별 명령어로 설정 (선택사항)

- [ ] REST API expansion

- [ ] Webhook integration스크립트를 사용하지 않는 경우 개별 명령어:

- [ ] Multiple agent concurrent execution

```bash

## ❓ FAQ# 1. Service Principal 생성

az ad sp create-for-rbac \

**Q: Is it free?**    --name "azure-ai-chatbot-wordpress" \

A: The plugin is free and open source, but AI service usage fees (Azure OpenAI, OpenAI, etc.) are separate.  --role "Cognitive Services User" \

  --scopes "/subscriptions/{SUBSCRIPTION_ID}/resourceGroups/{RG}/providers/Microsoft.CognitiveServices/accounts/{ACCOUNT}"

**Q: Which AI services are supported?**  

A: **Chat mode** supports Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, and OpenAI-compatible APIs. **Agent mode** supports Azure AI Foundry Assistants API.# 2. 출력에서 다음 정보 복사:

# - appId → Client ID

**Q: What's the difference between Chat mode and Agent mode?**  # - password → Client Secret

A: **Chat mode** is a simple conversational chatbot requiring only an API Key. **Agent mode** provides advanced features like Thread management, Function Calling, RAG, etc., and requires Entra ID authentication.# - tenant → Tenant ID



**Q: Can I use it commercially?**  # 3. Agent ID는 Azure AI Foundry (https://ai.azure.com)에서 확인

A: Yes, freely available under GPL-2.0+ license.```



**Q: How do I get updates?**  #### Client Secret 재생성 (분실 시)

A: Download the latest ZIP file from [GitHub Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest) and update manually. (Automatic updates planned when registered on WordPress.org)

```bash

**Q: Is the API Key secure?**  # 기존 Service Principal의 새 Client Secret 생성

A: Yes, safely stored with AES-256 encryption and processed only on server. Not exposed to client (browser).az ad app credential reset \

  --id "{CLIENT_ID}" \

**Q: How do I get technical support?**    --append \

A: Contact via [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues) or email admin@eldensolution.kr.  --years 1



**Q: Are there test methods?**  # 출력된 password를 WordPress에 입력

A: Yes, you can test Azure connections with `test-chat-mode.sh` and `test-agent-mode.sh` scripts.```



------



⭐ If you find this plugin useful, please star it on GitHub!### 1단계: Azure AI Foundry 정보 확인 (레거시)



🐛 Found a bug? [Open an issue](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues).Azure Portal에서 다음 정보를 확인하세요:



💬 Have questions? Contact admin@eldensolution.kr.- **API Key**: AI Foundry 리소스 → "키 및 엔드포인트"

- **프로젝트 엔드포인트**: `https://[리소스명].services.ai.azure.com/api/projects/[프로젝트명]`

🌐 More solutions: [www.eldensolution.kr](https://www.eldensolution.kr)- **에이전트 ID**: AI Foundry에서 생성한 에이전트 ID (예: `asst_xxxxx`)


### 2단계: 플러그인 설정

1. WordPress 관리자 → **AI Chatbot** → **설정**
2. Azure 정보 입력
3. **위젯 활성화** 체크
4. **설정 저장**

### 3단계: 테스트

- **연결 테스트** 버튼으로 Azure 연결 확인
- 웹사이트 방문하여 채팅 버튼 확인

## 📁 파일 구조

```
azure-ai-chatbot/
├── azure-ai-chatbot.php      # 메인 플러그인 파일
├── README.md                  # 이 파일
├── assets/                    # CSS/JS 리소스
│   ├── chatbot.css           # 프론트엔드 스타일
│   ├── chatbot.js            # 프론트엔드 스크립트
│   ├── admin.css             # 관리자 스타일
│   └── admin.js              # 관리자 스크립트
├── templates/                 # PHP 템플릿
│   ├── settings-page.php     # 설정 페이지
│   └── guide-page.php        # 가이드 페이지
└── docs/                      # 문서
    └── USER_GUIDE.md         # 사용자 가이드 (편집 가능)
```

## ⚙️ 설정 옵션

### Azure 연결

| 설정 | 설명 | 필수 |
|------|------|------|
| API Key | Azure AI API 키 | ✅ |
| 프로젝트 엔드포인트 | Azure AI 프로젝트 URL | ✅ |
| 에이전트 ID | 사용할 에이전트 ID | ✅ |

### 위젯 설정

| 설정 | 설명 | 기본값 |
|------|------|--------|
| 위젯 활성화 | 채팅 위젯 표시 여부 | 비활성화 |
| 위젯 위치 | 버튼 위치 (오른쪽/왼쪽 하단) | 오른쪽 하단 |
| 채팅 제목 | 채팅창 제목 | "AI 도우미" |
| 환영 메시지 | 첫 메시지 | "안녕하세요! ..." |

### 디자인

| 설정 | 설명 | 기본값 |
|------|------|--------|
| 주 색상 | 버튼 및 사용자 메시지 색상 | #667eea |
| 보조 색상 | 그라데이션 두 번째 색상 | #764ba2 |

## 🎨 커스터마이징

### CSS 커스터마이징

테마의 `style.css`에 추가:

```css
/* 채팅 버튼 크기 */
.chatbot-toggle {
    width: 70px !important;
    height: 70px !important;
}

/* 채팅창 크기 */
.chatbot-window {
    width: 400px !important;
    height: 650px !important;
}
```

### Function Calling 추가

`functions.php`에 추가:

```php
add_filter('azure_chatbot_function_call', function($result, $function_name, $arguments) {
    if ($function_name === 'my_custom_function') {
        // 커스텀 로직
        return ['result' => 'success'];
    }
    return $result;
}, 10, 3);
```

## 🔧 개발자 가이드

### 훅 (Hooks)

**필터:**
- `azure_chatbot_function_call` - Function calling 처리
- `azure_chatbot_before_send` - 메시지 전송 전
- `azure_chatbot_response_format` - 응답 포맷 변경

**액션:**
- `azure_chatbot_after_response` - 응답 받은 후
- `azure_chatbot_widget_loaded` - 위젯 로드 완료

### API 엔드포인트

```
POST /wp-json/azure-chatbot/v1/chat
```

**요청 본문:**
```json
{
    "message": "사용자 메시지",
    "thread_id": "thread_xxxxx" (선택)
}
```

**응답:**
```json
{
    "success": true,
    "reply": "AI 응답",
    "thread_id": "thread_xxxxx"
}
```

## 📊 시스템 요구사항

- **WordPress**: 6.0 이상
- **PHP**: 7.4 이상
- **Azure 구독**: Active Azure subscription
- **메모리**: 최소 128MB PHP memory limit
- **SSL**: HTTPS 권장 (API 보안)

## 🐛 문제 해결

### 채팅 버튼이 보이지 않음

1. **설정** → **위젯 활성화** 체크 확인
2. API Key, 엔드포인트, 에이전트 ID 모두 입력 확인
3. 브라우저 캐시 삭제 및 새로고침

### API 오류 발생

1. Azure Portal에서 API Key 재확인
2. **연결 테스트** 버튼으로 진단
3. `/wp-content/debug.log` 확인

### 디버그 모드 활성화

`wp-config.php`에 추가:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 💰 비용 안내

### Azure AI Foundry 가격 (2025년 기준)

**GPT-4o 모델:**
- 입력: $2.50 per 1M tokens
- 출력: $10.00 per 1M tokens

**예상 비용:**
- 월 1,000건 대화 (평균 500토큰) ≈ $3-5
- 월 10,000건 대화 ≈ $30-50

자세한 요금: [Azure 가격 계산기](https://azure.microsoft.com/pricing/calculator/)

## 🔐 보안 고려사항

### API Key 암호화 저장

**중요**: 이 플러그인은 API Key를 평문으로 저장하지 않습니다!

#### 암호화 상세
- **알고리즘**: AES-256-CBC
- **키 생성**: WordPress 보안 상수 조합 (SHA-256 해시)
- **IV**: 랜덤 생성 (매번 다름)
- **요구사항**: OpenSSL PHP 확장 (대부분의 서버에 기본 설치)

#### 자동 보안 키 생성 ✨

**플러그인 활성화 시 자동으로:**

1. `wp-config.php`의 보안 키 확인
2. 보안 키가 없거나 기본값(`put your unique phrase here`)이면:
   - WordPress.org API에서 새 보안 키 자동 생성
   - `wp-config.php`에 자동 추가/업데이트
   - 기존 파일 백업 (`wp-config.php.backup-YYYYMMDD-HHMMSS`)
   - 성공 여부를 관리자 화면에 알림
3. 파일 쓰기 권한이 없으면 수동 설정 안내

**성공 시 표시:**
```
✅ WordPress 보안 키가 자동으로 생성되어 wp-config.php에 추가되었습니다!
백업 파일: wp-config.php.backup-2025-01-15-143022
```

**수동 설정 (필요 시):**
```php
// wp-config.php에 추가
define('AUTH_KEY', 'your-unique-phrase');
define('SECURE_AUTH_KEY', 'your-unique-phrase');
define('LOGGED_IN_KEY', 'your-unique-phrase');
define('NONCE_KEY', 'your-unique-phrase');
define('AUTH_SALT', 'your-unique-phrase');
define('SECURE_AUTH_SALT', 'your-unique-phrase');
define('LOGGED_IN_SALT', 'your-unique-phrase');
define('NONCE_SALT', 'your-unique-phrase');
```

보안 키 생성: https://api.wordpress.org/secret-key/1.1/salt/

### 보안 기능

- ✅ **AES-256 암호화**: API Key 데이터베이스 암호화
- ✅ **API Key 마스킹**: 설정 페이지에서 전체 키 숨김 (예: ab12••••••••xy89)
- ✅ **서버 사이드 처리**: API Key는 서버에서만 사용, 클라이언트 노출 없음
- ✅ **WordPress Nonce**: CSRF 공격 방어
- ✅ **입력 검증**: 모든 입력 sanitization
- ✅ **권한 확인**: 관리자만 설정 변경 가능
- ❌ **Rate Limiting**: 향후 업데이트 예정

### 보안 권장사항

1. **HTTPS 사용**: SSL 인증서 필수
2. **WordPress 업데이트**: 최신 버전 유지
3. **강력한 비밀번호**: 관리자 계정 보안
4. **2FA 활성화**: 2단계 인증 사용
5. **정기 백업**: 데이터베이스 및 파일 백업
6. **보안 플러그인**: Wordfence, iThemes Security 등 사용

## 📈 성능 최적화

### 권장 설정

1. **캐싱 플러그인 사용**: WP Rocket, W3 Total Cache
2. **CDN 활용**: Cloudflare, Amazon CloudFront
3. **이미지 최적화**: Imagify, ShortPixel
4. **데이터베이스 최적화**: WP-Optimize

### 속도 개선 팁

- Thread ID를 로컬 스토리지에 저장하여 불필요한 생성 방지
- 에이전트 프롬프트를 간결하게 유지
- Function calling 응답 캐싱 고려

## 🌍 다국어 지원

현재 한국어로 제공되며, 향후 영어 등 추가 언어를 지원할 예정입니다.

### 번역 기여

번역에 참여하고 싶으신가요?
- `.pot` 파일: `languages/azure-ai-chatbot.pot`
- 연락처: admin@eldensolution.kr

## 🤝 기여하기

### 버그 리포트

다음 정보와 함께 이메일 보내주세요:
- WordPress 버전
- PHP 버전
- 플러그인 버전
- 오류 메시지
- 재현 단계

**연락처**: admin@eldensolution.kr

### 기능 제안

새 기능 아이디어가 있으신가요?
- 이메일: admin@eldensolution.kr
- 제목: [Feature Request] 기능 제목

### 코드 기여

1. Fork this repository
2. Create your feature branch: `git checkout -b feature/AmazingFeature`
3. Commit your changes: `git commit -m 'Add some AmazingFeature'`
4. Push to the branch: `git push origin feature/AmazingFeature`
5. Open a Pull Request

## 📝 변경 로그

### 2.0.0 (2025-01-XX)

**추가:**
- ✨ 관리자 페이지에서 모든 설정 가능
- ✨ 마크다운 가이드 편집 기능
- ✨ 색상 및 위치 커스터마이징
- ✨ 연결 테스트 기능
- ✨ Function calling 확장 포인트

**개선:**
- 🎨 향상된 UI/UX
- 🔒 보안 강화 (Nonce 검증)
- 📱 모바일 반응형 개선
- ⚡ 성능 최적화

**수정:**
- 🐛 Thread ID 저장 버그 수정
- 🐛 색상 선택기 버그 수정

### 1.0.0 (초기 릴리스)
- 기본 채팅 기능
- Azure AI Foundry 연동

## 📚 추가 리소스

### 공식 문서
- [Azure AI Foundry](https://learn.microsoft.com/azure/ai-foundry/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)

### 튜토리얼
- [Azure AI Agent 생성하기](https://ai.azure.com)
- [Function Calling 가이드](https://learn.microsoft.com/azure/ai-foundry/agents/)

### 커뮤니티
- **이메일 지원**: admin@eldensolution.kr
- **웹사이트**: https://www.eldensolution.kr

## 📄 라이선스

이 프로젝트는 GPL-2.0+ 라이선스 하에 배포됩니다.

```
Copyright (C) 2025 Elden Solution (엘던솔루션)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
```

## 👤 제작자

**엘던솔루션 (Elden Solution)**
- 웹사이트: https://www.eldensolution.kr
- 이메일: admin@eldensolution.kr
- 위치: 대한민국

## 🙏 감사의 말

이 플러그인을 만드는 데 도움을 주신 분들:
- Microsoft Azure AI 팀
- WordPress 커뮤니티
- 모든 베타 테스터분들

Developed with ❤️ by [Elden Solution](https://www.eldensolution.kr)

## 💡 향후 계획

### v2.3.0 (예정)
- [ ] 실시간 스트리밍 응답 (Server-Sent Events)
- [ ] 대화 내역 대시보드
- [ ] 파일 업로드 지원 (이미지, PDF)
- [ ] 음성 입력/출력 (Speech-to-Text, Text-to-Speech)

### v2.4.0 (예정)
- [ ] 완전한 다국어 지원 (영어, 일본어, 중국어 등)
- [ ] 고급 분석 대시보드 (대화 통계, 사용자 패턴)
- [ ] A/B 테스트 기능
- [ ] 화이트라벨 옵션

### v3.0.0 (장기)
- [ ] AI 학습 데이터 관리 (Fine-tuning)
- [ ] 멀티사이트 지원
- [ ] REST API 확장
- [ ] Webhook 통합
- [ ] 다중 에이전트 동시 실행

## ❓ FAQ

**Q: 무료인가요?**  
A: 플러그인은 무료 오픈소스이지만, AI 서비스 사용료(Azure OpenAI, OpenAI 등)는 별도로 발생합니다.

**Q: 어떤 AI 서비스를 지원하나요?**  
A: **Chat 모드**에서는 Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok 및 OpenAI 호환 API를 모두 지원합니다. **Agent 모드**에서는 Azure AI Foundry Assistants API를 지원합니다.

**Q: Chat 모드와 Agent 모드의 차이는?**  
A: **Chat 모드**는 간단한 대화형 챗봇으로 API Key만 있으면 됩니다. **Agent 모드**는 Thread 관리, Function Calling, RAG 등 고급 기능을 제공하며 Entra ID 인증이 필요합니다.

**Q: 상업적 이용이 가능한가요?**  
A: 네, GPL-2.0+ 라이선스 하에 자유롭게 사용 가능합니다.

**Q: 업데이트는 어떻게 받나요?**  
A: [GitHub Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)에서 최신 ZIP 파일을 다운로드하여 수동으로 업데이트합니다. (향후 WordPress.org 등록 시 자동 업데이트 지원 예정)

**Q: API Key는 안전한가요?**  
A: 네, AES-256 암호화로 안전하게 저장되며 서버에서만 처리됩니다. 클라이언트(브라우저)에는 노출되지 않습니다.

**Q: 기술 지원은 어떻게 받나요?**  
A: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)에 문의하시거나 admin@eldensolution.kr로 이메일 주세요.

**Q: 테스트 방법이 있나요?**  
A: 네, `test-chat-mode.sh`와 `test-agent-mode.sh` 스크립트로 Azure 연결을 테스트할 수 있습니다.

---

⭐ 이 플러그인이 유용하다면 GitHub에서 Star를 눌러주세요!

🐛 버그를 발견하셨나요? [Issue를 등록](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)해주세요.

💬 질문이 있으신가요? admin@eldensolution.kr로 연락주세요.

🌐 더 많은 솔루션: [www.eldensolution.kr](https://www.eldensolution.kr)
