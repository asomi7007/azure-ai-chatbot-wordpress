# v3.0.50 - Critical Bug Fix: Deleted Option References

## 🐛 Critical: 삭제된 옵션 참조 버그 완전 수정

### ⚠️ 긴급 수정 (2건)

#### 버그 1: OAuth 콜백 페이지에서 삭제된 옵션 참조
**파일**: `templates/oauth-auto-setup.php` (line 28-29)  
**문제**: OAuth 인증 완료 후 리디렉션 시 삭제된 `azure_ai_chatbot_operation_mode` 옵션을 참조하여 항상 'chat' 반환  
**수정**: `azure_chatbot_settings['mode']` 단일 소스 사용

```php
// ❌ 이전 (삭제된 옵션 참조)
$operation_mode = get_option('azure_ai_chatbot_operation_mode', 'chat');

// ✅ 수정 (단일 소스)
$settings = get_option('azure_chatbot_settings', array());
$operation_mode = isset($settings['mode']) ? $settings['mode'] : 'chat';
```

#### 버그 2: 설정 페이지에서 삭제된 옵션 참조
**파일**: `templates/settings-page.php` (line 78-79)  
**문제**: 설정 페이지 로드 시 삭제된 옵션을 먼저 확인하여 Mode 표시 오류  
**수정**: `azure_chatbot_settings['mode']` 직접 사용

```php
// ❌ 이전 (삭제된 옵션 확인)
$current_mode_option = get_option('azure_ai_chatbot_operation_mode');
if ($current_mode_option) {
    $mode = $current_mode_option;
} else {
    $mode = $options['mode'] ?? 'chat';
}

// ✅ 수정 (단일 소스만 사용)
$mode = $options['mode'] ?? 'chat';
```

### 개선사항
**✅ Agent 조회 시 PHP 메시지를 사용자에게 표시**
- 파일: `templates/oauth-auto-setup.php` (line 2422-2431)
- PHP에서 "Azure OpenAI 리소스는 Agent를 지원하지 않습니다" 메시지 전송
- JavaScript에서 이 메시지를 alert로 표시
- 사용자에게 명확한 피드백 제공

```javascript
// ✅ PHP에서 보낸 메시지 확인 및 표시
var message = response.data && response.data.message ? response.data.message : 'Agent 없음';
console.log('[Auto Setup] [Agent] ' + message);

// ✅ 사용자에게 명확한 메시지 표시
if (response.data && response.data.message) {
    alert('ℹ️ Agent 정보: ' + response.data.message);
}
```

### 파일 변경 내역
- ✅ `templates/oauth-auto-setup.php`: 삭제된 옵션 참조 제거 + Agent 메시지 표시
- ✅ `templates/settings-page.php`: 삭제된 옵션 확인 로직 제거
- ✅ `CHANGELOG.md`: v3.0.50 긴급 버그 수정 상세 내역
- ✅ `readme.txt`: v3.0.50 변경 이력 업데이트

### 영향
- **OAuth 인증 후 Mode 유지 버그 완전 해결**
- **사용자에게 Agent 조회 실패 이유 명확히 전달**
- **v3.0.47에서 삭제한 옵션 참조 완전 제거**

### 설치 방법
1. ZIP 파일 다운로드: `azure-ai-chatbot-wordpress-3.0.50.zip`
2. WordPress 관리자 → 플러그인 → 새로 추가 → 플러그인 업로드
3. ZIP 파일 선택 후 설치
4. 플러그인 활성화

---

**Full Changelog**: https://github.com/asomi7007/azure-ai-chatbot-wordpress/blob/main/CHANGELOG.md
