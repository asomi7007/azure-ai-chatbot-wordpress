#!/bin/bash

# Git Push Script for Azure AI Chatbot WordPress Plugin
# This script helps push changes to GitHub repository

echo "================================"
echo "Git Push Script"
echo "================================"
echo ""

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo "❌ Error: Not a git repository"
    echo "Run 'git init' first"
    exit 1
fi

# Show current branch
CURRENT_BRANCH=$(git branch --show-current)
echo "Current branch: $CURRENT_BRANCH"
echo ""

# Show git status
echo "[1/5] Checking status..."
git status --short
echo ""

# Add all changes
echo "[2/5] Adding changes..."
git add .
echo "✅ All changes staged"
echo ""

# Show what will be committed
echo "[3/5] Files to be committed:"
git diff --cached --name-status
echo ""

# Commit message
echo "[4/5] Creating commit..."
read -p "Enter commit message (or press Enter for default): " COMMIT_MSG

if [ -z "$COMMIT_MSG" ]; then
    COMMIT_MSG="docs: restructure README with Azure setup scripts and plugin uniqueness emphasis

- Complete English README rewrite following GitHub best practices
- Reduced H2 sections from 12+ to 8 main sections
- Added 'Why This Plugin?' section highlighting first Azure AI Foundry agent support
- Featured Azure Cloud Shell scripts in Quick Start (test-chat-mode.sh, test-agent-mode.sh)
- Moved version history to end
- Removed all emojis for professional tone
- Created matching Korean README-ko.md
- Fixed user guide page with auto-generated TOC and smooth scrolling
- Completed JavaScript providerConfig translations (6 AI providers)
- Updated translations: ko_KR (109 strings), en_US (100 strings)"
fi

git commit -m "$COMMIT_MSG"
echo "✅ Commit created"
echo ""

# Push to GitHub
echo "[5/5] Pushing to GitHub..."
git push origin $CURRENT_BRANCH

if [ $? -eq 0 ]; then
    echo ""
    echo "================================"
    echo "✅ Push Successful!"
    echo "================================"
    echo "Repository: https://github.com/asomi7007/azure-ai-chatbot-wordpress"
    echo "Branch: $CURRENT_BRANCH"
    echo ""
else
    echo ""
    echo "================================"
    echo "❌ Push Failed"
    echo "================================"
    echo "Please check your credentials and network connection"
    echo ""
    exit 1
fi
