#!/bin/bash

# Azure AI Chatbot - Agent 모드 연결 테스트 스크립트
# Azure Cloud Shell에서 Ctrl+V로 붙여넣기 후 실행

cat > test_agent_mode.sh << 'SCRIPT_EOF'
#!/bin/bash

echo "=========================================="
echo "Azure AI Chatbot - Agent 모드 연결 테스트"
echo "=========================================="
echo ""

# 1단계: 구독 선택
echo "📋 1단계: Azure 구독 선택"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 현재 구독 표시
CURRENT_SUB=$(az account show --query "{Name:name, ID:id}" -o tsv 2>/dev/null)
if [ -n "$CURRENT_SUB" ]; then
    echo "현재 구독: $CURRENT_SUB"
    echo ""
    read -p "다른 구독을 선택하시겠습니까? (y/N): " CHANGE_SUB
    
    if [[ "$CHANGE_SUB" =~ ^[Yy]$ ]]; then
        echo ""
        echo "사용 가능한 구독 목록:"
        az account list --query "[].{번호:name, ID:id}" -o table
        echo ""
        read -p "구독 이름 또는 ID 입력: " SUB_INPUT
        az account set --subscription "$SUB_INPUT"
        echo "✅ 구독 변경 완료"
    fi
else
    echo "사용 가능한 구독 목록:"
    az account list --query "[].{번호:name, ID:id}" -o table
    echo ""
    read -p "구독 이름 또는 ID 입력: " SUB_INPUT
    az account set --subscription "$SUB_INPUT"
    echo "✅ 구독 선택 완료"
fi

# 구독 정보 가져오기
SUBSCRIPTION_ID=$(az account show --query "id" -o tsv)
TENANT_ID=$(az account show --query "tenantId" -o tsv)

echo ""
echo "🏢 2단계: AI Foundry 리소스 선택"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# AI Services 리소스 목록 조회
echo "🔍 AI Foundry/AI Services 리소스 검색 중..."
AI_RESOURCES=$(az cognitiveservices account list --query "[?kind=='AIServices']" -o json)

if [ "$AI_RESOURCES" == "[]" ] || [ -z "$AI_RESOURCES" ]; then
    echo "❌ 현재 구독에 AI Foundry 리소스가 없습니다."
    echo ""
    echo "Azure AI Foundry에서 프로젝트를 생성해주세요:"
    echo "https://ai.azure.com"
    exit 1
fi

# 리소스 목록 표시
echo ""
echo "발견된 AI Foundry/AI Services 리소스:"
echo ""
echo "$AI_RESOURCES" | jq -r '.[] | "\(.name) [\(.resourceGroup)] - \(.location)"' | nl
echo ""

# 리소스 개수 확인
RESOURCE_COUNT=$(echo "$AI_RESOURCES" | jq '. | length')

if [ "$RESOURCE_COUNT" -eq 1 ]; then
    # 리소스가 1개면 자동 선택
    RESOURCE_NAME=$(echo "$AI_RESOURCES" | jq -r '.[0].name')
    RESOURCE_GROUP=$(echo "$AI_RESOURCES" | jq -r '.[0].resourceGroup')
    echo "✅ 자동 선택: $RESOURCE_NAME"
else
    # 여러 개면 선택
    read -p "리소스 번호 선택 (1-$RESOURCE_COUNT): " RESOURCE_NUM
    RESOURCE_INDEX=$((RESOURCE_NUM - 1))
    RESOURCE_NAME=$(echo "$AI_RESOURCES" | jq -r ".[$RESOURCE_INDEX].name")
    RESOURCE_GROUP=$(echo "$AI_RESOURCES" | jq -r ".[$RESOURCE_INDEX].resourceGroup")
    echo "✅ 선택: $RESOURCE_NAME"
fi

# 프로젝트 이름 입력
echo ""
read -p "프로젝트 이름 (리소스와 동일하면 엔터): " PROJECT_NAME
PROJECT_NAME=${PROJECT_NAME:-$RESOURCE_NAME}

echo ""
echo "🔐 3단계: Service Principal 확인 및 생성"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Service Principal 이름
SP_NAME="azure-ai-chatbot-wp-${RESOURCE_NAME}"
echo "Service Principal 이름: $SP_NAME"

# 기존 Service Principal 확인
echo "🔍 기존 Service Principal 확인 중..."
EXISTING_SP=$(az ad sp list --display-name "$SP_NAME" --query "[0]" -o json 2>/dev/null)

if [ -z "$EXISTING_SP" ] || [ "$EXISTING_SP" == "null" ]; then
    echo "📝 새 Service Principal 생성 중..."
    
    # Resource ID 생성
    RESOURCE_ID="/subscriptions/$SUBSCRIPTION_ID/resourceGroups/$RESOURCE_GROUP/providers/Microsoft.CognitiveServices/accounts/$RESOURCE_NAME"
    
    # Service Principal 생성
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
        echo "❌ Service Principal 생성 실패"
        echo "$SP_OUTPUT"
        exit 1
    fi
else
    echo "✅ 기존 Service Principal 발견"
    CLIENT_ID=$(echo $EXISTING_SP | jq -r '.appId')
    
    echo ""
    echo "⚠️ 기존 Service Principal의 Client Secret은 조회할 수 없습니다."
    read -p "새 Client Secret을 생성하시겠습니까? (Y/n): " CREATE_SECRET
    
    if [[ ! "$CREATE_SECRET" =~ ^[Nn]$ ]]; then
        echo "🔑 새 Client Secret 생성 중..."
        SECRET_OUTPUT=$(az ad app credential reset \
            --id "$CLIENT_ID" \
            --append \
            --years 1 \
            2>&1)
        
        if echo "$SECRET_OUTPUT" | grep -q "password"; then
            CLIENT_SECRET=$(echo $SECRET_OUTPUT | jq -r '.password')
            echo "✅ 새 Client Secret 생성 완료!"
        else
            echo "❌ Client Secret 생성 실패"
            echo "$SECRET_OUTPUT"
            exit 1
        fi
    else
        echo ""
        read -p "기존 Client Secret을 입력하세요: " CLIENT_SECRET
    fi
    
    # 권한 확인 및 추가
    echo ""
    echo "🔍 권한 확인 중..."
    RESOURCE_ID="/subscriptions/$SUBSCRIPTION_ID/resourceGroups/$RESOURCE_GROUP/providers/Microsoft.CognitiveServices/accounts/$RESOURCE_NAME"
    
    ROLE_ASSIGNED=$(az role assignment list \
        --assignee "$CLIENT_ID" \
        --scope "$RESOURCE_ID" \
        --query "[?roleDefinitionName=='Cognitive Services User'].roleDefinitionName" -o tsv)
    
    if [ -z "$ROLE_ASSIGNED" ]; then
        echo "📝 권한 부여 중..."
        az role assignment create \
            --assignee "$CLIENT_ID" \
            --role "Cognitive Services User" \
            --scope "$RESOURCE_ID" > /dev/null 2>&1
        echo "✅ 권한 부여 완료!"
    else
        echo "✅ 권한 이미 부여됨"
    fi
fi

# 엔드포인트 생성
AGENT_ENDPOINT="https://${RESOURCE_NAME}.services.ai.azure.com/api/projects/${PROJECT_NAME}"

echo ""
echo "🤖 4단계: AI Agent 확인"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# OAuth Token 생성
echo "🔑 인증 토큰 생성 중..."
TOKEN_RESPONSE=$(curl -s -X POST "https://login.microsoftonline.com/$TENANT_ID/oauth2/v2.0/token" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "client_id=$CLIENT_ID" \
    -d "client_secret=$CLIENT_SECRET" \
    -d "scope=https://cognitiveservices.azure.com/.default" \
    -d "grant_type=client_credentials")

ACCESS_TOKEN=$(echo $TOKEN_RESPONSE | jq -r '.access_token')

if [ "$ACCESS_TOKEN" == "null" ] || [ -z "$ACCESS_TOKEN" ]; then
    echo "❌ 인증 실패!"
    echo "응답: $TOKEN_RESPONSE"
    echo ""
    echo "가능한 원인:"
    echo "1. Client ID 또는 Client Secret이 잘못되었습니다"
    echo "2. Service Principal 권한이 부족합니다"
    echo "3. 리소스 접근이 차단되었습니다"
    exit 1
fi

echo "✅ 인증 성공!"

# Agent 목록 조회
echo ""
echo "🔍 Agent 목록 조회 중..."
AGENTS_RESPONSE=$(curl -s -X GET \
    "${AGENT_ENDPOINT}/assistants?api-version=v1" \
    -H "Authorization: Bearer $ACCESS_TOKEN" \
    -H "Content-Type: application/json")

AGENT_COUNT=$(echo $AGENTS_RESPONSE | jq -r '.data | length' 2>/dev/null || echo "0")

if [ "$AGENT_COUNT" == "0" ] || [ "$AGENT_COUNT" == "null" ]; then
    echo ""
    echo "❌ Agent가 존재하지 않습니다!"
    echo ""
    echo "다음 방법 중 하나를 선택하세요:"
    echo ""
    echo "1️⃣ AI Foundry Portal에서 생성 (권장)"
    echo "   https://ai.azure.com → Agents → Create"
    echo ""
    echo "2️⃣ 지금 생성하기"
    echo ""
    read -p "지금 Agent를 생성하시겠습니까? (y/N): " CREATE_AGENT
    
    if [[ "$CREATE_AGENT" =~ ^[Yy]$ ]]; then
        echo ""
        read -p "Agent 이름: " AGENT_NAME
        read -p "Agent 설명 (옵션): " AGENT_DESC
        read -p "사용할 모델 (기본: gpt-4o): " AGENT_MODEL
        AGENT_MODEL=${AGENT_MODEL:-"gpt-4o"}
        
        echo ""
        echo "📝 Agent 생성 중..."
        
        CREATE_RESPONSE=$(curl -s -X POST \
            "${AGENT_ENDPOINT}/assistants?api-version=v1" \
            -H "Authorization: Bearer $ACCESS_TOKEN" \
            -H "Content-Type: application/json" \
            -d "{
                \"model\": \"$AGENT_MODEL\",
                \"name\": \"$AGENT_NAME\",
                \"description\": \"$AGENT_DESC\",
                \"instructions\": \"당신은 친절한 AI 도우미입니다. 사용자의 질문에 정확하고 도움이 되는 답변을 제공하세요.\"
            }")
        
        AGENT_ID=$(echo $CREATE_RESPONSE | jq -r '.id')
        
        if [ "$AGENT_ID" != "null" ] && [ -n "$AGENT_ID" ]; then
            echo "✅ Agent 생성 완료!"
            echo "Agent ID: $AGENT_ID"
        else
            echo "❌ Agent 생성 실패"
            echo "응답: $CREATE_RESPONSE"
            exit 1
        fi
    else
        echo ""
        echo "⚠️ Agent를 생성한 후 다시 실행해주세요."
        echo ""
        echo "임시로 사용할 정보:"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo "• Agent 엔드포인트: $AGENT_ENDPOINT"
        echo "• Client ID: $CLIENT_ID"
        echo "• Client Secret: $CLIENT_SECRET"
        echo "• Tenant ID: $TENANT_ID"
        echo ""
        echo "📌 참고 정보:"
        echo "• 리소스 이름: $RESOURCE_NAME"
        echo "• 리소스 그룹: $RESOURCE_GROUP"
        exit 0
    fi
else
    echo "✅ $AGENT_COUNT 개의 Agent 발견!"
    echo ""
    echo "사용 가능한 Agents:"
    echo "$AGENTS_RESPONSE" | jq -r '.data[] | "  - \(.id): \(.name // "Unnamed")"'
    echo ""
    
    if [ "$AGENT_COUNT" == "1" ]; then
        AGENT_ID=$(echo $AGENTS_RESPONSE | jq -r '.data[0].id')
        AGENT_NAME=$(echo $AGENTS_RESPONSE | jq -r '.data[0].name // "Unnamed"')
        echo "✅ 자동 선택: $AGENT_ID ($AGENT_NAME)"
    else
        echo "Agent 목록:"
        echo "$AGENTS_RESPONSE" | jq -r '.data[] | "\(.id): \(.name // "Unnamed")"' | nl
        echo ""
        read -p "Agent 번호 선택 (1-$AGENT_COUNT): " AGENT_NUM
        AGENT_INDEX=$((AGENT_NUM - 1))
        AGENT_ID=$(echo $AGENTS_RESPONSE | jq -r ".data[$AGENT_INDEX].id")
        AGENT_NAME=$(echo $AGENTS_RESPONSE | jq -r ".data[$AGENT_INDEX].name // 'Unnamed'")
        echo "✅ 선택: $AGENT_ID ($AGENT_NAME)"
    fi
fi

echo ""
echo "🧪 5단계: 연결 테스트"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Thread 생성 테스트
echo "1️⃣ Thread 생성 테스트..."
THREAD_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST \
    "${AGENT_ENDPOINT}/threads?api-version=v1" \
    -H "Authorization: Bearer $ACCESS_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{}')

HTTP_CODE=$(echo "$THREAD_RESPONSE" | grep "HTTP_CODE:" | cut -d':' -f2)
BODY=$(echo "$THREAD_RESPONSE" | sed '/HTTP_CODE:/d')

if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "201" ]; then
    THREAD_ID=$(echo "$BODY" | jq -r '.id')
    echo "✅ Thread 생성 성공! (ID: $THREAD_ID)"
    
    # 메시지 전송 테스트
    echo ""
    echo "2️⃣ 메시지 전송 테스트..."
    MESSAGE_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST \
        "${AGENT_ENDPOINT}/threads/${THREAD_ID}/messages?api-version=v1" \
        -H "Authorization: Bearer $ACCESS_TOKEN" \
        -H "Content-Type: application/json" \
        -d '{"role": "user", "content": "안녕하세요"}')
    
    HTTP_CODE=$(echo "$MESSAGE_RESPONSE" | grep "HTTP_CODE:" | cut -d':' -f2)
    
    if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "201" ]; then
        echo "✅ 메시지 전송 성공!"
        
        # Run 생성 테스트
        echo ""
        echo "3️⃣ Agent 실행 테스트..."
        RUN_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST \
            "${AGENT_ENDPOINT}/threads/${THREAD_ID}/runs?api-version=v1" \
            -H "Authorization: Bearer $ACCESS_TOKEN" \
            -H "Content-Type: application/json" \
            -d "{\"assistant_id\": \"$AGENT_ID\"}")
        
        HTTP_CODE=$(echo "$RUN_RESPONSE" | grep "HTTP_CODE:" | cut -d':' -f2)
        BODY=$(echo "$RUN_RESPONSE" | sed '/HTTP_CODE:/d')
        
        if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "201" ]; then
            RUN_ID=$(echo "$BODY" | jq -r '.id')
            echo "✅ Agent 실행 성공! (Run ID: $RUN_ID)"
            
            echo ""
            echo "=========================================="
            echo "✅ Agent 모드 연결 성공!"
            echo "=========================================="
            echo ""
            echo "WordPress 플러그인 설정:"
            echo "• 작동 모드: Agent 모드 (Assistants API v1)"
            echo "• Agent 엔드포인트: $AGENT_ENDPOINT"
            echo "• Agent ID: $AGENT_ID"
            echo "• Client ID: $CLIENT_ID"
            echo "• Client Secret: $CLIENT_SECRET"
            echo "• Tenant ID: $TENANT_ID"
            echo ""
            echo "📌 참고 정보:"
            echo "• 리소스 이름: $RESOURCE_NAME"
            echo "• 리소스 그룹: $RESOURCE_GROUP"
            echo "• 프로젝트 이름: $PROJECT_NAME"
            echo "• Service Principal: $SP_NAME"
            exit 0
        else
            echo "❌ Agent 실행 실패: HTTP $HTTP_CODE"
            echo "응답: $BODY" | jq '.' 2>/dev/null || echo "$BODY"
        fi
    else
        echo "❌ 메시지 전송 실패: HTTP $HTTP_CODE"
        BODY=$(echo "$MESSAGE_RESPONSE" | sed '/HTTP_CODE:/d')
        echo "응답: $BODY" | jq '.' 2>/dev/null || echo "$BODY"
    fi
else
    echo "❌ Thread 생성 실패: HTTP $HTTP_CODE"
    echo "응답: $BODY" | jq '.' 2>/dev/null || echo "$BODY"
fi

echo ""
echo "=========================================="
echo "❌ 연결 테스트 실패"
echo "=========================================="
echo ""
echo "문제 해결 방법:"
echo "1. Service Principal 권한 확인"
echo "   - Azure Portal > $RESOURCE_NAME > 액세스 제어(IAM)"
echo "   - '$SP_NAME'에 'Cognitive Services User' 권한 확인"
echo ""
echo "2. 네트워크 접근 확인"
echo "   - Azure Portal > $RESOURCE_NAME > 네트워킹"
echo "   - '모든 네트워크' 또는 Cloud Shell IP 허용"
echo ""
echo "3. Agent ID 확인"
echo "   - AI Foundry Portal에서 Agent ID 확인: https://ai.azure.com"
echo ""
echo "현재 설정값:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "• Agent 엔드포인트: $AGENT_ENDPOINT"
echo "• Agent ID: $AGENT_ID"
echo "• Client ID: $CLIENT_ID"
echo "• Client Secret: $CLIENT_SECRET"
echo "• Tenant ID: $TENANT_ID"
echo ""
echo "📌 참고 정보:"
echo "• 리소스 이름: $RESOURCE_NAME"
echo "• 리소스 그룹: $RESOURCE_GROUP"

exit 1
SCRIPT_EOF

chmod +x test_agent_mode.sh
./test_agent_mode.sh
