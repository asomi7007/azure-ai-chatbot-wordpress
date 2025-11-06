# 변경 이력

## [3.0.14] - 2025-11-07

### 🔧 OAuth 탭 자동 표시 기능 추가
- **⚡ URL 파라미터 기반 탭 표시**: `tab=oauth-auto-setup` 파라미터가 있을 때 OAuth 자동 설정 섹션 자동 표시
- **📜 자동 스크롤**: OAuth 자동 설정 섹션으로 자동 스크롤하여 사용자 편의성 향상

### 기술 세부사항
**문제:**
- OAuth 인증 후 `tab=oauth-auto-setup` 파라미터로 리다이렉트되지만
- OAuth 자동 설정 섹션이 기본적으로 숨겨져 있어(`display: none`) 사용자가 "Auto Setting" 버튼을 수동으로 클릭해야 함
- 자동 설정이 시작되지 않음

**해결:**
- 페이지 로드 시 URL 파라미터 확인: `new URLSearchParams(window.location.search)`
- `tab=oauth-auto-setup`일 때 자동으로 섹션 표시: `$('#oauth-auto-setup-section').show()`
- 300ms 후 해당 섹션으로 스크롤하여 사용자가 바로 볼 수 있도록 개선

### 영향
- ❌ 이전: OAuth 인증 후 리다이렉트 → 섹션 숨겨짐 → 수동으로 버튼 클릭 필요
- ✅ 수정: OAuth 인증 후 리다이렉트 → 섹션 자동 표시 → 자동 스크롤 → 자동 설정 시작

## [3.0.13] - 2025-11-07

### 🐛 긴급 버그 수정
- **⚡ 비동기 Promise 처리 수정**: Resource Group 선택 모달이 비동기이므로 AI 리소스 확인 로직을 Promise 내부로 이동
- **🔧 Null 참조 에러 방지**: `chosenRG`가 `null`인 상태에서 `.name` 접근 시도하던 문제 해결

### 기술 세부사항
**문제:**
- `showSelectionModal`이 Promise를 반환하는 비동기 함수
- 모달 선택을 기다리지 않고 바로 `chosenRG.name`에 접근하여 `Cannot read properties of null (reading 'name')` 에러 발생
- Resource Group이 여러 개일 때만 발생 (1개일 때는 동기적으로 처리되어 정상 작동)

**해결:**
- `checkAIResources(rg)` 함수 생성하여 AI 리소스 확인 로직 분리
- Resource Group 1개: 즉시 `checkAIResources` 호출
- Resource Group 여러 개: 모달 선택 후 `.then()` 내에서 `checkAIResources` 호출
- 중복 코드 제거

### 영향
- ❌ 이전: Resource Group 여러 개 → 모달 선택 무시 → `chosenRG = null` → JavaScript 에러
- ✅ 수정: Resource Group 여러 개 → 모달 선택 → 선택된 RG로 AI 리소스 확인 → 정상 진행

## [3.0.12] - 2025-11-07

### 🐛 긴급 버그 수정
- **⚡ OAuth 리다이렉트 페이지 수정**: OAuth 인증 후 OAuth 자동 설정 탭(`tab=oauth-auto-setup`)으로 이동하도록 수정
- **🔧 JavaScript 에러 방지**: `.oauth-step-2` 요소 존재 여부 확인 후 스크롤, `loadSubscriptions` 함수 존재 확인

### 기술 세부사항
**문제:**
- OAuth 인증 후 일반 설정 페이지로 리다이렉트되어 `.oauth-step-2` 요소가 없음
- `$(".oauth-step-2").offset().top` 실행 시 `Cannot read properties of undefined (reading 'top')` 에러 발생
- 자동 설정이 시작되지 않음

**해결:**
- 리다이렉트 URL에 `tab=oauth-auto-setup` 파라미터 추가
- 요소 존재 확인: `if ($oauthStep2.length > 0)` 체크 후 스크롤
- 함수 존재 확인: `if (typeof loadSubscriptions === "function")` 체크 후 실행

### 영향
- ❌ 이전: OAuth 인증 후 일반 설정 페이지로 이동 → JavaScript 에러 발생
- ✅ 수정: OAuth 인증 후 OAuth 자동 설정 탭으로 이동 → 자동 설정 정상 작동

## [3.0.11] - 2025-11-07

### 🚀 주요 기능 추가 및 버그 수정
- **✨ OAuth → Agent Mode 자동 연동**: OAuth 설정 저장 시 Agent Mode 필드(Client ID, Secret, Tenant ID)에도 자동으로 값 채워짐
- **🔧 세션 관리 개선**: localStorage 기반 토큰 플래그로 팝업 창과 부모 창 간 세션 유지 문제 해결
- **🗑️ 불필요한 경고 제거**: `oauth_success=1` 파라미터 존재 시 세션 경고 메시지 표시하지 않음

### 기술 세부사항
**1. OAuth 설정 → Agent Mode 자동 저장**
- `save_oauth_settings` AJAX 핸들러에 `save_to_agent_mode` 파라미터 추가
- OAuth 설정 저장 시 `azure_client_id`, `azure_client_secret`, `azure_tenant_id` 옵션도 동시 저장
- 사용자가 수동으로 두 곳에 동일한 값을 입력하지 않아도 됨

**2. 세션 유지 개선**
- OAuth 팝업에서 토큰 저장 시 `localStorage`에도 플래그 저장
- 부모 창 리다이렉트 시 `has_token=1` 파라미터 추가
- `autoSetupMode` 결정 시 세션 토큰과 localStorage 토큰 모두 확인
- 자동 설정 완료 후 localStorage 플래그 자동 제거

**3. 경고 메시지 조건 개선**
- `oauth_success=1`일 때는 세션 없어도 경고 표시 안 함 (OAuth 리다이렉트 직후이므로)
- localStorage 토큰 만료 시간 5분으로 설정하여 오래된 플래그 자동 제거

### 영향
- ❌ 이전: OAuth 설정 저장 후 Agent Mode 설정 탭에서 동일한 값 다시 입력 필요
- ✅ 수정: OAuth 설정 저장 시 Agent Mode 필드에도 자동으로 채워짐
- ❌ 이전: `autoSetupMode = false` (세션 유지 실패)
- ✅ 수정: `autoSetupMode = true` (localStorage 기반 토큰 확인)

## [3.0.10] - 2025-11-07

### 🐛 핵심 버그 수정
- **⚡ OAuth 리다이렉트 URL 수정**: `esc_url()`이 `&`를 `&#038;`로 변환하여 `oauth_success` 파라미터가 전달되지 않던 문제 해결
- **🔄 자동 설정 활성화**: URL 인코딩 문제로 `autoSetupMode`가 `false`로 설정되던 버그 수정

### 기술 세부사항
- `esc_url()` 대신 `json_encode()` + `add_query_arg()` 사용
- JavaScript에서 URL을 안전하게 처리하도록 JSON 인코딩 적용
- 디버깅을 위한 `console.log('[OAuth] Redirecting to:')` 추가

### 영향
이 수정으로 OAuth 인증 후 자동 설정이 정상적으로 작동합니다:
- ❌ 이전: `/admin.php?page=azure-ai-chatbot#038;oauth_success=1` → `autoSetupMode = false`
- ✅ 수정: `/admin.php?page=azure-ai-chatbot&oauth_success=1` → `autoSetupMode = true`

## [3.0.9] - 2025-11-07

### 🔧 버그 수정
- **🌐 WARNING 메시지 한글화**: Azure CLI의 영어 경고 메시지를 한글로 변환하여 표시
- **🗑️ 기존 앱 전체 삭제**: 동일 Redirect URI를 가진 모든 App Registration을 삭제하도록 수정
- **📊 자동 설정 디버깅**: OAuth 성공 후 자동 설정이 작동하지 않는 문제 디버깅을 위한 상세 로그 추가

### 개선됨 (Improved)
- **🔍 Cloud Shell 스크립트**: Client Secret 생성 시 WARNING 메시지를 감지하여 한글로 표시
- **🗑️ 삭제 기능 강화**: "기존 앱 삭제하고 새로 생성" 선택 시 하나가 아닌 모든 기존 앱 삭제
- **🐛 디버깅 로그**: Subscription 로드, autoSetupMode 확인, 함수 실행 여부 등 상세 로그 추가

### 기술 세부사항
- WARNING 메시지 필터링: `grep -qi "WARNING:.*credentials"` 패턴 매칭
- 전체 앱 삭제: `jq -r '.[].AppId' | while read` 루프로 모든 앱 ID 처리
- AJAX fail 핸들러 추가로 네트워크 오류 캐치
- startAutoResourceCreation 함수 존재 여부 확인 로직 추가

## [3.0.8] - 2025-11-07

### 개선됨 (Improved)
- 📝 **Admin Consent 안내 개선**: 승인 후 리다이렉트되는 페이지를 무시하고 창을 닫으라는 명확한 안내 추가
- 🎯 **사용자 경험 개선**: "승인 후 창을 닫아주세요" 메시지로 혼란 방지
- 📄 **Admin Consent 완료 페이지**: 자동으로 닫히는 HTML 페이지 추가 (docs/admin-consent-complete.html)

### 기술 세부사항
- 승인 절차 4단계에 "⚠️ 승인 후 표시되는 페이지는 무시하고 브라우저 창을 닫아주세요" 추가
- read -p 프롬프트 메시지를 "승인을 완료하고 창을 닫았으면" 으로 명확화

## [3.0.7] - 2025-11-07

### 🔧 핵심 수정사항
- **🚨 무한 대기 문제 해결**: `az ad app create` 명령에 30초 타임아웃 추가
- **⏱️ Client Secret 생성 타임아웃**: 30초 제한으로 무한 대기 방지
- **⏱️ API 권한 추가 타임아웃**: 각 권한당 20초 제한 설정

### 개선됨 (Improved)
- **🛡️ 강력한 에러 처리**: 모든 Azure CLI 명령에 타임아웃 및 EXIT_CODE 체크
- **📝 상세한 에러 메시지**: 타임아웃, 권한 부족, 토큰 만료 등 각 상황별 명확한 안내
- **✅ GUID 검증**: App ID가 올바른 GUID 형식인지 검증
- **✅ Secret 검증**: Client Secret이 올바른 형식인지 검증 (30자 이상, 특수문자 포함)

### 기술 세부사항
- **타임아웃 설정**: App 생성 30s, Secret 생성 30s, 권한 추가 각 20s
- **set +e/set -e**: 타임아웃 발생 시에도 스크립트가 에러 메시지 표시 후 종료
- **정규식 검증**: GUID 및 Secret 값의 형식 검증으로 잘못된 응답 감지
- **새 msg() 키**: app_creation_timeout, secret_creation_timeout, permission_timeout, token_expired, insufficient_privileges, error_details

### 🎯 해결된 문제
- Cloud Shell에서 `az ad app create` 실행 후 무한 대기하던 문제
- 권한 부족 시 명확한 에러 메시지 없이 멈추던 문제
- Azure AD API 응답 지연 시 스크립트가 영원히 기다리던 문제

## [3.0.6] - 2025-11-07

### 수정됨 (Fixed)
- 🐛 **Cloud Shell 스크립트 타임아웃**: `az ad app list` 명령 타임아웃 30초 → 5초로 단축
- 🔍 **필터링 최적화**: 서버 측 필터 대신 클라이언트 측(jq) 필터링으로 변경하여 성능 개선
- 🌐 **언어 선택 버그 수정**: 모든 하드코딩된 메시지를 `msg()` 함수로 통일하여 다국어 지원 정상화
- ⚡ **빠른 권한 체크**: Azure AD 권한 확인을 5초 타임아웃으로 빠르게 처리

### 개선됨 (Improved)
- 🛡️ **에러 처리 강화**: `set +e`/`set -e`로 타임아웃 발생 시에도 스크립트 계속 진행
- 📝 **일관된 메시지**: 영어/한국어 메시지가 `msg()` 함수로 중앙 관리됨
- 🎯 **사용자 경험**: 언어 선택이 전체 스크립트에 걸쳐 일관되게 적용

### 기술 세부사항
- **타임아웃 단축**: 30s → 5s (권한 체크), 10s (앱 목록 조회)
- **jq 필터링**: `--filter` 서버 측 파라미터 제거, jq로 클라이언트 측 처리
- **msg() 함수 확장**: 15개 이상의 새 메시지 키 추가 (login_required, single_subscription, etc.)

## [3.0.5] - 2025-11-06

### 추가됨 (Added)
- 🎨 **모달 기반 선택 UI**: prompt() 대신 WordPress 관리자 스타일 모달로 리소스 선택
- ⌨️ **키보드 접근성**: ESC로 닫기, Enter로 확인, 자동 포커스 이동
- 🎯 **다중 리소스 선택**: 여러 Resource Group/AI 리소스 있을 때 선택 UI 제공
- 📝 **모델/지역 입력**: 신규 생성 시 모델, 지역, 배포이름 입력 모달
- 🔘 **라디오 버튼 선택**: 기본 선택(첫 항목) 및 '새로 생성' 옵션
- ♿ **ARIA 속성**: role="dialog", aria-modal, aria-labelledby 추가

### 개선됨 (Improved)
- 🎨 **WordPress UI 통합**: WP 관리자 버튼 스타일(button-primary, button-secondary) 적용
- 🔄 **비동기 Promise 기반**: 모달 선택/입력이 async/await 패턴으로 동작
- 📱 **반응형 모달**: max-width 95%, 모바일 친화적 레이아웃
- 🎯 **기본값 제공**: 모달에서 기본 선택/입력값 자동 설정

### 변경됨 (Changed)
- 🔄 **UX 개선**: 브라우저 기본 prompt() → 커스텀 모달로 전면 교체
- 📋 **선택 방식**: 번호 입력 → 라디오 버튼 선택으로 변경

### 수정됨 (Fixed)
- ✅ **AJAX 엔드포인트 검증**: 모든 서버측 핸들러 응답 스키마 확인 완료
- 🔐 **Nonce 보안**: 클라이언트-서버 간 nonce 파라미터 일치 확인

### 기술 세부사항
- **모달 구현**: ensureAdminModal(), showSelectionModal(), showInputModal()
- **이벤트 처리**: jQuery 기반 동적 이벤트 바인딩
- **포커스 관리**: azureModal.open 커스텀 이벤트로 초기 포커스 제어
- **응답 형식**: { success: true, data: {...} } 표준 WordPress AJAX 응답

## [2.4.0] - 2025-10-26

### 추가됨 (Added)
- 🏗️ **Azure 리소스 자동 생성**: Resource Group, AI Foundry Project 자동 생성
- 🤖 **모델 자동 배포**: Chat 모드에서 AI Foundry에 모델 자동 배포
- 🌍 **동적 지역 선택**: Azure 구독에서 실제 사용 가능한 지역 동적 조회
- 📦 **동적 모델 선택**: 선택한 지역에서 사용 가능한 GPT 모델 목록 동적 조회
- ⚙️ **모드별 자동화**: Chat/Agent 모드에 따라 다른 리소스 생성 프로세스
- 🏷️ **Azure 명명 규칙**: 자동 생성되는 리소스 이름에 Azure 표준 명명 규칙 적용
- 🎯 **TPM 용량 선택**: Chat 모드에서 모델 배포 시 토큰 처리량(10K-240K TPM) 선택

### 개선됨 (Improved)
- 🔄 **AI Foundry 통합**: 모든 모드에서 Azure AI Foundry 기반으로 통합
- 📊 **리소스 생성 UI**: 단계별 안내와 예상 시간 표시
- 🌐 **다국어 지원**: 영어/한국어 번역 추가 (40+ 새 문자열)
- 🔧 **API 호출 확장**: PUT/POST/DELETE HTTP 메서드 지원, 전체 URL 처리
- ⏱️ **타임아웃 증가**: 리소스 생성을 위해 60초로 타임아웃 연장
- ✅ **검증 강화**: 리소스 이름 패턴 검증 (3-64자, Azure 표준 준수)

### 변경됨 (Changed)
- 🔄 **Chat 모드 아키텍처**: Azure OpenAI → AI Foundry Project + Model Deployment
- 📝 **명명 규칙 통일**: 모든 모드에서 `ai-{워크로드}-{환경}` 패턴 사용
- 🗺️ **지역 필터링**: AI Foundry 지원 지역만 표시 (이전: OpenAI 지역)

### 수정됨 (Fixed)
- 🐛 **에러 처리 개선**: Azure API 호출 시 HTTP 상태 코드 및 JSON 파싱 에러 처리
- 🔧 **call_azure_api 메서드 확장**: 다양한 HTTP 메서드 및 요청 본문 지원

### 기술 세부사항
- **생성 프로세스**: Hub(30초) → Project → Model Deployment(Chat만)
- **소요 시간**: Chat 모드 2-3분, Agent 모드 1-2분
- **API 엔드포인트**: 
  - 지역 조회: `Microsoft.MachineLearningServices` 프로바이더
  - 모델 배포: AI Foundry Online Endpoints
  - 리소스 생성: Azure Resource Manager API

## [2.3.0] - 2025-10-22

### 추가됨 (Added)
- ✨ **OAuth 2.0 자동 설정 기능**: Azure 리소스 자동 검색 및 설정
- 🔐 **Azure App Registration 통합**: OAuth 인증으로 안전한 Azure API 접근
- 🤖 **Agent ID 자동 조회**: AI Foundry Project의 Agent 목록 자동 검색
- 🎯 **모드별 자동화**: Chat/Agent 모드에 따라 다른 자동화 동작
- 📋 **설정 마법사 UI**: Azure Cloud Shell 스크립트 및 Portal 가이드 제공
- 📚 **OAuth 설정 가이드**: 자세한 설정 문서 및 스크립트 제공

### 개선됨 (Improved)
- 🎨 **관리자 UI 개선**: 리소스 선택 드롭다운 캐스케이드
- 🔄 **자동 토큰 갱신**: Access Token 자동 갱신 기능
- 🛡️ **보안 강화**: CSRF 보호, 세션 기반 토큰 저장
- 📱 **복사 버튼**: Cloud Shell 명령어, Redirect URI 원클릭 복사

## [2.2.7] - 2025-10-21

### 수정됨 (Fixed)
- 🐛 **public_access 설정 저장 오류 수정**: 체크박스를 해제해도 저장되지 않던 문제 해결
- 🔧 **sanitize_settings 함수 개선**: `public_access` 값을 올바르게 처리하도록 수정

## [2.2.6] - 2025-10-21

### 개선됨 (Improved)
- 🎨 **스마트 위젯 표시**: `public_access` 옵션이 비활성화되고 사용자가 로그인하지 않은 경우 위젯을 아예 렌더링하지 않음
- ✨ **UX 개선**: 사용할 수 없는 챗봇 위젯이 표시되지 않도록 하여 더 나은 사용자 경험 제공
- 📦 **ZIP 파일 최적화**: Bandizip을 사용하여 파일 크기 46% 감소 (130.59 KB → 84.19 KB)

## [2.2.5] - 2025-10-21

### 추가됨 (Added)
- ✨ **비로그인 사용자 접근 허용 옵션**: 설정 페이지에 "비로그인 사용자 접근 허용" 체크박스 추가
- 🔓 **익명 사용자 지원**: WordPress 계정이 없는 방문자도 챗봇 사용 가능 (기본값: 허용)

### 수정됨 (Fixed)
- 🐛 **로그인 제한 문제 해결**: 비로그인 사용자가 챗봇 사용 시 "로그인이 필요합니다" 메시지가 표시되던 문제 수정
- 🔧 **public_access 옵션 추가**: 관리자가 비로그인 사용자 접근 여부를 제어할 수 있도록 설정 추가

## [2.2.4] - 2025-10-05

### 수정됨 (Fixed)
- 🐛 **Chat 모드 HTTP 404 오류 수정**: API 버전 초기화 누락으로 인한 404 오류 완전 해결
- 🔧 **API 버전 로직 개선**: Agent 모드(v1)와 Chat 모드(2024-08-01-preview) 버전 분리
- 🌐 **다중 제공자 API 최적화**: Azure OpenAI, OpenAI, Gemini, Claude, Grok별 엔드포인트 및 인증 방식 개선

## [2.2.3] - 2025-10-05

### 개선됨 (Improved)
- 📖 **README.md 버전 기록 상세화**: v2.2.3 ~ v1.0.0 전체 버전 기록 추가
- 💡 **FAQ 섹션 강화**: AI 서비스 지원, Chat/Agent 모드 차이, 보안, 테스트 방법 등 추가
- 🚀 **향후 계획 업데이트**: 실현 가능한 로드맵으로 수정
- 📦 **다운로드 링크 추가**: 각 버전별 릴리즈 노트 및 다운로드 링크 제공

## [2.2.2] - 2025-10-05

### 수정됨 (Changed)
- 📝 **Plugin URI 업데이트**: GitHub 저장소 링크로 변경
- 📚 **README 개선**: 최신 릴리즈 링크 및 버전 배지 추가
- 📖 **readme.txt 업데이트**: 전체 변경 이력 및 GitHub 링크 추가

## [2.2.1] - 2025-10-05

### 수정됨 (Fixed)
- 🐛 **엔드포인트 입력 개선**: 사용자가 엔드포인트 입력 시 trailing slash 자동 제거 (blur 이벤트)
- 🎨 **UX 향상**: 실시간 입력 검증으로 404 에러 사전 방지

## [2.2.0] - 2025-10-05

### 추가됨 (Added)
- ✨ **다중 AI 제공자 지원**: Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, 기타 (OpenAI 호환)
- ✨ **동적 UI 업데이트**: 제공자 선택 시 엔드포인트, 모델명, API Key 설명 자동 변경
- ✨ **Agent 모드 테스트 스크립트**: Service Principal 자동 생성 및 권한 관리 포함 (test-agent-mode.sh)
- 🔧 **모드별 오류 메시지**: Chat 모드와 Agent 모드에 맞는 404 에러 안내

### 수정됨 (Fixed)
- 🐛 **Trailing Slash 문제 완전 해결**: 로드/저장/생성자에서 3중 제거로 404 에러 방지
- 🎨 **설정 UI 개선**: 테스트 결과를 버튼 아래 블록으로 이동, 미리보기 통합, 저장 버튼 추가

## [2.1.0] - 2025-10-05

### 추가됨 (Added)
- ✨ **듀얼 모드 지원**: Chat 모드 (API Key) + Agent 모드 (Entra ID)
- ✨ **Assistants API v1**: Azure AI Foundry Assistants API 완벽 통합
- ✨ **Thread 관리**: 대화 컨텍스트 자동 유지 (localStorage 기반)
- ✨ **적응형 폴링**: Run 상태 확인 시 250ms → 1000ms 점진적 증가
- ✨ **Service Principal 인증**: Entra ID OAuth 2.0 Client Credentials
- ✨ **상세한 에러 로깅**: 클라이언트/서버 양측 디버그 로그
- ✨ **연결 테스트 기능**: 설정 페이지에서 즉시 Azure 연결 확인
- ✨ **자동 설정 스크립트**: Azure Cloud Shell에서 원클릭 설정
- 🔐 AES-256 암호화로 API Key/Client Secret 안전하게 저장
- 🎨 색상 및 위젯 위치 커스터마이징
- 📖 편집 가능한 마크다운 사용 가이드
- 🔄 실시간 위젯 미리보기
- 📝 API Key/Client Secret 표시/숨김 토글
- 🎯 Function Calling 완전 지원
- 📱 반응형 채팅 위젯

### 변경됨 (Changed)
- 🔄 **API 버전**: `2024-12-01-preview` → `v1` (모든 리전 호환)
- 🔄 **메시지 조회**: `output_text` + `text` 타입 모두 처리
- 🔄 **Tool Outputs 엔드포인트**: `submit_tool_outputs` → `submitToolOutputs` (camelCase)
- 🔄 **Nonce 검증**: 사용자 정의 nonce → WordPress 표준 `wp_rest` nonce
- 🔄 **Public Access**: 로그인/비로그인 사용자 구분 처리

### 수정됨 (Fixed)
- 🐛 **HTTP 403 에러**: REST API nonce 검증 오류 수정
- 🐛 **HTTP 400 에러**: API 버전 미지원 문제 해결 (v1 사용)
- 🐛 **메시지 미수신**: 응답 메시지 파싱 로직 개선
- 🐛 **Run Timeout**: 대기 시간 최적화 및 상태 체크 개선
- 🐛 **Thread ID 누락**: localStorage 저장 조건 수정
- 🐛 설정 페이지가 제대로 표시되지 않던 문제 해결
- 🐛 가이드 페이지 목차 네비게이션 작동
- 🎨 좌측 사이드바 고정 및 우측 콘텐츠 스크롤 개선

### 보안 (Security)
- � **WordPress REST API 표준 준수**: `wp_rest` nonce 사용
- 🔒 **로그인 사용자 검증**: Nonce 필수 검증
- 🔒 **비로그인 사용자 옵션**: `public_access` 설정으로 제어
- 🔒 **Client Secret 보안**: 한 번만 표시, 재생성 가능
- 🔒 OpenSSL을 이용한 AES-256 암호화
- 🔑 WordPress 고유 키 기반 암호화 키 생성
- ✅ Nonce 검증으로 CSRF 공격 방지

### 성능 (Performance)
- ⚡ **적응형 폴링**: 초기 250ms로 빠른 응답, 최대 1000ms
- ⚡ **Thread 재사용**: 불필요한 Thread 생성 방지
- ⚡ **Token 캐싱**: OAuth token 재사용 (WordPress Transients)

## [1.0.0] - 2025-10-03

### 추가됨 (Added)
- 🎉 초기 릴리즈
- 🤖 Azure AI Foundry 에이전트 통합
- 💬 기본 채팅 위젯
- ⚙️ wp-config.php 기반 설정

---

## 알려진 이슈 (Known Issues)

### v2.0.0
- **Assistants API 지역 제한**: 일부 Azure 리전에서는 Assistants API가 제공되지 않을 수 있음
  - 해결: Chat 모드 사용 또는 다른 리전으로 마이그레이션

---

## 로드맵 (Roadmap)

### v2.1.0 (계획 중)
- [ ] 실시간 스트리밍 응답 (SSE)
- [ ] 대화 내역 관리 대시보드
- [ ] Function Calling UI 설정
- [ ] 음성 입력/출력

### v2.2.0 (계획 중)
- [ ] 다국어 지원 (영어, 일본어)
- [ ] 고급 분석 및 통계
- [ ] A/B 테스트 기능
- [ ] 테마 커스터마이징

---

## 마이그레이션 가이드

### 1.0.0 → 2.0.0

#### Chat 모드 (기존 설정 유지)
- 기존 API Key 자동으로 Chat 모드로 전환
- 추가 작업 불필요

#### Agent 모드 (신규 기능)
1. Azure Cloud Shell에서 setup 스크립트 실행
2. Service Principal 정보 획득
3. WordPress 설정에서 "Agent 모드" 선택
4. 정보 입력 후 저장
