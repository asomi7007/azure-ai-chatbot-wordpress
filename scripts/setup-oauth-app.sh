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
                "use_subscription") echo "이 구독을 사용하시겠습니까? (Y/n): " ;;
                "cancelled") echo "❌ 작업이 취소되었습니다." ;;
                "subscription_list") echo "🔍 사용 가능한 구독 목록:" ;;
                "select_subscription") echo "사용할 구독 번호를 입력하세요 (1-$2): " ;;
                "invalid_number") echo "❌ 잘못된 번호입니다." ;;
                "subscription_set") echo "✅ 선택한 구독으로 설정 완료" ;;
                "current_subscription") echo "✅ 사용 중인 구독:" ;;
                "creating_app") echo "🔧 App Registration 생성 중: $2" ;;
                "client_id") echo "✅ Application (Client) ID: $2" ;;
                "tenant_id") echo "✅ Directory (Tenant) ID: $2" ;;
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
                "login_required") echo "❌ Azure에 로그인이 필요합니다."; echo "   다음 명령어를 실행하세요: az login" ;;
                "single_subscription") echo "✅ 사용 가능한 구독: $2 ($3)" ;;
                "operation_cancelled") echo "❌ 작업이 취소되었습니다." ;;
                "subscription_set_complete") echo "✅ 선택한 구독으로 설정 완료" ;;
                "using_subscription") echo "✅ 사용 중인 구독: $2 ($3)" ;;
                "checking_existing_app") echo "🔍 기존 App Registration 확인 중..." ;;
                "ad_list_timeout") echo "⚠️  Azure AD 앱 목록 조회 시간 초과." ;;
                "ad_list_no_permission") echo "⚠️  Azure AD 앱 목록 조회 권한이 없거나 오류가 발생했습니다." ;;
                "continue_create_new") echo "   계속 진행하여 새 앱을 생성합니다." ;;
                "app_search_failed") echo "⚠️  기존 앱 검색 실패. 새 앱을 생성합니다." ;;
                "existing_app_found") echo "⚠️  동일한 Redirect URI를 사용하는 기존 앱이 발견되었습니다:" ;;
                "choose_action") echo "다음 중 선택하세요:"; echo "1) 기존 앱 사용 (Client Secret만 새로 생성)"; echo "2) 기존 앱 삭제하고 새로 생성"; echo "3) 취소" ;;
                "using_existing_app") echo "✅ 기존 앱 사용: $2 ($3)" ;;
                "deleting_existing_app") echo "🗑️  기존 앱 삭제 중..." ;;
                "deletion_complete") echo "✅ 삭제 완료" ;;
                "app_creation_failed") echo "❌ App Registration 생성 실패" ;;
                "app_creation_timeout") echo "⚠️  App Registration 생성 시간 초과 (30초)."; echo "   Azure AD API 응답이 지연되고 있습니다. 다시 시도하거나 Azure Portal에서 수동으로 생성하세요." ;;
                "token_expired") echo "⚠️  Azure 토큰이 만료되었습니다."; echo "   다음 명령어를 실행한 후 다시 시도하세요:"; echo ""; echo "   az login"; echo "" ;;
                "insufficient_privileges") echo "⚠️  Azure AD 앱 생성 권한이 없습니다."; echo "   Azure Portal에서 관리자에게 다음 권한을 요청하세요:"; echo "   - Application Developer 역할 또는"; echo "   - Application Administrator 역할"; echo "" ;;
                "error_details") echo "   오류 내용:" ;;
                "secret_creation_timeout") echo "⚠️  Client Secret 생성 시간 초과 (30초)."; echo "   다시 시도하거나 Azure Portal에서 수동으로 생성하세요." ;;
                "secret_creation_failed") echo "❌ Client Secret 생성 실패" ;;
                "permission_timeout") echo "⚠️  API 권한 추가 시간 초과 (20초)."; echo "   Azure Portal에서 수동으로 권한을 추가하세요." ;;
                "permission_failed") echo "⚠️  API 권한 추가 실패. 계속 진행합니다." ;;
                "invalid_choice") echo "❌ 잘못된 선택입니다." ;;
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
                "use_subscription") echo "Use this subscription? (Y/n): " ;;
                "cancelled") echo "❌ Operation cancelled." ;;
                "subscription_list") echo "🔍 Available subscriptions:" ;;
                "select_subscription") echo "Enter subscription number (1-$2): " ;;
                "invalid_number") echo "❌ Invalid number." ;;
                "subscription_set") echo "✅ Subscription configured successfully" ;;
                "current_subscription") echo "✅ Using subscription:" ;;
                "creating_app") echo "🔧 Creating App Registration: $2" ;;
                "client_id") echo "✅ Application (Client) ID: $2" ;;
                "tenant_id") echo "✅ Directory (Tenant) ID: $2" ;;
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
                "login_required") echo "❌ Azure login required."; echo "   Please run: az login" ;;
                "single_subscription") echo "✅ Available subscription: $2 ($3)" ;;
                "operation_cancelled") echo "❌ Operation cancelled." ;;
                "subscription_set_complete") echo "✅ Subscription configured successfully" ;;
                "using_subscription") echo "✅ Using subscription: $2 ($3)" ;;
                "checking_existing_app") echo "🔍 Checking for existing App Registration..." ;;
                "ad_list_timeout") echo "⚠️  Azure AD app list query timed out." ;;
                "ad_list_no_permission") echo "⚠️  No permission to list Azure AD apps or an error occurred." ;;
                "continue_create_new") echo "   Continuing to create a new app." ;;
                "app_search_failed") echo "⚠️  Failed to search existing apps. Creating a new app." ;;
                "existing_app_found") echo "⚠️  Found existing app(s) with the same Redirect URI:" ;;
                "choose_action") echo "Choose an action:"; echo "1) Use existing app (Create new Client Secret only)"; echo "2) Delete existing app and create new"; echo "3) Cancel" ;;
                "using_existing_app") echo "✅ Using existing app: $2 ($3)" ;;
                "deleting_existing_app") echo "🗑️  Deleting existing app..." ;;
                "deletion_complete") echo "✅ Deletion complete" ;;
                "app_creation_failed") echo "❌ Failed to create App Registration" ;;
                "app_creation_timeout") echo "⚠️  App Registration creation timed out (30 seconds)."; echo "   Azure AD API response is delayed. Please retry or create manually in Azure Portal." ;;
                "token_expired") echo "⚠️  Azure token has expired."; echo "   Please run the following command and try again:"; echo ""; echo "   az login"; echo "" ;;
                "insufficient_privileges") echo "⚠️  Insufficient privileges to create Azure AD apps."; echo "   Please request the following role from your Azure administrator:"; echo "   - Application Developer role or"; echo "   - Application Administrator role"; echo "" ;;
                "error_details") echo "   Error details:" ;;
                "secret_creation_timeout") echo "⚠️  Client Secret creation timed out (30 seconds)."; echo "   Please retry or create manually in Azure Portal." ;;
                "secret_creation_failed") echo "❌ Failed to create Client Secret" ;;
                "permission_timeout") echo "⚠️  API permission addition timed out (20 seconds)."; echo "   Please add permissions manually in Azure Portal." ;;
                "permission_failed") echo "⚠️  Failed to add API permissions. Continuing..." ;;
                "invalid_choice") echo "❌ Invalid choice." ;;
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
    msg "login_required"
    exit 1
fi

# 현재 구독 정보 먼저 표시
CURRENT_SUBSCRIPTION_ID=$(az account show --query id -o tsv)
CURRENT_SUBSCRIPTION_NAME=$(az account show --query name -o tsv)

# 항상 구독 목록 표시
if [ "$SUBSCRIPTION_COUNT" -eq "1" ]; then
    echo ""
    msg "single_subscription" "$CURRENT_SUBSCRIPTION_NAME" "$CURRENT_SUBSCRIPTION_ID"
    echo ""
    
    if [ "$LANG" = "en" ]; then
        read -p "Do you want to use this subscription? (Y/n): " USE_CURRENT
    else
        read -p "이 구독을 사용하시겠습니까? (Y/n): " USE_CURRENT
    fi
    USE_CURRENT=${USE_CURRENT:-Y}
    
    if [[ ! "$USE_CURRENT" =~ ^[Yy]$ ]]; then
        msg "operation_cancelled"
        exit 1
    fi
else
    echo ""
    msg "subscription_list"
    echo ""
    
    # 구독 목록을 번호와 함께 표시
    az account list --query "[].{Name:name, SubscriptionId:id, State:state}" -o tsv | awk -F'\t' '
    BEGIN {
        printf "   %-4s %-32s %-38s %-10s\n", "No.", "Name", "SubscriptionId", "State"
        printf "   %-4s %-32s %-38s %-10s\n", "----", "--------------------------------", "--------------------------------------", "----------"
    }
    {
        printf "   %-4d %-32s %-38s %-10s\n", NR, $1, $2, $3
    }'
    
    echo ""
    if [ "$LANG" = "en" ]; then
        read -p "Enter subscription number to use (1-$SUBSCRIPTION_COUNT): " SUB_NUM
    else
        read -p "사용할 구독 번호를 입력하세요 (1-$SUBSCRIPTION_COUNT): " SUB_NUM
    fi
    
    if [ -z "$SUB_NUM" ] || [ "$SUB_NUM" -lt 1 ] || [ "$SUB_NUM" -gt "$SUBSCRIPTION_COUNT" ]; then
        msg "invalid_number"
        exit 1
    fi
    
    # 선택한 구독으로 설정
    SUBSCRIPTION_ID=$(az account list --query "[$(($SUB_NUM - 1))].id" -o tsv)
    az account set --subscription "$SUBSCRIPTION_ID"
    echo ""
    msg "subscription_set_complete"
fi

# 최종 구독 정보 표시
SUBSCRIPTION_ID=$(az account show --query id -o tsv)
SUBSCRIPTION_NAME=$(az account show --query name -o tsv)
msg "using_subscription" "$SUBSCRIPTION_NAME" "$SUBSCRIPTION_ID"
echo ""

# 기존 App Registration 확인 (타임아웃 방지: 5초로 단축)
msg "checking_existing_app"

# Azure AD 권한 확인 (타임아웃 5초, 빠른 체크)
set +e
timeout 5s az ad app list --query "[0].appId" -o tsv > /dev/null 2>&1
EXIT_CODE=$?
set -e

if [ $EXIT_CODE -ne 0 ]; then
    if [ $EXIT_CODE -eq 124 ]; then
        msg "ad_list_timeout"
    else
        msg "ad_list_no_permission"
    fi
    msg "continue_create_new"
    EXISTING_APPS="[]"
else
    # 권한이 있으면 기존 앱 검색 (필터 없이 전체 목록에서 jq로 필터링)
    set +e
    ALL_APPS=$(timeout 10s az ad app list --query "[].{appId:appId, displayName:displayName, web:web}" -o json 2>&1)
    EXIT_CODE=$?
    set -e
    
    if [ $EXIT_CODE -eq 124 ] || [ $EXIT_CODE -ne 0 ]; then
        msg "app_search_failed"
        EXISTING_APPS="[]"
    else
        # jq로 리다이렉트 URI 필터링 (Cloud Shell에는 jq가 기본 설치됨)
        EXISTING_APPS=$(echo "$ALL_APPS" | jq "[.[] | select(.web.redirectUris[]? == \"$REDIRECT_URI\") | {AppId: .appId, DisplayName: .displayName}]" 2>/dev/null || echo "[]")
    fi
fi

if [ "$EXISTING_APPS" != "[]" ] && [ -n "$EXISTING_APPS" ]; then
    echo ""
    msg "existing_app_found"
    echo ""
    echo "$EXISTING_APPS" | jq -r '.[] | "   - \(.DisplayName) (\(.AppId))"'
    echo ""
    msg "choose_action"
    echo ""
    
    if [ "$LANG" = "en" ]; then
        read -p "Choose (1-3): " EXISTING_APP_CHOICE
    else
        read -p "선택 (1-3): " EXISTING_APP_CHOICE
    fi
    
    case "$EXISTING_APP_CHOICE" in
        1)
            # 기존 앱 사용
            APP_ID=$(echo "$EXISTING_APPS" | jq -r '.[0].AppId')
            APP_NAME=$(echo "$EXISTING_APPS" | jq -r '.[0].DisplayName')
            echo ""
            msg "using_existing_app" "$APP_NAME" "$APP_ID"
            echo ""
            ;;
        2)
            # 기존 앱 삭제
            echo ""
            msg "deleting_existing_app"
            EXISTING_APP_ID=$(echo "$EXISTING_APPS" | jq -r '.[0].AppId')
            az ad app delete --id "$EXISTING_APP_ID" 2>/dev/null
            msg "deletion_complete"
            echo ""
            
            # 새 앱 생성 (타임아웃 30초)
            APP_NAME="WordPress-Azure-AI-Chatbot-$(date +%Y%m%d%H%M%S)"
            msg "creating_app" "$APP_NAME"
            
            set +e
            APP_CREATE_OUTPUT=$(timeout 30s az ad app create \
                --display-name "$APP_NAME" \
                --sign-in-audience "AzureADMyOrg" \
                --web-redirect-uris "$REDIRECT_URI" \
                --query appId -o tsv 2>&1)
            EXIT_CODE=$?
            set -e
            
            if [ $EXIT_CODE -eq 124 ]; then
                msg "app_creation_timeout"
                exit 1
            fi
            
            # GUID 형식 검증
            if echo "$APP_CREATE_OUTPUT" | grep -qE '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'; then
                APP_ID="$APP_CREATE_OUTPUT"
            else
                msg "app_creation_failed"
                echo ""
                msg "error_details"
                echo "   $APP_CREATE_OUTPUT"
                echo ""
                exit 1
            fi
            
            msg "client_id" "$APP_ID"
            echo ""
            ;;
        3)
            msg "operation_cancelled"
            exit 1
            ;;
        *)
            msg "invalid_choice"
            exit 1
            ;;
    esac
else
    # 기존 앱 없음, 새로 생성
    APP_NAME="WordPress-Azure-AI-Chatbot-$(date +%Y%m%d%H%M%S)"
    
    msg "creating_app" "$APP_NAME"
    echo ""
    
    # 타임아웃 30초로 앱 생성 (복잡한 작업이므로 충분한 시간 부여)
    set +e
    APP_CREATE_OUTPUT=$(timeout 30s az ad app create \
        --display-name "$APP_NAME" \
        --sign-in-audience "AzureADMyOrg" \
        --web-redirect-uris "$REDIRECT_URI" \
        --query appId -o tsv 2>&1)
    EXIT_CODE=$?
    set -e
    
    # 타임아웃 체크
    if [ $EXIT_CODE -eq 124 ]; then
        msg "app_creation_timeout"
        exit 1
    fi
    
    # GUID 형식 검증 (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)
    if echo "$APP_CREATE_OUTPUT" | grep -qE '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'; then
        APP_ID="$APP_CREATE_OUTPUT"
    else
        # 에러 발생
        msg "app_creation_failed"
        echo ""
        
        if echo "$APP_CREATE_OUTPUT" | grep -qi "token is expired\|token has expired\|lifetime validation failed"; then
            msg "token_expired"
        elif echo "$APP_CREATE_OUTPUT" | grep -qi "insufficient privileges\|authorization\|permission"; then
            msg "insufficient_privileges"
        else
            msg "error_details"
            echo "   $APP_CREATE_OUTPUT"
            echo ""
        fi
        exit 1
    fi
    
    msg "client_id" "$APP_ID"
    echo ""
fi

# Tenant ID 가져오기
TENANT_ID=$(az account show --query tenantId -o tsv)
msg "tenant_id" "$TENANT_ID"
echo ""

# Client Secret 생성 (타임아웃 30초)
msg "creating_secret"

set +e
SECRET_RESPONSE=$(timeout 30s az ad app credential reset --id "$APP_ID" --append --query password -o tsv 2>&1)
EXIT_CODE=$?
set -e

if [ $EXIT_CODE -eq 124 ]; then
    msg "secret_creation_timeout"
    exit 1
fi

if [ -z "$SECRET_RESPONSE" ] || ! echo "$SECRET_RESPONSE" | grep -qE '^[A-Za-z0-9~_\.\-]{30,}$'; then
    msg "secret_creation_failed"
    echo ""
    msg "error_details"
    echo "   $SECRET_RESPONSE"
    echo ""
    exit 1
fi

CLIENT_SECRET="$SECRET_RESPONSE"
msg "secret_value"
echo "$CLIENT_SECRET"
msg "save_secret"
echo ""

# API 권한 추가 (타임아웃 20초)
msg "adding_permissions"

# Microsoft Graph - User.Read
if [ "$LANG" = "en" ]; then
    echo "  - Microsoft Graph: User.Read"
else
    echo "  - Microsoft Graph: User.Read"
fi

set +e
timeout 20s az ad app permission add --id "$APP_ID" \
    --api 00000003-0000-0000-c000-000000000000 \
    --api-permissions e1fe6dd8-ba31-4d61-89e7-88639da4683d=Scope \
    > /dev/null 2>&1
EXIT_CODE=$?
set -e

if [ $EXIT_CODE -eq 124 ]; then
    msg "permission_timeout"
    exit 1
elif [ $EXIT_CODE -ne 0 ]; then
    msg "permission_failed"
fi

# Azure Service Management - user_impersonation  
if [ "$LANG" = "en" ]; then
    echo "  - Azure Service Management: user_impersonation"
else
    echo "  - Azure Service Management: user_impersonation"
fi

set +e
timeout 20s az ad app permission add --id "$APP_ID" \
    --api 797f4846-ba00-4fd7-ba43-dac1f8f63013 \
    --api-permissions 41094075-9dad-400e-a0bd-54e686782033=Scope \
    > /dev/null 2>&1
EXIT_CODE=$?
set -e

if [ $EXIT_CODE -eq 124 ]; then
    msg "permission_timeout"
    exit 1
elif [ $EXIT_CODE -ne 0 ]; then
    msg "permission_failed"
fi

msg "permissions_done"
echo ""

# Admin Consent URL 생성
CONSENT_URL="https://login.microsoftonline.com/$TENANT_ID/adminconsent?client_id=$APP_ID"

# Admin Consent URL 안내
echo "========================================="
if [ "$LANG" = "ko" ]; then
    echo "🔐 관리자 동의 필요 (필수 단계!)"
else
    echo "🔐 Admin Consent Required (Mandatory!)"
fi
echo "========================================="
echo ""

if [ "$LANG" = "ko" ]; then
    echo "⚠️  중요: Azure 자동 설정을 완료하려면 관리자 동의가 필요합니다!"
    echo ""
    echo "📌 아래 링크를 Ctrl+클릭하여 브라우저에서 열어주세요:"
else
    echo "⚠️  Important: Admin consent is required to complete Azure setup!"
    echo ""
    echo "📌 Ctrl+Click the link below to open in your browser:"
fi
echo ""
echo -e "\033]8;;${CONSENT_URL}\033\\${CONSENT_URL}\033]8;;\033\\"
echo ""

if [ "$LANG" = "ko" ]; then
    echo "   (링크를 클릭할 수 없다면 위 URL을 복사하여 브라우저에 붙여넣으세요)"
    echo ""
    echo "승인 절차:"
    echo "  1. 위 링크를 클릭하여 브라우저에서 엽니다"
    echo "  2. Azure 계정으로 로그인합니다"
    echo "  3. 권한 요청 화면에서 '승인(Accept)' 버튼을 클릭합니다"
    echo "  4. 승인이 완료되면 이 터미널로 돌아옵니다"
    echo ""
    read -p "✅ 승인을 완료했으면 Enter 키를 눌러 계속 진행하세요... " CONSENT_DONE
else
    echo "   (If the link doesn't work, copy and paste the URL into your browser)"
    echo ""
    echo "Approval steps:"
    echo "  1. Click the link above to open in browser"
    echo "  2. Sign in with your Azure account"
    echo "  3. Click 'Accept' button on the permissions page"
    echo "  4. Return to this terminal after approval"
    echo ""
    read -p "✅ Press Enter after completing the approval... " CONSENT_DONE
fi
echo ""

if [ "$LANG" = "ko" ]; then
    echo "✅ 관리자 동의 단계 완료"
else
    echo "✅ Admin consent step completed"
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
