# Azure AI Chatbot - Language Files

This directory contains translation files for the Azure AI Chatbot plugin.

## Supported Languages

- **English (en_US)**: Default language (built-in strings)
- **Korean (ko_KR)**: Full translation available

## File Structure

- `*.po` - Portable Object files (human-readable translation files)
- `*.mo` - Machine Object files (compiled translation files used by WordPress)
- `azure-ai-chatbot.pot` - Translation template (for creating new translations)

## How Translations Work

1. **Automatic Language Detection**: The plugin automatically detects the user's browser language or WordPress site language
2. **Fallback**: If a translation is not available, English is used as the default
3. **Translation Priority**: 
   - WordPress site language setting (Settings → General → Site Language)
   - Browser language preference
   - English (default)

## Adding New Translations

If you want to add a translation for your language:

1. Install [Poedit](https://poedit.net/) (free translation editor)
2. Open `azure-ai-chatbot.pot` in Poedit
3. Create a new translation for your language
4. Save the file as `azure-ai-chatbot-{locale}.po` (e.g., `azure-ai-chatbot-ja.po` for Japanese)
5. Poedit will automatically generate the `.mo` file
6. Place both files in this directory
7. The plugin will automatically use the translation

## Compiling PO to MO (Advanced)

If you edit `.po` files manually, you need to compile them:

### Using msgfmt (command line):
```bash
msgfmt -o azure-ai-chatbot-ko_KR.mo azure-ai-chatbot-ko_KR.po
msgfmt -o azure-ai-chatbot-en_US.mo azure-ai-chatbot-en_US.po
```

### Using Poedit (GUI):
1. Open the `.po` file in Poedit
2. Click "File" → "Save" (automatically compiles to `.mo`)

## Translation Status

| Language | Code | Status | Translator |
|----------|------|--------|------------|
| English | en_US | ✅ Complete | Elden Solution |
| Korean | ko_KR | ✅ Complete | Elden Solution |

## Contributing Translations

We welcome translation contributions! Please submit `.po` and `.mo` files via:
- GitHub Pull Request: https://github.com/asomi7007/azure-ai-chatbot-wordpress
- Email: support@eldensolution.com

## Translation Strings

The plugin uses WordPress i18n best practices with the text domain `azure-ai-chatbot`.

Key translatable strings:
- Widget interface (buttons, placeholders, messages)
- Admin notices and error messages
- Settings page labels and descriptions
- Help text and documentation

## Need Help?

For translation questions or issues:
- GitHub Issues: https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues
- Email: support@eldensolution.com
