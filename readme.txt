=== Azure AI Chatbot ===
Contributors: eldensolution
Tags: azure, ai, chatbot, chat, ai-assistant
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 3.0.51
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Azure AI Foundry 에이전트를 WordPress에 통합하는 강력한 채팅 위젯 플러그인

== Description ==

Azure AI Chatbot은 Azure AI Foundry의 강력한 AI 에이전트를 WordPress 웹사이트에 쉽게 통합할 수 있는 플러그인입니다. OAuth 2.0 자동 설정 기능으로 클릭 몇 번만으로 완전한 AI 챗봇을 구축할 수 있습니다.

= 주요 기능 =

* **OAuth 2.0 자동 설정**: Azure 승인 → 리소스 선택 → 설정 완료 (수동 입력 최소화)
* **신규/기존 리소스 완전 지원**: 새로 생성하거나 기존 AI 리소스 모두 자동 설정
* **API Key 자동 조회**: Azure Management API를 통한 자동 조회 및 암호화 저장
* **듀얼 모드**: Chat 모드 (간단) + Agent 모드 (고급 Function Calling)
* 관리자 페이지에서 모든 설정 가능
* API Key AES-256 암호화 저장
* 색상 및 위젯 위치 커스터마이징
* 연결 테스트 기능
* 편집 가능한 마크다운 사용 가이드
* 실시간 위젯 미리보기
* Function Calling 완전 지원
* 반응형 디자인

= 시스템 요구사항 =

* WordPress 6.0 이상
* PHP 7.4 이상
* Azure AI Foundry 계정
* Azure AI 에이전트

== Installation ==

= 자동 설치 =

1. WordPress 관리자 페이지에서 플러그인 → 새로 추가
2. "Azure AI Chatbot" 검색
3. 지금 설치 → 활성화

= 수동 설치 =

1. 플러그인 ZIP 파일 다운로드
2. WordPress 관리자 → 플러그인 → 새로 추가 → 플러그인 업로드
3. ZIP 파일 선택 후 지금 설치
4. 플러그인 활성화

= 설정 =

**🚀 OAuth 2.0 자동 설정 (추천)**

1. WordPress 관리자 → AI Chatbot → OAuth 자동 설정
2. "Azure 승인" 버튼 클릭 → Microsoft 로그인
3. 리소스 그룹 선택 (기존 선택 또는 새로 생성)
4. AI Foundry Project 선택 (기존 선택 또는 새로 생성)
5. Chat/Agent 모드 선택
6. 자동으로 모든 설정 완료!

**자동 설정 장점:**
- ✅ API Key 자동 조회 및 암호화 저장
- ✅ 엔드포인트, 배포 이름 자동 설정
- ✅ Agent ID 자동 생성 또는 기존 Agent 선택
- ✅ 신규/기존 리소스 모두 완전 지원
- ✅ 클릭 몇 번으로 설정 완료

**빠른 시작: Azure 설정값 자동으로 가져오기 (구형 방식)**

Azure Cloud Shell (https://shell.azure.com)에서 다음 명령어 중 하나를 실행하세요:

Chat 모드 (간단):
`curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash`

Agent 모드 (고급):
`curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash`

스크립트가 자동으로:
- Azure 리소스 검색
- API Key 및 엔드포인트 추출
- 배포된 모델 확인
- 연결 테스트 실행
- WordPress 설정값 출력

**수동 설정 방법:**

1. WordPress 관리자 → AI Chatbot → 설정
2. 모드 선택 (Chat 또는 Agent)
3. Azure 정보 입력:
   * Chat 모드: API Key, 엔드포인트, 배포 이름
   * Agent 모드: Client ID, Client Secret, Tenant ID, 에이전트 ID
4. 연결 테스트 버튼으로 확인
5. 위젯 활성화 및 디자인 설정
6. 비로그인 사용자 접근 허용 여부 선택
7. 변경사항 저장

자세한 가이드: https://github.com/asomi7007/azure-ai-chatbot-wordpress/blob/main/docs/AZURE_SETUP.md

== Frequently Asked Questions ==

= OAuth 자동 설정을 사용할 수 있나요? =

네! v3.0.14부터 OAuth 2.0 자동 설정을 지원합니다:

1. WordPress 관리자 → AI Chatbot → OAuth 자동 설정
2. "Azure 승인" 버튼 클릭
3. 리소스 선택 또는 새로 생성
4. 모든 설정 자동 완료!

기존 AI 리소스도 완전 지원하여 API Key까지 자동으로 조회해서 저장합니다.

= Azure 설정을 쉽게 할 수 있나요? =

네! 두 가지 방법이 있습니다:

**방법 1: OAuth 자동 설정 (추천)**
WordPress 관리자에서 OAuth 자동 설정 기능 사용

**방법 2: Azure Cloud Shell 스크립트**

Chat 모드: `curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-chat-mode.sh | bash`
Agent 모드: `curl -s https://raw.githubusercontent.com/asomi7007/azure-ai-chatbot-wordpress/main/test-agent-mode.sh | bash`

스크립트가 모든 필요한 정보를 자동으로 찾아서 출력해줍니다.

= Azure AI Foundry 계정이 필요한가요? =

네, Azure AI Foundry에서 에이전트를 생성하거나 Azure OpenAI 모델을 배포해야 합니다.

= Chat 모드와 Agent 모드의 차이는 무엇인가요? =

Chat 모드: 간단한 대화형 챗봇 (API Key만 필요)
Agent 모드: 고급 기능 지원 (Function Calling, RAG, 파일 업로드 등, Entra ID 인증 필요)

= API Key는 안전하게 저장되나요? =

네, API Key와 Client Secret은 AES-256 암호화로 안전하게 저장됩니다.

= 비로그인 사용자도 챗봇을 사용할 수 있나요? =

네, 설정 페이지에서 "비로그인 사용자 접근 허용" 옵션을 체크하면 모든 방문자가 사용할 수 있습니다. (기본값: 허용)

= 무료로 사용할 수 있나요? =

플러그인은 무료입니다. Azure AI Foundry 사용료는 별도로 발생할 수 있습니다.

= 다국어를 지원하나요? =

현재 한국어와 영어를 지원하며, 추가 언어는 향후 지원 예정입니다.

== Screenshots ==

1. 설정 페이지 - Azure AI 연결 설정
2. 외관 설정 - 색상 및 위치 커스터마이징
3. 채팅 위젯 - 웹사이트에 표시되는 모습
4. 사용 가이드 - 편집 가능한 마크다운 가이드

== Changelog ==

= 3.0.41 - 2025-11-08 =
* 수정: OAuth 자동 설정 완료 후 선택한 Chat/Agent 모드 유지
* 수정: Chat 모드로 시작해도 Agent 설정 데이터 저장 보장
* 개선: 모드 전환 시 명확한 성공/경고 메시지 표시
* 개선: 자동 설정 안정성 및 사용자 경험 향상

= 3.0.40 - 2025-11-08 =
* 개선: 관리자 UI에서 모든 "V2" 텍스트 제거
* 개선: README 및 가이드 문서 전면 개편
* 개선: 한영 번역 명확성 향상

= 3.0.17 - 2025-11-07 =
* 수정: Resource Group 생성 실패 시 상세 에러 메시지 표시
* 수정: 토큰 만료 감지 및 재인증 안내 개선
* 추가: AJAX 요청 실패 시 상세 디버깅 정보 표시
* 개선: 에러 처리 로직 전면 개선 및 사용자 피드백 강화

= 3.0.16 - 2025-11-07 =
* 추가: 기존 리소스 선택 시 설정 자동 채우기 완전 구현
* 추가: 배포 목록 자동 조회 (기존 AI Foundry Project에서)
* 추가: API Key 자동 조회 및 암호화 저장 (Azure Management API 활용)
* 추가: 기존 Project에서 Agent 선택 또는 새로 생성 지원
* 개선: 신규/기존 리소스 모두 완전 자동 설정 지원
* 개선: OAuth 승인 → 리소스 선택 → 설정 완료 (수동 입력 최소화)

= 3.0.15 - 2025-11-07 =
* 추가: 자동 설정 완료 후 WordPress 설정 자동 저장
* 추가: Chat/Agent 모드 설정 필드 자동 채우기
* 추가: 엔드포인트, 배포 이름, Agent ID 자동 저장
* 추가: OAuth 설정에서 보안 정보 자동 연동

= 3.0.14 - 2025-11-07 =
* 추가: URL 파라미터 기반 OAuth 탭 자동 표시
* 개선: Admin Consent 완료 후 자동으로 OAuth 설정 탭 활성화
* 수정: 리다이렉트 URL 처리 최적화

= 3.0.13 - 2025-11-07 =
* 수정: Resource Group 선택 모달의 Promise 처리 개선
* 수정: 비동기 처리 오류로 인한 무한 로딩 문제 해결
* 개선: 모달 창 응답성 향상

= 3.0.12 - 2025-11-07 =
* 수정: OAuth 리다이렉트를 oauth-auto-setup 탭으로 올바르게 처리
* 수정: JavaScript 오류 방지 및 안정성 향상
* 개선: 팝업 창과 부모 창 간 통신 최적화

= 3.0.11 - 2025-11-07 =
* 추가: OAuth → Agent Mode 자동 연동 기능
* 추가: localStorage 기반 세션 관리
* 수정: 경고 메시지 표시 조건 개선
* 개선: 자동 설정 플로우 완성도 향상

= 3.0.10 - 2025-11-07 =
* 수정: Admin Consent URL 인코딩 문제 해결
* 수정: 리다이렉트 URL 처리 개선
* 개선: OAuth 설정 안정성 향상

= 3.0.1 - 2025-11-07 =
* 추가: OAuth 2.0 자동 설정 기능 (클릭 몇 번으로 설정 완료)
* 추가: Azure AI 리소스 자동 생성 및 설정
* 추가: Admin Consent 자동 처리
* 보안: OAuth 토큰 보안 관리

= 3.0.0 - 2025-11-07 =
* 메이저 업데이트: OAuth 2.0 자동 설정 시스템 도입
* 추가: Azure 승인 기반 자동 설정 UI
* 추가: 리소스 그룹 자동 생성/선택 기능
* 추가: AI Foundry Project 자동 생성 기능
* 개선: 기존 수동 설정과 자동 설정 병행 지원

= 2.2.7 - 2025-10-21 =
* 수정: public_access 설정 저장 오류 해결 (체크박스 해제가 저장되지 않던 문제)
* 수정: sanitize_settings 함수에 public_access 처리 로직 추가

= 2.2.6 - 2025-10-21 =
* 개선: public_access 비활성화 시 비로그인 사용자에게 위젯 자체를 표시하지 않음
* 개선: 사용 불가능한 챗봇 위젯이 보이지 않도록 UX 향상
* 최적화: Bandizip 사용으로 ZIP 파일 크기 46% 감소 (84.19 KB)

= 2.2.5 - 2025-10-21 =
* 추가: 비로그인 사용자 접근 허용 옵션 (설정 페이지)
* 수정: 비로그인 사용자가 챗봇 사용 시 "로그인이 필요합니다" 메시지 제거
* 개선: public_access 옵션으로 관리자가 접근 제어 가능 (기본값: 허용)

= 2.2.3 - 2025-10-05 =
* 개선: README.md 버전 기록 상세화 (v2.2.3 ~ v1.0.0 전체 기록)
* 개선: FAQ 섹션 대폭 강화 (AI 서비스, 모드 차이, 보안 등)
* 개선: 향후 계획 현실화
* 추가: 각 버전별 다운로드 링크 제공

= 3.0.51 - 2025-11-14 =
* 개선: Mode 선택 문제 진단을 위한 디버깅 로깅 추가
* 추가: 페이지 로드 시 DB/localStorage/라디오 버튼 상태 확인 로그
* 추가: 라디오 버튼 변경 이벤트 추적 로그
* 추가: OAuth 버튼 클릭 시 모든 라디오 버튼 상태 검증 로그
* 개선: Agent 모드 선택이 Chat으로 변경되는 문제의 근본 원인 파악용 상세 로깅

= 3.0.50 - 2025-11-14 =
* 수정: [Critical] OAuth 콜백 페이지에서 삭제된 azure_ai_chatbot_operation_mode 옵션 참조 버그 완전 수정
* 수정: [Critical] 설정 페이지에서 삭제된 옵션 확인 로직 제거
* 개선: Agent 조회 실패 시 PHP 메시지를 사용자에게 alert로 표시
* 개선: azure_chatbot_settings['mode'] 단일 소스 완전 통일
* 수정: OAuth 인증 후 Mode 유지 버그 완전 해결
* 수정: localStorage 우선순위 개선 및 삭제 시점 조정
* 개선: 페이지 로드 시 operationMode에 따른 UI 자동 초기화

= 3.0.49 - 2025-01-13 =
* 수정: Agent API 엔드포인트를 Microsoft Learn 문서 기준으로 수정 (/agents/v1.0/projects/{name}/agents)
* 개선: Agent 응답 데이터 파싱 로직 개선 (value/data/직접배열 형식 모두 지원)
* 추가: Agent API URL 상세 로깅 추가
* 개선: 빈 Agent 목록에 대한 사용자 친화적 메시지 제공

= 3.0.48 - 2025-01-12 =
* 수정: [Critical] OAuth Client Secret 복호화 누락 버그 (AADSTS7000215 오류 완전 해결)
* 수정: [Critical] OAuth 인증 후 Agent 모드가 Chat 모드로 변경되는 버그 수정
* 수정: [Critical] Azure OpenAI 리소스에서 Agent 조회 시도하여 빈 결과 반환하는 버그 수정
* 추가: Client Secret 형식 검증 (GUID/Secret ID 자동 감지 및 경고)
* 추가: 리소스 타입 검증 (AI Foundry Project만 Agent 조회)
* 개선: Operation Mode 단일 소스 통일 (azure_chatbot_settings['mode'])
* 개선: OAuth 토큰 요청 에러 로깅 강화 및 단계별 해결 가이드

= 3.0.47 - 2025-01-23 =
* 개선: 중앙화된 암호화 아키텍처로 완전 리팩토링 (Single Source of Truth)
* 추가: 버전 관리 암호화 시스템 (v2 format: base64:v2:IV+encrypted_data)
* 추가: 레거시 암호화 값 자동 마이그레이션 (AES/Base64 자동 감지 및 변환)
* 추가: 8단계 암호화 시스템 검증 도구 (연결 테스트 통합)
* 추가: 완전한 플러그인 재설정 메커니즘 (DB 설정, Transients, Admin Notice)
* 수정: Mode 선택 레이스 컨디션 완전 제거 (라디오 버튼 단일 이벤트 소스)
* 개선: 코드 품질 - 헬퍼 함수로 코드 중복 60% 감소 (verify_ajax_permissions, delete_transients_by_pattern, mask_sensitive_value)
* 개선: DB 쿼리 최적화 (prepare + esc_like)
* 보안: SHA-256 키 파생 (WordPress AUTH_KEY + SECURE_AUTH_KEY + LOGGED_IN_KEY + NONCE_KEY)
* 보안: AES-256-CBC 암호화 (OpenSSL 미지원 시 base64 v1 fallback)

= 2.2.2 - 2025-10-05 =
* 변경: Plugin URI를 GitHub 저장소 링크로 업데이트
* 개선: README에 최신 릴리즈 링크 및 버전 배지 추가
* 개선: readme.txt에 전체 변경 이력 및 GitHub 링크 추가

= 2.2.1 - 2025-10-05 =
* 수정: 엔드포인트 입력 시 trailing slash 자동 제거 (blur 이벤트)
* 개선: 실시간 입력 검증으로 404 에러 사전 방지

= 2.2.0 - 2025-10-05 =
* 추가: 다중 AI 제공자 지원 (Azure OpenAI, OpenAI, Google Gemini, Anthropic Claude, xAI Grok, 기타)
* 추가: 동적 UI - 제공자 선택 시 엔드포인트/모델명/API Key 설명 자동 변경
* 추가: Agent 모드 테스트 스크립트 (Service Principal 자동 생성 포함)
* 추가: 모드별 오류 메시지 (Chat/Agent 구분)
* 수정: Trailing slash 3중 제거 (로드/저장/생성자)
* 개선: 설정 UI (테스트 결과 위치, 미리보기 통합, 저장 버튼)

= 2.1.0 - 2025-10-05 =
* 추가: 듀얼 모드 지원 (Chat 모드 + Agent 모드)
* 추가: Azure AI Foundry Assistants API v1 통합
* 추가: Entra ID OAuth 2.0 Client Credentials 인증
* 추가: Thread 관리 및 적응형 폴링
* 추가: 연결 테스트 및 자동 설정 스크립트
* 보안: AES-256 Client Secret 암호화

= 2.0.0 - 2025-10-04 =
* 추가: 관리자 페이지에서 모든 설정 가능
* 추가: AES-256 API Key 암호화
* 추가: 색상 및 위젯 위치 커스터마이징
* 추가: Azure AI 연결 테스트 기능

= 1.0.0 - 2025-10-03 =
* 초기 릴리즈

== Upgrade Notice ==

= 3.0.48 =
긴급 업데이트: OAuth Client Secret 복호화 누락 버그 수정. OAuth 인증 실패(AADSTS7000215) 해결. 즉시 업그레이드 권장.

= 3.0.47 =
중요 개선: 중앙화된 암호화 아키텍처 완전 리팩토링, 레거시 값 자동 마이그레이션, 코드 품질 60% 향상.

= 3.0.41 =
중요 수정: OAuth 자동 설정 모드 지속성 및 Agent 데이터 캡처 보장. 자동 설정 사용 시 업그레이드 권장.

= 3.0.40 =
UI 개선: 모든 V2 표시 제거, 문서 개선. 기존 설정은 그대로 유지됩니다.

= 3.0.39 =
중요 업데이트: Chat 엔드포인트 .openai.azure.com 형식 자동 변환. Agent 2개 이상 시 선택 UI 추가.

= 3.0.38 =
중요 업데이트: 모드 무관 양방향 자동 설정. Chat/Agent 어떤 모드 선택해도 양쪽 설정 모두 자동 저장.

= 3.0.17 =
긴급 수정: Resource Group 생성 실패 및 토큰 만료 문제 디버깅 개선. 에러 메시지 상세화.

= 3.0.16 =
중요 업데이트: 기존 AI 리소스 선택 시에도 설정 자동 채우기 완전 지원. API Key까지 자동 조회하여 완전 자동화 구현.

= 3.0.15 =
중요 업데이트: OAuth 자동 설정 완료 후 WordPress 설정 자동 저장 기능 추가. 수동 입력 최소화.

= 3.0.1 =
메이저 업데이트: OAuth 2.0 자동 설정 기능 추가. 클릭 몇 번으로 Azure AI 챗봇 완전 설정.

= 3.0.0 =
메이저 업데이트: OAuth 2.0 자동 설정 시스템 도입. Azure 승인 기반 완전 자동 설정.

= 2.2.1 =
Hotfix: 엔드포인트 입력 시 trailing slash 자동 제거로 404 에러 방지

= 2.2.0 =
주요 업데이트: 6개 AI 제공자 지원, Agent 모드 테스트 스크립트 추가

= 2.1.0 =
주요 업데이트: Chat/Agent 듀얼 모드 지원, Entra ID 인증 추가

== Additional Info ==

* GitHub: https://github.com/asomi7007/azure-ai-chatbot-wordpress
* 최신 릴리즈: https://github.com/asomi7007/azure-ai-chatbot-wordpress/releases/latest
* 문제 보고: https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues

문제 신고: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)
