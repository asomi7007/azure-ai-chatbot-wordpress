# Azure AI Chatbot Fixes & Debugging

## Changes Made

### 1. Fixed UI Error: `toggleModeCards is not a function`
- **File**: `templates/oauth-auto-setup.php`
- **Issue**: The `toggleModeCards` function was called in `init()` but was missing from the `autoSetup` object definition.
- **Fix**: Added the `toggleModeCards` function to show/hide the Chat/Agent cards based on the selected mode.

### 2. Enhanced Debugging for "Project Not Found"
- **File**: `includes/class-azure-oauth.php`
- **Change**: Modified `ajax_get_ai_projects` to collect detailed debug information during the resource scan process.
    - Logs API response counts.
    - Logs each resource's Name, Type, Kind, Endpoint, and Classification (Hub/Project/Skipped).
    - Logs details of Hub query steps.
- **File**: `templates/oauth-auto-setup.php`
- **Change**: Updated the JavaScript to print this debug information to the **Browser Console** when the project list is loaded (success or failure).

## Verification Steps

1.  **Reload the Admin Page**: Go to the "Azure AI Chatbot" settings page in WordPress admin.
2.  **Check UI**:
    - Verify that the page loads without the "toggleModeCards is not a function" error in the console.
    - Verify that the "Subscription" dropdown works and you can select a subscription.
3.  **Reproduce "Project Not Found"**:
    - Select a Subscription.
    - Select a Resource Group.
    - Open the **Browser Developer Tools** (F12) and go to the **Console** tab.
    - Look for the "AI Foundry Project" dropdown to load.
    - If it fails (or succeeds), look for a log message starting with: `[Auto Setup] Debug Info received from server`.
    - Expand this object to see:
        - `resources_scan`: List of all resources found and why they were accepted or skipped.
        - `steps`: Log of API calls made.

## Troubleshooting "Project Not Found"

If the project list is still empty, check the `resources_scan` in the console log:
- **If `total_resources` is 0**: The Resource Group might be empty or the API version used (`2023-05-01` / `2023-04-01`) is not compatible with your region.
- **If resources are found but skipped**: Check the `result` field.
    - `skipped_no_endpoint`: The resource exists but has no `properties.endpoint` or `properties.discoveryUrl`.
    - `skipped`: The resource type/kind did not match the expected values for an AI Hub or Project.
