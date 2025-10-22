#!/bin/bash

# Azure App Registration 자동 설정 스크립트
# WordPress Azure AI Chatbot 플러그인용 OAuth 앱 등록

set -e

echo "========================================="
echo "Azure AI Chatbot OAuth App Setup"
echo "========================================="
echo ""

# WordPress URL 파라미터로 받기 또는 입력받기
SITE_URL="$1"

if [ -z "$SITE_URL" ]; then
    read -p "WordPress 사이트 URL을 입력하세요 (예: https://example.com): " SITE_URL
fi

if [ -z "$SITE_URL" ]; then
    echo ""
    echo "❌ 사이트 URL이 필요합니다."
    echo ""
    echo "💡 사용법:"
    echo "   bash <(curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/scripts/setup-oauth-app.sh) https://your-site.com"
    echo ""
    echo "   또는"
    echo ""
    echo "   curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/scripts/setup-oauth-app.sh > setup.sh"
    echo "   bash setup.sh https://your-site.com"
    echo ""
    exit 1
fi

# trailing slash 제거
SITE_URL=$(echo "$SITE_URL" | sed 's:/*$::')

# Redirect URI 생성
REDIRECT_URI="${SITE_URL}/wp-admin/admin.php?page=azure-chatbot-settings&azure_callback=1"

echo ""
echo "✅ Redirect URI: $REDIRECT_URI"
echo ""

# 현재 구독 확인
echo "📋 현재 Azure 구독 확인 중..."
SUBSCRIPTION_ID=$(az account show --query id -o tsv 2>/dev/null || echo "")

if [ -z "$SUBSCRIPTION_ID" ]; then
    echo "❌ Azure에 로그인이 필요합니다."
    echo "   다음 명령어를 실행하세요: az login"
    exit 1
fi

SUBSCRIPTION_NAME=$(az account show --query name -o tsv)
echo "✅ 구독: $SUBSCRIPTION_NAME ($SUBSCRIPTION_ID)"
echo ""

# App Registration 생성
APP_NAME="WordPress-Azure-AI-Chatbot-$(date +%Y%m%d%H%M%S)"

echo "🔧 App Registration 생성 중: $APP_NAME"
APP_ID=$(az ad app create \
    --display-name "$APP_NAME" \
    --sign-in-audience "AzureADMyOrg" \
    --web-redirect-uris "$REDIRECT_URI" \
    --query appId -o tsv)

if [ -z "$APP_ID" ]; then
    echo "❌ App Registration 생성 실패"
    exit 1
fi

echo "✅ Application (Client) ID: $APP_ID"
echo ""

# Tenant ID 가져오기
TENANT_ID=$(az account show --query tenantId -o tsv)
echo "✅ Directory (Tenant) ID: $TENANT_ID"
echo ""

# Client Secret 생성
echo "🔑 Client Secret 생성 중..."
SECRET_RESPONSE=$(az ad app credential reset --id "$APP_ID" --append --query password -o tsv)

if [ -z "$SECRET_RESPONSE" ]; then
    echo "❌ Client Secret 생성 실패"
    exit 1
fi

CLIENT_SECRET="$SECRET_RESPONSE"
echo "✅ Client Secret: $CLIENT_SECRET"
echo "⚠️  이 Secret 값을 안전하게 저장하세요. 다시 볼 수 없습니다!"
echo ""

# API 권한 추가
echo "🔐 API 권한 추가 중..."

# Microsoft Graph - User.Read
echo "  - Microsoft Graph: User.Read"
az ad app permission add --id "$APP_ID" \
    --api 00000003-0000-0000-c000-000000000000 \
    --api-permissions e1fe6dd8-ba31-4d61-89e7-88639da4683d=Scope \
    > /dev/null 2>&1

# Azure Service Management - user_impersonation  
echo "  - Azure Service Management: user_impersonation"
az ad app permission add --id "$APP_ID" \
    --api 797f4846-ba00-4fd7-ba43-dac1f8f63013 \
    --api-permissions 41094075-9dad-400e-a0bd-54e686782033=Scope \
    > /dev/null 2>&1

echo "✅ API 권한 추가 완료"
echo ""

# Admin Consent 안내
echo "⚠️  관리자 동의 필요"
echo "   Azure Portal에서 다음 단계를 수행하세요:"
echo "   1. https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/~/CallAnAPI/appId/$APP_ID"
echo "   2. 'API permissions' 클릭"
echo "   3. 'Grant admin consent for [조직명]' 클릭"
echo ""

# 결과 출력
echo "========================================="
echo "✅ OAuth App 설정 완료!"
echo "========================================="
echo ""
echo "📝 WordPress 플러그인에 입력할 값:"
echo ""
echo "Client ID:"
echo "$APP_ID"
echo ""
echo "Client Secret:"
echo "$CLIENT_SECRET"
echo ""
echo "Tenant ID:"
echo "$TENANT_ID"
echo ""
echo "Redirect URI:"
echo "$REDIRECT_URI"
echo ""
echo "========================================="
echo ""
echo "🚀 다음 단계:"
echo "1. Azure Portal에서 Admin Consent 부여 (위 URL 참고)"
echo "2. WordPress 관리자 > Azure AI Chatbot > 설정"
echo "3. 'Azure OAuth 설정' 섹션에 위 값 입력"
echo "4. 'OAuth 설정 저장' 버튼 클릭"
echo "5. 'Azure 자동 설정 시작' 버튼 클릭"
echo ""
echo "📖 상세 가이드:"
echo "https://github.com/asomi7007/azure-ai-chatbot-wordpress/blob/main/docs/AZURE_AUTO_SETUP.md"
echo ""
