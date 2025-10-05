# Azure AI Chatbot for WordPress

[![Version](https://img.shields.io/badge/version-2.2.4-blue.svg)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)
[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)

A modern WordPress plugin that brings Azure AI Foundry agents and OpenAI-compatible chat models to your website with full Assistants API integration.

**[English](#) | [한국어](README-ko.md)**

---

## Why This Plugin?

This is the **WordPress plugin** that enables Azure AI Foundry Agent mode integration. While other plugins support basic chat APIs, this plugin uniquely provides:

- **Azure AI Foundry Agents**: Full Assistants API support with Function Calling, RAG, and file uploads
- **Dual-Mode Architecture**: Choose between simple Chat mode or advanced Agent mode
- **Enterprise Authentication**: Entra ID OAuth 2.0 support for enterprise deployments
- **Zero-Code Setup**: Automated scripts handle all Azure configuration

---

## Table of Contents

- [Features](#features)
- [Quick Start](#quick-start)
  - [Chat Mode Setup](#chat-mode-setup)
  - [Agent Mode Setup](#agent-mode-setup)
- [Installation](#installation)
- [Configuration](#configuration)
- [Security](#security)
- [Customization](#customization)
- [Troubleshooting](#troubleshooting)
- [Version History](#version-history)
- [FAQ](#faq)
- [Contributing](#contributing)
- [License](#license)

---

## Features

### Dual Mode Support

**Chat Mode** - Simple and Versatile
- Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok
- API Key authentication
- Real-time streaming responses
- Multi-provider support

**Agent Mode** - Advanced Capabilities
- Azure AI Foundry Assistants API v1
- Entra ID OAuth 2.0 authentication
- Function Calling and tool integration
- RAG (Retrieval Augmented Generation)
- File upload support
- Persistent conversation threads

### Core Features

- **Zero-Code Setup**: Complete configuration from WordPress admin panel
- **Enterprise Security**: AES-256 encryption for credentials
- **Fully Customizable**: Colors, position, messages, styling
- **Responsive Design**: Perfect on desktop and mobile
- **Connection Testing**: Verify API connections before deployment
- **Multilingual Support**: Korean and English with auto-detection

---

## Quick Start

### Chat Mode Setup

**Step 1: Get Configuration Values**

Run this script in [Azure Cloud Shell](https://shell.azure.com):

```bash
curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash
```

The script will:
1. List your Azure subscriptions
2. Find Azure OpenAI resources
3. Show deployed models
4. Retrieve endpoint and API key
5. Test the connection
6. Output WordPress configuration values

**Example Output:**
```
✅ Chat Mode Connection Successful!

WordPress Configuration:
• Mode: Chat Mode
• Endpoint: https://your-resource.openai.azure.com
• Deployment Name: gpt-4o
• API Key: abc123...xyz789
```

**Step 2: Configure WordPress**

1. Navigate to **AI Chatbot** → **Settings**
2. Select **Chat Mode**
3. Choose **Azure OpenAI**
4. Enter the values from script output
5. Click **Save Changes** → **Test Connection**

---

### Agent Mode Setup

**Step 1: Get Configuration Values**

Run this script in [Azure Cloud Shell](https://shell.azure.com):

```bash
curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash
```

The script will:
1. Find AI Foundry resources
2. Create or use existing Service Principal
3. Generate Client Secret
4. List or help create agents
5. Test complete connection
6. Output all WordPress configuration values

**Example Output:**
```
✅ Agent Mode Connection Successful!

WordPress Configuration:
• Mode: Agent Mode
• Endpoint: https://your-resource.services.ai.azure.com/api/projects/your-project
• Agent ID: asst_abc123xyz789
• Client ID: 12345678-1234-1234-1234-123456789012
• Client Secret: def456...uvw789
• Tenant ID: 87654321-4321-4321-4321-210987654321
```

**Step 2: Configure WordPress**

1. Navigate to **AI Chatbot** → **Settings**
2. Select **Agent Mode**
3. Enter the values from script output
4. Click **Save Changes** → **Test Connection**

---

## Installation

### Method 1: ZIP Upload (Recommended)

1. Download `azure-ai-chatbot-wordpress.zip` from [Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)
2. WordPress Admin → **Plugins** → **Add New** → **Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Click **Activate Plugin**

### Method 2: Git Clone

```bash
git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git
# Upload to /wp-content/plugins/
# Activate in WordPress admin
```

---

## Configuration

### Chat Mode Providers

| Provider | Endpoint | Model Examples | Authentication |
|----------|----------|----------------|----------------|
| **Azure OpenAI** | `https://{resource}.openai.azure.com` | `gpt-4o` | API Key |
| **OpenAI** | `https://api.openai.com` | `gpt-4-turbo` | API Key (sk-) |
| **Google Gemini** | `https://generativelanguage.googleapis.com` | `gemini-2.0-flash-exp` | API Key |
| **Anthropic Claude** | `https://api.anthropic.com` | `claude-3-5-sonnet-20241022` | API Key (sk-ant-) |
| **xAI Grok** | `https://api.x.ai` | `grok-beta` | API Key |
| **Other** | Custom endpoint | OpenAI-compatible models | API Key |

### Agent Mode Requirements

- **Agent ID**: Created in Azure AI Foundry (starts with `asst_`)
- **Tenant ID**: Microsoft Entra tenant ID
- **Client ID**: Service Principal application ID
- **Client Secret**: Generated secret (created by script)
- **Project Path**: Full Azure resource path

**Project Path Format:**
```
subscriptions/{subscription-id}/resourceGroups/{resource-group}/providers/Microsoft.MachineLearningServices/workspaces/{project-name}
```

---

## Security

### Credential Encryption

All sensitive data is encrypted before storage:

- **Algorithm**: AES-256-CBC
- **Key Storage**: WordPress authentication keys and salts
- **Encrypted Fields**: API Keys, Client Secrets
- **Decryption**: Only when needed for API calls

### Security Best Practices

1. **Use HTTPS**: Always run WordPress over HTTPS
2. **Regular Updates**: Keep WordPress and PHP up to date
3. **Restrict Admin Access**: Limit who can access plugin settings
4. **Rotate Secrets**: Regenerate API keys and secrets periodically
5. **Monitor Logs**: Check debug logs for unauthorized access

### Network Security

- Configure Azure firewall rules to allow WordPress server IP
- Use Service Principal with minimum required permissions
- Enable Azure Monitor for API usage tracking

---

## Customization

### Visual Customization

Configure in **AI Chatbot** → **Settings**:

- **Chatbot Title**: Header text
- **Welcome Message**: Initial greeting
- **Button Color**: Hex color code (e.g., `#667eea`)
- **Button Position**: Bottom right or bottom left

### Advanced CSS

Add to **Appearance** → **Customize** → **Additional CSS**:

```css
/* Change chatbot window size */
.azure-chatbot-window {
    width: 450px !important;
    height: 700px !important;
}

/* Custom message colors */
.azure-chatbot-message.user {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* Custom send button */
.azure-chatbot-send-btn {
    background: #667eea !important;
}
```

### JavaScript Hooks

```javascript
// Chatbot event listeners
document.addEventListener('azure-chatbot-opened', function() {
    console.log('Chatbot opened');
});

document.addEventListener('azure-chatbot-message-sent', function(event) {
    console.log('Message sent:', event.detail.message);
});
```

---

## File Structure

```
azure-ai-chatbot-wordpress/
├── azure-ai-chatbot.php       # Main plugin file
├── assets/
│   ├── admin.css              # Admin styles
│   ├── admin.js               # Admin scripts
│   ├── chatbot.css            # Widget styles
│   └── chatbot.js             # Widget scripts
├── templates/
│   ├── settings-page.php      # Settings UI
│   └── guide-page.php         # User guide
├── languages/
│   ├── *.po                   # Translation sources
│   ├── *.mo                   # Compiled translations
│   └── compile-po-to-mo.py    # Compiler script
├── docs/
│   ├── AZURE_SETUP.md         # Detailed setup guide
│   └── USER_GUIDE.md          # User documentation
├── test-chat-mode.sh          # Chat mode test script
├── test-agent-mode.sh         # Agent mode test script
├── README.md                  # This file (English)
├── README-ko.md               # Korean version
├── CHANGELOG.md               # Complete version history
└── LICENSE                    # GPL-2.0+
```

---

## Troubleshooting

### HTTP 404 Error (Chat Mode)

**Issue**: Getting 404 errors when testing

**Solutions**:
1. Remove trailing slash from endpoint URL
2. Verify deployment name (case-sensitive)
3. Check API key validity
4. Try different API version

**Manual Test**:
```bash
curl -X POST "https://YOUR-RESOURCE.openai.azure.com/openai/deployments/YOUR-DEPLOYMENT/chat/completions?api-version=2024-08-01-preview" \
  -H "api-key: YOUR-KEY" \
  -H "Content-Type: application/json" \
  -d '{"messages":[{"role":"user","content":"Hello"}]}'
```

### Agent Mode Connection Failed

**Issue**: Can't connect to Azure AI Foundry

**Solutions**:
1. Verify Service Principal has "Cognitive Services User" role
2. Check project path format
3. Confirm Client Secret is valid
4. Review network firewall rules

**Check Permissions**:
```bash
az role assignment list --assignee YOUR-CLIENT-ID
```

### Chatbot Not Appearing

**Issue**: Widget not showing on website

**Solutions**:
1. Clear WordPress cache
2. Verify plugin is activated
3. Check browser console (F12) for JavaScript errors
4. Temporarily disable other plugins
5. Switch to default theme to test

### Enable Debug Logging

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Log location: `/wp-content/debug.log`

---

## Version History

### Latest Release: v2.2.4 (2025-10-05)

**Fixes:**
- Fixed HTTP 404 errors in Chat mode
- Improved API version initialization logic
- Enhanced endpoint handling for multiple providers

[Download v2.2.4](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.4)

### Recent Updates

**v2.2.3** - Documentation and FAQ improvements  
**v2.2.2** - GitHub badges and changelog additions  
**v2.2.1** - Endpoint slash handling fix  
**v2.2.0** - Multi-provider support (6 AI services)  
**v2.1.0** - Dual mode introduction (Chat + Agent)  
**v2.0.0** - Complete plugin redesign  
**v1.0.0** - Initial release

[Full Changelog](CHANGELOG.md)

---

## FAQ

**Q: Which AI services can I use?**  
A: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, and any OpenAI-compatible API.

**Q: Does this plugin support Azure AI Foundry agents?**  
A: Yes, this plugin provides comprehensive Azure AI Foundry Agent mode integration with full Assistants API support.

**Q: Do I need coding skills?**  
A: No. Azure setup uses automated scripts, and all configuration is done via WordPress admin panel.

**Q: Is it secure?**  
A: Yes. All credentials are encrypted with AES-256. Use HTTPS for production.

**Q: What's the difference between Chat and Agent mode?**  
A: Chat mode is simple API calls. Agent mode uses Azure AI Foundry for advanced features like Function Calling and RAG.

**Q: Can I use it on multiple sites?**  
A: Yes, but each WordPress installation needs separate configuration.

**Q: Does it work with WordPress Multisite?**  
A: Yes. Each site can have independent configuration.

---

## Contributing

Contributions are welcome!

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/name`)
3. Commit your changes (`git commit -m 'Add feature'`)
4. Push to the branch (`git push origin feature/name`)
5. Open a Pull Request

Please follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/).

---

## Support

- **Documentation**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)
- **Discussions**: [GitHub Discussions](https://github.com/asomi7007/azure-ai-chatbot-wordpress/discussions)
- **Releases**: [Latest Version](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

---

## License

GPL-2.0+ License - see [LICENSE](LICENSE) file for details.

Free to use, modify, and distribute.

---

## Acknowledgments

Built for WordPress and Azure AI Foundry users who need enterprise-grade AI chat capabilities.

**Made with ❤️ for WordPress & Azure AI**