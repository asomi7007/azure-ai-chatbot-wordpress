# Azure AI Chatbot 사용 가이드

## 📖 소개 {#introduction}

**Azure AI Chatbot**은 Azure AI Foundry의 강력한 에이전트를 WordPress 웹사이트에 쉽게 통합할 수 있는 플러그인입니다.

### 주요 특징

- ✅ **간편한 설정**: wp-config.php 수정 없이 관리자 페이지에서 모든 설정 가능
- 🎨 **커스터마이징**: 색상, 위치, 메시지 등 자유롭게 변경
- 🤖 **AI 기능**: Function Calling, RAG, 파일 업로드 등 모든 Azure AI 기능 지원
- 📱 **반응형 디자인**: 데스크톱과 모바일 모두 완벽 지원
- 🔒 **보안**: API 키는 서버에만 저장되어 안전

### 제작자 정보

- **이름**: 허석 (Heo Seok)
- **이메일**: admin@edueldensolution.kr
- **웹사이트**: https://edueldensolution.kr

---

## 🚀 설치 방법 {#installation}

### 1단계: 플러그인 업로드

1. FTP 또는 파일 관리자로 WordPress 설치 디렉토리에 접속
2. `/wp-content/plugins/` 폴더로 이동
3. `azure-ai-chatbot` 폴더를 업로드
4. 다음 파일 구조를 확인:

```
/wp-content/plugins/azure-ai-chatbot/
├── azure-ai-chatbot.php
├── assets/
│   ├── chatbot.css
│   ├── chatbot.js
│   ├── admin.css
│   └── admin.js
├── templates/
│   ├── settings-page.php
│   └── guide-page.php
├── docs/
│   └── USER_GUIDE.md
└── README.md
```

### 2단계: 플러그인 활성화

1. WordPress 관리자 페이지 로그인
2. **플러그인** 메뉴 클릭
3. **Azure AI Chatbot** 찾기
4. **활성화** 버튼 클릭

### 3단계: Azure 정보 확인

Azure Portal에서 다음 정보를 확인하세요:

#### API Key 확인
1. [Azure Portal](https://portal.azure.com) 접속
2. AI Foundry 리소스 검색 (예: `eduelden04-2296-resource`)
3. 좌측 메뉴에서 **"키 및 엔드포인트"** 클릭
4. **KEY 1** 또는 **KEY 2** 복사

#### 엔드포인트 확인
- 형식: `https://[리소스명].services.ai.azure.com/api/projects/[프로젝트명]`
- 예시: `https://eduelden04-2296-resource.services.ai.azure.com/api/projects/eduelden04-2296`

#### 에이전트 ID 확인
1. [AI Foundry](https://ai.azure.com) 접속
2. 프로젝트 선택
3. **Agents** 섹션에서 에이전트 선택
4. 에이전트 ID 복사 (예: `asst_MFgZgVv0Yo2tHojj8mPrdqL7`)

---

## ⚙️ 설정 {#configuration}

### 기본 설정

WordPress 관리자 페이지에서 **AI Chatbot** → **설정** 메뉴로 이동합니다.

#### 1. Azure AI Foundry 연결 설정

| 항목 | 설명 | 예시 |
|------|------|------|
| **API Key** | Azure에서 발급받은 API 키 | `a1b2c3d4e5f6...` |
| **프로젝트 엔드포인트** | AI Foundry 프로젝트 URL | `https://eduelden04-2296-resource.services.ai.azure.com/api/projects/eduelden04-2296` |
| **에이전트 ID** | 사용할 에이전트의 ID | `asst_MFgZgVv0Yo2tHojj8mPrdqL7` |

#### 2. 위젯 설정

| 항목 | 설명 | 기본값 |
|------|------|--------|
| **위젯 활성화** | 채팅 위젯 표시 여부 | 비활성화 |
| **위젯 위치** | 채팅 버튼 위치 | 오른쪽 하단 |
| **채팅 제목** | 채팅창 상단 제목 | "AI 도우미" |
| **환영 메시지** | 첫 메시지 | "안녕하세요! 무엇을 도와드릴까요?" |

#### 3. 디자인 설정

| 항목 | 설명 | 기본값 |
|------|------|--------|
| **주 색상** | 버튼 및 사용자 메시지 색상 | `#667eea` |
| **보조 색상** | 그라데이션 두 번째 색상 | `#764ba2` |

### 연결 테스트

설정 페이지 하단의 **"연결 테스트"** 버튼을 클릭하여 Azure 연결을 확인할 수 있습니다.

---

## 🎯 주요 기능 {#features}

### 1. 기본 대화 기능

- 사용자가 메시지를 입력하면 Azure AI 에이전트가 응답
- 대화 내용은 Thread로 관리되어 문맥 유지
- 로컬 스토리지에 Thread ID 저장으로 세션 지속

### 2. Function Calling (함수 호출)

에이전트가 특정 작업을 수행해야 할 때 WordPress 함수를 호출할 수 있습니다.

#### 기본 제공 함수

- `get_current_time`: 현재 서버 시간 반환

#### 커스텀 함수 추가 방법

`functions.php` 또는 별도 플러그인에 다음 코드를 추가:

```php
add_filter('azure_chatbot_function_call', function($result, $function_name, $arguments) {
    if ($function_name === 'get_weather') {
        // 날씨 API 호출
        $weather = call_weather_api($arguments['location']);
        return [
            'temperature' => $weather['temp'],
            'condition' => $weather['condition']
        ];
    }
    
    return $result; // 다른 함수는 기본 처리
}, 10, 3);
```

### 3. RAG (검색 증강 생성)

Azure AI Foundry에서 파일 검색 도구를 설정하면, 에이전트가 업로드된 문서를 자동으로 검색하여 답변합니다.

#### 파일 업로드 방법

1. [AI Foundry](https://ai.azure.com) 접속
2. 프로젝트 → **Agents** → 에이전트 선택
3. **Files** 탭에서 문서 업로드 (PDF, DOCX, TXT 등)
4. **Tools** 탭에서 **File search** 활성화

### 4. 스트리밍 응답

현재 폴링 방식을 사용하지만, 향후 업데이트에서 실시간 스트리밍을 지원할 예정입니다.

---

## 🎨 커스터마이징 {#customization}

### CSS 커스터마이징

테마의 `style.css`에 다음 코드를 추가하여 스타일 변경:

```css
/* 채팅 버튼 크기 변경 */
.chatbot-toggle {
    width: 70px !important;
    height: 70px !important;
}

/* 채팅창 크기 변경 */
.chatbot-window {
    width: 400px !important;
    height: 650px !important;
}

/* 봇 메시지 스타일 */
.bot-message .message-text {
    background: #f0f0f0 !important;
    color: #333 !important;
}
```

### JavaScript 커스터마이징

`assets/chatbot.js` 파일을 복사하여 수정할 수 있습니다.

**예: 메시지 전송 시 사운드 재생**

```javascript
sendMessage: function() {
    // 기존 코드...
    
    // 사운드 재생
    const audio = new Audio('/wp-content/uploads/send-sound.mp3');
    audio.play();
    
    // 나머지 코드...
}
```

### PHP 훅 활용

플러그인은 다양한 필터와 액션을 제공합니다:

```php
// 메시지 전송 전 수정
add_filter('azure_chatbot_before_send', function($message, $thread_id) {
    // 메시지에 사용자 정보 추가
    $user = wp_get_current_user();
    return "사용자: {$user->display_name}\n\n{$message}";
}, 10, 2);

// 응답 받은 후 처리
add_action('azure_chatbot_after_response', function($response, $thread_id) {
    // 로그 기록
    error_log("AI 응답: " . $response);
}, 10, 2);
```

---

## 🔧 문제 해결 {#troubleshooting}

### 채팅 버튼이 보이지 않음

**원인**: 위젯이 비활성화되었거나 설정이 완료되지 않음

**해결 방법**:
1. **AI Chatbot** → **설정** 메뉴 확인
2. **위젯 활성화** 체크박스 확인
3. API Key, 엔드포인트, 에이전트 ID 모두 입력 확인
4. **설정 저장** 클릭
5. 브라우저 캐시 삭제 후 새로고침

### "API Error: HTTP 401" 오류

**원인**: API Key가 잘못되었거나 만료됨

**해결 방법**:
1. Azure Portal에서 새 API Key 발급
2. 플러그인 설정 페이지에서 API Key 업데이트
3. **연결 테스트** 버튼으로 확인

### "API Error: HTTP 404" 오류

**원인**: 엔드포인트 또는 에이전트 ID가 잘못됨

**해결 방법**:
1. Azure AI Foundry에서 정확한 엔드포인트 확인
2. 에이전트 ID 재확인 (`asst_`로 시작)
3. URL 끝에 슬래시(`/`) 제거

### 응답이 매우 느림

**원인**: Azure 서비스 지연 또는 복잡한 처리

**해결 방법**:
1. Azure 서비스 상태 확인: [Azure Status](https://status.azure.com)
2. 에이전트 프롬프트를 간결하게 수정
3. 네트워크 연결 확인

### Thread가 계속 초기화됨

**원인**: 브라우저 로컬 스토리지 비활성화 또는 쿠키 차단

**해결 방법**:
1. 브라우저 설정에서 쿠키 및 로컬 스토리지 허용
2. 시크릿 모드가 아닌 일반 모드 사용
3. 브라우저 확장 프로그램 비활성화 테스트

### 디버그 로그 확인

`wp-config.php`에 다음 코드 추가:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

로그 파일 위치: `/wp-content/debug.log`

---

## ❓ 자주 묻는 질문 {#faq}

### Q1. API 사용료는 얼마나 되나요?

Azure AI Foundry는 사용량 기반 과금입니다:
- GPT-4o: 입력 $2.50/1M 토큰, 출력 $10.00/1M 토큰
- 월 1,000건 대화 (평균 500토큰) ≈ $3~5

자세한 요금은 [Azure 가격 계산기](https://azure.microsoft.com/pricing/calculator/)에서 확인하세요.

### Q2. 다국어를 지원하나요?

네, Azure AI는 100개 이상의 언어를 지원합니다. 에이전트 프롬프트에서 원하는 언어를 지정하면 됩니다.

### Q3. 여러 에이전트를 사용할 수 있나요?

현재 버전은 하나의 에이전트만 지원합니다. 향후 업데이트에서 다중 에이전트 전환 기능을 추가할 예정입니다.

### Q4. 대화 내역을 데이터베이스에 저장할 수 있나요?

기본적으로는 Azure에만 저장되지만, WordPress 액션 훅을 사용하여 데이터베이스에 저장할 수 있습니다:

```php
add_action('azure_chatbot_after_response', function($response, $thread_id) {
    global $wpdb;
    $wpdb->insert('wp_chatbot_logs', [
        'thread_id' => $thread_id,
        'message' => $response,
        'created_at' => current_time('mysql')
    ]);
}, 10, 2);
```

### Q5. 특정 페이지에만 위젯을 표시할 수 있나요?

`functions.php`에 다음 코드 추가:

```php
add_action('wp_footer', function() {
    if (!is_page('contact')) {
        ?>
        <style>
            #azure-chatbot-widget { display: none !important; }
        </style>
        <?php
    }
}, 5);
```

### Q6. 로그인한 사용자만 채팅을 사용하게 할 수 있나요?

`azure-ai-chatbot.php`의 `handle_chat` 메서드에 다음 코드 추가:

```php
// Nonce 검증 후
if (!is_user_logged_in()) {
    return new WP_Error('unauthorized', '로그인이 필요합니다', ['status' => 401]);
}
```

---

## 📚 추가 리소스

### 공식 문서

- [Azure AI Foundry 문서](https://learn.microsoft.com/azure/ai-foundry/)
- [Azure AI Agent Service](https://learn.microsoft.com/azure/ai-foundry/agents/)
- [WordPress 플러그인 개발](https://developer.wordpress.org/plugins/)

### 커뮤니티

- **이슈 및 버그 리포트**: admin@edueldensolution.kr
- **기능 요청**: admin@edueldensolution.kr
- **사용자 포럼**: 준비 중

### 업데이트 노트

#### v2.0.0 (2025-01-XX)
- ✨ 관리자 페이지에서 모든 설정 가능
- ✨ 마크다운 가이드 편집 기능
- ✨ 색상 커스터마이징
- ✨ 위치 선택 기능
- ✨ 연결 테스트 기능
- 🔒 보안 강화 (Nonce 검증)
- 🎨 개선된 UI/UX

---

## 🤝 기여 및 지원

### 버그 리포트

버그를 발견하셨나요? 다음 정보와 함께 연락주세요:

1. WordPress 버전
2. PHP 버전
3. 플러그인 버전
4. 오류 메시지 또는 스크린샷
5. 재현 단계

이메일: admin@edueldensolution.kr

### 기능 제안

새로운 기능을 제안하고 싶으신가요? 다음 내용을 포함하여 연락주세요:

1. 제안 기능 설명
2. 사용 사례
3. 기대 효과

### 후원

이 플러그인이 도움이 되셨나요? 후원을 통해 개발을 지원해주세요:

- **PayPal**: [준비 중]
- **Patreon**: [준비 중]
- **Ko-fi**: [준비 중]

---

## 📄 라이선스

이 플러그인은 GPL-2.0+ 라이선스 하에 배포됩니다.

```
Copyright (C) 2025 허석 (Heo Seok)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

---

## 📞 연락처

**제작자**: 허석 (Heo Seok)  
**이메일**: admin@edueldensolution.kr  
**웹사이트**: https://edueldensolution.kr

---

**마지막 업데이트**: 2025년 1월

이 가이드는 지속적으로 업데이트됩니다. 최신 버전은 플러그인 관리 페이지에서 확인하세요.
