# Azure AI Chatbot v2.2.4 릴리즈 노트

## 🐛 Chat 모드 HTTP 404 오류 완전 수정

v2.2.4는 Chat 모드에서 발생하던 HTTP 404 오류를 완전히 해결한 버전입니다.

## 주요 수정 사항

### 🔧 API 버전 초기화 로직 개선
- **문제**: Chat 모드 사용 시 Azure OpenAI API 호출에서 HTTP 404 오류 발생
- **원인**: `api_version` 속성이 선언되었으나 초기화되지 않아 API 요청에 버전 파라미터가 누락됨
- **해결**: 
  - Agent 모드 (Entra ID 인증): `api_version = 'v1'`
  - Chat 모드 (API Key 인증): `api_version = '2024-08-01-preview'`
  - 인증 타입에 따라 적절한 API 버전 자동 설정

### 🌐 다중 AI 제공자 API 최적화
각 AI 서비스별 특성에 맞는 API 엔드포인트 및 인증 방식 최적화:

- **Azure OpenAI**: `/openai/deployments/{deployment}/chat/completions` + `api-key` 헤더 + `api-version` 파라미터
- **OpenAI**: `/v1/chat/completions` + `Authorization: Bearer` 헤더 + `model` 파라미터
- **Google Gemini**: `/v1beta/models/{model}:generateContent` + `key` 쿼리 파라미터 + `contents` 구조
- **Anthropic Claude**: `/v1/messages` + `x-api-key` + `anthropic-version` 헤더
- **xAI Grok**: `/v1/chat/completions` + `Authorization: Bearer` (OpenAI 호환)
- **기타 제공자**: `/v1/chat/completions` + `Authorization: Bearer` (OpenAI 호환)

## 테스트 완료
- ✅ Chat 모드 연결 테스트 정상 동작 확인
- ✅ Agent 모드 기존 기능 정상 동작 유지
- ✅ Azure OpenAI API 호출 200 OK 응답 확인

## 업그레이드 방법

### 자동 업데이트
WordPress 관리자 페이지의 플러그인 메뉴에서 업데이트 버튼 클릭

### 수동 설치
1. [ZIP 파일 다운로드](https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/download/v2.2.4/azure-ai-chatbot-wordpress.zip)
2. 기존 플러그인 비활성화 및 삭제
3. 새 버전 업로드 및 활성화
4. 설정 재확인

## 호환성
- WordPress 6.0 이상
- PHP 7.4 이상
- Azure AI Foundry / OpenAI / Google Gemini / Anthropic Claude / xAI Grok

## 문의 및 버그 리포트
- GitHub Issues: https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues
- 이메일: support@eldensolution.com

---

**전체 변경 이력**: [v2.2.3...v2.2.4](https://github.com/asomi7007/azure-ai-chatbot-wordpress/compare/v2.2.3...v2.2.4)
