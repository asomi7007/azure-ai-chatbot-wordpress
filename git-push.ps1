# Git Push Script for Azure AI Chatbot WordPress Plugin
# PowerShell version for Windows

Write-Host "================================" -ForegroundColor Cyan
Write-Host "Git Push Script" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in a git repository
if (-not (Test-Path ".git")) {
    Write-Host "❌ Error: Not a git repository" -ForegroundColor Red
    Write-Host "Run 'git init' first"
    exit 1
}

# Show current branch
$currentBranch = git branch --show-current
Write-Host "Current branch: $currentBranch" -ForegroundColor Yellow
Write-Host ""

# Show git status
Write-Host "[1/5] Checking status..." -ForegroundColor Green
git status --short
Write-Host ""

# Add all changes
Write-Host "[2/5] Adding changes..." -ForegroundColor Green
git add .
Write-Host "✅ All changes staged" -ForegroundColor Green
Write-Host ""

# Show what will be committed
Write-Host "[3/5] Files to be committed:" -ForegroundColor Green
git diff --cached --name-status
Write-Host ""

# Commit message
Write-Host "[4/5] Creating commit..." -ForegroundColor Green
$commitMsg = Read-Host "Enter commit message (or press Enter for default)"

if ([string]::IsNullOrWhiteSpace($commitMsg)) {
    $commitMsg = @"
docs: restructure README with Azure setup scripts and plugin uniqueness emphasis

- Complete English README rewrite following GitHub best practices
- Reduced H2 sections from 12+ to 8 main sections
- Added 'Why This Plugin?' section highlighting first Azure AI Foundry agent support
- Featured Azure Cloud Shell scripts in Quick Start (test-chat-mode.sh, test-agent-mode.sh)
- Moved version history to end
- Removed all emojis for professional tone
- Created matching Korean README-ko.md
- Fixed user guide page with auto-generated TOC and smooth scrolling
- Completed JavaScript providerConfig translations (6 AI providers)
- Updated translations: ko_KR (109 strings), en_US (100 strings)
"@
}

git commit -m $commitMsg
Write-Host "✅ Commit created" -ForegroundColor Green
Write-Host ""

# Push to GitHub
Write-Host "[5/5] Pushing to GitHub..." -ForegroundColor Green
git push origin $currentBranch

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "================================" -ForegroundColor Cyan
    Write-Host "✅ Push Successful!" -ForegroundColor Green
    Write-Host "================================" -ForegroundColor Cyan
    Write-Host "Repository: https://github.com/asomi7007/azure-ai-chatbot-wordpress" -ForegroundColor Yellow
    Write-Host "Branch: $currentBranch" -ForegroundColor Yellow
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "================================" -ForegroundColor Cyan
    Write-Host "❌ Push Failed" -ForegroundColor Red
    Write-Host "================================" -ForegroundColor Cyan
    Write-Host "Please check your credentials and network connection"
    Write-Host ""
    exit 1
}
