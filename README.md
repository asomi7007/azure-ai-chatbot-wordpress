# Azure AI Chatbot for WordPress

Azure AI Foundry의 강력한 AI 에이전트를 WordPress 웹사이트에 쉽게 통합하는 플러그인입니다.

![Version](https://img.shields.io/badge/version-2.1.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)

## ✨ 주요 기능

- ✅ **쉬운 설정**: wp-config.php 수정 없이 관리자 페이지에서 모든 설정
- 🎨 **완전한 커스터마이징**: 색상, 위치, 메시지 자유롭게 변경
- 🤖 **Azure AI 완벽 지원**: Function Calling, RAG, 파일 업로드 등
- 📱 **반응형 디자인**: 데스크톱과 모바일 완벽 지원
- 🔒 **보안**: API 키는 서버에서만 관리
- 📖 **마크다운 가이드**: 편집 가능한 상세 가이드 제공
- 🧪 **연결 테스트**: 설정 페이지에서 즉시 테스트 가능

## 📦 설치 방법

### ZIP 파일로 설치 (권장) ⭐

가장 쉽고 빠른 설치 방법입니다!

1. **[GitHub Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)에서 최신 `azure-ai-chatbot-wordpress.zip` 다운로드**
2. WordPress 관리자 페이지 접속
3. **플러그인** → **새로 추가** → **플러그인 업로드** 클릭
4. 다운로드한 ZIP 파일 선택
5. **지금 설치** 클릭
6. 설치 완료 후 **플러그인 활성화** 클릭

> 💡 **Tip**: ZIP 파일 압축을 풀 필요 없습니다! 그대로 업로드하세요.

### 수동 설치 (개발자용)

소스 코드를 직접 편집하려는 경우:

1. 이 저장소를 다운로드하거나 복제
   ```bash
   git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git
   ```
2. 폴더를 `/wp-content/plugins/` 디렉토리에 업로드
3. WordPress 관리자 페이지에서 플러그인 활성화

### WordPress.org에서 설치 (향후 지원 예정)

1. WordPress 관리자 페이지 → **플러그인** → **새로 추가**
2. "Azure AI Chatbot" 검색
3. **지금 설치** → **활성화**

## 🚀 빠른 시작

### 방법 1: Chat 모드 (간단 - 권장) ⭐

Chat 모드는 API Key만으로 즉시 사용 가능합니다!

#### 📋 Azure Cloud Shell에서 자동 테스트 (Ctrl+V로 붙여넣기)

Azure Cloud Shell ([shell.azure.com](https://shell.azure.com))에서 아래 전체를 **Ctrl+V**로 붙여넣기하면 자동으로 테스트됩니다:

```bash
cat > test_chat_mode.sh << 'SCRIPT_EOF'
#!/bin/bash
echo "========================================="
echo "Azure AI Chatbot - Chat 모드 연결 테스트"
echo "========================================="
echo ""
RESOURCE_NAME="your-resource-name"  # ← 여기만 수정하세요!
DEPLOYMENT_NAME="gpt-4o"
echo "🔍 Azure OpenAI 리소스 검색 중..."
RESOURCE_GROUP=$(az cognitiveservices account list --query "[?name=='$RESOURCE_NAME'].resourceGroup | [0]" -o tsv)
if [ -z "$RESOURCE_GROUP" ]; then
    echo "❌ 리소스를 찾을 수 없습니다: $RESOURCE_NAME"
    echo "사용 가능한 Azure OpenAI 리소스:"
    az cognitiveservices account list --query "[?kind=='OpenAI'].{Name:name, ResourceGroup:resourceGroup}" -o table
    exit 1
fi
ENDPOINT=$(az cognitiveservices account show --name "$RESOURCE_NAME" --resource-group "$RESOURCE_GROUP" --query "properties.endpoint" -o tsv)
API_KEY=$(az cognitiveservices account keys list --name "$RESOURCE_NAME" --resource-group "$RESOURCE_GROUP" --query "key1" -o tsv)
echo "✅ 리소스: $RESOURCE_NAME"
echo "📍 엔드포인트: $ENDPOINT"
echo "🔑 API Key: ${API_KEY:0:8}...${API_KEY: -4}"
echo ""
ENDPOINT="${ENDPOINT%/}"
TEST_URL="${ENDPOINT}/openai/deployments/${DEPLOYMENT_NAME}/chat/completions?api-version=2024-08-01-preview"
echo "🧪 Chat API 테스트 중..."
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "${TEST_URL}" \
  -H "Content-Type: application/json" \
  -H "api-key: ${API_KEY}" \
  -d '{"messages":[{"role":"user","content":"Hello"}],"max_tokens":10}')
HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE:" | cut -d':' -f2)
if [ "$HTTP_CODE" == "200" ]; then
    echo "✅ 성공! HTTP $HTTP_CODE"
    echo "응답: $(echo "$RESPONSE" | sed '/HTTP_CODE:/d' | jq -r '.choices[0].message.content' 2>/dev/null)"
    echo ""
    echo "========================================="
    echo "✅ WordPress 플러그인 설정값"
    echo "========================================="
    echo "작동 모드: Chat 모드 (OpenAI 호환)"
    echo "Chat 엔드포인트: ${ENDPOINT}"
    echo "배포 이름: ${DEPLOYMENT_NAME}"
    echo "API Key: ${API_KEY}"
else
    echo "❌ 실패: HTTP $HTTP_CODE"
    echo "$(echo "$RESPONSE" | sed '/HTTP_CODE:/d' | jq '.' 2>/dev/null)"
fi
SCRIPT_EOF
chmod +x test_chat_mode.sh
./test_chat_mode.sh
```

> **💡 사용 방법:**
> 1. 위 코드 전체를 복사 (Ctrl+C)
> 2. Azure Cloud Shell 열기 ([shell.azure.com](https://shell.azure.com))
> 3. 붙여넣기 (Ctrl+V) → Enter
> 4. `RESOURCE_NAME="your-resource-name"` 부분만 실제 리소스명으로 수정
> 5. Enter 키를 한 번 더 누르면 자동 실행!

#### WordPress 플러그인 설정

위 스크립트 결과에서 나온 값을 그대로 WordPress 관리자 페이지에 입력하세요:

1. WordPress 관리자 → **Azure AI Chatbot** → **설정**
2. **작동 모드**: `Chat 모드 (OpenAI 호환)` 선택
3. 스크립트 결과에서 나온 값 입력
4. **저장** 버튼 클릭
5. **연결 테스트** 버튼으로 확인

---

### 방법 2: Agent 모드 (고급 기능) 🤖

Agent 모드는 Azure AI Foundry의 **Assistants API v1**을 사용하여 다음 고급 기능을 제공합니다:

**✨ Agent 모드 주요 기능:**
- 🧵 **Thread 관리**: 대화 컨텍스트 자동 유지 (재방문 시 이전 대화 기억)
- 🛠️ **Function Calling**: 외부 API 호출, 데이터베이스 조회 등 확장 가능
- 📎 **파일 업로드**: 문서 분석 및 RAG (Retrieval-Augmented Generation)
- 🔄 **비동기 Run**: 장시간 실행 작업 지원
- 📊 **상태 추적**: 실시간 Run 상태 모니터링 (queued → in_progress → completed)

**⚠️ 중요: API 버전**
- Azure AI Foundry Assistants API는 **`api-version=v1`만 지원**됩니다
- `2024-12-01-preview` 등 날짜 기반 버전은 Sweden Central 등 일부 리전에서 작동하지 않습니다
- 본 플러그인은 `v1`을 사용하여 모든 리전에서 호환됩니다

#### Azure Cloud Shell 완전 자동 설정 스크립트

**⚡ 복사 → 붙여넣기 → 실행:**

```bash
cat > setup_azure_agent.sh << 'EOFSCRIPT'
#!/bin/bash
set -e

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# 🚀 Azure AI Chatbot WordPress - Agent 모드 자동 설정
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📋 Azure AI 프로젝트 정보 입력"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 사용자 입력
read -p "Resource Group 이름: " RESOURCE_GROUP
read -p "AI Foundry 리소스 이름: " ACCOUNT_NAME
read -p "프로젝트 이름 (리소스와 동일하면 엔터): " PROJECT_NAME
PROJECT_NAME=${PROJECT_NAME:-$ACCOUNT_NAME}
read -p "Service Principal 이름 (기본: azure-ai-chatbot-wp): " SP_NAME
SP_NAME=${SP_NAME:-"azure-ai-chatbot-wp"}

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "� Azure 구독 정보 확인 중..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

SUBSCRIPTION_ID=$(az account show --query "id" -o tsv)
TENANT_ID=$(az account show --query "tenantId" -o tsv)
RESOURCE_ID="/subscriptions/$SUBSCRIPTION_ID/resourceGroups/$RESOURCE_GROUP/providers/Microsoft.CognitiveServices/accounts/$ACCOUNT_NAME"

echo "✅ Subscription ID: $SUBSCRIPTION_ID"
echo "✅ Tenant ID: $TENANT_ID"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔐 Service Principal 생성 중..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Service Principal 생성 시도
SP_OUTPUT=$(az ad sp create-for-rbac \
  --name "$SP_NAME" \
  --role "Cognitive Services User" \
  --scopes "$RESOURCE_ID" \
  2>&1)

if echo "$SP_OUTPUT" | grep -q "appId"; then
    echo "✅ Service Principal 생성 완료!"
    CLIENT_ID=$(echo $SP_OUTPUT | jq -r '.appId')
    CLIENT_SECRET=$(echo $SP_OUTPUT | jq -r '.password')
else
    echo "⚠️  이미 존재하는 Service Principal입니다."
    echo "   새 Client Secret을 생성합니다..."
    
    APP_ID=$(az ad sp list --display-name "$SP_NAME" --query "[0].appId" -o tsv)
    if [ -z "$APP_ID" ]; then
        echo "❌ Service Principal을 찾을 수 없습니다."
        echo "   다른 이름으로 다시 시도하거나 Azure Portal에서 수동으로 생성하세요."
        exit 1
    fi
    
    SP_OUTPUT=$(az ad app credential reset --id "$APP_ID" --append --years 1)
    CLIENT_ID=$APP_ID
    CLIENT_SECRET=$(echo $SP_OUTPUT | jq -r '.password')
    echo "✅ Client Secret 재생성 완료!"
fi

AGENT_ENDPOINT="https://${ACCOUNT_NAME}.services.ai.azure.com/api/projects/${PROJECT_NAME}"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🤖 AI Agent 확인 중..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Token 생성
TOKEN=$(curl -s -X POST "https://login.microsoftonline.com/$TENANT_ID/oauth2/v2.0/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "client_id=$CLIENT_ID" \
  -d "client_secret=$CLIENT_SECRET" \
  -d "scope=https://ai.azure.com/.default" \
  -d "grant_type=client_credentials" | jq -r '.access_token')

if [ "$TOKEN" == "null" ] || [ -z "$TOKEN" ]; then
    echo "⚠️  토큰 생성 실패. 권한 설정 중일 수 있습니다."
    echo "   1-2분 후 다시 시도하거나 Agent ID를 수동으로 입력하세요."
    AGENT_ID="[AI Foundry에서 확인 필요]"
else
    # Assistants 목록 조회 (v1 API 사용)
    ASSISTANTS=$(curl -s \
      "${AGENT_ENDPOINT}/assistants?api-version=v1" \
      -H "Authorization: Bearer $TOKEN")
    
    AGENT_COUNT=$(echo $ASSISTANTS | jq -r '.data | length' 2>/dev/null || echo "0")
    
    if [ "$AGENT_COUNT" == "0" ]; then
        echo ""
        echo "❌ Agent가 존재하지 않습니다!"
        echo ""
        echo "다음 방법 중 하나를 선택하세요:"
        echo ""
        echo "1️⃣  AI Foundry Portal에서 생성 (권장)"
        echo "   https://ai.azure.com → Agents → Create"
        echo ""
        echo "2️⃣  Azure Cloud Shell에서 생성:"
        echo ""
        read -p "   지금 생성하시겠습니까? (y/n): " CREATE_AGENT
        
        if [ "$CREATE_AGENT" == "y" ]; then
            read -p "   Agent 이름: " AGENT_NAME
            read -p "   Agent 설명 (옵션): " AGENT_DESC
            read -p "   사용할 모델 (기본: gpt-4o): " AGENT_MODEL
            AGENT_MODEL=${AGENT_MODEL:-"gpt-4o"}
            
            NEW_AGENT=$(curl -s -X POST \
              "${AGENT_ENDPOINT}/assistants?api-version=v1" \
              -H "Authorization: Bearer $TOKEN" \
              -H "Content-Type: application/json" \
              -d "{\"model\":\"$AGENT_MODEL\",\"name\":\"$AGENT_NAME\",\"description\":\"$AGENT_DESC\",\"instructions\":\"당신은 친절한 AI 도우미입니다.\"}")
            
            AGENT_ID=$(echo $NEW_AGENT | jq -r '.id')
            echo "✅ Agent 생성 완료: $AGENT_ID"
        else
            AGENT_ID="[나중에 AI Foundry에서 생성 후 입력]"
        fi
    else
        echo "✅ $AGENT_COUNT 개의 Agent 발견!"
        echo ""
        echo "사용 가능한 Agents:"
        echo $ASSISTANTS | jq -r '.data[] | "  - \(.id): \(.name // "Unnamed")"'
        echo ""
        
        if [ "$AGENT_COUNT" == "1" ]; then
            AGENT_ID=$(echo $ASSISTANTS | jq -r '.data[0].id')
            AGENT_NAME=$(echo $ASSISTANTS | jq -r '.data[0].name // "Unnamed"')
            echo "✅ 자동 선택: $AGENT_ID ($AGENT_NAME)"
        else
            read -p "사용할 Agent ID를 입력하세요: " AGENT_ID
        fi
    fi
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ 설정 완료!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 WordPress에 아래 값을 복사하여 입력하세요:"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Agent 엔드포인트:"
echo "$AGENT_ENDPOINT"
echo ""
echo "Agent ID:"
echo "$AGENT_ID"
echo ""
echo "Client ID:"
echo "$CLIENT_ID"
echo ""
echo "Client Secret:"
echo "$CLIENT_SECRET"
echo ""
echo "Tenant ID:"
echo "$TENANT_ID"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "⚠️  중요: Client Secret은 지금만 표시됩니다!"
echo "         안전한 곳에 즉시 저장하세요!"
echo ""
EOFSCRIPT

chmod +x setup_azure_agent.sh
./setup_azure_agent.sh
```

**� 사용 방법:**
1. Azure Cloud Shell (https://shell.azure.com) 접속
2. 위 전체 코드 블록 복사
3. Cloud Shell에 붙여넣기
4. 프롬프트에 따라 정보 입력
5. 출력된 값들을 WordPress 설정에 입력

**📋 출력 예시:**

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Agent 엔드포인트:
https://your-resource.services.ai.azure.com/api/projects/your-project

Agent ID:
asst_xxxxxxxxxxxxxxxxxxxxxx

Client ID:
xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx

Client Secret:
xxx~xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

Tenant ID:
xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

#### 개별 명령어로 설정 (선택사항)

스크립트를 사용하지 않는 경우 개별 명령어:

```bash
# 1. Service Principal 생성
az ad sp create-for-rbac \
  --name "azure-ai-chatbot-wordpress" \
  --role "Cognitive Services User" \
  --scopes "/subscriptions/{SUBSCRIPTION_ID}/resourceGroups/{RG}/providers/Microsoft.CognitiveServices/accounts/{ACCOUNT}"

# 2. 출력에서 다음 정보 복사:
# - appId → Client ID
# - password → Client Secret
# - tenant → Tenant ID

# 3. Agent ID는 Azure AI Foundry (https://ai.azure.com)에서 확인
```

#### Client Secret 재생성 (분실 시)

```bash
# 기존 Service Principal의 새 Client Secret 생성
az ad app credential reset \
  --id "{CLIENT_ID}" \
  --append \
  --years 1

# 출력된 password를 WordPress에 입력
```

---

### 1단계: Azure AI Foundry 정보 확인 (레거시)

Azure Portal에서 다음 정보를 확인하세요:

- **API Key**: AI Foundry 리소스 → "키 및 엔드포인트"
- **프로젝트 엔드포인트**: `https://[리소스명].services.ai.azure.com/api/projects/[프로젝트명]`
- **에이전트 ID**: AI Foundry에서 생성한 에이전트 ID (예: `asst_xxxxx`)

### 2단계: 플러그인 설정

1. WordPress 관리자 → **AI Chatbot** → **설정**
2. Azure 정보 입력
3. **위젯 활성화** 체크
4. **설정 저장**

### 3단계: 테스트

- **연결 테스트** 버튼으로 Azure 연결 확인
- 웹사이트 방문하여 채팅 버튼 확인

## 📁 파일 구조

```
azure-ai-chatbot/
├── azure-ai-chatbot.php      # 메인 플러그인 파일
├── README.md                  # 이 파일
├── assets/                    # CSS/JS 리소스
│   ├── chatbot.css           # 프론트엔드 스타일
│   ├── chatbot.js            # 프론트엔드 스크립트
│   ├── admin.css             # 관리자 스타일
│   └── admin.js              # 관리자 스크립트
├── templates/                 # PHP 템플릿
│   ├── settings-page.php     # 설정 페이지
│   └── guide-page.php        # 가이드 페이지
└── docs/                      # 문서
    └── USER_GUIDE.md         # 사용자 가이드 (편집 가능)
```

## ⚙️ 설정 옵션

### Azure 연결

| 설정 | 설명 | 필수 |
|------|------|------|
| API Key | Azure AI API 키 | ✅ |
| 프로젝트 엔드포인트 | Azure AI 프로젝트 URL | ✅ |
| 에이전트 ID | 사용할 에이전트 ID | ✅ |

### 위젯 설정

| 설정 | 설명 | 기본값 |
|------|------|--------|
| 위젯 활성화 | 채팅 위젯 표시 여부 | 비활성화 |
| 위젯 위치 | 버튼 위치 (오른쪽/왼쪽 하단) | 오른쪽 하단 |
| 채팅 제목 | 채팅창 제목 | "AI 도우미" |
| 환영 메시지 | 첫 메시지 | "안녕하세요! ..." |

### 디자인

| 설정 | 설명 | 기본값 |
|------|------|--------|
| 주 색상 | 버튼 및 사용자 메시지 색상 | #667eea |
| 보조 색상 | 그라데이션 두 번째 색상 | #764ba2 |

## 🎨 커스터마이징

### CSS 커스터마이징

테마의 `style.css`에 추가:

```css
/* 채팅 버튼 크기 */
.chatbot-toggle {
    width: 70px !important;
    height: 70px !important;
}

/* 채팅창 크기 */
.chatbot-window {
    width: 400px !important;
    height: 650px !important;
}
```

### Function Calling 추가

`functions.php`에 추가:

```php
add_filter('azure_chatbot_function_call', function($result, $function_name, $arguments) {
    if ($function_name === 'my_custom_function') {
        // 커스텀 로직
        return ['result' => 'success'];
    }
    return $result;
}, 10, 3);
```

## 🔧 개발자 가이드

### 훅 (Hooks)

**필터:**
- `azure_chatbot_function_call` - Function calling 처리
- `azure_chatbot_before_send` - 메시지 전송 전
- `azure_chatbot_response_format` - 응답 포맷 변경

**액션:**
- `azure_chatbot_after_response` - 응답 받은 후
- `azure_chatbot_widget_loaded` - 위젯 로드 완료

### API 엔드포인트

```
POST /wp-json/azure-chatbot/v1/chat
```

**요청 본문:**
```json
{
    "message": "사용자 메시지",
    "thread_id": "thread_xxxxx" (선택)
}
```

**응답:**
```json
{
    "success": true,
    "reply": "AI 응답",
    "thread_id": "thread_xxxxx"
}
```

## 📊 시스템 요구사항

- **WordPress**: 6.0 이상
- **PHP**: 7.4 이상
- **Azure 구독**: Active Azure subscription
- **메모리**: 최소 128MB PHP memory limit
- **SSL**: HTTPS 권장 (API 보안)

## 🐛 문제 해결

### 채팅 버튼이 보이지 않음

1. **설정** → **위젯 활성화** 체크 확인
2. API Key, 엔드포인트, 에이전트 ID 모두 입력 확인
3. 브라우저 캐시 삭제 및 새로고침

### API 오류 발생

1. Azure Portal에서 API Key 재확인
2. **연결 테스트** 버튼으로 진단
3. `/wp-content/debug.log` 확인

### 디버그 모드 활성화

`wp-config.php`에 추가:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 💰 비용 안내

### Azure AI Foundry 가격 (2025년 기준)

**GPT-4o 모델:**
- 입력: $2.50 per 1M tokens
- 출력: $10.00 per 1M tokens

**예상 비용:**
- 월 1,000건 대화 (평균 500토큰) ≈ $3-5
- 월 10,000건 대화 ≈ $30-50

자세한 요금: [Azure 가격 계산기](https://azure.microsoft.com/pricing/calculator/)

## 🔐 보안 고려사항

### API Key 암호화 저장

**중요**: 이 플러그인은 API Key를 평문으로 저장하지 않습니다!

#### 암호화 상세
- **알고리즘**: AES-256-CBC
- **키 생성**: WordPress 보안 상수 조합 (SHA-256 해시)
- **IV**: 랜덤 생성 (매번 다름)
- **요구사항**: OpenSSL PHP 확장 (대부분의 서버에 기본 설치)

#### 자동 보안 키 생성 ✨

**플러그인 활성화 시 자동으로:**

1. `wp-config.php`의 보안 키 확인
2. 보안 키가 없거나 기본값(`put your unique phrase here`)이면:
   - WordPress.org API에서 새 보안 키 자동 생성
   - `wp-config.php`에 자동 추가/업데이트
   - 기존 파일 백업 (`wp-config.php.backup-YYYYMMDD-HHMMSS`)
   - 성공 여부를 관리자 화면에 알림
3. 파일 쓰기 권한이 없으면 수동 설정 안내

**성공 시 표시:**
```
✅ WordPress 보안 키가 자동으로 생성되어 wp-config.php에 추가되었습니다!
백업 파일: wp-config.php.backup-2025-01-15-143022
```

**수동 설정 (필요 시):**
```php
// wp-config.php에 추가
define('AUTH_KEY', 'your-unique-phrase');
define('SECURE_AUTH_KEY', 'your-unique-phrase');
define('LOGGED_IN_KEY', 'your-unique-phrase');
define('NONCE_KEY', 'your-unique-phrase');
define('AUTH_SALT', 'your-unique-phrase');
define('SECURE_AUTH_SALT', 'your-unique-phrase');
define('LOGGED_IN_SALT', 'your-unique-phrase');
define('NONCE_SALT', 'your-unique-phrase');
```

보안 키 생성: https://api.wordpress.org/secret-key/1.1/salt/

### 보안 기능

- ✅ **AES-256 암호화**: API Key 데이터베이스 암호화
- ✅ **API Key 마스킹**: 설정 페이지에서 전체 키 숨김 (예: ab12••••••••xy89)
- ✅ **서버 사이드 처리**: API Key는 서버에서만 사용, 클라이언트 노출 없음
- ✅ **WordPress Nonce**: CSRF 공격 방어
- ✅ **입력 검증**: 모든 입력 sanitization
- ✅ **권한 확인**: 관리자만 설정 변경 가능
- ❌ **Rate Limiting**: 향후 업데이트 예정

### 보안 권장사항

1. **HTTPS 사용**: SSL 인증서 필수
2. **WordPress 업데이트**: 최신 버전 유지
3. **강력한 비밀번호**: 관리자 계정 보안
4. **2FA 활성화**: 2단계 인증 사용
5. **정기 백업**: 데이터베이스 및 파일 백업
6. **보안 플러그인**: Wordfence, iThemes Security 등 사용

## 📈 성능 최적화

### 권장 설정

1. **캐싱 플러그인 사용**: WP Rocket, W3 Total Cache
2. **CDN 활용**: Cloudflare, Amazon CloudFront
3. **이미지 최적화**: Imagify, ShortPixel
4. **데이터베이스 최적화**: WP-Optimize

### 속도 개선 팁

- Thread ID를 로컬 스토리지에 저장하여 불필요한 생성 방지
- 에이전트 프롬프트를 간결하게 유지
- Function calling 응답 캐싱 고려

## 🌍 다국어 지원

현재 한국어로 제공되며, 향후 영어 등 추가 언어를 지원할 예정입니다.

### 번역 기여

번역에 참여하고 싶으신가요?
- `.pot` 파일: `languages/azure-ai-chatbot.pot`
- 연락처: admin@eldensolution.kr

## 🤝 기여하기

### 버그 리포트

다음 정보와 함께 이메일 보내주세요:
- WordPress 버전
- PHP 버전
- 플러그인 버전
- 오류 메시지
- 재현 단계

**연락처**: admin@eldensolution.kr

### 기능 제안

새 기능 아이디어가 있으신가요?
- 이메일: admin@eldensolution.kr
- 제목: [Feature Request] 기능 제목

### 코드 기여

1. Fork this repository
2. Create your feature branch: `git checkout -b feature/AmazingFeature`
3. Commit your changes: `git commit -m 'Add some AmazingFeature'`
4. Push to the branch: `git push origin feature/AmazingFeature`
5. Open a Pull Request

## 📝 변경 로그

### 2.0.0 (2025-01-XX)

**추가:**
- ✨ 관리자 페이지에서 모든 설정 가능
- ✨ 마크다운 가이드 편집 기능
- ✨ 색상 및 위치 커스터마이징
- ✨ 연결 테스트 기능
- ✨ Function calling 확장 포인트

**개선:**
- 🎨 향상된 UI/UX
- 🔒 보안 강화 (Nonce 검증)
- 📱 모바일 반응형 개선
- ⚡ 성능 최적화

**수정:**
- 🐛 Thread ID 저장 버그 수정
- 🐛 색상 선택기 버그 수정

### 1.0.0 (초기 릴리스)
- 기본 채팅 기능
- Azure AI Foundry 연동

## 📚 추가 리소스

### 공식 문서
- [Azure AI Foundry](https://learn.microsoft.com/azure/ai-foundry/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)

### 튜토리얼
- [Azure AI Agent 생성하기](https://ai.azure.com)
- [Function Calling 가이드](https://learn.microsoft.com/azure/ai-foundry/agents/)

### 커뮤니티
- **이메일 지원**: admin@eldensolution.kr
- **웹사이트**: https://www.eldensolution.kr

## 📄 라이선스

이 프로젝트는 GPL-2.0+ 라이선스 하에 배포됩니다.

```
Copyright (C) 2025 Elden Solution (엘던솔루션)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
```

## 👤 제작자

**엘던솔루션 (Elden Solution)**
- 웹사이트: https://www.eldensolution.kr
- 이메일: admin@eldensolution.kr
- 위치: 대한민국

## 🙏 감사의 말

이 플러그인을 만드는 데 도움을 주신 분들:
- Microsoft Azure AI 팀
- WordPress 커뮤니티
- 모든 베타 테스터분들

Developed with ❤️ by [Elden Solution](https://www.eldensolution.kr)

## 💡 향후 계획

### v2.1.0 (예정)
- [ ] 실시간 스트리밍 응답
- [ ] 대화 내역 대시보드
- [ ] 다중 에이전트 지원
- [ ] 음성 입력/출력

### v2.2.0 (예정)
- [ ] 완전한 다국어 지원
- [ ] 고급 분석 대시보드
- [ ] A/B 테스트 기능
- [ ] 화이트라벨 옵션

### v3.0.0 (장기)
- [ ] AI 학습 데이터 관리
- [ ] 멀티사이트 지원
- [ ] REST API 확장
- [ ] Webhook 통합

## ❓ FAQ

**Q: 무료인가요?**  
A: 플러그인은 무료이지만, Azure AI Foundry 사용료는 별도로 발생합니다.

**Q: 다른 AI 서비스를 지원하나요?**  
A: 현재는 Azure AI Foundry만 지원하며, 향후 OpenAI API 등 추가 예정입니다.

**Q: 상업적 이용이 가능한가요?**  
A: 네, GPL 라이선스 하에 자유롭게 사용 가능합니다.

**Q: 업데이트는 어떻게 받나요?**  
A: WordPress 관리자 페이지에서 자동으로 업데이트 알림을 받습니다.

**Q: 기술 지원은 어떻게 받나요?**  
A: admin@eldensolution.kr로 문의하시거나 사용 가이드를 참고하세요.

---

⭐ 이 플러그인이 유용하다면 GitHub에서 Star를 눌러주세요!

🐛 버그를 발견하셨나요? [Issue를 등록](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)해주세요.

💬 질문이 있으신가요? admin@eldensolution.kr로 연락주세요.

🌐 더 많은 솔루션: [www.eldensolution.kr](https://www.eldensolution.kr)
