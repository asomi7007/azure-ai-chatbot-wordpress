# v3.0.51 - Debugging Improvements for Mode Selection Issues

## 🔍 디버깅 개선: Mode 선택 문제 진단 로깅 추가

### 목적
사용자가 Agent 모드를 선택해도 Chat 모드로 변경되는 문제의 **근본 원인 파악**을 위한 상세 디버깅 로그 추가

### 추가된 디버그 로그 위치

#### 1. 페이지 로드 시 (lines 860-865)
- DB에서 읽은 mode 값
- localStorage에 저장된 mode 값
- 라디오 버튼에 설정된 최종 값 확인

```javascript
console.log('[DEBUG] DB mode value:', dbMode);
console.log('[DEBUG] localStorage value:', localStorage.getItem('azure_oauth_operation_mode'));
console.log('[DEBUG] Radio button set - verifying:', $('input[name="oauth_mode"]:checked').val());
```

#### 2. 라디오 버튼 변경 시 (lines 945-946)
- 이전 mode와 새로운 mode 추적
- 어떤 라디오 버튼이 변경 이벤트를 트리거했는지 확인

```javascript
console.log('[DEBUG] Radio button changed - from:', previousMode, 'to:', mode);
console.log('[DEBUG] Radio button that triggered change:', this.value, 'checked:', this.checked);
```

#### 3. OAuth 버튼 클릭 시 (lines 761-773)
- 페이지에 있는 모든 라디오 버튼의 상태 확인
- 어떤 값이 실제로 선택되었는지 확인
- 전역 operationMode 변수와 라디오 버튼 값 비교
- localStorage에 저장되는 최종 값 확인

```javascript
console.log('[DEBUG] Total radio buttons found:', allRadios.length);
allRadios.each(function(index) {
    console.log('[DEBUG] Radio', index, '- value:', jQuery(this).val(), '- checked:', jQuery(this).prop('checked'));
});
console.log('[DEBUG] Selected mode from :checked selector:', selectedMode);
console.log('[DEBUG] Global operationMode variable:', operationMode);
console.log('[Auto Setup] ✅ Saving operation mode to localStorage before OAuth:', selectedMode);
```

### 예상되는 디버그 출력 흐름

#### 정상 케이스 (Agent 모드 선택):
```
[Auto Setup] Page loaded
[DEBUG] DB mode value: chat
[DEBUG] localStorage value: null
[Auto Setup] Initializing UI with mode: chat
[DEBUG] Radio button set - verifying: chat
[DEBUG] Radio button changed - from: chat to: agent
[DEBUG] Radio button that triggered change: agent checked: true
[Auto Setup] Operation mode 저장 완료: agent
[DEBUG] Total radio buttons found: 2
[DEBUG] Radio 0 - value: chat - checked: false
[DEBUG] Radio 1 - value: agent - checked: true
[DEBUG] Selected mode from :checked selector: agent
[DEBUG] Global operationMode variable: agent
[Auto Setup] ✅ Saving operation mode to localStorage before OAuth: agent
```

#### 문제 케이스 (예상):
```
[Auto Setup] Page loaded
[DEBUG] DB mode value: agent
[DEBUG] localStorage value: chat  ← 🚨 이전 값이 남아있음
[Auto Setup] Initializing UI with mode: chat  ← 🚨 localStorage가 DB보다 우선순위 높음
[DEBUG] Radio button set - verifying: chat  ← 🚨 라디오 버튼이 chat으로 설정됨
[DEBUG] Selected mode from :checked selector: chat  ← 🚨 여전히 chat?
```

### 진단 가능한 문제들

이 로그를 통해 다음을 확인할 수 있습니다:
- ✅ 라디오 버튼 HTML이 제대로 렌더링되는지
- ✅ 라디오 버튼 변경 이벤트가 제대로 발생하는지
- ✅ localStorage와 DB 값의 우선순위 문제가 있는지
- ✅ 전역 변수와 실제 DOM 상태가 일치하는지

### 파일 변경 내역
- ✅ `azure-ai-chatbot.php`: Version 3.0.51
- ✅ `templates/oauth-auto-setup.php`: 디버그 로깅 추가
- ✅ `CHANGELOG.md`: v3.0.51 디버깅 개선 상세 내역
- ✅ `README.md`, `README-ko.md`: 버전 배지 3.0.51로 업데이트
- ✅ `readme.txt`: Stable tag 및 변경 이력 3.0.51로 업데이트

### 설치 방법
1. ZIP 파일 다운로드: `azure-ai-chatbot-wordpress-3.0.51.zip`
2. WordPress 관리자 → 플러그인 → 새로 추가 → 플러그인 업로드
3. ZIP 파일 선택 후 설치
4. 플러그인 활성화
5. 브라우저 개발자 도구(F12) → Console 탭에서 디버그 로그 확인

### 사용 방법
1. OAuth 자동 설정 페이지 접속
2. 브라우저 개발자 도구(F12) 열기
3. Console 탭 확인
4. Agent 모드 선택
5. Azure 승인 버튼 클릭
6. 콘솔 로그에서 `[DEBUG]` 태그로 시작하는 로그 확인
7. Mode가 어느 시점에서 변경되는지 추적

---

**Full Changelog**: https://github.com/asomi7007/azure-ai-chatbot-wordpress/blob/main/CHANGELOG.md
