=== Azure AI Chatbot ===
Contributors: eldensolution
Tags: azure, ai, chatbot, chat, ai-assistant
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 3.0.10
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Azure AI Foundry 에이전트를 WordPress에 통합하는 강력한 채팅 위젯 플러그인

== Description ==

Azure AI Chatbot은 Azure AI Foundry의 강력한 AI 에이전트를 WordPress 웹사이트에 쉽게 통합할 수 있는 플러그인입니다.

= 주요 기능 =

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

**빠른 시작: Azure 설정값 자동으로 가져오기**

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

= Azure 설정을 쉽게 할 수 있나요? =

네! Azure Cloud Shell에서 자동 설정 스크립트를 실행하세요:

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
