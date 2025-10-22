# Azure 자동 설정 기능

WordPress 관리자 페이지에서 Azure OAuth 인증을 통해 자동으로 Azure AI Foundry 리소스 정보를 가져올 수 있는 기능입니다.

## 사전 준비: Azure App Registration

자동 설정 기능을 사용하려면 먼저 Azure AD에 애플리케이션을 등록해야 합니다.

### 1. Azure Portal에서 App Registration 생성

```bash
# Azure Portal에서 수동으로 등록하거나 CLI 사용
az ad app create \
  --display-name "WordPress Azure AI Chatbot Auto Setup" \
  --sign-in-audience "AzureADMyOrg" \
  --web-redirect-uris "https://your-wordpress-site.com/wp-admin/admin.php?page=azure-chatbot-settings&azure_callback=1"
```

**중요**: `your-wordpress-site.com`을 실제 WordPress 사이트 도메인으로 변경하세요.

### 2. API 권한 추가

Azure Portal > App registrations > 생성한 앱 > API permissions:

1. **Microsoft Graph** (위임된 권한)
   - `User.Read` - 로그인한 사용자 정보 읽기

2. **Azure Service Management** (위임된 권한)
   - `user_impersonation` - Azure 리소스 관리 권한

권한 추가 후 **"Grant admin consent"** 클릭 필요

### 3. Client Secret 생성

Azure Portal > App registrations > 생성한 앱 > Certificates & secrets:

1. "New client secret" 클릭
2. Description 입력 (예: "WordPress Plugin")
3. Expires 선택 (예: 24 months)
4. **생성된 Secret Value를 복사** (다시 볼 수 없으므로 안전하게 보관)

### 4. Application (client) ID 확인

Azure Portal > App registrations > 생성한 앱 > Overview:

- **Application (client) ID** 복사
- **Directory (tenant) ID** 복사

## WordPress 플러그인 설정

### 방법 1: wp-config.php에 상수 정의 (권장)

```php
// wp-config.php 파일에 추가
define('AZURE_CHATBOT_OAUTH_CLIENT_ID', 'your-client-id-here');
define('AZURE_CHATBOT_OAUTH_CLIENT_SECRET', 'your-client-secret-here');
define('AZURE_CHATBOT_OAUTH_TENANT_ID', 'your-tenant-id-here');
```

### 방법 2: WordPress 관리자 페이지에서 입력

WordPress 관리자 > Azure AI Chatbot > 설정:

1. "Azure OAuth 설정" 섹션에서 값 입력
2. Client ID
3. Client Secret
4. Tenant ID

## 자동 설정 사용 방법

### 1. 자동 설정 시작

WordPress 관리자 > Azure AI Chatbot > 설정:

1. **"Azure 자동 설정"** 버튼 클릭
2. Azure 로그인 페이지로 리다이렉트
3. Microsoft 계정으로 로그인
4. 권한 요청 동의

### 2. 리소스 선택

인증 완료 후 자동으로 WordPress 설정 페이지로 돌아옵니다:

1. **Subscription 선택** - 드롭다운에서 사용할 구독 선택
2. **Resource Group 선택** - 드롭다운에서 리소스 그룹 선택
3. **모드 선택**:
   - **Chat 모드**: Azure OpenAI Service 또는 AI Services 선택
   - **Agent 모드**: AI Foundry Project 선택
4. **리소스 선택** - 사용할 리소스 선택

### 3. 자동 값 추출

리소스 선택 후 **"값 가져오기"** 버튼 클릭:

- **Chat 모드**:
  - Endpoint URL 자동 추출
  - API Key 자동 추출
  
- **Agent 모드**:
  - Project Endpoint 자동 추출
  - Agent ID 목록 표시 (수동 선택)
  - Subscription Key 자동 추출

### 4. 설정 저장

자동으로 입력된 값 확인 후 **"설정 저장"** 버튼 클릭

## 보안 고려사항

### Access Token 관리

- Access Token은 **세션에만 저장**되며 DB에 저장되지 않음
- 세션 만료 시간: 1시간
- 리소스 조회 완료 후 즉시 폐기 권장

### Client Secret 보호

- wp-config.php 사용 시 파일 권한 확인 (644 또는 640)
- DB 저장 시 AES-256 암호화
- .env 파일 사용 시 .gitignore에 추가

### CSRF 방지

- OAuth State 파라미터로 요청 검증
- Nonce 사용으로 중복 요청 차단

## 문제 해결

### "인증 실패" 오류

1. Client ID, Secret, Tenant ID 확인
2. Redirect URI가 정확히 일치하는지 확인
3. API 권한이 부여되었는지 확인

### "리소스 조회 실패" 오류

1. Azure 계정에 해당 구독 접근 권한이 있는지 확인
2. API 권한에서 "user_impersonation" 확인
3. Admin consent가 부여되었는지 확인

### "만료된 세션" 오류

- 자동 설정 시작 후 1시간 이내에 완료해야 함
- 다시 "Azure 자동 설정" 버튼 클릭하여 재시작

## 수동 설정과의 비교

| 항목 | 수동 설정 | 자동 설정 |
|------|----------|----------|
| 설정 시간 | 10-15분 | 2-3분 |
| Azure Portal 접속 | 필요 | 불필요 |
| 키 복사/붙여넣기 | 수동 | 자동 |
| 오타 가능성 | 있음 | 없음 |
| 초기 설정 | App Registration 필요 | App Registration 필요 (1회) |

## Azure CLI 자동 설정 스크립트

App Registration을 자동으로 생성하는 스크립트:

```bash
# Azure Cloud Shell에서 실행
curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/scripts/setup-oauth-app.sh | bash
```

스크립트는 다음을 수행합니다:
1. App Registration 생성
2. API 권한 추가
3. Client Secret 생성
4. Redirect URI 설정
5. 생성된 값 출력 (Client ID, Secret, Tenant ID)

## 참고 자료

- [Azure AD OAuth 2.0 문서](https://learn.microsoft.com/azure/active-directory/develop/v2-oauth2-auth-code-flow)
- [Azure Management REST API](https://learn.microsoft.com/rest/api/azure/)
- [WordPress 플러그인 보안](https://developer.wordpress.org/plugins/security/)
