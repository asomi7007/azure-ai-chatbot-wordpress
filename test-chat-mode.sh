#!/bin/bash

# Azure AI Chatbot - Chat 모드 연결 테스트 스크립트
# Azure Cloud Shell에서 Ctrl+V로 붙여넣기 후 실행

cat > test_chat_mode.sh << 'SCRIPT_EOF'
#!/bin/bash

echo "========================================="
echo "Azure AI Chatbot - Chat 모드 연결 테스트"
echo "========================================="
echo ""

# Azure 리소스 정보
RESOURCE_NAME="your-resource-name"  # ← 여기만 수정하세요!
RESOURCE_GROUP=""  # 자동 검색
DEPLOYMENT_NAME="gpt-4o"

echo "🔍 Azure OpenAI 리소스 검색 중..."
echo ""

# 리소스 그룹 자동 검색
RESOURCE_GROUP=$(az cognitiveservices account list --query "[?name=='$RESOURCE_NAME'].resourceGroup | [0]" -o tsv)

if [ -z "$RESOURCE_GROUP" ]; then
    echo "❌ 리소스를 찾을 수 없습니다: $RESOURCE_NAME"
    echo ""
    echo "사용 가능한 Azure OpenAI 리소스:"
    az cognitiveservices account list --query "[?kind=='OpenAI'].{Name:name, ResourceGroup:resourceGroup, Location:location}" -o table
    exit 1
fi

echo "✅ 리소스 발견: $RESOURCE_NAME (그룹: $RESOURCE_GROUP)"
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

echo "📍 엔드포인트: $ENDPOINT"
echo "🔑 API Key: ${API_KEY:0:8}...${API_KEY: -4}"
echo ""

# 엔드포인트 정리 (끝의 / 제거)
ENDPOINT="${ENDPOINT%/}"

# API 버전 설정
API_VERSION="2024-08-01-preview"

# 테스트할 URL들
echo "📍 테스트 시작..."
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
