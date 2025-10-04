# Azure AI Chatbot - Azure 설정 가이드

## 목차
1. [Chat 모드 설정 (간단)](#chat-모드-설정)
2. [Agent 모드 설정 (고급)](#agent-모드-설정)
3. [연결 테스트](#연결-테스트)
4. [문제 해결](#문제-해결)

---

## Chat 모드 설정

Chat 모드는 **API Key만으로 즉시 사용 가능**합니다.

### 1단계: Azure Portal에서 정보 확인

```bash
# Azure Cloud Shell에서 실행
RESOURCE_GROUP="your-resource-group"
ACCOUNT_NAME="your-account-name"

# API Key 확인
az cognitiveservices account keys list \
  --name $ACCOUNT_NAME \
  --resource-group $RESOURCE_GROUP \
  --query "key1" -o tsv

# 엔드포인트 확인
az cognitiveservices account show \
  --name $ACCOUNT_NAME \
  --resource-group $RESOURCE_GROUP \
  --query "properties.endpoint" -o tsv

# 배포된 모델 확인
az cognitiveservices account deployment list \
  --name $ACCOUNT_NAME \
  --resource-group $RESOURCE_GROUP \
  --query "[].name" -o table
```

### 2단계: WordPress 설정

```
작동 모드: Chat 모드 (OpenAI 호환)
Chat 엔드포인트: https://[ACCOUNT_NAME].openai.azure.com
배포 이름: gpt-4o
API Key: [1단계에서 확인한 Key]
```

---

## Agent 모드 설정

Agent 모드는 **Assistants API v1**을 사용하여 고급 기능을 제공합니다.

### 특징
- ✅ Thread 관리 (대화 컨텍스트 유지)
- ✅ Function Calling (외부 API 호출)
- ✅ 파일 업로드 (RAG)
- ✅ 비동기 Run 처리

### ⚡ 원클릭 자동 설정 스크립트

**Azure Cloud Shell (https://shell.azure.com)에서 실행:**

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
echo "🔍 Azure 구독 정보 확인 중..."
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

### 출력 예시

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

---

## 연결 테스트

### Agent 모드 연결 테스트 스크립트

```bash
# 테스트 변수 설정
TENANT_ID="your-tenant-id"
CLIENT_ID="your-client-id"
CLIENT_SECRET="your-client-secret"
ASSISTANT_ID="your-assistant-id"
AGENT_ENDPOINT="https://your-resource.services.ai.azure.com/api/projects/your-project"

# Token 생성
TOKEN=$(curl -s -X POST "https://login.microsoftonline.com/$TENANT_ID/oauth2/v2.0/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "client_id=$CLIENT_ID" \
  -d "client_secret=$CLIENT_SECRET" \
  -d "scope=https://ai.azure.com/.default" \
  -d "grant_type=client_credentials" | jq -r '.access_token')

echo "Token: ${TOKEN:0:50}..."

# Assistant 조회
echo ""
echo "Assistant 조회..."
curl -s "${AGENT_ENDPOINT}/assistants/${ASSISTANT_ID}?api-version=v1" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Thread 생성
echo ""
echo "Thread 생성..."
THREAD=$(curl -s -X POST "${AGENT_ENDPOINT}/threads?api-version=v1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}' | jq '.')

THREAD_ID=$(echo $THREAD | jq -r '.id')
echo "Thread ID: $THREAD_ID"

# Message 추가
echo ""
echo "Message 추가..."
curl -s -X POST "${AGENT_ENDPOINT}/threads/${THREAD_ID}/messages?api-version=v1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"role":"user","content":"Hello!"}' | jq '.'

# Run 생성
echo ""
echo "Run 생성..."
RUN=$(curl -s -X POST "${AGENT_ENDPOINT}/threads/${THREAD_ID}/runs?api-version=v1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"assistant_id\":\"$ASSISTANT_ID\"}" | jq '.')

RUN_ID=$(echo $RUN | jq -r '.id')
echo "Run ID: $RUN_ID"

# Run 상태 확인
echo ""
echo "Run 상태 확인 (5초 대기)..."
sleep 5
curl -s "${AGENT_ENDPOINT}/threads/${THREAD_ID}/runs/${RUN_ID}?api-version=v1" \
  -H "Authorization: Bearer $TOKEN" | jq '.status'

# Messages 조회
echo ""
echo "Messages 조회..."
curl -s "${AGENT_ENDPOINT}/threads/${THREAD_ID}/messages?api-version=v1" \
  -H "Authorization: Bearer $TOKEN" | jq '.data[0].content[0].text.value'

echo ""
echo "✅ 연결 테스트 완료!"
```

---

## 문제 해결

### HTTP 403 (Forbidden)

**원인**: Service Principal 권한 없음

**해결**:
```bash
# 권한 재할당
az role assignment create \
  --assignee {CLIENT_ID} \
  --role "Cognitive Services User" \
  --scope "/subscriptions/{SUBSCRIPTION_ID}/resourceGroups/{RG}/providers/Microsoft.CognitiveServices/accounts/{ACCOUNT}"
```

### HTTP 400 (API version not supported)

**원인**: 잘못된 API 버전 사용

**해결**:
- API 버전을 `v1`로 변경
- `2024-12-01-preview` 등 날짜 기반 버전은 일부 리전에서 작동하지 않음

### Token 생성 실패

**원인**: Client Secret 만료 또는 잘못됨

**해결**:
```bash
# Client Secret 재생성
az ad app credential reset \
  --id {CLIENT_ID} \
  --append \
  --years 1
```

### Agent not found

**원인**: Agent가 생성되지 않았거나 다른 프로젝트에 있음

**해결**:
1. AI Foundry Portal (https://ai.azure.com) 접속
2. 올바른 프로젝트 선택
3. Agents 메뉴에서 Agent 생성 또는 확인

---

## 참고 자료

- [Azure AI Foundry 문서](https://learn.microsoft.com/azure/ai-foundry/)
- [Assistants API 가이드](https://learn.microsoft.com/azure/ai-services/openai/how-to/assistant)
- [Service Principal 생성](https://learn.microsoft.com/azure/active-directory/develop/howto-create-service-principal-portal)
- [Azure RBAC 역할](https://learn.microsoft.com/azure/role-based-access-control/built-in-roles)
