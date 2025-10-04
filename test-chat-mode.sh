#!/bin/bash

# Azure AI Chatbot - Chat 모드 연결 테스트 스크립트
# Azure Cloud Shell에서 Ctrl+V로 붙여넣기 후 실행

cat > test_chat_mode.sh << 'SCRIPT_EOF'
#!/bin/bash

echo "========================================="
echo "Azure AI Chatbot - Chat 모드 연결 테스트"
echo "========================================="
echo ""

# 1단계: 구독 선택
echo "📋 1단계: Azure 구독 선택"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
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

echo ""
echo "� 2단계: Azure OpenAI 리소스 선택"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# OpenAI 리소스 목록 조회
echo "🔍 Azure OpenAI 리소스 검색 중..."
OPENAI_RESOURCES=$(az cognitiveservices account list --query "[?kind=='OpenAI' || kind=='AIServices']" -o json)

if [ "$OPENAI_RESOURCES" == "[]" ] || [ -z "$OPENAI_RESOURCES" ]; then
    echo "❌ 현재 구독에 Azure OpenAI 리소스가 없습니다."
    echo ""
    echo "다른 구독을 확인하거나 Azure Portal에서 리소스를 생성해주세요."
    echo "https://portal.azure.com"
    exit 1
fi

# 리소스 목록 표시
echo ""
echo "발견된 Azure OpenAI/AI Services 리소스:"
echo ""
echo "$OPENAI_RESOURCES" | jq -r '.[] | "\(.name) [\(.resourceGroup)] - \(.location)"' | nl
echo ""

# 리소스 개수 확인
RESOURCE_COUNT=$(echo "$OPENAI_RESOURCES" | jq '. | length')

if [ "$RESOURCE_COUNT" -eq 1 ]; then
    # 리소스가 1개면 자동 선택
    RESOURCE_NAME=$(echo "$OPENAI_RESOURCES" | jq -r '.[0].name')
    RESOURCE_GROUP=$(echo "$OPENAI_RESOURCES" | jq -r '.[0].resourceGroup')
    echo "✅ 자동 선택: $RESOURCE_NAME"
else
    # 여러 개면 선택
    read -p "리소스 번호 선택 (1-$RESOURCE_COUNT): " RESOURCE_NUM
    RESOURCE_INDEX=$((RESOURCE_NUM - 1))
    RESOURCE_NAME=$(echo "$OPENAI_RESOURCES" | jq -r ".[$RESOURCE_INDEX].name")
    RESOURCE_GROUP=$(echo "$OPENAI_RESOURCES" | jq -r ".[$RESOURCE_INDEX].resourceGroup")
    echo "✅ 선택: $RESOURCE_NAME"
fi

echo ""
echo "📊 3단계: 배포된 모델 확인"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 배포 목록 조회
echo "🔍 배포된 모델 검색 중..."
DEPLOYMENTS=$(az cognitiveservices account deployment list \
    --name "$RESOURCE_NAME" \
    --resource-group "$RESOURCE_GROUP" \
    -o json 2>/dev/null)

if [ -z "$DEPLOYMENTS" ] || [ "$DEPLOYMENTS" == "[]" ]; then
    echo "❌ 배포된 모델이 없습니다."
    echo ""
    echo "Azure Portal에서 모델을 배포해주세요:"
    echo "https://portal.azure.com → $RESOURCE_NAME → 모델 배포"
    exit 1
fi

echo ""
echo "배포된 모델 목록:"
echo ""
echo "$DEPLOYMENTS" | jq -r '.[] | "\(.name) (\(.properties.model.name) \(.properties.model.version))"' | nl
echo ""

DEPLOYMENT_COUNT=$(echo "$DEPLOYMENTS" | jq '. | length')

if [ "$DEPLOYMENT_COUNT" -eq 1 ]; then
    DEPLOYMENT_NAME=$(echo "$DEPLOYMENTS" | jq -r '.[0].name')
    echo "✅ 자동 선택: $DEPLOYMENT_NAME"
else
    read -p "배포 번호 선택 (1-$DEPLOYMENT_COUNT): " DEPLOY_NUM
    DEPLOY_INDEX=$((DEPLOY_NUM - 1))
    DEPLOYMENT_NAME=$(echo "$DEPLOYMENTS" | jq -r ".[$DEPLOY_INDEX].name")
    echo "✅ 선택: $DEPLOYMENT_NAME"
fi

echo ""
echo "🔐 4단계: 인증 정보 가져오기"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 엔드포인트 가져오기
ENDPOINT=$(az cognitiveservices account show \
    --name "$RESOURCE_NAME" \
    --resource-group "$RESOURCE_GROUP" \
    --query "properties.endpoint" -o tsv)

# API Key 가져오기
API_KEY=$(az cognitiveservices account keys list \
    --name "$RESOURCE_NAME" \
    --resource-group "$RESOURCE_GROUP" \
    --query "key1" -o tsv)

echo "✅ 리소스: $RESOURCE_NAME"
echo "✅ 그룹: $RESOURCE_GROUP"
echo "✅ 엔드포인트: $ENDPOINT"
echo "✅ API Key: ${API_KEY:0:8}...${API_KEY: -4}"
echo ""

# 엔드포인트 정리 (끝의 / 제거)
ENDPOINT="${ENDPOINT%/}"

# API 버전 설정
API_VERSION="2024-08-01-preview"

echo ""
echo "🧪 5단계: 연결 테스트"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 방법 1: Azure OpenAI 표준 경로
TEST_URL_1="${ENDPOINT}/openai/deployments/${DEPLOYMENT_NAME}/chat/completions?api-version=${API_VERSION}"
echo "1️⃣ Azure OpenAI 표준 경로 테스트"
echo "URL: ${TEST_URL_1}"
echo ""

RESPONSE_1=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "${TEST_URL_1}" \
  -H "Content-Type: application/json" \
  -H "api-key: ${API_KEY}" \
  -d '{
    "messages": [
      {"role": "system", "content": "You are a helpful assistant."},
      {"role": "user", "content": "Hello"}
    ],
    "max_tokens": 10
  }')

HTTP_CODE_1=$(echo "$RESPONSE_1" | grep "HTTP_CODE:" | cut -d':' -f2)
BODY_1=$(echo "$RESPONSE_1" | sed '/HTTP_CODE:/d')

if [ "$HTTP_CODE_1" == "200" ]; then
    echo "✅ 성공! HTTP $HTTP_CODE_1"
    echo "응답: $BODY_1" | jq -r '.choices[0].message.content' 2>/dev/null || echo "$BODY_1"
    echo ""
    echo "========================================="
    echo "✅ Chat 모드 연결 성공!"
    echo "========================================="
    echo ""
    echo "WordPress 플러그인 설정:"
    echo "• Chat 엔드포인트: ${ENDPOINT}"
    echo "• 배포 이름: ${DEPLOYMENT_NAME}"
    echo "• API Key: (입력한 값 사용)"
    exit 0
else
    echo "❌ 실패: HTTP $HTTP_CODE_1"
    echo "응답: $BODY_1" | jq '.' 2>/dev/null || echo "$BODY_1"
    echo ""
fi

# 방법 2: 직접 경로 (일부 리전)
TEST_URL_2="${ENDPOINT}/chat/completions?api-version=${API_VERSION}"
echo "2️⃣ 직접 경로 테스트"
echo "URL: ${TEST_URL_2}"
echo ""

RESPONSE_2=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "${TEST_URL_2}" \
  -H "Content-Type: application/json" \
  -H "api-key: ${API_KEY}" \
  -H "azureml-model-deployment: ${DEPLOYMENT_NAME}" \
  -d '{
    "messages": [
      {"role": "system", "content": "You are a helpful assistant."},
      {"role": "user", "content": "Hello"}
    ],
    "max_tokens": 10
  }')

HTTP_CODE_2=$(echo "$RESPONSE_2" | grep "HTTP_CODE:" | cut -d':' -f2)
BODY_2=$(echo "$RESPONSE_2" | sed '/HTTP_CODE:/d')

if [ "$HTTP_CODE_2" == "200" ]; then
    echo "✅ 성공! HTTP $HTTP_CODE_2"
    echo "응답: $BODY_2" | jq -r '.choices[0].message.content' 2>/dev/null || echo "$BODY_2"
    echo ""
    echo "========================================="
    echo "✅ Chat 모드 연결 성공!"
    echo "========================================="
    echo ""
    echo "WordPress 플러그인 설정:"
    echo "• Chat 엔드포인트: ${ENDPOINT}"
    echo "• 배포 이름: ${DEPLOYMENT_NAME}"
    echo "• API Key: (입력한 값 사용)"
    echo ""
    echo "⚠️ 주의: 이 경로는 표준이 아닐 수 있습니다."
    exit 0
else
    echo "❌ 실패: HTTP $HTTP_CODE_2"
    echo "응답: $BODY_2" | jq '.' 2>/dev/null || echo "$BODY_2"
    echo ""
fi

# 방법 3: API 버전 확인
echo "3️⃣ 사용 가능한 API 버전 확인 시도"
echo ""

# 다른 API 버전들 테스트
API_VERSIONS=("2024-10-21" "2024-06-01" "2024-02-01" "2023-12-01-preview")

for VERSION in "${API_VERSIONS[@]}"; do
    echo "  - API Version: $VERSION 테스트 중..."
    TEST_URL="${ENDPOINT}/openai/deployments/${DEPLOYMENT_NAME}/chat/completions?api-version=${VERSION}"
    
    RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "${TEST_URL}" \
      -H "Content-Type: application/json" \
      -H "api-key: ${API_KEY}" \
      -d '{
        "messages": [
          {"role": "system", "content": "You are a helpful assistant."},
          {"role": "user", "content": "Hello"}
        ],
        "max_tokens": 10
      }')
    
    HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE:" | cut -d':' -f2)
    
    if [ "$HTTP_CODE" == "200" ]; then
        BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE:/d')
        echo "  ✅ 성공! HTTP $HTTP_CODE"
        echo "  응답: $(echo "$BODY" | jq -r '.choices[0].message.content' 2>/dev/null || echo "$BODY")"
        echo ""
        echo "========================================="
        echo "✅ Chat 모드 연결 성공!"
        echo "========================================="
        echo ""
        echo "WordPress 플러그인 설정:"
        echo "• Chat 엔드포인트: ${ENDPOINT}"
        echo "• 배포 이름: ${DEPLOYMENT_NAME}"
        echo "• API Key: (입력한 값 사용)"
        echo "• 권장 API 버전: ${VERSION}"
        exit 0
    else
        echo "  ❌ 실패: HTTP $HTTP_CODE"
    fi
done

echo ""
echo "========================================="
echo "❌ 모든 테스트 실패"
echo "========================================="
echo ""
echo "문제 해결 방법:"
echo "1. 배포 이름 확인"
echo "   실제 배포된 모델 확인:"
az cognitiveservices account deployment list \
    --name "$RESOURCE_NAME" \
    --resource-group "$RESOURCE_GROUP" \
    --query "[].{Name:name, Model:properties.model.name, Version:properties.model.version}" -o table 2>/dev/null
echo ""
echo "2. 네트워크 접근 확인"
echo "   - Azure Portal > $RESOURCE_NAME > 네트워킹"
echo "   - '모든 네트워크' 또는 Cloud Shell IP 허용"
echo ""
echo "3. 역할 권한 확인"
echo "   - 'Cognitive Services User' 또는 'Cognitive Services OpenAI User' 필요"
echo ""

exit 1
SCRIPT_EOF

chmod +x test_chat_mode.sh
./test_chat_mode.sh
