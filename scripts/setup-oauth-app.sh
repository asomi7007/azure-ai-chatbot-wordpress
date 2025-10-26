#!/bin/bash

# Azure App Registration 자동 설정 스크립트
# WordPress Azure AI Chatbot 플러그인용 OAuth 앱 등록

set -e

# Language Selection / 언어 선택
echo "========================================="
echo "Azure AI Chatbot OAuth App Setup"
echo "========================================="
echo ""
echo "Select language / 언어를 선택하세요:"
echo "1) English"
echo "2) 한국어 (Korean)"
echo ""
read -p "Choose (1-2) / 선택 (1-2): " LANG_CHOICE

case "$LANG_CHOICE" in
    1)
        LANG="en"
        ;;
    2)
        LANG="ko"
        ;;
    *)
        echo "Invalid choice. Defaulting to English."
        echo "잘못된 선택입니다. 영어로 진행합니다."
        LANG="en"
        ;;
esac

echo ""

# Multilingual message function
msg() {
    local key="$1"
    case "$LANG" in
        ko)
            case "$key" in
                "enter_url") echo "WordPress 사이트 URL을 입력하세요 (예: https://example.com): " ;;
                "url_required") echo "❌ 사이트 URL이 필요합니다." ;;
                "usage") echo "💡 사용법:" ;;
                "redirect_uri") echo "✅ Redirect URI:" ;;
                "checking_subscription") echo "📋 Azure 구독 확인 중..." ;;
                "no_login") echo "❌ Azure에 로그인이 필요합니다." ;;
                "login_cmd") echo "   다음 명령어를 실행하세요: az login" ;;
                "available_subscription") echo "✅ 사용 가능한 구독:" ;;
                "use_subscription") echo "이 구독을 사용하시겠습니까? (y/n): " ;;
                "cancelled") echo "❌ 작업이 취소되었습니다." ;;
                "subscription_list") echo "🔍 사용 가능한 구독 목록:" ;;
                "select_subscription") echo "사용할 구독 번호를 입력하세요 (1-$2): " ;;
                "invalid_number") echo "❌ 잘못된 번호입니다." ;;
                "subscription_set") echo "✅ 선택한 구독으로 설정 완료" ;;
                "current_subscription") echo "✅ 사용 중인 구독:" ;;
                "creating_app") echo "🔧 App Registration 생성 중:" ;;
                "client_id") echo "✅ Application (Client) ID:" ;;
                "tenant_id") echo "✅ Directory (Tenant) ID:" ;;
                "creating_secret") echo "🔑 Client Secret 생성 중..." ;;
                "secret_value") echo "✅ Client Secret:" ;;
                "save_secret") echo "⚠️  이 Secret 값을 안전하게 저장하세요. 다시 볼 수 없습니다!" ;;
                "adding_permissions") echo "🔐 API 권한 추가 중..." ;;
                "permissions_done") echo "✅ API 권한 추가 완료" ;;
                "granting_consent") echo "🔐 관리자 동의 처리 중..." ;;
                "consent_timeout") echo "⚠️  자동 동의 부여에 실패했습니다." ;;
                "consent_success") echo "✅ 관리자 동의가 성공적으로 부여되었습니다!" ;;
                "consent_manual") echo "📌 관리자 동의 수동 처리 필요:" ;;
                "setup_complete") echo "✅ OAuth App 설정 완료!" ;;
                "wordpress_values") echo "📝 WordPress 플러그인에 입력할 값:" ;;
                "next_steps") echo "🚀 다음 단계:" ;;
                "guide") echo "📖 상세 가이드:" ;;
                *) echo "$key" ;;
            esac
            ;;
        en|*)
            case "$key" in
                "enter_url") echo "Enter WordPress site URL (e.g., https://example.com): " ;;
                "url_required") echo "❌ Site URL is required." ;;
                "usage") echo "💡 Usage:" ;;
                "redirect_uri") echo "✅ Redirect URI:" ;;
                "checking_subscription") echo "📋 Checking Azure subscriptions..." ;;
                "no_login") echo "❌ Azure login required." ;;
                "login_cmd") echo "   Please run: az login" ;;
                "available_subscription") echo "✅ Available subscription:" ;;
                "use_subscription") echo "Use this subscription? (y/n): " ;;
                "cancelled") echo "❌ Operation cancelled." ;;
                "subscription_list") echo "🔍 Available subscriptions:" ;;
                "select_subscription") echo "Enter subscription number (1-$2): " ;;
                "invalid_number") echo "❌ Invalid number." ;;
                "subscription_set") echo "✅ Subscription configured successfully" ;;
                "current_subscription") echo "✅ Using subscription:" ;;
                "creating_app") echo "🔧 Creating App Registration:" ;;
                "client_id") echo "✅ Application (Client) ID:" ;;
                "tenant_id") echo "✅ Directory (Tenant) ID:" ;;
                "creating_secret") echo "🔑 Creating Client Secret..." ;;
                "secret_value") echo "✅ Client Secret:" ;;
                "save_secret") echo "⚠️  Save this secret value securely. You won't be able to see it again!" ;;
                "adding_permissions") echo "🔐 Adding API permissions..." ;;
                "permissions_done") echo "✅ API permissions added successfully" ;;
                "granting_consent") echo "🔐 Processing admin consent..." ;;
                "consent_timeout") echo "⚠️  Automatic consent grant failed." ;;
                "consent_success") echo "✅ Admin consent granted successfully!" ;;
                "consent_manual") echo "📌 Manual admin consent required:" ;;
                "setup_complete") echo "✅ OAuth App Setup Complete!" ;;
                "wordpress_values") echo "📝 Values to enter in WordPress plugin:" ;;
                "next_steps") echo "🚀 Next Steps:" ;;
                "guide") echo "📖 Detailed Guide:" ;;
                *) echo "$key" ;;
            esac
            ;;
    esac
}

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

# 현재 구독 정보 먼저 표시
CURRENT_SUBSCRIPTION_ID=$(az account show --query id -o tsv)
CURRENT_SUBSCRIPTION_NAME=$(az account show --query name -o tsv)

# 항상 구독 목록 표시
if [ "$SUBSCRIPTION_COUNT" -eq "1" ]; then
    echo ""
    echo "✅ 사용 가능한 구독: $CURRENT_SUBSCRIPTION_NAME ($CURRENT_SUBSCRIPTION_ID)"
    echo ""
    read -p "이 구독을 사용하시겠습니까? (y/n): " USE_CURRENT
    
    if [ "$USE_CURRENT" != "y" ] && [ "$USE_CURRENT" != "Y" ]; then
        echo "❌ 작업이 취소되었습니다."
        exit 1
    fi
else
    echo ""
    echo "🔍 사용 가능한 구독 목록:"
    echo ""
    
    # 구독 목록을 번호와 함께 표시
    az account list --query "[].{Name:name, SubscriptionId:id, State:state}" -o table | awk 'NR==1 {print "   No. " $0} NR>1 {printf "   %2d  %s\n", NR-1, $0}'
    
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

# 최종 구독 정보 표시
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

# Admin Consent 자동 부여 (여러 방법 시도)
echo "🔐 관리자 동의 처리 중..."
echo ""

CONSENT_GRANTED=false

# 방법 1: az ad app permission admin-consent (표준 방법)
echo "📌 방법 1: Azure CLI 명령어로 시도 중..."
CONSENT_RESULT=$(timeout 5s az ad app permission admin-consent --id "$APP_ID" 2>&1 || echo "FAILED")

if ! echo "$CONSENT_RESULT" | grep -qi "FAILED\|error\|forbidden\|timeout"; then
    echo "   ✅ 성공!"
    CONSENT_GRANTED=true
else
    echo "   ⚠️  실패: $(echo "$CONSENT_RESULT" | head -n 1)"
    echo ""
    
    # 방법 2: az ad app permission grant (대안 방법)
    echo "📌 방법 2: Permission Grant API로 시도 중..."
    
    # Microsoft Graph permission grant
    GRANT_RESULT_1=$(az ad app permission grant --id "$APP_ID" \
        --api 00000003-0000-0000-c000-000000000000 \
        --scope "User.Read" 2>&1 || echo "FAILED")
    
    # Azure Service Management permission grant
    GRANT_RESULT_2=$(az ad app permission grant --id "$APP_ID" \
        --api 797f4846-ba00-4fd7-ba43-dac1f8f63013 \
        --scope "user_impersonation" 2>&1 || echo "FAILED")
    
    if ! echo "$GRANT_RESULT_1$GRANT_RESULT_2" | grep -qi "FAILED\|error"; then
        echo "   ✅ Permission Grant 성공!"
        CONSENT_GRANTED=true
    else
        echo "   ⚠️  실패"
        echo ""
    fi
fi

# 권한 상태 확인 (최대 20초 대기)
if [ "$CONSENT_GRANTED" = true ]; then
    echo ""
    echo "⏳ 권한 적용 대기 중 (최대 20초)..."
    
    for i in {1..4}; do
        sleep 5
        
        # 권한 상태 확인
        PERMISSION_STATUS=$(az ad app permission list --id "$APP_ID" --query "[].{Resource:resourceAppId, Permission:resourceAccess[0].id, Consent:oauth2PermissionGrants}" -o json 2>/dev/null || echo "[]")
        
        if echo "$PERMISSION_STATUS" | grep -q "User.Read\|user_impersonation"; then
            echo "   ✅ 권한 적용 확인됨! ($((i * 5))초 소요)"
            break
        else
            echo "   ⏳ 대기 중... ($((i * 5))초)"
        fi
    done
    echo ""
fi

# 최종 결과 출력
if [ "$CONSENT_GRANTED" = true ]; then
    echo "✅ 관리자 동의가 성공적으로 처리되었습니다!"
    echo ""
else
    echo "⚠️  자동 동의 부여에 실패했습니다."
    echo ""
    echo "📌 다음 방법으로 수동 승인하세요:"
    echo ""
    echo "방법 A - Azure Portal 사용 (권장):"
    echo "   1. 브라우저에서 다음 URL 열기:"
    echo "      https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/~/CallAnAPI/appId/$APP_ID"
    echo "   2. 'API permissions' 클릭"
    echo "   3. 'Grant admin consent for [조직명]' 버튼 클릭"
    echo ""
    echo "방법 B - Azure CLI 명령어:"
    echo "   az ad app permission admin-consent --id $APP_ID"
    echo ""
    echo "방법 C - 관리자 동의 URL (브라우저에서 열기):"
    echo "   https://login.microsoftonline.com/$TENANT_ID/adminconsent?client_id=$APP_ID"
    echo ""
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
