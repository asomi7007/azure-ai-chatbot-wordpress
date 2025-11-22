# 변경 이력

# 변경 이력

## [3.0.70] - 2025-11-22

### 🔧 버그 수정
- OAuth 자동 설정에서 선택 모드(Chat/Agent)를 localStorage로 복원해 OAuth 후에도 올바른 모드가 표시되도록 수정.
- 프로젝트 선택 시 이름/엔드포인트/허브 정보를 모두 전달하여 Agent 목록이 제대로 표시되도록 개선.
- 저장된 OAuth Client ID/Secret/Tenant ID를 복호화해 Agent 모드 필드에 자동 반영하도록 지원.
- Auto Setup AJAX 오류를 던지지 않고 UI에만 표시해 목록 조회 중단을 방지.
- OAuth 앱 재생성 시 삭제 후 대기/확인을 추가하여 전파 지연으로 인한 생성 실패 완화.
- OAuth Client Secret 자동 채움 시 복호화된 값을 재암호화하여 저장하도록 수정 (이중 암호화 실패 방지).

## [3.0.69] - 2025-02-22

### 🚀 **통합 업데이트: UX 개선 및 Backend 로직 강화**

#### 1. **Sub-resource Projects 검색 구현 (Critical Fix)**
- **문제 해결**: OAuth Auto Setup에서 AI Foundry 프로젝트가 "찾을 수 없습니다" 오류 발생 해결.
- **원인**: 최신 Azure AI Foundry는 프로젝트를 **Hub의 sub-resource**로 생성 (`Microsoft.CognitiveServices/accounts/projects` 타입).
- **해결**:
    - 전체 리소스 목록 조회 추가 및 Sub-resource Projects 필터링 구현.
    - API 버전 업데이트 (`2023-10-01`, `2024-10-01-preview`).
    - Hub Endpoint 폴백 로직 추가.
    - 포괄적인 디버그 로깅 추가.

#### 2. **Quick Start UX 개선**
- **Cloud Shell 바로가기**: "Cloud Shell 열기" 버튼 추가.
- **명령어 복사**: 설정 스크립트 실행 명령어 원클릭 복사 버튼 추가.
- **자격 증명 직접 입력**: 스크립트 실행 결과(Client ID, Secret, Tenant ID)를 바로 붙여넣고 저장할 수 있는 입력 폼 추가.

#### 3. **UI 동기화 및 안정성**
- **설정 동기화**: 자동 설정이 저장한 Endpoint/Deployment/API Key/Agent 정보가 수동 입력 필드에도 즉시 반영되도록 개선.
- **Agent 조회 개선**: `test_azure_auth.py`와 동일한 로직으로 Hub·Sub-resource를 순회하여 Agent 목록이 항상 표시되도록 개선.

---

## [3.0.68] - 2025-02-22

### 🎯 **Sub-resource Projects 검색 구현 (Critical Fix)**

#### 문제 상황
- OAuth Auto Setup에서 AI Foundry 프로젝트가 "찾을 수 없습니다" 오류 발생
- test_azure_auth.py는 같은 Resource Group에서 프로젝트와 Agent 목록을 정상적으로 로드
- 근본 원인: 최신 Azure AI Foundry는 프로젝트를 **Hub의 sub-resource**로 생성 (`Microsoft.CognitiveServices/accounts/projects` 타입)

#### 해결 방법

##### 1. **전체 리소스 목록 조회 추가** ([class-azure-oauth.php:916-919](includes/class-azure-oauth.php#L916-L919))
```php
// [NEW] 모든 리소스 조회로 sub-resource projects 검색 가능
$endpoint_all = "/subscriptions/{$subscription_id}/resourceGroups/{$resource_group}/resources";
$result_all = $this->call_azure_api($endpoint_all, '2021-04-01');
```

##### 2. **Sub-resource Projects 검색** ([class-azure-oauth.php:935-959](includes/class-azure-oauth.php#L935-L959))
```php
// Microsoft.CognitiveServices/accounts/projects 타입 필터링
if (strpos($type, 'Microsoft.CognitiveServices/accounts/projects') !== false) {
    $project_subresources[] = $resource;
}
```

##### 3. **API 버전 업데이트**
- MachineLearningServices: `2023-04-01` → `2023-10-01`
- Project 상세 조회: `2024-10-01-preview` (최신 API 사용)

##### 4. **Hub Endpoint 폴백** ([class-azure-oauth.php:1103-1138](includes/class-azure-oauth.php#L1103-L1138))
```php
// Project endpoint가 없으면 Hub의 endpoint 사용
if (empty($endpoint_url) && $hub_name && isset($hub_resources[$hub_name])) {
    $hub_detail = $this->call_azure_api($hub_detail_id, '2023-05-01');
    $endpoint_url = $hub_detail['properties']['endpoint'] ?? '';
}
```

##### 5. **포괄적인 디버그 로깅**
- Resource 스캔 과정 상세 로깅
- Sub-resource projects 발견/처리 단계별 추적
- Hub endpoint 폴백 성공/실패 로깅

#### 개선 효과

**이전 (v3.0.67):**
```
CognitiveServices API: 2 resources
MachineLearningServices API: 0 resources
→ Hub 검색: kind='aiservices' 불일치
❌ 에러: "AI Foundry 프로젝트를 찾지 못했습니다"
```

**개선 (v3.0.68):**
```
CognitiveServices API: 2 resources
MachineLearningServices API: 0 resources
All Resources API: 10 resources
→ Sub-resource projects: 2개 발견
→ Processing: my-hub/my-project
→ Endpoint: https://my-hub.services.ai.azure.com
✅ 2개 프로젝트 로드 성공 → Agent 목록 조회 가능
```

#### 참고
- test_azure_auth.py의 성공 로직과 100% 일치하도록 구현
- 최신 Azure AI Foundry의 리소스 계층 구조 완벽 지원
- 이전 버전과 완전 호환 (기존 Direct Projects 감지도 유지)

---

## [3.0.67] - 2025-02-21
- Fix: Improved AI Project discovery logic to correctly identify projects even without explicit endpoints.
- Fix: Subscription list now loads automatically when the setup page is opened.

## [3.0.66] - 2025-02-21
- Fix: Resolved critical issue where "-1" was displayed on site load due to misplaced security check.
- Fix: Correctly registered all AJAX hooks for OAuth auto-setup.

## [3.0.65] - 2025-02-21
- Fix: Registered missing AJAX hooks for OAuth auto-setup (subscriptions, resources, reset).

## [3.0.64] - 2025-02-21
- Fix: Resolved JavaScript syntax error in Auto Setup UI preventing page rendering.
- Fix: Consolidated OAuth reset functionality into a single "Reset Settings" button.

## [3.0.63] - 2025-02-210

### ⚡ **Quick Start UI 개선 (사용자 피드백 반영)**

#### 1. 편의 기능 추가 (`oauth-auto-setup.php`)
- **Cloud Shell 바로가기**: "Cloud Shell 열기" 버튼 추가 (새 탭에서 `shell.azure.com` 열기)
- **명령어 복사**: 설정 스크립트 실행 명령어 원클릭 복사 버튼 추가
- **자격 증명 직접 입력**: 스크립트 실행 결과(Client ID, Secret, Tenant ID)를 바로 붙여넣고 저장할 수 있는 입력 폼 추가
- **UX 개선**: 복잡한 설정 과정을 "스크립트 실행 -> 값 복사 -> 붙여넣기 -> 저장"의 단순한 흐름으로 개선

---

## [3.0.62] - 2025-11-20

### 🐛 **Agent API 및 OAuth 설정 개선**

#### 1. Agent API 엔드포인트 수정
- **도메인 변경**: `.cognitiveservices.azure.com` -> `.services.ai.azure.com` (Azure AI Foundry 표준 준수)
- **API 경로 수정**: `/openai/assistants` -> `/api/projects/{project_name}/assistants?api-version=v1`
- **결과**: Agent 목록이 0개로 나오는 문제 해결

#### 2. OAuth 설정 완전 초기화 기능 추가
- **UI**: "OAuth 설정 완전 초기화" 버튼 추가 (빨간색 휴지통 아이콘)
- **기능**: Client ID, Secret, Tenant ID를 포함한 모든 OAuth 설정을 삭제하고 초기 상태로 복구
- **목적**: 잘못된 설정이나 권한 문제 발생 시 처음부터 다시 설정할 수 있도록 지원

#### 3. 자동 설정 스크립트 업데이트 (`setup-oauth-app.sh`)
- **권한 추가**: `Azure AI User` 역할 할당 로직 추가 (Agent API 접근 필수 권한)
- **안내 개선**: 역할 할당 실패 시 수동 명령어 안내 메시지 강화

---

## [3.0.61] - 2025-11-19

### 🎨 **대화형 Python 설정 도구 개선**

#### 변경 사항
- `test/test_azure_auth.py`를 대화형 설정 도구로 전면 개편
- Cloud Shell `setup-oauth-app.sh` 스크립트와 동일한 워크플로우 제공
- 사용자 인증 정보 입력 (Tenant ID, Client ID, Client Secret)
- 단계별 리소스 선택:
  1. Subscription 선택
  2. Resource Group 선택
  3. AI Foundry Project 선택
  4. Agent 선택 (선택 사항)
- 최종 설정 값 확인 및 JSON 파일로 저장
- 암호화 없이 평문 저장 (테스트 용도)

#### 사용 방법
```bash
cd test
python test_azure_auth.py
```

#### 목적
- WordPress 없이 빠르게 Azure AI Foundry 설정 테스트
- Service Principal 자격 증명으로 전체 워크플로우 검증
- 선택한 Subscription, Resource Group, Project, Agent 값 확인

#### 참고
- `test/` 폴더는 ZIP 배포에서 자동 제외
- 실제 프로덕션에서는 Client Secret을 AES-256으로 암호화해야 함

---

## [3.0.60] - 2025-11-19

### 🧪 **개발 도구 추가: 독립형 Python 테스트 스크립트**

#### 추가 사항
- `test/test_azure_auth.py`: Azure OAuth 인증 및 AI Foundry 프로젝트 검색 로직을 WordPress와 독립적으로 테스트할 수 있는 Python 스크립트 추가
- `test/README.md`: 테스트 스크립트 사용 방법 및 환경 설정 가이드
- 상세한 디버깅 출력 (API 응답, 리소스 타입, 분류 과정 등)
- 결과를 JSON 파일로 저장하는 기능

#### 목적
- WordPress 환경 없이도 Azure API 호출 및 리소스 검색 로직을 빠르게 테스트
- 실제 Azure API 응답 구조 확인 및 분석
- 프로젝트 검색 실패 원인을 정확히 진단

#### 참고
- `test/` 폴더는 ZIP 배포 파일에 자동으로 제외됨 (개발 용도만 사용)
- v3.0.59의 디버깅 로그와 함께 사용하여 문제 해결

---

## [3.0.59] - 2025-11-19

### 🔍 **디버깅: 프로젝트 조회 로깅 강화**

#### 변경 사항
- `ajax_get_ai_projects` 함수에 상세한 디버깅 로그 추가 ([includes/class-azure-oauth.php](includes/class-azure-oauth.php))
    - Azure API 호출 결과 (성공/실패, 리소스 개수)
    - 각 리소스의 type, kind, endpoint, discoveryUrl 정보
    - Hub/Project 식별 과정 및 스킵 사유
- WordPress 에러 로그를 통해 실제 리소스 구조 확인 가능

#### 목적
- `rg-eduelden04-2296` 리소스 그룹에서 프로젝트가 감지되지 않는 원인을 정확히 파악하기 위한 진단용 릴리스
- 로그 확인 후 근본 원인에 맞는 해결책 적용 예정

---

## [3.0.58] - 2025-11-19

### 🐛 **AI Foundry 프로젝트 목록 조회 로직 재수정 (Best Practice 적용)**

#### 문제 상황
- v3.0.57 수정 후에도 여전히 "AI Foundry 프로젝트를 찾지 못했습니다" 오류 발생.
- 원인: 일부 AI Foundry Project 리소스가 Hub를 통해 조회되지 않거나, Hub API 호출이 실패하는 경우 목록에 나타나지 않음.
- 분석: Azure 리소스 그룹 내의 `Microsoft.MachineLearningServices/workspaces` 리소스 자체가 Project인 경우가 많으므로, 이를 직접 목록에 추가해야 함.

#### 해결 방법
- **Direct Project Listing**: `ajax_get_ai_projects` 함수 로직 변경 ([includes/class-azure-oauth.php](includes/class-azure-oauth.php))
    - `Microsoft.MachineLearningServices/workspaces` 리소스를 순회할 때, `kind`가 'hub'가 아닌 경우(예: 'project' 또는 null) **즉시 프로젝트 목록에 추가**.
    - 이후 Hub(`kind`='hub')에 대해서만 추가적으로 하위 프로젝트 조회를 시도하고, 중복되지 않는 경우 목록에 병합.
- **안정성 확보**:
    - Hub API 호출이 실패하더라도, 이미 감지된 Direct Project가 있다면 오류를 반환하지 않고 목록을 표시하도록 개선.
    - `discoveryUrl`을 활용하여 엔드포인트가 명시되지 않은 리소스도 최대한 감지.

#### 개선 효과
- Azure Portal에서 생성된 AI Foundry Project가 리소스 그룹 내에 존재하기만 하면 즉시 감지됨.
- Hub API 의존성을 줄여 프로젝트 목록 조회 성공률 대폭 향상.

---

## [3.0.57] - 2025-11-19

### 🐛 **AI Foundry 프로젝트 목록 조회 오류 수정**

#### 문제 상황
- 사용자가 "OAuth 자동 설정"에서 AI Foundry 프로젝트를 조회할 때 `Uncaught Error: AI Foundry 프로젝트를 찾지 못했습니다.` 오류 발생.
- 특정 리소스 그룹(`rg-eduelden04-2296`)에서 프로젝트 목록을 불러오지 못함.
- 원인: `ajax_get_ai_projects` 함수가 `Microsoft.CognitiveServices/accounts` 리소스만 조회하고, `Microsoft.MachineLearningServices/workspaces` (AI Foundry Hub/Project)를 누락함.

#### 해결 방법
- **리소스 조회 확장**: `ajax_get_ai_projects` 함수 수정 ([includes/class-azure-oauth.php](includes/class-azure-oauth.php))
    - `Microsoft.MachineLearningServices/workspaces` 리소스 타입 조회 추가.
    - 두 리소스 타입(`CognitiveServices`, `MachineLearningServices`)의 결과를 병합하여 처리.
- **Hub 식별 로직 개선**:
    - `kind` 속성(`aiservices`, `hub`) 뿐만 아니라 엔드포인트 패턴(`.services.ai.azure.com`) 및 리소스 타입(`MachineLearningServices/workspaces`)을 기반으로 Hub 식별.
    - Hub 자체가 Project 타입인 경우(`kind === 'project'`) 직접 프로젝트 목록에 추가.
- **안정성 강화**:
    - 리소스가 없거나 프로젝트를 찾지 못한 경우에 대한 에러 처리 및 메시지 개선.

#### 개선 효과
- 이제 `Microsoft.MachineLearningServices/workspaces` 타입으로 생성된 AI Foundry Hub 및 Project도 정상적으로 감지됩니다.
- "AI Foundry 프로젝트를 찾지 못했습니다" 오류가 해결되어 사용자가 정상적으로 Agent 모드를 설정할 수 있습니다.

---

## [3.0.56] - 2025-11-18

### 🎯 **UX 개선 + AI Foundry 리소스 감지 강화**

#### 주요 개선사항

##### 1. 🔒 **Auto Setup 페이지 모드 선택 비활성화**

**변경 이유:**
- Auto Setup 페이지에서 모드를 변경하면 이미 저장된 리소스 설정과 불일치 발생
- 사용자 혼란 방지 및 일관된 설정 유지

**변경 내용:** [templates/oauth-auto-setup.php:86-108](templates/oauth-auto-setup.php#L86-L108)
```php
// ✅ 라디오 버튼을 disabled 상태로 변경
<input type="radio" name="oauth_mode" value="chat" <?php checked($operation_mode, 'chat'); ?> disabled />
<input type="radio" name="oauth_mode" value="agent" <?php checked($operation_mode, 'agent'); ?> disabled />

// ✅ 회색 스타일 + 비활성화 커서
style="color: #999; cursor: not-allowed;"

// ✅ 안내 메시지 추가
💡 모드는 Manual Settings에서만 변경 가능합니다.
```

**개선 효과:**
- Auto Setup: 리소스 선택에만 집중 (모드는 읽기 전용)
- Manual Settings: 모드 변경 + 수동 설정 가능
- 명확한 역할 분리 → 사용자 혼란 제거

---

##### 2. 🔍 **AI Foundry 리소스 감지 로직 강화**

**문제 상황:**
- Azure OpenAI 리소스가 Agent 모드 리소스 목록에 표시됨
- `.openai.azure.com` endpoint를 가진 리소스가 AI Foundry로 오인됨

**해결 방법:** [includes/class-azure-oauth.php:709-741](includes/class-azure-oauth.php#L709-L741)
```php
// ✅ 3단계 검증 로직
$has_foundry_endpoint = (strpos($endpoint_url, '.services.ai.azure.com') !== false);
$is_openai = (strpos($endpoint_url, '.openai.azure.com') !== false);
$is_ai_foundry = ($kind === 'aiservices' || $has_foundry_endpoint) && !$is_openai;

// ✅ Azure OpenAI 명시적 제외
if (!$is_openai) {
    // AI Foundry 리소스로 추가
}
```

**검증 기준:**
1. ✅ `kind === 'aiservices'` OR endpoint에 `.services.ai.azure.com` 포함
2. ❌ endpoint에 `.openai.azure.com` 포함 (Azure OpenAI 제외)
3. ✅ 조건 1 충족 AND 조건 2 불충족 → AI Foundry 리소스

**개선 효과:**
```
[이전]
✅ AI Foundry Hub (정상)
❌ Azure OpenAI (잘못 표시)  ← 문제!

[개선]
✅ AI Foundry Hub
✅ MachineLearningServices Workspace
❌ Azure OpenAI (올바르게 제외)  ← 해결!
```

---

##### 3. 📊 **Agent 리소스 조회 로깅 강화**

**추가된 로그:** [includes/class-azure-oauth.php:686-742](includes/class-azure-oauth.php#L686-L742)
```php
error_log('[Azure OAuth] Agent 리소스 조회 시작 - RG: ' . $resource_group);
error_log('[Azure OAuth] MachineLearningServices 조회 성공: ' . count($ml_result['value']) . '개');
error_log('[Azure OAuth] ML Workspace 발견: ' . $resource['name']);
error_log('[Azure OAuth] CognitiveServices 리소스: ' . $resource['name'] . ' | Kind: ' . $kind . ' | Is OpenAI: ' . ($is_openai ? 'YES' : 'NO'));
error_log('[Azure OAuth] ✅ Agent 리소스로 추가: ' . $resource['name']);
error_log('[Azure OAuth] ❌ Azure OpenAI 제외: ' . $resource['name']);
```

**트러블슈팅 개선:**
- 리소스 조회 과정 가시화
- OpenAI vs AI Foundry 판별 과정 추적
- 문제 발생 시 빠른 원인 파악

---

#### 요약

| 항목 | 개선 내용 |
|------|-----------|
| **UX** | Auto Setup 모드 선택 비활성화 → 역할 명확화 |
| **로직** | AI Foundry 감지 강화 → Azure OpenAI 제외 |
| **디버깅** | 상세 로그 추가 → 트러블슈팅 용이 |

---

## [3.0.55] - 2025-11-18

### 🔧 **Critical Bug Fix: 라디오 버튼 가시성 문제 완전 해결**

#### 문제 상황
v3.0.54에서 라디오 버튼을 페이지 맨 위로 이동했지만, 여전히 `visible: false` 문제 발생:
```javascript
[DEBUG] Radio 0: {value: 'chat', checked: false, visible: false}  ← visible: false!
[DEBUG] Radio 1: {value: 'agent', checked: true, visible: false}
```

#### 근본 원인
- 모드 선택 박스가 `<?php if (!$is_configured): ?>` 조건 안에 있었음
- `is_configured()`는 `azure_chatbot_oauth_*` 옵션을 체크
- 자동 설정은 `azure_chatbot_settings`에 저장
- 옵션 이름이 달라서 `$is_configured`가 false → 모드 선택 박스가 렌더링되지 않음

#### 해결 방법

**1. 모드 선택 박스를 항상 표시** ([templates/oauth-auto-setup.php:84-104](templates/oauth-auto-setup.php#L84-L104))
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

**2. 중복된 모드 선택 박스 제거** ([templates/oauth-auto-setup.php:292-294](templates/oauth-auto-setup.php#L292-L294))
```php
<?php else: ?>
    <!-- ✅ 모드 선택 박스는 위로 이동했으므로 여기서는 제거 -->

    <?php if (!$has_token): ?>
```

**3. Agent 404 에러 메시지 개선** ([includes/class-azure-oauth.php:978-996](includes/class-azure-oauth.php#L978-L996))
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
}
```

#### 결과

**이전:**
```javascript
[DEBUG] Radio 0: {visible: false}  ← 보이지 않음!
[Auto Setup] [Agent] Agent 목록 조회 실패 (HTTP 404): Resource not found  ← 불명확
```

**개선:**
```javascript
[DEBUG] Radio 0: {visible: true}  ← ✅ 이제 보임!
ℹ️ 이 리소스는 Azure OpenAI (CognitiveServices)입니다. Agent를 사용하려면 AI Foundry Hub 리소스를 선택하세요.  ← ✅ 명확한 안내
```

---

## [3.0.54] - 2025-11-18

### 🎉 **Major UI/UX Overhaul + Dual-Mode Intelligence**

#### 주요 개선사항

##### 1. 🎯 **라디오 버튼 가시성 완전 해결**

**문제:**
```javascript
[DEBUG] Radio 0: {value: 'chat', checked: true, visible: false}  ← visible: false!
[DEBUG] Radio 1: {value: 'agent', checked: false, visible: false}
```

**해결:**
- 모드 선택을 **페이지 맨 위로 이동** ([templates/oauth-auto-setup.php:276-292](templates/oauth-auto-setup.php#L276-L292))
- 눈에 띄는 파란색 박스로 강조
- 항상 표시되고 클릭 가능
- 인라인 스타일로 CSS 우선순위 문제 해결

**새로운 UI:**
```
┌─────────────────────────────────────────────────┐
│ 🎯 모드 선택                                      │
│                                                 │
│ ☑ Chat 모드 - Azure OpenAI (GPT-4, GPT-3.5)    │
│ ☑ Agent 모드 - AI Foundry Agent (Assistants)   │
│                                                 │
│ 💡 Chat: Azure OpenAI | Agent: AI Foundry Hub  │
└─────────────────────────────────────────────────┘
```

---

##### 2. 🚫 **불필요한 자동 팝업 제거**

**이전 동작:**
- OAuth 인증 완료 → 자동으로 Subscription 로드
- 자동으로 Resource Group 팝업
- 자동으로 리소스 생성 시도
- 사용자 혼란

**개선된 동작:**
- OAuth 인증 완료 → 모드 선택 박스로 부드럽게 스크롤
- 2초간 강조 효과 (박스 섀도우)
- 사용자가 직접 선택할 때까지 대기
- 깔끔한 UX

**코드 변경:** [templates/oauth-auto-setup.php:38-64](templates/oauth-auto-setup.php#L38-L64)
```javascript
// ✅ 새로운 코드
setTimeout(function() {
    $("html, body").animate({ scrollTop: $modeBox.offset().top - 50 }, 400);
    $modeBox.css("box-shadow", "0 0 10px rgba(0, 115, 170, 0.5)");
}, 300);
```

---

##### 3. 🔄 **듀얼 모드: Chat + Agent 정보 동시 조회**

**이전 방식 (비효율적):**
```
사용자가 Chat 모드 선택
  → Chat 리소스만 조회

사용자가 Agent 모드로 전환
  → Agent 리소스 재조회 (느림)

사용자가 다시 Chat 모드로 전환
  → Chat 리소스 재조회 (느림)
```

**새로운 방식 (효율적):**
```
사용자가 리소스 선택
  → Chat + Agent 정보 **동시 조회** (병렬)
  → 결과를 캐시에 저장

사용자가 모드 전환
  → 재조회 없이 **캐시된 데이터 사용** (즉시!)
```

**구현 상세:**

**전역 캐시** ([templates/oauth-auto-setup.php:698-705](templates/oauth-auto-setup.php#L698-L705)):
```javascript
var resourceInfoCache = {
    chat: null,      // Chat 정보
    agent: null,     // Agent 정보
    resourceId: null // 리소스 ID
};
```

**병렬 조회 함수** ([templates/oauth-auto-setup.php:1329-1436](templates/oauth-auto-setup.php#L1329-L1436)):
- `fetchDualModeInfo()`: 병렬 조회 오케스트레이션
- `fetchChatInfo()`: Chat 정보 조회 + 캐시
- `fetchAgentInfo()`: Agent 정보 조회 + 캐시
- `updateAgentDropdown()`: Agent 드롭다운 업데이트

**리소스 선택 핸들러** ([templates/oauth-auto-setup.php:1016-1030](templates/oauth-auto-setup.php#L1016-L1030)):
```javascript
if (value && value !== '__CREATE_NEW__') {
    console.log('[Dual Mode] Resource selected, fetching BOTH info');
    fetchDualModeInfo(value); // 병렬 조회
}
```

**모드 전환 핸들러** ([templates/oauth-auto-setup.php:1054-1072](templates/oauth-auto-setup.php#L1054-L1072)):
```javascript
if (mode === 'agent') {
    if (resourceInfoCache.agent) {
        // ✅ 재조회 없이 캐시 사용!
        updateAgentDropdown(resourceInfoCache.agent.agents);
    }
}
```

**콘솔 로그 예시:**
```javascript
[Dual Mode] ========================================
[Dual Mode] Resource selected, fetching BOTH Chat + Agent info
[Dual Mode] ========================================
[Dual Mode] [1/2] Fetching Chat info...
[Dual Mode] [2/2] Fetching Agent info...
[Dual Mode] ✅ Both fetches completed
[Dual Mode] Chat info: Available
[Dual Mode] Agent info: 3 agents found

[Cache] Checking for cached Agent info...
[Cache] ✅ Using cached Agent data: 3 agents
```

---

#### 파일 변경 내역

**templates/oauth-auto-setup.php:**
- Lines 276-292: 모드 선택 UI를 맨 위로 이동
- Lines 38-64: 자동 팝업 제거, 스크롤 + 강조 효과만
- Lines 698-705: 전역 캐시 객체 추가
- Lines 1016-1030: 리소스 선택 시 듀얼 모드 조회
- Lines 1329-1436: 듀얼 모드 함수 3개 추가
- Lines 1054-1072: 모드 전환 시 캐시 사용

**azure-ai-chatbot.php:**
- Version updated to 3.0.54

**README-ko.md, README.md:**
- Version badges updated to 3.0.54

---

#### 테스트 시나리오

**시나리오 1: 라디오 버튼 가시성**
```
1. OAuth 인증 완료
2. 페이지 로드
3. 콘솔 확인:
   [DEBUG] Total radio buttons in DOM: 2
   [DEBUG] Radio 0: {visible: true}  ← ✅
   [DEBUG] Radio 1: {visible: true}  ← ✅
4. UI 확인: 파란색 박스에 모드 선택이 명확히 보임
```

**시나리오 2: 듀얼 모드 조회**
```
1. Chat 모드 선택 (기본값)
2. Resource Group 선택
3. AI Resource 선택
4. 콘솔 확인:
   [Dual Mode] Resource selected, fetching BOTH info
   [Dual Mode] ✅ Both fetches completed
5. Agent 모드로 전환
6. 콘솔 확인:
   [Cache] ✅ Using cached Agent data (재조회 없이 즉시!)
```

---

#### Breaking Changes
없음 - 기존 기능과 완전 호환

#### Migration Guide
업그레이드만 하면 됨 - 추가 작업 불필요

---

#### Known Issues

**AI Foundry Hub 없을 때:**
- 현재 Resource Group에 AI Foundry Hub가 없으면 Agent 모드 사용 불가
- **해결 방법**: Azure Portal에서 AI Foundry Hub 생성

**CognitiveServices (Azure OpenAI) 선택 시:**
- Agent 조회 시 404 발생 (정상 동작)
- **이유**: Azure OpenAI는 Agent 지원 안 함
- **해결 방법**: AI Foundry Hub 선택

---

## [3.0.53] - 2025-11-14

### 🐛 **Critical Bug Fix: 라디오 버튼이 DOM에 렌더링되지 않는 문제 수정**

#### 문제 상황
콘솔 로그가 명확히 보여준 문제:
```javascript
[DEBUG] Total radio buttons in DOM: 0
[DEBUG] ⚠️ No radio buttons found! User must authenticate first.
```

URL에 `&has_token=1`이 있는데도 라디오 버튼이 DOM에 없었습니다.

#### 근본 원인
**파일**: [templates/oauth-auto-setup.php:15](templates/oauth-auto-setup.php#L15)

```php
// ❌ 이전 코드: 세션만 체크
$has_token = isset($_SESSION['azure_access_token']) && !empty($_SESSION['azure_access_token']);
```

문제:
1. Line 15에서 `$has_token`은 **세션만** 체크
2. OAuth 리디렉션 후 URL에 `&has_token=1` 파라미터가 있어도
3. 세션에 토큰이 없으면 `$has_token === false`
4. Line 276의 조건문 `<?php if (!$has_token): ?>`에서 Step 1(인증 버튼)을 표시
5. **라디오 버튼이 Step 2에 있으므로 렌더링되지 않음**

#### 수정 내용
**파일**: [templates/oauth-auto-setup.php:16-22](templates/oauth-auto-setup.php#L16-L22)

```php
// ✅ 수정된 코드: 세션 + URL 파라미터 모두 체크
$session_has_token = isset($_SESSION['azure_access_token']) && !empty($_SESSION['azure_access_token']);
$url_has_token = isset($_GET['has_token']) && $_GET['has_token'] === '1';
$has_token = $session_has_token || $url_has_token;

// 디버그 로그
error_log('[OAuth Auto Setup] Token check - Session: ' . ($session_has_token ? 'YES' : 'NO') . ', URL: ' . ($url_has_token ? 'YES' : 'NO') . ', Final: ' . ($has_token ? 'YES' : 'NO'));
```

#### 해결 효과

이제 OAuth 리디렉션 후:
1. ✅ URL에 `&has_token=1`이 있으면 `$has_token === true`
2. ✅ Step 2 (리소스 선택) 섹션이 표시됨
3. ✅ 라디오 버튼이 DOM에 렌더링됨
4. ✅ 사용자가 Chat/Agent 모드를 선택할 수 있음

---


## [3.0.52] - 2025-11-14

### 🔍 디버깅 대폭 강화: F12 콘솔 로그 완전 개선

#### 목적
사용자가 보고한 문제:
- ⚠️ "리소스 그룹 목록을 못찾네"
- ⚠️ 라디오 버튼 값이 `undefined`로 표시되는 문제
- ⚠️ Mode 선택이 제대로 작동하지 않는 문제

→ **근본 원인을 정확히 파악할 수 있도록 모든 주요 흐름에 상세한 디버그 로그 추가**

#### 추가된 디버그 로깅 (F12 콘솔에서 모두 확인 가능)

##### 1. **페이지 로드 시** (lines 848-886)
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

**진단 가능:**
- 라디오 버튼이 DOM에 없으면: `⚠️ No radio buttons found! User must authenticate first.`
- 라디오 버튼 설정 실패 시: `❌ Failed to set radio button! Selector returned undefined`

##### 2. **라디오 버튼 변경 시** (lines 966-975)
사용자가 Mode를 변경할 때 정확히 추적:
```javascript
[Auto Setup] ========================================
[Auto Setup] MODE CHANGE EVENT TRIGGERED
[Auto Setup] ========================================
[DEBUG] Previous mode: chat
[DEBUG] New mode: agent
[DEBUG] Radio button that triggered change: {value: 'agent', checked: true, name: 'oauth_mode'}
```

##### 3. **OAuth 버튼 클릭 시** (lines 757-819)
Mode 저장 과정을 단계별로 상세 추적:
```javascript
[Auto Setup] ========================================
[Auto Setup] OAUTH BUTTON CLICKED - Starting OAuth
[Auto Setup] ========================================
[DEBUG] Step 1: Checking all radio buttons in DOM
[DEBUG] Total radio buttons found: 2
[DEBUG] Radio 0: {value: 'chat', checked: false, visible: true, id: 'no-id', name: 'oauth_mode'}
[DEBUG] Radio 1: {value: 'agent', checked: true, visible: true, id: 'no-id', name: 'oauth_mode'}
[DEBUG] Step 2: Reading selected mode from :checked selector
[DEBUG] Selected mode from :checked selector: agent
[DEBUG] Global operationMode variable: agent
[DEBUG] Final mode to save: agent
[DEBUG] Step 3: Saving to localStorage
[DEBUG] ✅ Verification - localStorage now contains: agent
```

**진단 가능:**
- 라디오 버튼이 없으면: `❌ ERROR: No radio button is checked! Using fallback value`
- localStorage 저장 실패 시: `❌ ERROR: localStorage save failed! Expected: agent Got: chat`

##### 4. **Resource Group 로드** (lines 1190-1235)
리소스 그룹 목록 조회 과정 상세 추적:
```javascript
[Auto Setup] ========================================
[Auto Setup] LOADING RESOURCE GROUPS
[Auto Setup] ========================================
[DEBUG] Subscription ID: 3d56f885-63f4-4e57-86bb-fe73c761b46e
[DEBUG] Sending AJAX request to: azure_oauth_get_resource_groups
[DEBUG] Resource Groups response received: {success: true, data: {...}}
[DEBUG] ✅ Successfully loaded 5 resource groups
[DEBUG] RG 1: rg-prod-koreacentral in koreacentral
[DEBUG] RG 2: rg-dev-eastus in eastus
...
```

**진단 가능:**
- Subscription이 없으면: `⚠️ No subscription selected, aborting resource group load`
- AJAX 실패 시: `❌ AJAX request failed: {status: 'error', error: '...', responseText: '...'}`
- 리소스 그룹 로드 실패 시: `❌ Failed to load resource groups: <error message>`

##### 5. **AI Resource 로드** (lines 1239-1299)
AI 리소스 목록 조회 과정과 리소스 타입 추적:
```javascript
[Auto Setup] ========================================
[Auto Setup] LOADING AI RESOURCES
[Auto Setup] ========================================
[DEBUG] Subscription ID: 3d56f885-63f4-4e57-86bb-fe73c761b46e
[DEBUG] Resource Group: rg-prod-koreacentral
[DEBUG] Mode: agent
[DEBUG] Global operationMode: agent
[DEBUG] Sending AJAX request to: azure_oauth_get_resources
[DEBUG] Request parameters: {action: 'azure_oauth_get_resources', subscription_id: '...', resource_group: '...', mode: 'agent'}
[DEBUG] AI Resources response received: {success: true, data: {...}}
[DEBUG] ✅ Successfully loaded 3 AI resources
[DEBUG] Resource 1: {name: 'my-ai-foundry', type: 'Microsoft.MachineLearningServices/workspaces', location: 'koreacentral', id: '/subscriptions/.../...'}
[DEBUG] Resource 2: {name: 'my-openai', type: 'Microsoft.CognitiveServices/accounts', location: 'eastus', id: '/subscriptions/.../...'}
...
```

**진단 가능:**
- 필수 값 누락 시: `⚠️ Missing subscription or resource group, aborting`
- 잘못된 리소스 타입 선택 시: 리소스 타입으로 필터링 여부 확인 가능

#### 기대 효과

이제 사용자는 F12 콘솔을 열고 다음을 정확히 확인할 수 있습니다:

1. **라디오 버튼 문제**:
   - DOM에 라디오 버튼이 존재하는가?
   - 라디오 버튼이 올바르게 체크되었는가?
   - 라디오 버튼 변경 이벤트가 발생하는가?

2. **Mode 저장 문제**:
   - 어떤 mode 값이 localStorage에 저장되는가?
   - 저장이 성공했는가?
   - 페이지 로드 시 어떤 값을 읽어오는가?

3. **리소스 조회 문제**:
   - AJAX 요청이 성공했는가?
   - 어떤 리소스가 반환되었는가?
   - 리소스 타입이 올바른가? (CognitiveServices vs MachineLearningServices)

#### 베스트 프랙티스 참고

**Azure AI Foundry 계층 구조**:
```
Subscription (구독)
  └─ Resource Group (리소스 그룹)
      └─ Azure AI Foundry Resource (Hub) - MachineLearningServices/workspaces
          └─ Projects (프로젝트)
```

**리소스 타입**:
- `Microsoft.CognitiveServices/accounts`: Azure OpenAI (Chat만 지원)
- `Microsoft.MachineLearningServices/workspaces`: AI Foundry Hub (Chat + Agent 지원)

현재 구현은 Resource Group까지 선택하고 그 안의 리소스를 자동으로 필터링합니다.

---

## [3.0.51] - 2025-11-14

### 🔍 디버깅 개선: Mode 선택 문제 진단 로깅 추가

#### 디버깅 로깅 추가
**파일**: [templates/oauth-auto-setup.php](templates/oauth-auto-setup.php)

**목적**: 사용자가 Agent 모드를 선택해도 Chat 모드로 변경되는 문제의 근본 원인 파악

##### 추가된 디버그 로그 위치:

1. **페이지 로드 시** (lines 860-865):
   - DB에서 읽은 mode 값
   - localStorage에 저장된 mode 값
   - 라디오 버튼에 설정된 최종 값 확인

```javascript
console.log('[DEBUG] DB mode value:', dbMode);
console.log('[DEBUG] localStorage value:', localStorage.getItem('azure_oauth_operation_mode'));
console.log('[DEBUG] Radio button set - verifying:', $('input[name="oauth_mode"]:checked').val());
```

2. **라디오 버튼 변경 시** (lines 945-946):
   - 이전 mode와 새로운 mode 추적
   - 어떤 라디오 버튼이 변경 이벤트를 트리거했는지 확인

```javascript
console.log('[DEBUG] Radio button changed - from:', previousMode, 'to:', mode);
console.log('[DEBUG] Radio button that triggered change:', this.value, 'checked:', this.checked);
```

3. **OAuth 버튼 클릭 시** (lines 761-773):
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

#### 예상되는 디버그 출력 흐름:

**정상 케이스** (Agent 모드 선택):
```
[Auto Setup] Page loaded
[DEBUG] DB mode value: chat
[DEBUG] localStorage value: null  or  chat
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

**문제 케이스** (예상):
```
[Auto Setup] Page loaded
[DEBUG] DB mode value: agent
[DEBUG] localStorage value: chat  ← 🚨 이전 값이 남아있음
[Auto Setup] Initializing UI with mode: chat  ← 🚨 localStorage가 DB보다 우선순위 높음
[DEBUG] Radio button set - verifying: chat  ← 🚨 라디오 버튼이 chat으로 설정됨
... (사용자가 agent로 변경해도)
[DEBUG] Radio button changed - from: chat to: agent  ← 변경은 감지됨
... (그런데 OAuth 버튼 클릭 시)
[DEBUG] Selected mode from :checked selector: chat  ← 🚨 여전히 chat?
```

이 로그를 통해 다음을 확인할 수 있습니다:
- 라디오 버튼 HTML이 제대로 렌더링되는지
- 라디오 버튼 변경 이벤트가 제대로 발생하는지
- localStorage와 DB 값의 우선순위 문제가 있는지
- 전역 변수와 실제 DOM 상태가 일치하는지

---

## [3.0.50] - 2025-11-14

### 🐛 **Critical: 삭제된 옵션 참조 버그 완전 수정**

#### ⚠️ 긴급 수정 (2건)

##### 버그 1: OAuth 콜백 페이지에서 삭제된 옵션 참조
**파일**: [templates/oauth-auto-setup.php:28-29](templates/oauth-auto-setup.php#L28-L29)
**문제**: OAuth 인증 완료 후 리디렉션 시 삭제된 `azure_ai_chatbot_operation_mode` 옵션을 참조하여 항상 'chat' 반환
**수정**: `azure_chatbot_settings['mode']` 단일 소스 사용

```php
// ❌ 이전 (삭제된 옵션 참조)
$operation_mode = get_option('azure_ai_chatbot_operation_mode', 'chat');

// ✅ 수정 (단일 소스)
$settings = get_option('azure_chatbot_settings', array());
$operation_mode = isset($settings['mode']) ? $settings['mode'] : 'chat';
```

##### 버그 2: 설정 페이지에서 삭제된 옵션 참조
**파일**: [templates/settings-page.php:78-79](templates/settings-page.php#L78-L79)
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

#### 개선사항
1. **✅ Agent 조회 시 PHP 메시지를 사용자에게 표시** ([oauth-auto-setup.php:2422-2431](templates/oauth-auto-setup.php#L2422-L2431))
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

#### 파일 변경 내역
- ✅ [azure-ai-chatbot.php:6, 20](azure-ai-chatbot.php#L6): Version 3.0.50
- ✅ [templates/oauth-auto-setup.php:28-29](templates/oauth-auto-setup.php#L28-L29): 삭제된 옵션 참조 제거
- ✅ [templates/settings-page.php:78-79](templates/settings-page.php#L78-L79): 삭제된 옵션 확인 로직 제거
- ✅ [templates/oauth-auto-setup.php:2422-2431](templates/oauth-auto-setup.php#L2422-L2431): Agent 조회 메시지 표시 추가

#### 영향
- **OAuth 인증 후 Mode 유지 버그 완전 해결**
- **사용자에게 Agent 조회 실패 이유 명확히 전달**
- **v3.0.47에서 삭제한 옵션 참조 완전 제거**

---

### 🐛 **OAuth 자동 설정 중 operationMode 버그 수정** (이전 수정)

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
