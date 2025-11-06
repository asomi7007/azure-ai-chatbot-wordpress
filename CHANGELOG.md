# 변경 이력

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
