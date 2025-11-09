# Azure AI Chatbot 사용 가이드

## 📖 목차

1. [소개](#-소개)
2. [설치하기](#-설치하기)
3. [초기 설정](#-초기-설정)
4. [OAuth 자동 설정](#-oauth-자동-설정)
5. [수동 설정](#-수동-설정)
6. [채팅 위젯 사용법](#-채팅-위젯-사용법)
7. [고급 기능](#-고급-기능)
8. [문제 해결](#-문제-해결)
9. [FAQ](#-자주-묻는-질문)

---

## 🌟 소개

**Azure AI Chatbot**은 WordPress 사이트에 강력한 AI 챗봇을 쉽게 추가할 수 있는 플러그인입니다.

### 주요 특징

✅ **듀얼 모드 지원**
- **Agent 모드**: Azure AI Foundry의 고급 Agent 기능
- **Chat 모드**: 다양한 AI 모델 (Azure OpenAI, OpenAI, Gemini, Claude 등)

✅ **원클릭 자동 설정**
- OAuth 2.0 기반 완전 자동 설정
- Azure 리소스 자동 생성 및 구성
- Chat + Agent 정보 동시 수집

✅ **강력한 보안**
- AES-256 암호화로 API 키 보호
- WordPress Nonce CSRF 방지
- Entra ID 인증 지원

✅ **완벽한 커스터마이징**
- 위치, 색상, 메시지 맞춤 설정
- 반응형 디자인
- Function Calling 지원

---

## 🚀 설치하기

### 방법 1: WordPress 관리자 페이지

1. WordPress 관리자 로그인
2. **플러그인** → **새로 추가** 메뉴로 이동
3. **플러그인 업로드** 버튼 클릭
4. `azure-ai-chatbot-wordpress.zip` 파일 선택
5. **지금 설치** 클릭
6. 설치 완료 후 **활성화** 클릭

### 방법 2: FTP 수동 설치

```bash
# 1. ZIP 파일 압축 해제
unzip azure-ai-chatbot-wordpress.zip

# 2. FTP로 서버 접속
# 3. 다음 경로에 업로드
/wp-content/plugins/azure-ai-chatbot-wordpress/

# 4. 권한 설정
chmod -R 755 azure-ai-chatbot-wordpress

# 5. WordPress 관리자에서 플러그인 활성화
```

---

## ⚙️ 초기 설정

플러그인 활성화 후 자동으로 WordPress 보안 키를 확인하고 필요시 생성합니다.

### 보안 키 자동 생성

플러그인이 자동으로 다음을 수행합니다:

1. `wp-config.php` 파일의 보안 키 확인
2. 보안 키가 없거나 기본값이면 자동 생성
3. 기존 파일 백업: `wp-config.php.backup-YYYYMMDD-HHMMSS`

**⚠️ 참고**: 수동으로 보안 키를 설정하려면 [WordPress Secret Key Generator](https://api.wordpress.org/secret-key/1.1/salt/)를 사용하세요.

---

## 🎯 OAuth 자동 설정

가장 쉽고 빠른 설정 방법입니다!

### 🔐 OAuth 인증이 필요한 이유

**OAuth 인증의 핵심 역할**:

1. **Azure 리소스 자동 탐지** 🔍
   - 사용자의 Azure 구독에서 AI Foundry 프로젝트, OpenAI 리소스 자동 검색
   - 기존에 생성된 Agent 목록 자동 조회
   - 리소스 그룹, 구독 정보 자동 수집

2. **API Key 안전 조회** 🔑
   - Azure Management API를 통한 안전한 API Key 조회
   - 조회된 API Key는 AES-256 암호화로 즉시 암호화
   - 평문 API Key는 메모리에서 즉시 삭제

3. **Agent 인증 정보 자동 구성** 🤖
   - Agent 모드 사용 시 필요한 Client ID 자동 설정
   - Tenant ID 자동 감지 및 저장
   - Client Secret 암호화 저장

4. **신규 리소스 생성** 📦
   - Azure 리소스가 없는 경우 직접 생성 가능
   - Resource Group, AI Resource, Model Deployment 자동 생성
   - 권한 설정 자동 구성

**자동 설정 프로세스**:
```
1. OAuth 인증 (Microsoft 로그인)
   ↓
2. Azure 리소스 접근 권한 부여
   ↓
3. 구독 → 리소스 그룹 → AI 리소스 검색
   ↓
4. 설정값 자동 추출 및 암호화
   ↓
5. WordPress DB에 안전하게 저장
   ↓
6. ✅ 즉시 사용 가능
```

**보안 고려사항** 🔒:
- ✅ OAuth 액세스 토큰은 **일시적**이며 설정 후 자동 삭제
- ✅ 토큰 만료 시간: 1시간 (설정은 5분 내 완료)
- ✅ 저장되는 것: **암호화된 API Key**와 **Agent 인증 정보**만
- ✅ Azure 계정 비밀번호는 **절대 저장되지 않음**
- ✅ 읽기 전용 권한만 사용 (리소스 수정 불가)

### 설정 단계

1. **AI Chatbot** 메뉴 클릭
2. **OAuth 자동 설정** 탭 선택
3. **모드 선택**:
   - 🤖 **Agent 모드**: Azure AI Foundry Agent 사용
   - 💬 **Chat 모드**: OpenAI 호환 모델 사용
4. **Azure 자동 설정 시작** 버튼 클릭
5. Microsoft 계정으로 로그인
6. 권한 승인
7. ✅ 자동 설정 완료!

### 자동으로 수행되는 작업

| 단계 | 작업 내용 |
|------|-----------|
| 1 | Azure 구독 확인 |
| 2 | 리소스 그룹 생성/선택 |
| 3 | AI 리소스 생성/구성 |
| 4 | Chat 배포 생성 (Chat 모드) |
| 5 | Agent 생성/선택 (Agent 모드) |
| 6 | API Key 자동 획득 및 암호화 |
| 7 | 엔드포인트 자동 설정 |
| 8 | WordPress 설정 자동 저장 |

### 자동 수집되는 정보

#### Chat 모드
- ✅ Chat Endpoint (`.openai.azure.com` 형식)
- ✅ Deployment Name
- ✅ API Key (AES-256 암호화)

#### Agent 모드
- ✅ Agent Endpoint
- ✅ Agent ID
- ✅ Client ID
- ✅ Tenant ID
- ✅ Client Secret (AES-256 암호화)

---

## 📝 수동 설정

OAuth 자동 설정을 사용하지 않는 경우 수동으로 설정할 수 있습니다.

### Agent 모드 설정

#### 1단계: Azure App Registration 생성

1. [Azure Portal](https://portal.azure.com) 접속
2. **Azure Active Directory** → **앱 등록** 메뉴
3. **새 등록** 클릭
4. 다음 정보 입력:
   - **이름**: WordPress Chatbot
   - **지원되는 계정 유형**: 단일 테넌트
5. **등록** 클릭

#### 2단계: Client Secret 생성

1. 생성된 앱 선택
2. **인증서 및 비밀** 메뉴 클릭
3. **새 클라이언트 암호** 클릭
4. 설명 입력 (예: WordPress Plugin)
5. 만료 기간 선택 (권장: 24개월)
6. **추가** 클릭
7. ⚠️ **값** 복사 (한 번만 표시됩니다!)

#### 3단계: Azure AI Foundry에서 Agent 생성

1. [Azure AI Foundry](https://ai.azure.com) 접속
2. 프로젝트 선택 또는 생성
3. **Agents** 메뉴에서 **Create new agent** 클릭
4. Agent 설정:
   - Name: 원하는 Agent 이름
   - Description: Agent 설명
   - Model: GPT-4 등 선택
5. Agent 생성 완료 후 다음 정보 복사:
   - **Endpoint**: Agent 엔드포인트 URL
   - **Agent ID**: Agent 고유 식별자

#### 4단계: WordPress에 설정 입력

1. WordPress 관리자 → **AI Chatbot** → **설정**
2. **Agent 모드** 라디오 버튼 선택
3. 다음 정보 입력:

| 필드 | 값 | 위치 |
|------|-----|------|
| Client ID | Application (client) ID | Azure Portal → App Registration → 개요 |
| Client Secret | 2단계에서 복사한 값 | 인증서 및 비밀 |
| Tenant ID | Directory (tenant) ID | Azure Portal → App Registration → 개요 |
| Agent Endpoint | Agent 엔드포인트 URL | Azure AI Foundry → Agent 상세 |
| Agent ID | Agent ID | Azure AI Foundry → Agent 상세 |

4. **설정 저장** 클릭
5. **연결 테스트** 버튼으로 확인

### Chat 모드 설정

#### Azure OpenAI

1. [Azure Portal](https://portal.azure.com)에서 OpenAI 리소스 생성
2. **Keys and Endpoint** 메뉴에서 정보 확인
3. **Deployments** 메뉴에서 모델 배포 생성
4. WordPress 설정에 입력:

```
Provider: Azure OpenAI
Endpoint: https://your-resource.openai.azure.com
Deployment: gpt-4o
API Key: [복사한 API Key]
```

#### OpenAI

1. [OpenAI Platform](https://platform.openai.com) 접속
2. API Keys 메뉴에서 새 키 생성
3. WordPress 설정에 입력:

```
Provider: OpenAI
Endpoint: https://api.openai.com
Model: gpt-4
API Key: [복사한 API Key]
```

#### Google Gemini

1. [Google AI Studio](https://aistudio.google.com) 접속
2. API Key 생성
3. WordPress 설정에 입력:

```
Provider: Other (OpenAI-compatible)
Endpoint: https://generativelanguage.googleapis.com
Model: gemini-pro
API Key: [복사한 API Key]
```

---

## 💬 채팅 위젯 사용법

### 위젯 활성화

1. **AI Chatbot** → **설정** 페이지 이동
2. **채팅 위젯 활성화** 체크박스 선택
3. 위젯 설정:
   - **위치**: 하단 우측, 하단 좌측, 상단 우측, 상단 좌측
   - **Primary Color**: 주 색상 선택
   - **Secondary Color**: 보조 색상 선택
   - **환영 메시지**: 첫 인사말 입력
   - **제목**: 채팅 창 제목
4. **설정 저장** 클릭

### 접근 권한 설정

- **모든 사용자 허용**: 
  - ✅ 체크 시 → 비로그인 사용자도 사용 가능
  - ❌ 체크 해제 시 → 로그인 사용자만 사용 가능

### 사용자 인터페이스

1. **채팅 아이콘** 클릭
2. 메시지 입력창에 질문 입력
3. **Enter** 키 또는 **전송** 버튼 클릭
4. AI 응답 확인

### 대화 기능

- ✅ 실시간 응답
- ✅ 대화 컨텍스트 유지 (Agent 모드)
- ✅ 마크다운 형식 지원
- ✅ 코드 블록 하이라이팅
- ✅ 링크 클릭 가능
- ✅ 모바일 최적화

---

## 🔧 고급 기능

### Function Calling

AI가 WordPress 함수를 호출하도록 설정할 수 있습니다.

#### 예제 1: 날씨 정보 제공

```php
// functions.php 또는 커스텀 플러그인에 추가
add_filter('azure_chatbot_function_call', 'my_weather_function', 10, 3);

function my_weather_function($result, $function_name, $arguments) {
    if ($function_name === 'get_weather') {
        $location = $arguments['location'];
        
        // 실제로는 날씨 API 호출
        return [
            'location' => $location,
            'temperature' => 25,
            'condition' => 'sunny',
            'humidity' => 60
        ];
    }
    return $result;
}
```

#### 예제 2: 제품 정보 조회

```php
add_filter('azure_chatbot_function_call', 'get_product_info', 10, 3);

function get_product_info($result, $function_name, $arguments) {
    if ($function_name === 'get_product_info') {
        $product_id = $arguments['product_id'];
        
        // WooCommerce 제품 조회
        $product = wc_get_product($product_id);
        
        if ($product) {
            return [
                'name' => $product->get_name(),
                'price' => $product->get_price(),
                'stock' => $product->get_stock_quantity(),
                'description' => $product->get_short_description()
            ];
        }
    }
    return $result;
}
```

### REST API 직접 호출

JavaScript에서 플러그인의 REST API를 직접 호출할 수 있습니다.

```javascript
// 채팅 메시지 전송
async function sendChatMessage(message) {
    const response = await fetch('/wp-json/azure-chatbot/v1/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpApiSettings.nonce
        },
        body: JSON.stringify({
            message: message,
            thread_id: sessionStorage.getItem('thread_id')
        })
    });
    
    const data = await response.json();
    
    // Thread ID 저장 (대화 컨텍스트 유지)
    if (data.thread_id) {
        sessionStorage.setItem('thread_id', data.thread_id);
    }
    
    return data.reply;
}

// 사용 예시
const reply = await sendChatMessage('안녕하세요!');
console.log(reply);
```

### 커스텀 후크

플러그인이 제공하는 다양한 후크를 활용할 수 있습니다.

```php
// 메시지 전송 전 처리
add_filter('azure_chatbot_before_send', function($message) {
    // 욕설 필터링
    $bad_words = ['욕설1', '욕설2'];
    foreach ($bad_words as $word) {
        $message = str_replace($word, str_repeat('*', mb_strlen($word)), $message);
    }
    return $message;
});

// 응답 수신 후 처리
add_filter('azure_chatbot_after_receive', function($response) {
    // 특정 키워드 강조
    $response = str_replace('중요', '<strong>중요</strong>', $response);
    return $response;
});

// 위젯 HTML 커스터마이징
add_filter('azure_chatbot_widget_html', function($html) {
    // 커스텀 CSS 클래스 추가
    $html = str_replace('class="chatbot-widget"', 'class="chatbot-widget my-custom-class"', $html);
    return $html;
});
```

---

## 🛠️ 문제 해결

### 자주 발생하는 문제

#### ❌ 채팅 위젯이 나타나지 않음

**증상**: 사이트에 채팅 아이콘이 표시되지 않음

**원인**:
1. 플러그인이 비활성화됨
2. "채팅 위젯 활성화" 옵션이 꺼져 있음
3. JavaScript 충돌
4. 테마 CSS 충돌

**해결방법**:

```bash
# 1. 플러그인 활성화 확인
WordPress 관리자 → 플러그인 → Azure AI Chatbot 활성화 확인

# 2. 설정 확인
AI Chatbot → 설정 → "채팅 위젯 활성화" 체크

# 3. 브라우저 콘솔 확인
F12 → Console 탭 → 에러 메시지 확인

# 4. 테마 충돌 확인
기본 테마(Twenty Twenty-Four)로 변경하여 테스트
```

#### ❌ API Key 저장 실패

**증상**: "API Key를 저장할 수 없습니다" 에러

**원인**:
1. WordPress 보안 키 누락
2. OpenSSL PHP 확장 비활성화
3. 파일 쓰기 권한 부족

**해결방법**:

```php
// 1. wp-config.php에 보안 키 추가
// https://api.wordpress.org/secret-key/1.1/salt/ 에서 생성

define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');
```

```bash
# 2. OpenSSL 확장 확인
php -m | grep openssl

# 없으면 설치
sudo apt-get install php-openssl  # Ubuntu/Debian
sudo yum install php-openssl      # CentOS/RHEL

# 3. 파일 권한 확인
chmod 644 wp-config.php
```

#### ❌ 연결 테스트 실패

**증상**: "연결에 실패했습니다" 메시지

**HTTP 상태 코드별 해결법**:

| 코드 | 원인 | 해결방법 |
|------|------|----------|
| 401 | 인증 실패 | API Key, Client Secret 재확인 |
| 404 | 리소스 없음 | Endpoint URL, Agent ID, Deployment Name 확인 |
| 429 | 요청 한도 초과 | 잠시 후 재시도, Azure 요금제 확인 |
| 500 | 서버 오류 | Azure 서비스 상태 확인 |
| 502/503 | 게이트웨이 오류 | Azure 리소스 재시작 |

**상세 디버깅**:

```php
// wp-config.php에 디버그 모드 활성화
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

```bash
# 로그 파일 확인
tail -f wp-content/debug.log
```

#### ❌ 대화가 끊김

**증상**: Agent 모드에서 이전 대화 내용을 기억하지 못함

**원인**:
- thread_id 저장 실패
- 브라우저 localStorage/sessionStorage 비활성화

**해결방법**:

```javascript
// 브라우저 콘솔에서 확인
console.log(sessionStorage.getItem('thread_id'));

// thread_id가 null이면 브라우저 설정 확인
// Chrome: 설정 → 개인정보 및 보안 → 쿠키 및 기타 사이트 데이터
```

### 성능 최적화

#### 응답 속도 개선

```php
// functions.php에 추가
add_filter('azure_chatbot_timeout', function() {
    return 30; // 타임아웃 30초로 설정
});

add_filter('azure_chatbot_cache_enabled', '__return_true');
add_filter('azure_chatbot_cache_duration', function() {
    return 3600; // 1시간 캐싱
});
```

#### 로딩 최적화

```php
// 특정 페이지에서만 위젯 로드
add_filter('azure_chatbot_load_widget', function($load) {
    // 홈페이지와 제품 페이지에서만 로드
    if (is_home() || is_product()) {
        return true;
    }
    return false;
});
```

---

## ❓ 자주 묻는 질문

### 일반

**Q: 플러그인은 무료인가요?**
A: 네, 플러그인은 GPL-2.0+ 라이선스로 무료입니다. 다만 Azure AI 서비스 사용에는 별도 요금이 발생할 수 있습니다.

**Q: 어떤 WordPress 버전을 지원하나요?**
A: WordPress 6.0 이상을 지원합니다.

**Q: PHP 버전 요구사항은?**
A: PHP 7.4 이상이 필요합니다.

### 기능

**Q: 여러 언어를 지원하나요?**
A: 네, AI 모델이 지원하는 모든 언어로 대화할 수 있습니다.

**Q: 대화 내역이 저장되나요?**
A: Agent 모드에서는 thread_id를 통해 대화 컨텍스트가 유지됩니다. 별도의 데이터베이스 저장은 하지 않습니다.

**Q: 모바일에서도 작동하나요?**
A: 네, 완전한 반응형 디자인으로 모바일에 최적화되어 있습니다.

**Q: 여러 채팅봇을 동시에 운영할 수 있나요?**
A: 현재 버전에서는 하나의 챗봇만 지원합니다.

### 설정

**Q: OAuth 자동 설정과 수동 설정의 차이는?**
A: OAuth 자동 설정은 Azure 리소스를 자동으로 생성하고 모든 설정을 자동으로 채웁니다. 수동 설정은 이미 생성된 리소스의 정보를 직접 입력해야 합니다.

**Q: 기존 Azure 리소스를 사용할 수 있나요?**
A: 네, OAuth 자동 설정에서 기존 리소스를 선택하거나 수동 설정으로 입력할 수 있습니다.

**Q: API Key는 어떻게 보호되나요?**
A: AES-256 암호화로 데이터베이스에 저장됩니다.

### 문제 해결

**Q: "연결 테스트 실패" 메시지가 나옵니다**
A: Endpoint URL, API Key, Deployment Name을 확인하세요. [문제 해결](#-문제-해결) 섹션을 참조하세요.

**Q: 채팅 위젯이 보이지 않습니다**
A: 플러그인 활성화, "채팅 위젯 활성화" 옵션, JavaScript 콘솔 에러를 확인하세요.

**Q: 응답이 너무 느립니다**
A: Azure 리전 선택, 모델 크기, 네트워크 상태를 확인하세요. 한국 리전(Korea Central) 사용을 권장합니다.

---

## 📞 지원

### 도움이 필요하신가요?

- 📧 **이메일**: support@eldensolution.kr
- 🌐 **웹사이트**: [https://www.eldensolution.kr](https://www.eldensolution.kr)
- 🐛 **버그 리포트**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)
- 💬 **토론**: [GitHub Discussions](https://github.com/asomi7007/azure-ai-chatbot-wordpress/discussions)

### 커뮤니티

- 📖 **Documentation**: [GitHub Wiki](https://github.com/asomi7007/azure-ai-chatbot-wordpress/wiki)
- 🎥 **Video Tutorials**: Coming soon
- 📝 **Blog**: [Elden Solution Blog](https://www.eldensolution.kr/blog)

---

## 📄 라이선스

GPL-2.0+ License - 자세한 내용은 [LICENSE](../../LICENSE) 파일을 참조하세요.

---

## 🙏 감사의 말

이 플러그인은 다음 기술을 사용합니다:

- [Azure AI Foundry](https://ai.azure.com)
- [Azure OpenAI Service](https://azure.microsoft.com/products/ai-services/openai-service)
- [WordPress REST API](https://developer.wordpress.org/rest-api/)
- [OpenSSL](https://www.openssl.org/)

---

<div align="center">

Made with ❤️ by [Elden Solution](https://www.eldensolution.kr)

© 2024 Elden Solution. All rights reserved.

</div>
