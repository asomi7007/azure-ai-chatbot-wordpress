# v3.0.55 - Critical Bug Fix: Radio Button Visibility

## 🔧 긴급 버그 수정 (Critical Bug Fix)

### 문제 상황
v3.0.54에서 라디오 버튼을 페이지 맨 위로 이동했지만, 여전히 **라디오 버튼이 보이지 않는** 문제가 발생했습니다:

```javascript
[DEBUG] Radio 0: {value: 'chat', checked: false, visible: false}  ← visible: false!
[DEBUG] Radio 1: {value: 'agent', checked: true, visible: false}
```

### 근본 원인

**문제의 코드 구조:**
```php
<?php if (!$is_configured): ?>
    <!-- ✅ 여기에 모드 선택 박스가 있었음 -->
    <div class="notice notice-info inline">
        <h3>🎯 모드 선택</h3>
        <input type="radio" name="oauth_mode" value="chat" />
        <input type="radio" name="oauth_mode" value="agent" />
    </div>
<?php endif; ?>
```

**왜 안 보였나:**
- `is_configured()` 함수는 `azure_chatbot_oauth_*` 옵션을 체크
- OAuth 자동 설정은 `azure_chatbot_settings`에 저장
- 옵션 이름이 달라서 `$is_configured`가 항상 `false`
- 결과: 모드 선택 박스가 HTML에 렌더링조차 되지 않음 ❌

### 해결 방법

**1. 모드 선택 박스를 조건문 밖으로 이동**

```php
<div class="inside">
    <!-- ✅ 항상 표시 (조건문 밖) -->
    <div class="notice notice-info inline" style="...">
        <h3>🎯 모드 선택</h3>
        <p>
            <input type="radio" name="oauth_mode" value="chat" ... />
            <label>☑ Chat 모드</label>
            
            <input type="radio" name="oauth_mode" value="agent" ... />
            <label>☑ Agent 모드</label>
        </p>
    </div>

    <?php if (!$is_configured): ?>
        <!-- Step 1: OAuth 설정 입력 -->
    <?php else: ?>
        <!-- Step 2: 리소스 선택 -->
    <?php endif; ?>
</div>
```

**2. 중복된 모드 선택 박스 제거**

이전에는 두 군데에 모드 선택 박스가 있었는데, 하나만 남기고 제거했습니다.

**3. Agent 404 에러 메시지 개선**

**이전 메시지:**
```
Agent 목록 조회 실패 (HTTP 404): Resource not found
```

**개선된 메시지:**
```
ℹ️ 이 리소스는 Azure OpenAI (CognitiveServices)입니다. 
Agent를 사용하려면 AI Foundry Hub 리소스를 선택하세요.
```

### 결과

**이전 (v3.0.54):**
```javascript
[DEBUG] Total radio buttons in DOM: 0  ← HTML에 없음!
[DEBUG] Radio 0: {visible: false}
```

**수정 후 (v3.0.55):**
```javascript
[DEBUG] Total radio buttons in DOM: 2  ← ✅ HTML에 있음!
[DEBUG] Radio 0: {value: 'chat', checked: true, visible: true}   ← ✅ 보임!
[DEBUG] Radio 1: {value: 'agent', checked: false, visible: true}  ← ✅ 보임!
```

## 📋 변경 내역 (Changes)

### 수정된 파일
- ✅ `azure-ai-chatbot.php`: Version 3.0.55
- ✅ `templates/oauth-auto-setup.php`:
  - Lines 84-104: 모드 선택 박스를 조건문 밖으로 이동 (항상 표시)
  - Lines 292-294: 중복된 모드 선택 박스 제거
- ✅ `includes/class-azure-oauth.php`:
  - Lines 978-996: Agent 404 에러 메시지 개선
- ✅ `CHANGELOG.md`: v3.0.55 상세 내역
- ✅ `README.md`, `README-ko.md`: 버전 배지 3.0.55
- ✅ `readme.txt`: Stable tag 및 changelog 업데이트

## 📦 설치 방법 (Installation)

1. ZIP 파일 다운로드: `azure-ai-chatbot-wordpress-3.0.55.zip` (204.34 KB)
2. WordPress 관리자 → 플러그인 → 새로 추가 → 플러그인 업로드
3. ZIP 파일 선택 후 설치
4. 플러그인 활성화

## 🔄 업그레이드 방법 (Upgrade)

이전 버전에서 자동 업데이트 또는 수동으로 ZIP 파일을 업로드하여 업그레이드하세요.

## 🎯 확인 방법

### 1. 라디오 버튼이 보이는지 확인
1. WordPress 관리자 → AI Chatbot → OAuth 자동 설정
2. 페이지 맨 위에 **파란색 박스**가 보여야 함
3. 그 안에 "Chat 모드"와 "Agent 모드" 라디오 버튼이 보여야 함

### 2. 콘솔 로그 확인 (F12)
```javascript
[DEBUG] Total radio buttons in DOM: 2  ← ✅ 2개여야 함
[DEBUG] Radio 0: {value: 'chat', visible: true}   ← ✅ visible: true
[DEBUG] Radio 1: {value: 'agent', visible: true}  ← ✅ visible: true
```

### 3. CognitiveServices 리소스 선택 시
Agent 모드를 선택하고 CognitiveServices (Azure OpenAI) 리소스를 선택하면:
```
ℹ️ 이 리소스는 Azure OpenAI (CognitiveServices)입니다. 
Agent를 사용하려면 AI Foundry Hub 리소스를 선택하세요.
```
이 메시지가 표시되어야 함.

## 🐛 이슈 보고 (Report Issues)

문제가 발생하면 [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)에 보고해 주세요.

## 📚 관련 문서

- [전체 변경 이력](https://github.com/asomi7007/azure-ai-chatbot-wordpress/blob/main/CHANGELOG.md)
- [사용자 가이드](https://github.com/asomi7007/azure-ai-chatbot-wordpress#readme)

---

**Full Changelog**: https://github.com/asomi7007/azure-ai-chatbot-wordpress/compare/v3.0.54...v3.0.55

## 감사합니다!

이번 업데이트로 라디오 버튼 가시성 문제가 **완전히 해결**되었습니다. 더 이상 라디오 버튼이 보이지 않는 문제가 발생하지 않습니다! 🎉
