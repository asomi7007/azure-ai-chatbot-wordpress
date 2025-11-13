# 변경 이력

## [3.0.50] - 2025-01-14

### 🐛 **OAuth 자동 설정 중 operationMode 버그 수정**

#### 주요 수정사항
1. **✅ Agent 모드 선택이 OAuth 리디렉션 후에도 유지되도록 수정** ([oauth-auto-setup.php:691-707](templates/oauth-auto-setup.php#L691-L707))
   - **기존 문제**: Agent 모드 선택 → OAuth 인증 → 리디렉션 후 Chat 모드로 변경됨
   - **원인**: localStorage에서 operationMode를 읽은 직후 삭제하여, 이후 DB의 'chat' 값으로 되돌아감
   - **수정**: localStorage 값을 자동 설정 완료 시까지 유지하고, 최종 저장 후 삭제

2. **✅ operationMode 우선순위 개선**
   - **기존**: DB 값 우선 → localStorage 값 나중에 확인 → 즉시 삭제
   - **수정**: localStorage 값 우선 (OAuth 자동 설정 중) → 없으면 DB 값 사용

3. **✅ 페이지 로드 시 UI 초기화 개선** ([oauth-auto-setup.php:850-863](templates/oauth-auto-setup.php#L850-L863))
   - operationMode 값에 따라 라디오 버튼 및 Agent 선택 UI 자동 초기화
   - Agent 모드 선택 시 Agent 선택 행 표시

#### 코드 변경 상세

##### operationMode 초기화 로직 개선
```javascript
// ❌ 이전 (DB 우선 + localStorage 즉시 삭제)
var operationMode = '<?php echo azure_chatbot_settings["mode"]; ?>';
if (localStorage.getItem('azure_oauth_operation_mode')) {
    operationMode = localStorage.getItem('azure_oauth_operation_mode');
    localStorage.removeItem('azure_oauth_operation_mode'); // ❌ 즉시 삭제
}

// ✅ 수정 (localStorage 우선 + 자동 설정 완료까지 유지)
var operationMode = 'chat';
var dbMode = '<?php echo azure_chatbot_settings["mode"]; ?>';
try {
    var savedMode = localStorage.getItem('azure_oauth_operation_mode');
    if (savedMode && (savedMode === 'chat' || savedMode === 'agent')) {
        operationMode = savedMode; // ✅ localStorage 우선
        // ⚠️ 자동 설정 완료 전까지 유지 (삭제하지 않음)
    } else {
        operationMode = dbMode;
    }
} catch(e) {
    operationMode = dbMode;
}
```

##### localStorage 삭제 시점 조정
```javascript
// ✅ completeSetup() 및 checkBothCollected() 함수에서 최종 저장 후 삭제
try {
    localStorage.removeItem('azure_oauth_token_saved');
    localStorage.removeItem('azure_oauth_token_saved_time');
    localStorage.removeItem('azure_oauth_operation_mode'); // ✅ 자동 설정 완료 시 삭제
} catch(e) {
    console.warn('[Auto Setup] Cannot clear localStorage:', e);
}
```

##### 페이지 로드 시 UI 초기화
```javascript
// ✅ operationMode에 따라 UI 초기화
console.log('[Auto Setup] Initializing UI with mode:', operationMode);
$('input[name="oauth_mode"][value="' + operationMode + '"]').prop('checked', true);

if (operationMode === 'agent') {
    $('#agent_selection_row').show();
} else {
    $('#agent_selection_row').hide();
    $('#oauth_agent').val('').prop('disabled', true);
}
```

#### 파일 변경 내역
- `templates/oauth-auto-setup.php`: operationMode 초기화 로직 개선 (라인 691-707)
- `templates/oauth-auto-setup.php`: localStorage 삭제 시점 조정 (라인 2057, 2148)
- `templates/oauth-auto-setup.php`: 페이지 로드 시 UI 초기화 (라인 850-863)

#### 테스트 완료
- ✅ Agent 모드 선택 → OAuth 인증 → 모드가 'agent'로 유지
- ✅ Chat 모드 선택 → OAuth 인증 → 모드가 'chat'로 유지
- ✅ localStorage 값이 자동 설정 완료까지 유지
- ✅ 최종 저장 후 localStorage 정리

---

## [3.0.49] - 2025-01-13

### 🔧 **Agent API 엔드포인트 수정 및 응답 파싱 개선**

#### 주요 수정사항
1. **✅ Agent API 엔드포인트를 Microsoft Learn 문서 기준으로 수정** ([class-azure-oauth.php:772-776](class-azure-oauth.php#L772-L776))
   - **기존**: `/api/projects/{projectName}/assistants?api-version=v1` (잘못된 엔드포인트)
   - **수정**: `/agents/v1.0/projects/{projectName}/agents` (Microsoft Learn 문서 기준)
   - 참고: [Get Agent API Documentation](https://learn.microsoft.com/en-us/rest/api/aifoundry/aiagents/get-agent/get-agent)

2. **✅ Agent 응답 데이터 파싱 로직 개선** ([class-azure-oauth.php:949-983](class-azure-oauth.php#L949-L983))
   - 다양한 응답 형식 지원: `{ value: [...] }`, `{ data: [...] }`, 직접 배열
   - 빈 Agent 목록에 대한 명확한 메시지 제공
   - 상세한 파싱 로그 추가

3. **✅ Agent 목록이 여러 개일 때 선택 가능**
   - JavaScript에서 이미 구현되어 있음 (1개: 자동 선택, 2개 이상: 모달 선택)

#### 코드 변경 상세

##### Agent API URL 수정
```php
// ❌ 이전 (잘못된 엔드포인트)
$base_endpoint = rtrim($project_endpoint_host, '/') . "/api/projects/{$project_name}";
$agents_url = $base_endpoint . '/assistants?api-version=v1';

// ✅ 수정 (Microsoft Learn 문서 기준)
$agents_url = rtrim($project_endpoint_host, '/') . "/agents/v1.0/projects/{$project_name}/agents";
```

##### 응답 파싱 개선
```php
// ✅ 유연한 응답 형식 처리
$agent_list = array();
if (isset($data['value']) && is_array($data['value'])) {
    $agent_list = $data['value'];  // Azure 표준 형식
} elseif (isset($data['data']) && is_array($data['data'])) {
    $agent_list = $data['data'];   // 대체 형식
} elseif (is_array($data) && !isset($data['error'])) {
    $agent_list = $data;           // 직접 배열
}

if (empty($agent_list)) {
    wp_send_json_success(array(
        'agents' => array(),
        'message' => 'AI Foundry Project에 생성된 Agent가 없습니다.'
    ));
}
```

#### 디버깅 개선
- Agent API URL 로깅 추가
- 응답 파싱 방식 로깅 (value/data/직접배열)
- Agent 개수 로깅

#### 버전 정보
- Plugin Version: `3.0.49`
- Updated Files:
  - [azure-ai-chatbot.php](azure-ai-chatbot.php#L6): Version 3.0.49
  - [class-azure-oauth.php](includes/class-azure-oauth.php): Agent API 수정
  - [README-ko.md](README-ko.md#L7): Version badge 3.0.49
  - [README.md](README.md#L3): Version badge 3.0.49

---

## [3.0.48] - 2025-11-13

### 🐛 **Critical Bug Fixes: OAuth 및 Mode 관리 버그 수정**

#### ⚠️ 긴급 버그 수정 (3건)

##### 버그 1: OAuth Client Secret 복호화 누락
**문제**: OAuth 설정 로드 시 저장된 암호화된 Client Secret을 복호화하지 않고 그대로 사용하여 모든 OAuth 인증이 실패하는 치명적 버그

**증상**: "Azure 자동 설정 시작" 버튼 클릭 시 다음 에러 발생
```
AADSTS7000215: Invalid client secret provided.
Ensure the secret being sent in the request is the client secret value,
not the client secret ID
```

**원인**: `load_config()` 함수에서 `get_option('azure_chatbot_oauth_client_secret')`로 암호화된 값을 가져왔지만, **복호화 과정 없이** 그대로 `$this->client_secret`에 저장하여 암호화된 문자열이 Azure API에 전송됨

##### 버그 2: OAuth 인증 후 Agent 모드가 Chat 모드로 변경
**문제**: OAuth 인증 완료 후 페이지 리디렉션 시 Agent 모드로 설정했던 것이 Chat 모드로 변경됨

**원인**: `oauth-auto-setup.php` 691번 라인에서 v3.0.47에서 삭제된 `azure_ai_chatbot_operation_mode` 옵션을 참조하여 항상 기본값 'chat'을 반환함

**증상**:
- 로그: `[Auto Setup] Operation mode loaded from localStorage: chat`
- 사용자가 Agent 모드 선택 → OAuth 인증 → 자동으로 Chat 모드로 변경

##### 버그 3: Azure OpenAI 리소스에서 Agent 조회 시도
**문제**: Azure OpenAI (Cognitive Services) 리소스에 대해 Agent 조회를 시도하여 항상 빈 결과 반환

**원인**: `ajax_get_agents()` 함수에서 리소스 타입을 확인하지 않고 모든 리소스에 대해 Agent API 호출 시도

**증상**:
- 로그: `[Auto Setup] [Agent] Agent 없음, 빈 설정으로 진행`
- Agent는 AI Foundry Project (Microsoft.MachineLearningServices)에만 존재하지만, Azure OpenAI (Microsoft.CognitiveServices)에서도 조회 시도

#### 핵심 수정사항
1. **✅ [Critical] OAuth 설정 로드 시 Client Secret 복호화 추가** ([class-azure-oauth.php:48-100](class-azure-oauth.php#L48-L100))
   - `load_config()` 함수에서 암호화된 값을 Encryption Manager로 복호화
   - 복호화 실패 시 자동 마이그레이션 시도
   - 상세한 복호화 상태 로깅 추가
   - **이 수정으로 OAuth 인증 완전 정상화**

2. **✅ [Critical] Operation Mode 로드 소스 수정** ([oauth-auto-setup.php:691-695](oauth-auto-setup.php#L691-L695))
   - 삭제된 `azure_ai_chatbot_operation_mode` 옵션 참조 제거
   - `azure_chatbot_settings['mode']` 단일 소스로 통일
   - **Agent 모드가 Chat 모드로 변경되는 버그 수정**

3. **✅ [Critical] Agent 조회 시 리소스 타입 검증 추가** ([class-azure-oauth.php:739-761](class-azure-oauth.php#L739-L761))
   - Azure OpenAI (Microsoft.CognitiveServices) 리소스 필터링
   - AI Foundry Project (Microsoft.MachineLearningServices)만 Agent 조회
   - 사용자 친화적 메시지 제공
   - **불필요한 API 호출 방지 및 명확한 피드백**

4. **✅ Client Secret 형식 검증 추가** ([class-azure-oauth.php:1006-1037](class-azure-oauth.php#L1006-L1037))
   - GUID 형식(Secret ID) 감지 및 경고
   - 최소 길이 검증 (20자 이상)
   - 특수문자 포함 여부 경고

5. **✅ AADSTS7000215 에러 특별 처리**
   - "Invalid client secret provided" 에러 감지
   - 사용자 친화적 에러 메시지 제공
   - 단계별 해결 가이드 포함

6. **✅ OAuth 토큰 요청 에러 로깅 강화**
   - 상세한 에러 코드 및 설명 로깅
   - 네트워크 오류 vs 인증 오류 구분
   - 디버깅 정보 제공

### 주요 변경사항

#### 📦 `includes/class-azure-oauth.php`
- **[Critical] load_config() 함수 수정** (라인 48-100):
  ```php
  // ❌ 이전 코드 (버그)
  $this->client_secret = get_option('azure_chatbot_oauth_client_secret', '');

  // ✅ 수정된 코드
  $encrypted_secret = get_option('azure_chatbot_oauth_client_secret', '');
  $encryption_manager = Azure_AI_Chatbot_Encryption_Manager::get_instance();
  $this->client_secret = $encryption_manager->decrypt($encrypted_secret);
  ```
  - 암호화된 값을 복호화하여 실제 Client Secret 사용
  - 복호화 실패 시 마이그레이션 자동 시도
  - 복호화 성공/실패 상세 로깅

- **새 검증 함수 추가**:
  - `validate_client_secret()`: Client Secret 형식 검증 (라인 1006-1037)
    - GUID 패턴 감지 (Secret ID 입력 방지)
    - 길이 검증 (최소 20자)
    - 특수문자 포함 여부 경고

- **[Critical] Agent 조회 리소스 타입 검증 추가** (라인 739-761):
  ```php
  // ✅ 리소스 타입 확인
  $resource_type = $resource_info['type'];

  // Cognitive Services (Azure OpenAI)는 Agent 미지원
  if (strpos($resource_type, 'Microsoft.CognitiveServices') !== false) {
      wp_send_json_success(array(
          'agents' => array(),
          'message' => 'Azure OpenAI 리소스는 Agent를 지원하지 않습니다.'
      ));
      return;
  }

  // AI Foundry Project만 Agent 조회
  if (strpos($resource_type, 'Microsoft.MachineLearningServices') === false) {
      wp_send_json_success(array(
          'agents' => array(),
          'message' => 'Agent는 AI Foundry Project에서만 사용할 수 있습니다.'
      ));
      return;
  }
  ```

- **OAuth 설정 저장 개선**:
  - `ajax_save_oauth_settings()`: 저장 전 형식 검증 (라인 1025-1030)
  - 잘못된 형식 감지 시 명확한 에러 메시지 반환

- **토큰 요청 에러 처리 강화**:
  - `request_access_token()`: AADSTS7000215 특별 처리 (라인 364-373)
  - `ajax_get_agents()`: Bearer Token 요청 실패 시 해결 가이드 제공 (라인 820-837)
  - 상태 코드 및 상세 에러 로깅 추가

#### 📦 `templates/oauth-auto-setup.php`
- **[Critical] Operation Mode 로드 소스 수정** (라인 691-695):
  ```php
  // ❌ 이전 코드 (버그)
  var operationMode = '<?php echo esc_js(get_option('azure_ai_chatbot_operation_mode', 'chat')); ?>';

  // ✅ 수정된 코드
  var operationMode = '<?php
      $settings = get_option('azure_chatbot_settings', array());
      echo esc_js(isset($settings['mode']) ? $settings['mode'] : 'chat');
  ?>';
  ```
  - v3.0.47에서 삭제된 옵션 참조 제거
  - 단일 소스 (azure_chatbot_settings['mode']) 사용

### 에러 메시지 예시

#### ❌ Secret ID 입력 시
```
❌ Client Secret ID를 입력하셨습니다.
Azure Portal의 "Certificates & secrets"에서
Secret의 "Value" 값을 복사하여 입력하세요.
(Secret ID가 아닙니다)
```

#### ❌ AADSTS7000215 에러 발생 시
```
❌ Client Secret 오류:
Azure Portal의 "Certificates & secrets"에서
Secret의 "Value" 값을 복사하여 다시 저장하세요.
(Secret ID가 아닌 Value를 입력해야 합니다)

해결 가이드:
1. Azure Portal → App registrations → 앱 선택
2. Certificates & secrets 메뉴 클릭
3. Client secrets 섹션에서 "+ New client secret" 클릭
4. Description 입력 후 Add 클릭
5. 생성된 Secret의 "Value" 컬럼 값을 즉시 복사
6. WordPress OAuth 설정에 Value 붙여넣기 후 저장
```

### 기술 세부사항
- **정규식 패턴**: `/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i`
- **에러 감지**: `AADSTS7000215` 및 `Invalid client secret` 문자열 검색
- **로깅 개선**: 토큰 요청 시작/응답 상태/에러 상세 정보

### 업그레이드 가이드
1. 플러그인 업데이트
2. 기존 OAuth 설정 확인
3. Secret ID를 입력한 경우 Value로 교체 필요
4. "Azure 자동 설정 시작" 버튼으로 재인증

---

## [3.0.47] - 2025-11-13

### 🔥 **코드 품질 개선 및 리팩토링 (Code Quality & Refactoring)**

#### 핵심 개선사항
1. **✅ Mode 저장 경합 상태 완전 제거**
   - `azure_chatbot_settings['mode']` 단일 소스로 통일
   - `azure_ai_chatbot_operation_mode` 옵션 제거
   - 경합 상태(race condition) 해결

2. **✅ 완전한 초기화 기능 구현**
   - 모든 DB 옵션 삭제
   - 모든 Transient 캐시 삭제 (Access Token, OAuth State 등)
   - 세션 토큰 삭제

3. **✅ 중복 코드 제거 및 헬퍼 함수 추가**
   - `verify_ajax_permissions()`: AJAX 권한 검증 통합
   - `delete_transients_by_pattern()`: Transient 캐시 일괄 삭제
   - `mask_sensitive_value()`: API Key/Secret 마스킹 통합

4. **✅ DB 쿼리 최적화**
   - 중복 DELETE 쿼리 제거
   - `$wpdb->prepare()` 및 `$wpdb->esc_like()` 사용으로 보안 강화

### 주요 변경사항

#### 📦 `includes/class-azure-oauth.php`
- **새 헬퍼 함수 추가**:
  - `verify_ajax_permissions()`: AJAX 요청 권한 검증 (라인 75-84)
  - `delete_transients_by_pattern()`: Transient 패턴 기반 일괄 삭제 (라인 92-106)

- **Mode 저장 경합 상태 해결**:
  - `ajax_set_operation_mode()`: 단일 소스로 통일 (라인 836-856)
  - 이전 버전 호환 옵션 자동 삭제

- **완전한 초기화 기능**:
  - `ajax_reset_config()`: 모든 옵션 및 캐시 삭제 (라인 1013-1057)
  - `ajax_reset_all_settings()`: OAuth 인증 정보 보존하고 리소스 설정만 초기화 (라인 1064-1111)

#### 📦 `azure-ai-chatbot.php`
- **마스킹 함수 통합**:
  - `mask_sensitive_value()`: 중복 코드 제거 (라인 629-642)
  - `get_masked_api_key()` 및 `get_masked_client_secret()` 리팩토링

#### 📦 `includes/class-encryption-manager.php`
- **중앙 암호화 관리 시스템**:
  - 싱글톤 패턴으로 단일 키 기반 암호화
  - 버전 관리 (v2: AES-256-CBC, v1: base64 fallback)
  - 마이그레이션 지원

### 기술 세부사항

**문제 1: Mode 저장 경합 상태**
```php
// ❌ 이전 코드 (두 곳에 저장)
update_option('azure_ai_chatbot_operation_mode', $mode);
$settings['mode'] = $mode;
update_option('azure_chatbot_settings', $settings);

// ✅ 수정 후 (단일 소스)
$settings = get_option('azure_chatbot_settings', array());
$settings['mode'] = $mode;
update_option('azure_chatbot_settings', $settings);
```

**문제 2: 불완전한 초기화**
```php
// ❌ 이전 코드 (일부만 삭제)
delete_option('azure_chatbot_oauth_client_id');
delete_option('azure_chatbot_settings');

// ✅ 수정 후 (모든 옵션 + 캐시 삭제)
delete_option('azure_chatbot_oauth_settings');
$this->delete_transients_by_pattern('azure_chatbot_access_token_');
delete_transient('azure_oauth_state');
delete_transient('azure_oauth_error');
```

**문제 3: 중복 마스킹 함수**
```php
// ❌ 이전 코드 (2개 함수, 동일 로직)
public function get_masked_api_key() { /* 34줄 */ }
public function get_masked_client_secret() { /* 34줄 */ }

// ✅ 수정 후 (헬퍼 함수 + 2개 래퍼)
private function mask_sensitive_value($value) { /* 14줄 */ }
public function get_masked_api_key() { return $this->mask_sensitive_value(...); }
public function get_masked_client_secret() { return $this->mask_sensitive_value(...); }
```

### 성능 개선
- AJAX 권한 검증 코드 15곳 → 1개 헬퍼 함수로 통합
- 중복 DB 쿼리 2개 → 1개 헬퍼 함수로 통합
- 마스킹 함수 68줄 → 28줄로 감소 (60% 코드 감소)

---

## [3.0.41] - 2025-11-08

### � **치명적 버그 수정 (Critical Bug Fixes)**
- **🔧 Operation Mode 유지 실패 수정**: OAuth 인증 후 사용자가 선택한 모드(Chat/Agent)가 항상 Chat으로 초기화되던 문제 해결
- **🤖 Agent 목록 조회 실패 수정**: Agent 모드에서 "Agent 없음" 오류가 발생하던 문제 해결
- **💾 localStorage 기반 모드 저장**: OAuth 리디렉션 과정에서 선택한 모드를 localStorage에 저장하여 복원
- **🔍 디버깅 강화**: Agent API 호출 실패 시 상세한 오류 정보를 콘솔에 출력
- **� API 버전 수정**: Assistants API 버전을 `2024-05-01-preview`로 변경하여 호환성 개선

### 주요 변경사항
#### 🔧 `templates/oauth-auto-setup.php`
- **localStorage 기반 모드 저장**: OAuth 인증 시작 전 선택한 모드를 localStorage에 저장
- **페이지 로드 시 모드 복원**: 인증 후 리다이렉트 시 localStorage에서 모드 값을 읽어와 `operationMode` 변수 초기화
- **Agent AJAX 응답 로깅 강화**: 성공/실패 여부와 관계없이 전체 응답을 콘솔에 기록

#### 🔧 `includes/class-azure-oauth.php`
- **API 버전 변경**: Assistants API 호출 시 `2025-04-01-preview` → `2024-05-01-preview`로 수정
- **상세 오류 로깅**: Agent 목록 조회 실패 시 HTTP 상태 코드, 응답 본문, 오류 메시지 상세 기록

### 기술 세부사항
**문제 1: Operation Mode 초기화**
- OAuth 인증을 위해 Microsoft 로그인 페이지로 이동 후 돌아올 때, PHP의 `get_option()`이 이전 값을 가져와 사용자의 선택이 무시됨
- JavaScript 전역 변수 `operationMode`가 항상 'chat'으로 초기화됨

**해결 1:**
```javascript
// OAuth 팝업 열기 전
function openOAuthPopup(url) {
    var selectedMode = jQuery('input[name="oauth_mode"]:checked').val() || 'chat';
    localStorage.setItem('azure_oauth_operation_mode', selectedMode);
    // ...
}

// 페이지 로드 시
var operationMode = '<?php echo esc_js(get_option(...)); ?>';
try {
    var savedMode = localStorage.getItem('azure_oauth_operation_mode');
    if (savedMode) {
        operationMode = savedMode;
        localStorage.removeItem('azure_oauth_operation_mode');
    }
} catch(e) { }
```

**문제 2: Agent 목록 조회 실패**
- API 버전 `2025-04-01-preview`가 일부 Azure 리전에서 지원되지 않음
- AJAX 응답의 디버그 정보가 콘솔에 출력되지 않아 원인 파악 어려움

**해결 2:**
```php
// API 버전 변경
$agents_url = '...' . '?api-version=2024-05-01-preview';

// 상세 로깅
error_log('[Azure OAuth] Agent 조회 요청 URL: ' . $agents_url);
error_log('[Azure OAuth] Agent 조회 응답 코드: ' . $status_code);
error_log('[Azure OAuth] Agent 조회 응답 본문: ' . $body);
```

```javascript
// 클라이언트 측 로깅
console.log('[Auto Setup] [Agent] get_agents 응답:', response);
console.error('[Auto Setup] [Agent] get_agents AJAX 실패:', {
    status: status,
    error: error,
    responseText: xhr.responseText
});
```

### 영향
- ❌ **이전 (v3.0.40)**: 
  - OAuth 인증 후 선택한 모드가 항상 'Chat'으로 초기화됨
  - Agent 모드를 선택했어도 Chat 모드로 설정됨
  - Agent 목록 조회 시 "Agent 없음" 오류 발생
- ✅ **현재 (v3.0.41)**: 
  - 사용자가 선택한 모드(Chat 또는 Agent)가 OAuth 인증 후에도 정확히 유지됨
  - Agent 모드 선택 시 Agent 목록이 정상적으로 조회됨
  - API 호출 실패 시 상세한 디버깅 정보 제공

### 사용자 경험 개선
- **자동 설정 플로우**: Agent 모드 선택 → OAuth 인증 → **Agent 모드 유지** → Agent 목록 표시 → 설정 완료
- **디버깅**: 문제 발생 시 브라우저 콘솔에서 상세한 오류 정보 확인 가능
- **안정성**: API 버전 호환성 개선으로 더 많은 Azure 리전에서 안정적으로 동작

## [3.0.40] - 2025-11-08

### ✨ **UI 개선 및 문서 정리**
- **🎨 V2 표시 제거**: 메뉴, 제목 등에서 모든 "V2" 텍스트 제거
- **📚 문서 개선**: README, 가이드 문서 전면 개편
- **🌐 한영 번역 개선**: 더 명확한 설명과 구조

### 주요 변경사항

#### UI 텍스트 정리
**변경 전:**
- 메뉴: "AI Chatbot V2"
- 페이지 제목: "Azure AI Chatbot V2"

**변경 후:**
- 메뉴: "AI Chatbot"
- 페이지 제목: "Azure AI Chatbot"

#### 문서 개선
- README.md: 한영 병기, 구조 개선
- 사용 가이드: 단계별 상세 설명 추가
- readme.txt: WordPress.org 표준 형식 준수
- 에러 메시지 가독성 향상

### 파일 변경사항
- `azure-ai-chatbot.php`: 메뉴 텍스트 정리
- `README.md`: 전면 개편
- `readme.txt`: 버전 3.0.40 업데이트
- `CHANGELOG.md`: 변경사항 기록

## [3.0.39] - 2025-11-09

### ✨ **자동 설정 개선: 엔드포인트 형식 수정 및 Agent 선택 UI**
- **🔧 Chat 엔드포인트 형식 수정**: `.cognitiveservices.azure.com` → `.openai.azure.com` 자동 변환
- **🎯 Agent 선택 UI 개선**: 2개 이상 Agent 발견 시 리소스 그룹처럼 모달 선택 UI 제공
- **📝 양방향 수집 로직 안정화**: Chat + Agent 정보 병렬 수집 시 오류 처리 개선

### 주요 변경사항

#### 🔧 Chat 엔드포인트 형식
**문제:**
- Azure Management API가 `.cognitiveservices.azure.com` 형식 반환
- Chat 모드는 `.openai.azure.com` 형식 필요

**해결:**
```javascript
// templates/oauth-auto-setup.php - getResourceApiKeyForBoth()
if (endpoint.includes('.cognitiveservices.azure.com')) {
    endpoint = endpoint.replace('.cognitiveservices.azure.com', '.openai.azure.com');
    console.log('[Auto Setup] [Chat] 엔드포인트 변환됨:', endpoint);
}
```

**예시:**
- ❌ 이전: `https://eduelden04-2296-resource.cognitiveservices.azure.com/`
- ✅ 현재: `https://eduelden04-2296-resource.openai.azure.com/`

#### 🎯 Agent 선택 UI
**문제:**
- Agent 2개 이상일 때 첫 번째만 자동 선택
- 사용자가 선호하는 Agent 선택 불가

**해결:**
```javascript
// Agent 선택 로직
if (agents.length === 1) {
    // 1개면 자동 선택
    processAgent(agents[0]);
} else {
    // 2개 이상이면 모달 표시
    showSelectionModal('Agent 선택', items, false)
    .then(function(res) {
        processAgent(agents[sel]);
    });
}
```

**UI:**
- 리소스 그룹 선택 모달과 동일한 UI/UX
- Agent 이름 + ID 표시
- 선택 취소 시 빈 설정으로 진행 (경고 없음)

#### 📝 양방향 수집 로직
**개선:**
- Chat 정보 수집 실패 시에도 Agent 수집 계속 진행
- Agent 선택 취소 시 빈 설정(`{}`)으로 callback 호출
- 에러 처리 개선: `console.warn` 사용, alert 제거

### 설정 예시

#### Chat 모드 (자동 변환)
```
chat_endpoint: https://your-resource.openai.azure.com
deployment_name: gpt-4o
api_key_encrypted: [암호화된 키]
```

#### Agent 모드 (2개 이상 선택)
```
Agent 선택 모달:
  ○ agent-prod (ID: a1b2c3d4)
  ● agent-dev (ID: e5f6g7h8)  ← 사용자 선택
  
저장:
agent_id: e5f6g7h8
agent_endpoint: https://project.region.services.ai.azure.com/...
```

### 디버그 로그
```
[Auto Setup] [Chat] 엔드포인트 변환됨: https://xxx.openai.azure.com/
[Auto Setup] [Agent] Agent 선택 모달 표시 (3개)
[Auto Setup] [Agent] 사용자 선택 Agent: agent-dev
```

## [3.0.38] - 2025-11-09

### ✨ **모드 무관 양방향 자동 설정**
- **🔄 Chat + Agent 양쪽 정보 동시 수집**: 사용자가 선택한 모드(Chat/Agent)와 무관하게 **양쪽 모두** 자동 수집
- **📦 통합 설정 저장**: 한 번의 자동 설정으로 Chat 모드 + Agent 모드 설정 모두 완료
- **🎯 사용자 요구사항**: "챗모드를 선택하던 에이전트 모드를 선택하던 챗모드의 값과 에이전트 모드 값을 다 자동으로 가지고 와서 체우라고"

### 주요 변경사항
#### 🔧 `templates/oauth-auto-setup.php`
- **`collectBothChatAndAgentConfig()` 함수 추가**: Chat + Agent 정보를 동시에 수집하는 새 함수
- **`checkAIResources()` 함수 수정**: 
  - ❌ 이전: `if (operationMode === 'agent')` 분기 처리 (한쪽만 수집)
  - ✅ 현재: `collectBothChatAndAgentConfig()` 호출 (양쪽 모두 수집)
- **양방향 수집 전용 함수**:
  - `getExistingResourceConfigForBoth()`: Chat 정보 수집 (배포, API Key, 엔드포인트)
  - `checkAndCreateAgentForBoth()`: Agent 정보 수집 (Agent 목록, Client ID/Secret)
  - `checkBothCollected()`: 양쪽 수집 완료 확인 및 리다이렉트

### 설정 프로세스
1. **OAuth 인증 및 리소스 선택**
2. **Chat 정보 자동 수집** ✅
   - 배포 목록 조회 → 첫 번째 배포 자동 선택
   - API Key 조회 → Azure OpenAI 엔드포인트 획득
   - `azure_oauth_save_existing_config` AJAX로 저장 (mode='chat')
3. **Agent 정보 자동 수집** ✅
   - Agent 목록 조회 → 첫 번째 Agent 자동 선택
   - OAuth Client ID/Secret 획득
   - `azure_oauth_save_existing_config` AJAX로 저장 (mode='agent')
4. **양쪽 수집 완료 확인**
   - `checkBothCollected()` → 통합 성공 메시지
   - "Chat 모드와 Agent 모드 설정이 모두 저장되었습니다."

### 저장되는 필드
#### Chat 모드
- ✅ `chat_endpoint`: Azure OpenAI 엔드포인트
- ✅ `deployment_name`: 배포 이름 (gpt-4o 등)
- ✅ `api_key_encrypted`: 암호화된 API Key

#### Agent 모드
- ✅ `agent_endpoint`: AI Foundry Project 엔드포인트
- ✅ `agent_id`: Agent ID
- ✅ `client_id`: OAuth Client ID
- ✅ `tenant_id`: Tenant ID
- ✅ `client_secret_encrypted`: 암호화된 Client Secret

### 디버그 로그
- **Chat 수집**: `[Auto Setup] [Chat] ...`
- **Agent 수집**: `[Auto Setup] [Agent] ...`
- **통합 확인**: `[Auto Setup] ========== Chat + Agent 양방향 수집 완료 ==========`

### 사용자 경험
- **이전**: Chat 모드 선택 → Chat 값만 저장, Agent 값 빈칸
- **현재**: Chat/Agent 어떤 모드 선택해도 → **양쪽 값 모두 자동 저장**
- **설정 페이지**: 모드 전환 시 양쪽 값 모두 유지 ✅

## [3.0.34] - 2025-11-08

### ✨ 성공 메시지 개선 및 Agent 모드 확인
- **📝 통합 성공 메시지**: "자동 설정이 완료되었습니다!" (모드별 상세 설명 포함)
- **🔍 Agent 모드 자동 설정 확인**: 
  - ✅ Agent 목록 조회
  - ✅ Agent 선택 (1개: 자동, 2개 이상: 모달)
  - ✅ Agent 설정 저장 (endpoint, agent_id, client_id, tenant_id, client_secret)
  - ✅ 설정 필드 개별 확인 로깅

### 성공 메시지 변경
**이전:**
- Chat 모드: "Chat 모드 설정이 완료되었습니다!"
- Agent 모드: "Agent 모드 설정이 완료되었습니다!"

**변경:**
- 공통: "자동 설정이 완료되었습니다!"
- Chat 모드 상세: "Chat 모드 설정(Endpoint, Deployment, API Key)이 저장되었습니다."
- Agent 모드 상세: "Agent 모드 설정(Project, Agent, Client ID/Secret)이 저장되었습니다."

### Agent 모드 자동 설정 플로우
1. OAuth 인증
2. Subscription 선택
3. Resource Group 선택
4. AI Foundry Project 선택
5. **Agent 목록 조회** ✅
6. **Agent 선택** (자동 또는 모달) ✅
7. **Client ID/Secret 포함 설정 저장** ✅
8. 성공 메시지 및 리다이렉트

### 확인된 기능
- ✅ Chat 모드: Endpoint, Deployment, API Key 자동 저장
- ✅ Agent 모드: Project, Agent, Client ID/Secret 자동 저장
- ✅ 양방향 설정 유지 (Chat 설정 ↔ Agent 설정)

## [3.0.33] - 2025-11-08

### 🔐 API Key 암호화 프로세스 상세 로깅
- **📊 암호화 전 과정 로깅**: `encrypt_api_key()` 함수의 모든 단계 출력
- **🔍 OpenSSL 상태 확인**: OpenSSL 사용 가능 여부 및 암호화 방식 출력
- **📏 데이터 길이 추적**: 원본 → 암호화 → base64 각 단계의 길이 출력
- **✅ 저장 상태 확인**: `$settings` 배열에 실제로 저장되었는지 확인

### WordPress debug.log 출력 예시
```
[Azure OAuth] API Key 암호화 시작:
  - Original API Key length: 88
  - Original API Key (first 10 chars): 6AZiAu7mKc...
  - Encrypted result: SUCCESS
  - Encrypted length: 128
  - Encrypted (first 20 chars): dG4yN3B5T...
  - Saved to $settings: YES

[Azure OAuth] encrypt_api_key() 호출됨
  - Input key empty: NO
  - Input key length: 88
  - openssl_encrypt available: YES
  - Encryption method: aes-256-cbc
  - Encryption key length: 32
  - IV length: 16
  - IV generated: YES
  - openssl_encrypt result: SUCCESS
  - Encrypted data length: 96
  - base64_encode result length: 128
  - Final result (first 30 chars): dG4yN3B5T...
```

### 진단 목적
이 로그로 다음을 확인 가능:
1. API Key가 함수에 전달되는지
2. OpenSSL이 정상 작동하는지
3. 암호화가 성공하는지
4. `$settings` 배열에 저장되는지

### 사용 방법
1. v3.0.33 업로드
2. 자동 설정 실행
3. debug.log 확인:
   ```bash
   tail -100 /var/www/wordpress/wp-content/debug.log | grep -A 20 "API Key 암호화"
   ```

## [3.0.32] - 2025-11-08

### 🧪 강제 디버그 로그 생성
- **📝 플러그인 로드 시 자동 로그**: 플러그인 초기화 시 debug.log에 자동으로 로그 작성
- **🔍 디버그 설정 확인**: `WP_DEBUG`, `WP_DEBUG_LOG` 상태 출력
- **📁 경로 확인**: `wp-content` 및 `debug.log` 경로 출력

### 로그 출력 예시
```
====================================
[Azure OAuth] Plugin Loaded - 2025-11-08 01:23:45
[Azure OAuth] WP_DEBUG: TRUE
[Azure OAuth] WP_DEBUG_LOG: TRUE
[Azure OAuth] wp-content path: /var/www/wordpress/wp-content
[Azure OAuth] debug.log path: /var/www/wordpress/wp-content/debug.log
====================================
```

### 사용 방법
1. v3.0.32 업로드
2. WordPress 관리자 페이지 새로고침
3. **즉시 debug.log 파일 생성됨**
4. 확인:
   ```bash
   ls -la /var/www/wordpress/wp-content/debug.log
   tail -50 /var/www/wordpress/wp-content/debug.log
   ```

### 주의사항
- 웹 서비스 재시작 **불필요**
- 플러그인 재활성화 **불필요**
- 단순 페이지 새로고침만으로 로그 생성

## [3.0.31] - 2025-11-08

### 🔍 필드별 상세 디버깅 로깅 추가
- **📊 각 필드 개별 확인**: `chat_endpoint`, `deployment_name`, `api_key_encrypted` 등 모든 필드 개별 출력
- **✅ NOT SET 표시**: 설정되지 않은 필드는 명확히 'NOT SET' 표시
- **📏 API Key 길이 표시**: 암호화된 API Key의 문자 수 출력

### 기술 세부사항
**브라우저 콘솔 출력 예시:**
```javascript
[Auto Setup] 설정 필드 확인:
  - mode: chat
  - chat_endpoint: https://... 또는 NOT SET
  - deployment_name: gpt-4o 또는 NOT SET
  - api_key_encrypted: YES (128 chars) 또는 NOT SET
  - chat_provider: azure-openai 또는 NOT SET
  - agent_endpoint: NOT SET
  - agent_id: NOT SET
  - client_id: NOT SET
  - tenant_id: NOT SET
```

### 디버깅 목적
이 버전으로 테스트하면 **어떤 필드가 실제로 저장되지 않는지** 정확히 파악 가능

### 다음 단계
1. v3.0.31 업로드
2. 자동 설정 실행
3. 브라우저 콘솔에서 "설정 필드 확인" 로그 확인
4. WordPress debug.log 확인:
   ```bash
   tail -100 /var/www/wordpress/wp-content/debug.log | grep "Azure OAuth"
   ```

## [3.0.30] - 2025-11-08

### 🔧 DB 저장 강제 실행 (Critical Fix)
- **💾 delete_option + add_option 사용**: `update_option`이 동일 값 저장하지 않는 문제 해결
- **📊 로깅 대폭 강화**: 저장 전/후 `$settings` 배열 전체 출력
- **🔍 필드별 상세 로깅**: 각 필드의 실제 값 또는 'NOT SET' 표시

### 기술 세부사항
**강제 저장 로직:**
```php
// update_option 대신 delete + add 사용
delete_option('azure_chatbot_settings');
$save_result = add_option('azure_chatbot_settings', $settings, '', 'yes');
```

**상세 로깅:**
```php
error_log('[Azure OAuth] 저장 전 $settings 배열:');
error_log(print_r($settings, true));
// ...
error_log('[Azure OAuth] DB에서 다시 읽은 설정:');
error_log(print_r($saved_settings, true));
```

### 디버깅 체크리스트
WordPress debug.log에서 다음 확인:
1. ✅ "저장 전 $settings 배열" - Chat 필드들이 있는지
2. ✅ "delete_option + add_option 결과: SUCCESS"
3. ✅ "DB에서 다시 읽은 설정" - Chat 필드들이 유지되는지
4. ✅ 브라우저 콘솔 `saveResponse.data.settings` - Chat 필드 포함 확인

### 영향
- ❌ 이전: `update_option`이 동일 값 감지 시 저장하지 않음
- ✅ 수정: `delete + add`로 무조건 강제 저장

## [3.0.29] - 2025-11-08

### 🐛 Session Warning 수정 및 설정 저장 개선
- **⚠️ Session Warning 수정**: `headers_sent()` 체크 추가로 "Session cannot be started" 경고 해결
- **📝 chat_provider 자동 설정**: JavaScript에서 제거하고 PHP에서 항상 `azure-openai`로 설정
- **🔍 로깅 개선**: `chat_provider` 필드도 error_log에 출력

### 기술 세부사항
**Session 수정:**
```php
if (!session_id() && !headers_sent()) {
    session_start();
}
```

**chat_provider 강제 설정:**
```php
// Chat Provider는 항상 azure-openai로 설정
$settings['chat_provider'] = 'azure-openai';
```

### 디버깅 요청
다음 로그를 확인하세요:
```bash
tail -100 /var/www/wordpress/wp-content/debug.log | grep "Azure OAuth"
```

### 영향
- ✅ Session 경고 제거
- ✅ chat_provider 항상 설정 보장
- 🔍 WordPress debug.log 확인 필요 (Chat 필드 저장 여부 확인)

## [3.0.28] - 2025-11-08

### 🐛 설정 저장 디버깅 강화
- **📊 AJAX 응답 상세 로깅 추가**: Chat/Agent 모드 설정 저장 시 saveResponse 전체 출력
- **⏰ DB 커밋 대기 시간 추가**: completeSetup에서 리다이렉트 전 2초 대기 (WordPress DB 커밋 보장)
- **🔍 설정 저장 결과 확인**: 브라우저 콘솔에서 저장된 설정 전체 출력

### 기술 세부사항
**추가된 로깅:**
```javascript
console.log('[Auto Setup] Chat 모드 설정 저장 응답:', saveResponse);
console.log('[Auto Setup] saveResponse.success:', saveResponse.success);
console.log('[Auto Setup] saveResponse.data:', saveResponse.data);
console.log('[Auto Setup] 저장된 설정:', saveResponse.data.settings);
```

**리다이렉트 지연:**
```javascript
setTimeout(function() {
    window.location.href = '...';
}, 2000); // DB 커밋 시간 보장
```

### 디버깅 체크리스트
1. ✅ AJAX 호출 성공 여부 (`saveResponse.success`)
2. ✅ `update_option` 반환값 (`saveResponse.data.save_result`)
3. ✅ DB에 저장된 실제 설정 (`saveResponse.data.settings`)
4. ✅ WordPress error_log의 상세 로그

### 영향
- 🔍 이전: AJAX 성공 메시지만 출력, DB 저장 상태 불명확
- ✅ 수정: 저장된 전체 설정을 콘솔에서 확인 가능, 리다이렉트 전 충분한 대기

## [3.0.17] - 2025-11-07

### 🐛 긴급 버그 수정 및 디버깅 개선
- **🔧 Resource Group 생성 실패 원인 표시**: 상세한 에러 메시지로 생성 실패 원인 파악 가능
- **🔑 토큰 만료 감지 개선**: 인증 토큰 만료 시 명확한 재인증 안내 표시
- **📊 AJAX 디버깅 강화**: 요청 실패 시 xhr.responseText 포함 상세 정보 표시
- **💬 사용자 피드백 개선**: 에러 상황별 명확한 안내 메시지

### 기술 세부사항
**개선된 에러 처리:**
- `createResourceGroup()`: AJAX fail 핸들러에서 xhr.responseText 출력
- `ajax_create_resource_group()`: HTTP 상태 코드별 에러 메시지 분기
- 토큰 만료(401) 감지 시 세션 초기화 및 재인증 안내
- Resource Group 조회 실패 시에도 신규 생성 시도

**추가된 로깅:**
- 모든 AJAX 응답 console.log로 출력
- 에러 응답 상세 정보 (status, error, responseText)
- WordPress error_log에 서버 측 에러 기록

### 영향
- ❌ 이전: 에러 발생 시 "Resource Group 생성 실패" 메시지만 표시
- ✅ 수정: 에러 원인, HTTP 코드, Azure 에러 메시지 모두 표시
- 🔍 디버깅: 콘솔에서 전체 요청/응답 흐름 추적 가능

## [3.0.16] - 2025-11-07

### ✨ 기존 리소스 선택 시 설정 자동 채우기 구현
- **🎯 기존 AI 리소스 선택 완전 지원**: 새로 생성할 때뿐만 아니라 기존 AI Foundry Project 선택 시에도 설정 자동 저장
- **📋 배포 목록 자동 조회**: 기존 리소스 선택 시 배포된 모델 목록 자동 표시 및 선택
- **🔑 API Key 자동 조회 및 저장**: Azure Management API를 통해 API Key 자동 조회하여 설정에 포함
- **🤖 Agent 모드 기존 리소스 지원**: 기존 AI Foundry Project에서 Agent 선택 또는 새로 생성

### 기술 세부사항
**새로 추가된 기능:**
- `getExistingResourceConfig()`: 기존 리소스에서 배포 목록 조회 및 설정 구성
- `getResourceApiKey()`: Azure API를 통한 API Key 자동 조회 및 암호화 저장
- `createNewAgentForExistingResource()`: 기존 Project에 새 Agent 생성
- `azure_oauth_get_deployments`: AI Foundry Project 배포 목록 조회 AJAX 핸들러
- `azure_oauth_save_existing_config`: API Key 포함 설정 저장 AJAX 핸들러

**자동화 플로우:**
1. **기존 Resource Group 선택** → 기존 AI Project 목록 표시
2. **기존 Project 선택** → Chat/Agent 모드에 따라 분기
3. **Chat 모드**: 배포 목록 조회 → 배포 선택 → API Key 조회 → 설정 자동 저장
4. **Agent 모드**: Agent 목록 표시 → 선택 또는 신규 생성 → 설정 자동 저장

**보안 강화:**
- API Key 암호화 저장 (`api_key_encrypted`)
- Azure Management API 권한 활용한 자동 Key 조회
- OAuth 토큰 기반 인증된 API 호출

### 영향
- ❌ 이전: 기존 리소스 선택 시 설정이 비어있어 수동 입력 필요
- ✅ 수정: 기존/신규 리소스 모두 완전 자동 설정 지원
- 🚀 완전 자동화: OAuth 승인 → 리소스 선택 → 설정 완료 (수동 입력 최소화)

## [3.0.15] - 2025-11-07

### ✨ 자동 설정 완료 후 WordPress 설정 자동 저장
- **🎯 Chat/Agent 모드 설정 자동 채우기**: OAuth 자동 설정 완료 시 Chat/Agent 모드 필드에 자동으로 값 저장
- **💾 WordPress 옵션 자동 저장**: 엔드포인트, 배포 이름, Agent ID 등 자동으로 저장
- **🔐 보안 정보 자동 연동**: Client ID, Secret, Tenant ID 자동 반영

### 기술 세부사항
**Chat 모드 자동 저장 항목:**
- `provider`: 'azure-openai'
- `chat_endpoint`: 생성된 AI Foundry 엔드포인트
- `deployment_name`: 배포 이름
- API Key는 보안상 수동 입력 필요

**Agent 모드 자동 저장 항목:**
- `agent_endpoint`: AI Foundry Project 엔드포인트
- `agent_id`: 생성된 Agent ID
- `client_id`, `client_secret_encrypted`, `tenant_id`: OAuth 설정에서 자동 복사

**구현 방식:**
1. AI 리소스 생성 성공 시 서버에서 설정 정보 반환 (`config` 객체)
2. `completeSetup(mode, config)` 함수에서 AJAX로 설정 저장
3. `ajax_save_final_config()` 핸들러에서 WordPress 옵션에 저장
4. 설정 페이지 로드 시 자동으로 채워진 값 표시

### 영향
- ❌ 이전: 자동 설정 완료 후 수동으로 모든 필드 입력 필요
- ✅ 수정: 자동 설정 완료 시 설정 페이지에 자동으로 값 채워짐
- 🔑 Chat 모드: API Key만 수동 입력 (보안상 Azure API에서 자동 조회 불가)
- ✅ Agent 모드: 모든 필드 자동 채워짐 (OAuth 설정 기반)

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

## [3.0.0] - 2025-11-07

### 🎉 OAuth 2.0 자동 설정 시스템 도입
- **🚀 Azure 승인 기반 자동 설정**: Microsoft 계정으로 로그인하여 Azure 리소스에 대한 권한 획득
- **🏗️ Resource Group 관리**: 기존 선택 또는 새로 생성 옵션 제공
- **🤖 AI Foundry Project 자동 생성**: Chat/Agent 모드에 맞는 AI 프로젝트 자동 설정
- **🔄 기존 호환성 유지**: 수동 설정 방식과 자동 설정 방식 병행 지원

### 기술 세부사항
**OAuth 2.0 인증 플로우:**
- Azure AD App Registration 자동 생성
- Client Credentials Flow 구현
- Azure Management API 권한 획득
- 팝업 기반 인증 UI

**자동 설정 기능:**
- Subscription 목록 자동 조회
- Resource Group 생성/선택 UI
- AI Foundry Project 생성 자동화
- 모드별 리소스 설정 자동 적용

### 영향
- ❌ 이전: 모든 Azure 설정을 수동으로 입력 필요
- ✅ 도입: Azure 승인 → 리소스 선택 → 자동 설정 완료
- 🔧 호환성: 기존 수동 설정 방식도 계속 지원

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
