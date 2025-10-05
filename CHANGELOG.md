# 변경 이력

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
