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
REDIRECT_URI="${SITE_URL}/wp-admin/admin.php?page=azure-ai-chatbot&azure_callback=1"

echo ""
echo "✅ Redirect URI: $REDIRECT_URI"
echo ""

# Azure 구독 선택
echo "📋 Azure 구독 확인 중..."

# 사용 가능한 구독 목록 가져오기
SUBSCRIPTION_COUNT=$(az account list --query "length(@)" -o tsv 2>/dev/null || echo "0")

if [ "$SUBSCRIPTION_COUNT" = "0" ]; then
    echo "❌ Azure에 로그인이 필요합니다."
    echo "   다음 명령어를 실행하세요: az login"
    exit 1
fi

# 구독이 여러 개인 경우 선택
if [ "$SUBSCRIPTION_COUNT" -gt "1" ]; then
    echo ""
    echo "🔍 사용 가능한 구독 목록:"
    echo ""
    az account list --query "[].{Number:to_string(to_number(to_string(null))), Name:name, SubscriptionId:id, State:state}" -o table | nl
    echo ""
    read -p "사용할 구독 번호를 입력하세요 (1-$SUBSCRIPTION_COUNT): " SUB_NUM
    
    if [ -z "$SUB_NUM" ] || [ "$SUB_NUM" -lt 1 ] || [ "$SUB_NUM" -gt "$SUBSCRIPTION_COUNT" ]; then
        echo "❌ 잘못된 번호입니다."
        exit 1
    fi
    
    # 선택한 구독으로 설정
    SUBSCRIPTION_ID=$(az account list --query "[$(($SUB_NUM - 1))].id" -o tsv)
    az account set --subscription "$SUBSCRIPTION_ID"
    echo ""
    echo "✅ 선택한 구독으로 설정 완료"
fi

# 현재 구독 정보 표시
SUBSCRIPTION_ID=$(az account show --query id -o tsv)
SUBSCRIPTION_NAME=$(az account show --query name -o tsv)
echo "✅ 사용 중인 구독: $SUBSCRIPTION_NAME ($SUBSCRIPTION_ID)"
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

# Admin Consent 자동 부여
echo "🔐 관리자 동의 부여 중..."
CONSENT_RESULT=$(az ad app permission admin-consent --id "$APP_ID" 2>&1)

if echo "$CONSENT_RESULT" | grep -q "Forbidden\|forbidden\|denied"; then
    echo "⚠️  관리자 권한이 부족하여 자동 동의를 부여할 수 없습니다."
    echo "   Azure Portal에서 수동으로 동의를 부여하세요:"
    echo "   1. https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/~/CallAnAPI/appId/$APP_ID"
    echo "   2. 'API permissions' 클릭"
    echo "   3. 'Grant admin consent for [조직명]' 클릭"
elif echo "$CONSENT_RESULT" | grep -q "error\|Error\|ERROR"; then
    echo "⚠️  관리자 동의 부여 중 오류 발생"
    echo "   Azure Portal에서 수동으로 동의를 부여하세요:"
    echo "   https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/~/CallAnAPI/appId/$APP_ID"
else
    echo "✅ 관리자 동의 자동 부여 완료!"
fi
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
