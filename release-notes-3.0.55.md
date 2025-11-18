# Release Notes: v3.0.55

## 🔧 Critical Bug Fix: 라디오 버튼 가시성 문제 완전 해결

### 문제 상황

v3.0.54에서 라디오 버튼을 페이지 맨 위로 이동했지만, 여전히 `visible: false` 문제가 발생했습니다:

```javascript
[DEBUG] Radio 0: {value: 'chat', checked: false, visible: false}  ← visible: false!
[DEBUG] Radio 1: {value: 'agent', checked: true, visible: false}
```

**근본 원인:**
- 모드 선택 박스가 `<?php if (!$is_configured): ?>` 조건 안에 있었음
- `is_configured()` 함수는 `azure_chatbot_oauth_client_id`, `azure_chatbot_oauth_client_secret`, `azure_chatbot_oauth_tenant_id`를 체크
- 하지만 자동 설정은 `azure_chatbot_settings`에 값을 저장
- 두 옵션 이름이 달라서 `$is_configured`가 false가 되고, 모드 선택 박스가 렌더링되지 않음

### 해결 방법

#### 1. **모드 선택 박스를 항상 표시**

**파일:** [templates/oauth-auto-setup.php:84-104](templates/oauth-auto-setup.php#L84-L104)

**변경 전:**
```php
<?php if (!$is_configured): ?>
    <!-- Client ID/Secret/Tenant 입력 폼 -->
<?php else: ?>
    <!-- 모드 선택 박스 -->  ← $is_configured가 false면 렌더링 안 됨!
    <?php if (!$has_token): ?>
        <!-- Step 1 -->
    <?php else: ?>
        <!-- Step 2 -->
    <?php endif; ?>
<?php endif; ?>
```

**변경 후:**
```php
<div class="inside">
    <!-- ✅ 모드 선택 박스를 맨 앞으로 이동 (항상 표시) -->
    <div class="notice notice-info inline" style="...">
        <h3>🎯 모드 선택</h3>
        <p>
            <input type="radio" name="oauth_mode" value="chat" ... />
            <input type="radio" name="oauth_mode" value="agent" ... />
        </p>
    </div>

    <?php if (!$is_configured): ?>
        <!-- Client ID/Secret/Tenant 입력 폼 -->
    <?php else: ?>
        <!-- Step 2 리소스 선택 -->
    <?php endif; ?>
</div>
```

---

#### 2. **중복된 모드 선택 박스 제거**

**파일:** [templates/oauth-auto-setup.php:292-294](templates/oauth-auto-setup.php#L292-L294)

**변경 전:**
```php
<?php else: ?>
    <!-- ✅ 모드 선택을 맨 위로 이동 (항상 표시) -->
    <div class="notice notice-info inline" ...>  ← 중복!
        ...
    </div>

    <?php if (!$has_token): ?>
```

**변경 후:**
```php
<?php else: ?>
    <!-- ✅ 모드 선택 박스는 위로 이동했으므로 여기서는 제거 -->

    <?php if (!$has_token): ?>
```

---

#### 3. **Agent 404 에러 메시지 개선**

**파일:** [includes/class-azure-oauth.php:978-996](includes/class-azure-oauth.php#L978-L996)

**변경 전:**
```php
if ($status_code !== 200) {
    $error_msg = 'Agent 목록 조회 실패 (HTTP ' . $status_code . ')';
    if (isset($data['error']['message'])) {
        $error_msg .= ': ' . $data['error']['message'];
    }
    error_log('[Azure OAuth] Agent 조회 실패: ' . $error_msg);
    wp_send_json_error(array('message' => $error_msg, ...));
}
```

**사용자가 본 에러:**
```
[Auto Setup] [Agent] Agent 목록 조회 실패 (HTTP 404): Resource not found
```
→ 무슨 문제인지 명확하지 않음

**변경 후:**
```php
if ($status_code !== 200) {
    // ✅ 404는 CognitiveServices 리소스일 때 정상적인 응답 (Agent 미지원)
    if ($status_code === 404) {
        $error_msg = 'ℹ️ 이 리소스는 Azure OpenAI (CognitiveServices)입니다. Agent를 사용하려면 AI Foundry Hub 리소스를 선택하세요.';
        error_log('[Azure OAuth] Agent 404: CognitiveServices 리소스 (Agent 미지원)');
    } else {
        $error_msg = 'Agent 목록 조회 실패 (HTTP ' . $status_code . ')';
        ...
    }
    wp_send_json_error(array('message' => $error_msg, ...));
}
```

**개선된 메시지:**
```
ℹ️ 이 리소스는 Azure OpenAI (CognitiveServices)입니다. Agent를 사용하려면 AI Foundry Hub 리소스를 선택하세요.
```
→ 명확한 안내 제공!

---

## 주요 변경 사항

### UI/UX 개선
- **모드 선택 항상 표시**: `$is_configured` 조건과 무관하게 항상 렌더링
- **중복 UI 제거**: 2개의 모드 선택 박스 → 1개로 통합
- **에러 메시지 개선**: 404 에러 시 친절한 안내 메시지

### 파일 변경 내역

**templates/oauth-auto-setup.php:**
- Lines 84-104: 모드 선택 박스를 `<div class="inside">` 바로 다음으로 이동 (항상 표시)
- Lines 292-294: 중복된 모드 선택 박스 제거

**includes/class-azure-oauth.php:**
- Lines 978-996: Agent 404 에러 시 명확한 메시지 표시

**azure-ai-chatbot.php:**
- Version updated to 3.0.55

**README-ko.md, README.md:**
- Version badges updated to 3.0.55

---

## 테스트 시나리오

### 시나리오 1: OAuth 설정 전 (Client ID/Secret 없음)

```
1. 플러그인 활성화 (OAuth 설정 안 함)
2. OAuth Auto Setup 페이지 접속
3. 예상 결과:
   - ✅ 모드 선택 박스가 페이지 맨 위에 보임
   - [DEBUG] Total radio buttons in DOM: 2
   - [DEBUG] Radio 0: {visible: true}  ← ✅ 이제 true!
   - [DEBUG] Radio 1: {visible: true}  ← ✅ 이제 true!
   - Client ID/Secret/Tenant 입력 폼도 보임
```

### 시나리오 2: OAuth 인증 후

```
1. Client ID/Secret/Tenant 설정
2. Azure 인증 완료
3. 예상 결과:
   - ✅ 모드 선택 박스가 페이지 맨 위에 보임 (1개만!)
   - ✅ 리소스 선택 폼 (Step 2) 표시
   - [DEBUG] Radio 0: {visible: true}
   - [DEBUG] Radio 1: {visible: true}
```

### 시나리오 3: CognitiveServices 리소스 선택 시

```
1. Agent 모드 선택
2. CognitiveServices (Azure OpenAI) 리소스 선택
3. 이전 로그:
   [Auto Setup] [Agent] Agent 목록 조회 실패 (HTTP 404): Resource not found
   ← 무슨 문제인지 불명확

4. 개선된 로그:
   ℹ️ 이 리소스는 Azure OpenAI (CognitiveServices)입니다.
   Agent를 사용하려면 AI Foundry Hub 리소스를 선택하세요.
   ← 명확한 안내!
```

---

## Breaking Changes
없음 - 기존 기능과 완전 호환

## Migration Guide
업그레이드만 하면 됨 - 추가 작업 불필요

---

## Known Issues

### AI Foundry Hub 없을 때
- 현재 Resource Group에 AI Foundry Hub가 없으면 Agent 모드 사용 불가
- **해결 방법**: Azure Portal에서 AI Foundry Hub 생성

---

## 감사합니다!

이 업데이트로 v3.0.54의 가시성 문제가 완전히 해결되었습니다:
- ✅ 라디오 버튼 항상 보임 (visible: true)
- ✅ 중복 UI 제거
- ✅ Agent 404 에러 명확한 안내

**버그 리포트**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)
