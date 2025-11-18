# v3.0.52 - Enhanced Debugging & Stability Improvements

## 🔍 디버깅 대폭 강화 (Enhanced Debugging)

### 주요 개선사항
사용자가 보고한 문제 해결을 위한 상세한 디버그 로깅 추가:
- ⚠️ "리소스 그룹 목록을 못찾네" 문제 진단
- ⚠️ 라디오 버튼 값이 `undefined`로 표시되는 문제 추적
- ⚠️ Mode 선택이 제대로 작동하지 않는 문제 분석

### F12 콘솔에서 확인 가능한 로그

#### 1. 페이지 로드 시
라디오 버튼의 DOM 존재 여부와 상태를 상세히 확인:
```javascript
[Auto Setup] ========================================
[Auto Setup] Page loaded - Checking for saved settings
[Auto Setup] ========================================
[DEBUG] Total radio buttons in DOM: 2
[DEBUG] Radio 0: {value: 'chat', checked: true, visible: true, disabled: false}
[DEBUG] Radio 1: {value: 'agent', checked: false, visible: true, disabled: false}
[DEBUG] DB mode value: chat
[DEBUG] localStorage value: chat
[DEBUG] Current operationMode variable: chat
[DEBUG] ✅ Radio button successfully set to: chat
```

#### 2. 라디오 버튼 변경 시
변경 이벤트와 저장 프로세스 추적:
```javascript
[DEBUG] Radio button change event triggered
[DEBUG] Event target: <input type="radio" value="agent">
[DEBUG] Previous mode: chat
[DEBUG] New mode: agent
[DEBUG] ✅ Mode saved to localStorage: agent
[DEBUG] ✅ Mode saved to sessionStorage: agent
```

#### 3. OAuth 버튼 클릭 시
모든 라디오 버튼 상태를 검증:
```javascript
[DEBUG] ========== OAuth Button Clicked ==========
[DEBUG] Total radio buttons found: 2
[DEBUG] Radio 0 - value: chat, checked: false, visible: true
[DEBUG] Radio 1 - value: agent, checked: true, visible: true
[DEBUG] Selected mode from :checked selector: agent
[DEBUG] Global operationMode variable: agent
[DEBUG] ✅ Mode saved to localStorage before OAuth: agent
[DEBUG] ✅ Mode saved to sessionStorage before OAuth: agent
```

#### 4. 리소스 조회 시
각 단계별 상세 로그:
```javascript
[Auto Setup] [Step 1] Loading subscriptions...
[Auto Setup] [Subscription] Total: 1
[Auto Setup] [Subscription] Selected: Visual Studio Enterprise Subscription

[Auto Setup] [Step 2] Loading resource groups...
[Auto Setup] [Resource Group] Total found: 3
[Auto Setup] [Resource Group] RG 0: {name: 'rg-prod', location: 'koreacentral'}

[Auto Setup] [Step 3] Loading AI resources...
[Auto Setup] [Resource] Mode check: agent
[Auto Setup] [Resource] Calling ajax_get_resources with: {mode: 'agent', ...}
[Auto Setup] [Resource] Total found: 2
```

### 안정성 개선
- ✅ 라디오 버튼 DOM 검증 로직 강화
- ✅ localStorage/sessionStorage 동시 저장으로 세션 유지 개선
- ✅ 전역 변수와 DOM 상태 동기화 보장
- ✅ 모든 주요 흐름에 try-catch 추가

### 디버깅 방법
1. WordPress 관리자 → AI Chatbot → OAuth 자동 설정
2. F12 개발자 도구 열기
3. Console 탭 확인
4. `[Auto Setup]` 또는 `[DEBUG]` 태그로 시작하는 로그 확인
5. 문제 발생 시점의 로그를 GitHub Issue에 첨부

### 파일 변경 내역
- ✅ `azure-ai-chatbot.php`: Version 3.0.52
- ✅ `templates/oauth-auto-setup.php`: 디버그 로깅 대폭 강화
- ✅ `CHANGELOG.md`: v3.0.52 변경사항 추가
- ✅ `README.md`, `README-ko.md`: 버전 배지 3.0.52로 업데이트
- ✅ `readme.txt`: Stable tag 및 변경 이력 3.0.52로 업데이트

### 설치 방법
1. ZIP 파일 다운로드: `azure-ai-chatbot-wordpress-3.0.52.zip`
2. WordPress 관리자 → 플러그인 → 새로 추가 → 플러그인 업로드
3. ZIP 파일 선택 후 설치
4. 플러그인 활성화
5. F12 개발자 도구 → Console 탭에서 상세 로그 확인

---

**Full Changelog**: https://github.com/asomi7007/azure-ai-chatbot-wordpress/blob/main/CHANGELOG.md
