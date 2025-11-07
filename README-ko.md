# Azure AI Chatbot for WordPress

**[English](README.md) | [한국어](#)**

Azure AI Foundry 에이전트와 OpenAI 호환 채팅 모델을 완전한 Assistants API 통합과 OAuth 2.0 자동 설정으로 WordPress 웹사이트에 제공하는 현대적인 플러그인입니다.

[![Version](https://img.shields.io/badge/version-3.0.16-blue.svg)](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)
[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)

---

## 🚀 신기능! OAuth 2.0 자동 설정

**제로 코드 설정**: 클릭 몇 번만으로 완전한 Azure AI 통합!

1. **Azure 승인** → Microsoft 계정으로 로그인
2. **리소스 선택** → 기존 선택 또는 새로 생성  
3. **자동 설정** → 모든 설정이 자동으로 채워짐
4. **채팅 시작** → AI 챗봇 즉시 사용 가능

✅ **기존 리소스 완전 지원**: 기존 AI Foundry 프로젝트를 선택하면 모든 설정 자동 채움  
✅ **API Key 자동 조회**: API Key를 자동으로 조회하고 암호화하여 저장  
✅ **완전 자동화**: OAuth 승인부터 최종 설정까지 자동 완료

---

## 왜 이 플러그인인가?

이 플러그인은 WordPress를 위한 **포괄적인 Azure AI Foundry Agent 모드 통합**을 제공합니다. 다른 플러그인들이 기본 채팅 API만 지원하는 반면, 이 플러그인은 독특하게 다음을 제공합니다:

- **🚀 OAuth 2.0 자동 설정**: 자동 리소스 탐지로 완전한 제로 코드 설정
- **Azure AI Foundry Agents**: Function Calling, RAG, 파일 업로드를 포함한 완전한 Assistants API 지원
- **듀얼 모드 아키텍처**: 간단한 Chat 모드 또는 고급 Agent 모드 선택 가능
- **엔터프라이즈 인증**: 기업 배포를 위한 Entra ID OAuth 2.0 지원
- **기존 리소스 지원**: 신규 및 기존 Azure AI 리소스 모두 작동

Azure AI Foundry의 강력한 에이전트 기능을 WordPress 사용자에게 제공하기 위해 제작되었습니다.

---

## 목차

- [주요 기능](#주요-기능)
- [빠른 시작](#빠른-시작)
- [설치 방법](#설치-방법)
- [설정](#설정)
- [보안](#보안)
- [커스터마이징](#커스터마이징)
- [파일 구조](#파일-구조)
- [문제 해결](#문제-해결)
- [버전 히스토리](#버전-히스토리)

---

## 주요 기능

### 듀얼 모드 지원

**Chat 모드** - 간단하고 다재다능
- Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok
- API Key 인증
- 실시간 스트리밍 응답
- 다중 제공자 지원

**Agent 모드** - 고급 기능
- Azure AI Foundry Assistants API v1
- Entra ID OAuth 2.0 인증
- Function Calling 및 도구 통합
- RAG (검색 증강 생성)
- 파일 업로드 지원
- 지속적인 대화 스레드

### 핵심 기능

- **제로 코드 설정**: WordPress 관리자 패널에서 완전한 설정
- **엔터프라이즈 보안**: 자격 증명 AES-256 암호화
- **완전 커스터마이징**: 색상, 위치, 메시지, 스타일링
- **반응형 디자인**: 데스크톱과 모바일에서 완벽
- **연결 테스트**: 배포 전 API 연결 확인
- **다국어 지원**: 한국어와 영어, 자동 감지

---

## 빠른 시작

### Chat 모드 설정

**1단계: 설정값 가져오기**

[Azure Cloud Shell](https://shell.azure.com)에서 이 스크립트 실행:

```bash
curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash
```

스크립트 기능:
1. Azure 구독 목록 표시
2. Azure OpenAI 리소스 찾기
3. 배포된 모델 표시
4. 엔드포인트 및 API 키 가져오기
5. 연결 테스트
6. WordPress 설정값 출력

**출력 예시:**
```
✅ Chat 모드 연결 성공!

WordPress 설정:
• 모드: Chat 모드
• 엔드포인트: https://your-resource.openai.azure.com
• 배포 이름: gpt-4o
• API Key: abc123...xyz789
```

**2단계: WordPress 설정**

1. **AI Chatbot** → **설정**으로 이동
2. **Chat 모드** 선택
3. **Azure OpenAI** 선택
4. 스크립트 출력값 입력
5. **변경사항 저장** → **연결 테스트** 클릭

---

### Agent 모드 설정

**1단계: 설정값 가져오기**

[Azure Cloud Shell](https://shell.azure.com)에서 이 스크립트 실행:

```bash
curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash
```

스크립트 기능:
1. AI Foundry 리소스 찾기
2. Service Principal 생성 또는 사용
3. Client Secret 생성
4. 에이전트 목록 표시 또는 생성 지원
5. 완전한 연결 테스트
6. 모든 WordPress 설정값 출력

**출력 예시:**
```
✅ Agent 모드 연결 성공!

WordPress 설정:
• 모드: Agent 모드
• 엔드포인트: https://your-resource.services.ai.azure.com/api/projects/your-project
• Agent ID: asst_abc123xyz789
• Client ID: 12345678-1234-1234-1234-123456789012
• Client Secret: def456...uvw789
• Tenant ID: 87654321-4321-4321-4321-210987654321
```

**2단계: WordPress 설정**

1. **AI Chatbot** → **설정**으로 이동
2. **Agent 모드** 선택
3. 스크립트 출력값 입력
4. **변경사항 저장** → **연결 테스트** 클릭

---

## 설치 방법

### 방법 1: ZIP 파일로 설치 (권장)

1. [Releases](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)에서 `azure-ai-chatbot-wordpress.zip` 다운로드
2. WordPress 관리자 → **플러그인** → **새로 추가** → **플러그인 업로드**
3. ZIP 선택 후 **지금 설치** 클릭
4. **플러그인 활성화** 클릭

### 방법 2: Git Clone

```bash
git clone https://github.com/asomi7007/azure-ai-chatbot-wordpress.git
# /wp-content/plugins/에 업로드
# WordPress 관리자에서 활성화
```

---

## 설정

### Chat 모드 제공자

| 제공자 | 엔드포인트 | 모델 예시 | 인증 |
|--------|-----------|----------|-----|
| **Azure OpenAI** | `https://{resource}.openai.azure.com` | `gpt-4o` | API Key |
| **OpenAI** | `https://api.openai.com` | `gpt-4-turbo` | API Key (sk-) |
| **Google Gemini** | `https://generativelanguage.googleapis.com` | `gemini-2.0-flash-exp` | API Key |
| **Anthropic Claude** | `https://api.anthropic.com` | `claude-3-5-sonnet-20241022` | API Key (sk-ant-) |
| **xAI Grok** | `https://api.x.ai` | `grok-beta` | API Key |
| **기타** | 사용자 정의 엔드포인트 | OpenAI 호환 모델 | API Key |

### Agent 모드 요구사항

- **Agent ID**: Azure AI Foundry에서 생성 (`asst_`로 시작)
- **Tenant ID**: Microsoft Entra 테넌트 ID
- **Client ID**: Service Principal 애플리케이션 ID
- **Client Secret**: 생성된 시크릿 (스크립트로 생성)
- **Project Path**: 전체 Azure 리소스 경로

**프로젝트 경로 형식:**
```
subscriptions/{subscription-id}/resourceGroups/{resource-group}/providers/Microsoft.MachineLearningServices/workspaces/{project-name}
```

---

## 보안

### 자격 증명 암호화

모든 민감한 데이터는 저장 전에 암호화됩니다:

- **알고리즘**: AES-256-CBC
- **키 저장소**: WordPress 인증 키 및 솔트
- **암호화 필드**: API Key, Client Secret
- **복호화**: API 호출 시에만 필요할 때

### 보안 권장사항

1. **HTTPS 사용**: WordPress를 항상 HTTPS에서 실행
2. **정기 업데이트**: WordPress와 PHP를 최신 상태로 유지
3. **관리자 접근 제한**: 플러그인 설정 접근 제한
4. **시크릿 교체**: 정기적으로 API 키와 시크릿 재생성
5. **로그 모니터링**: 무단 접근에 대한 디버그 로그 확인

### 네트워크 보안

- WordPress 서버 IP를 허용하도록 Azure 방화벽 규칙 구성
- 최소 필요 권한으로 Service Principal 사용
- API 사용량 추적을 위해 Azure Monitor 활성화

---

## 커스터마이징

### 시각적 커스터마이징

**AI Chatbot** → **설정**에서 구성:

- **챗봇 제목**: 헤더 텍스트
- **환영 메시지**: 초기 인사말
- **버튼 색상**: Hex 색상 코드 (예: `#667eea`)
- **버튼 위치**: 우측 하단 또는 좌측 하단

### 고급 CSS

**외모** → **사용자 정의하기** → **추가 CSS**에 추가:

```css
/* 챗봇 창 크기 변경 */
.azure-chatbot-window {
    width: 450px !important;
    height: 700px !important;
}

/* 사용자 정의 메시지 색상 */
.azure-chatbot-message.user {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* 사용자 정의 전송 버튼 */
.azure-chatbot-send-btn {
    background: #667eea !important;
}
```

### JavaScript 훅

```javascript
// 챗봇 이벤트 리스너
document.addEventListener('azure-chatbot-opened', function() {
    console.log('챗봇 열림');
});

document.addEventListener('azure-chatbot-message-sent', function(event) {
    console.log('메시지:', event.detail.message);
});
```

---

## 파일 구조

```
azure-ai-chatbot-wordpress/
├── azure-ai-chatbot.php       # 메인 플러그인 파일
├── assets/
│   ├── admin.css              # 관리자 스타일
│   ├── admin.js               # 관리자 스크립트
│   ├── chatbot.css            # 위젯 스타일
│   └── chatbot.js             # 위젯 스크립트
├── templates/
│   ├── settings-page.php      # 설정 UI
│   └── guide-page.php         # 사용자 가이드
├── languages/
│   ├── *.po                   # 번역 소스
│   ├── *.mo                   # 컴파일된 번역
│   └── compile-po-to-mo.py    # 컴파일러 스크립트
├── docs/
│   ├── AZURE_SETUP.md         # 상세 설정 가이드
│   └── USER_GUIDE.md          # 사용자 문서
├── test-chat-mode.sh          # Chat 모드 테스트 스크립트
├── test-agent-mode.sh         # Agent 모드 테스트 스크립트
├── README.md                  # 영문 버전
├── README-ko.md               # 이 파일 (한글)
├── CHANGELOG.md               # 전체 버전 히스토리
└── LICENSE                    # GPL-2.0+
```

---

## 문제 해결

### HTTP 404 오류 (Chat 모드)

**문제**: 테스트 시 404 오류 발생

**해결 방법**:
1. 엔드포인트 URL에서 끝의 슬래시 제거
2. 배포 이름 확인 (대소문자 구분)
3. API 키 유효성 확인
4. 다른 API 버전 시도

**수동 테스트**:
```bash
curl -X POST "https://YOUR-RESOURCE.openai.azure.com/openai/deployments/YOUR-DEPLOYMENT/chat/completions?api-version=2024-08-01-preview" \
  -H "api-key: YOUR-KEY" \
  -H "Content-Type: application/json" \
  -d '{"messages":[{"role":"user","content":"안녕하세요"}]}'
```

### Agent 모드 연결 실패

**문제**: Azure AI Foundry에 연결할 수 없음

**해결 방법**:
1. Service Principal에 "Cognitive Services User" 역할이 있는지 확인
2. 프로젝트 경로 형식 확인
3. Client Secret이 유효한지 확인
4. 네트워크 방화벽 규칙 확인

**권한 확인**:
```bash
az role assignment list --assignee YOUR-CLIENT-ID
```

### 챗봇이 표시되지 않음

**문제**: 웹사이트에 위젯이 표시되지 않음

**해결 방법**:
1. WordPress 캐시 삭제
2. 플러그인이 활성화되어 있는지 확인
3. 브라우저 콘솔(F12)에서 JavaScript 오류 확인
4. 다른 플러그인 임시 비활성화
5. 기본 테마로 전환하여 테스트

### 디버그 로깅 활성화

`wp-config.php`에 추가:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

로그 위치: `/wp-content/debug.log`

---

## 버전 히스토리

### 최신 릴리즈: v2.2.7 (2025-10-21)

**버그 수정:**
- 🐛 **public_access 설정 저장 오류**: 체크박스 해제가 저장되지 않던 문제 수정
- 🔧 **sanitize_settings 함수**: public_access 필드 처리 로직 추가

[v2.2.7 다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.7)

### v2.2.6 (2025-10-21)

**개선사항:**
- 🎨 **스마트 위젯 표시**: public_access 비활성화 시 비로그인 사용자에게 위젯 미표시
- ✨ **UX 개선**: 사용 불가능한 위젯이 보이지 않도록 개선
- 📦 **ZIP 파일 최적화**: Bandizip으로 46% 크기 감소

[v2.2.6 다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.6)

### v2.2.5 (2025-10-21)

**추가기능:**
- ✨ **비로그인 사용자 접근 옵션**: 설정 페이지 체크박스 추가
- 🔓 **익명 사용자 지원**: 로그인하지 않은 방문자도 사용 가능 (기본값: 허용)

[v2.2.5 다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.5)

### v2.2.4 (2025-10-05)

**수정사항:**
- Chat 모드 HTTP 404 오류 수정
- API 버전 초기화 로직 개선
- 다중 제공자 엔드포인트 처리 개선

[v2.2.4 다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/tag/v2.2.4)

### 이전 버전

**v2.2.3** - 문서 및 FAQ 개선  
**v2.2.2** - GitHub 배지 및 변경 이력 추가  
**v2.2.1** - 엔드포인트 슬래시 문제 수정  
**v2.2.0** - 다중 제공자 지원 (6개 AI 서비스)  
**v2.1.0** - 듀얼 모드 도입 (Chat + Agent)  
**v2.0.0** - 플러그인 완전 재설계  
**v1.0.0** - 초기 릴리즈

[전체 변경 이력](CHANGELOG.md)
[모든 릴리즈 보기](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases)

---

## 자주 묻는 질문

**Q: 어떤 AI 서비스를 사용할 수 있나요?**  
A: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok 및 OpenAI 호환 API를 사용할 수 있습니다.

**Q: 이 플러그인이 Azure AI Foundry 에이전트를 지원하나요?**  
A: 네. 이 플러그인은 완전한 Assistants API 지원과 함께 포괄적인 Azure AI Foundry Agent 모드 통합을 제공합니다.

**Q: 코딩 기술이 필요한가요?**  
A: 아니요. Azure 설정은 자동화 스크립트를 사용하고, WordPress 관리자 패널에서 설정할 수 있습니다.

**Q: 안전한가요?**  
A: 네. 모든 자격 증명은 AES-256으로 암호화됩니다. 프로덕션 환경에서는 HTTPS를 사용하세요.

**Q: Chat 모드와 Agent 모드의 차이점은 무엇인가요?**  
A: Chat 모드는 간단한 API 호출입니다. Agent 모드는 Function Calling과 RAG 같은 고급 기능을 제공하는 Azure AI Foundry를 사용합니다.

**Q: 여러 사이트에서 사용할 수 있나요?**  
A: 네. 각 WordPress 설치마다 개별적으로 설정해야 합니다.

**Q: WordPress 멀티사이트와 호환되나요?**  
A: 네. 각 사이트가 독립적인 설정을 가질 수 있습니다.

---

## 기여하기

기여를 환영합니다!

1. 저장소 Fork
2. 기능 브랜치 생성 (`git checkout -b feature/name`)
3. 변경사항 커밋 (`git commit -m 'Add feature'`)
4. 브랜치에 푸시 (`git push origin feature/name`)
5. Pull Request 열기

[WordPress 코딩 표준](https://developer.wordpress.org/coding-standards/)을 따라주세요.

---

## 지원

- **문서**: [docs/](docs/)
- **이슈**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)
- **토론**: [GitHub Discussions](https://github.com/asomi7007/azure-ai-chatbot-wordpress/discussions)
- **릴리즈**: [최신 버전](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest)

---

## 라이선스

GPL-2.0+ 라이선스 - [LICENSE](LICENSE) 파일 참조

자유롭게 사용, 수정, 배포 가능합니다.

---

## 감사의 글

엔터프라이즈급 AI 채팅 기능이 필요한 WordPress 및 Azure AI Foundry 사용자를 위해 제작되었습니다.

**Made with ❤️ for WordPress & Azure AI**
