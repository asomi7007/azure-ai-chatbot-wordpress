# Release Notes: v3.0.54

## 🎉 Major UI/UX Overhaul + Dual-Mode Intelligence

### 주요 개선사항

#### 1. 🎯 **라디오 버튼 가시성 완전 해결**

**문제:**
```javascript
[DEBUG] Radio 0: {value: 'chat', checked: true, visible: false}  ← visible: false!
[DEBUG] Radio 1: {value: 'agent', checked: false, visible: false}
```

**해결:**
- 모드 선택을 **페이지 맨 위로 이동**
- 눈에 띄는 파란색 박스로 강조
- 항상 표시되고 클릭 가능

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

#### 2. 🚫 **불필요한 자동 팝업 제거**

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

```javascript
// ✅ 새로운 코드
setTimeout(function() {
    $("html, body").animate({ scrollTop: $modeBox.offset().top - 50 }, 400);
    $modeBox.css("box-shadow", "0 0 10px rgba(0, 115, 170, 0.5)");
}, 300);
```

---

#### 3. 🔄 **듀얼 모드: Chat + Agent 정보 동시 조회**

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

```javascript
// 전역 캐시
var resourceInfoCache = {
    chat: null,      // Chat 정보
    agent: null,     // Agent 정보
    resourceId: null // 리소스 ID
};

// 리소스 선택 시
function fetchDualModeInfo(resourceId) {
    var chatPromise = fetchChatInfo(resourceId);    // 병렬 조회
    var agentPromise = fetchAgentInfo(resourceId);  // 병렬 조회

    jQuery.when(chatPromise, agentPromise).done(function() {
        console.log('[Dual Mode] ✅ Both fetches completed');
        // 캐시에 저장됨
    });
}

// 모드 전환 시
$('input[name="oauth_mode"]').on('change', function() {
    if (mode === 'agent') {
        if (resourceInfoCache.agent) {
            // ✅ 재조회 없이 캐시 사용!
            updateAgentDropdown(resourceInfoCache.agent.agents);
        }
    }
});
```

**콘솔 로그 예시:**
```
[Dual Mode] ========================================
[Dual Mode] Resource selected, fetching BOTH Chat + Agent info
[Dual Mode] Resource ID: /subscriptions/.../...
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

### 주요 변경 사항

#### UI/UX 개선
- **라디오 버튼**: 테이블 안 → 페이지 맨 위
- **자동 팝업**: 제거 (선택적 활성화)
- **강조 효과**: 모드 선택 박스 2초간 강조

#### 성능 개선
- **병렬 조회**: Chat + Agent 정보 동시 조회
- **캐시 시스템**: 모드 전환 시 재조회 불필요
- **즉시 반응**: 캐시된 데이터 사용

#### 개발자 경험
- **상세한 로그**: `[Dual Mode]`, `[Cache]` 태그로 명확히 구분
- **디버깅 용이**: 각 단계별 로그 출력

---

### 파일 변경 내역

**templates/oauth-auto-setup.php:**
- Lines 276-292: 모드 선택 UI를 맨 위로 이동
- Lines 38-64: 자동 팝업 제거, 스크롤 + 강조 효과만
- Lines 698-705: 전역 캐시 객체 추가
- Lines 1016-1030: 리소스 선택 시 듀얼 모드 조회
- Lines 1329-1436: 듀얼 모드 함수 3개 추가
  - `fetchDualModeInfo()`: 병렬 조회 오케스트레이션
  - `fetchChatInfo()`: Chat 정보 조회 + 캐시
  - `fetchAgentInfo()`: Agent 정보 조회 + 캐시
  - `updateAgentDropdown()`: Agent 드롭다운 업데이트
- Lines 1054-1072: 모드 전환 시 캐시 사용

---

### 테스트 시나리오

#### 시나리오 1: 라디오 버튼 가시성
```
1. OAuth 인증 완료
2. 페이지 로드
3. 콘솔 확인:
   [DEBUG] Total radio buttons in DOM: 2
   [DEBUG] Radio 0: {visible: true}  ← ✅
   [DEBUG] Radio 1: {visible: true}  ← ✅
4. UI 확인: 파란색 박스에 모드 선택이 명확히 보임
```

#### 시나리오 2: 듀얼 모드 조회
```
1. Chat 모드 선택 (기본값)
2. Resource Group 선택: rg-eduelden04-2296
3. AI Resource 선택: eduelden04-2296-resource
4. 콘솔 확인:
   [Dual Mode] ========================================
   [Dual Mode] Resource selected, fetching BOTH info
   [Dual Mode] [1/2] Fetching Chat info...
   [Dual Mode] [2/2] Fetching Agent info...
   [Dual Mode] ✅ Both fetches completed
   [Dual Mode] Chat info: Available
   [Dual Mode] Agent info: Not found (CognitiveServices)
5. Agent 모드로 전환
6. 콘솔 확인:
   [Cache] Checking for cached Agent info...
   [Cache] ⚠️ No cached Agent data
   (CognitiveServices 리소스이므로 Agent 없음 - 정상)
```

#### 시나리오 3: AI Foundry Hub 선택 (Agent 사용 가능)
```
1. Agent 모드 선택
2. Resource Group 선택
3. AI Foundry Hub 리소스 선택
4. 콘솔 확인:
   [Dual Mode] ✅ Both fetches completed
   [Dual Mode] Agent info: 3 agents found
5. Chat 모드로 전환
6. Agent 모드로 다시 전환
7. 콘솔 확인:
   [Cache] ✅ Using cached Agent data: 3 agents
   (재조회 없이 즉시 표시!)
```

---

### Breaking Changes
없음 - 기존 기능과 완전 호환

### Migration Guide
업그레이드만 하면 됨 - 추가 작업 불필요

---

### Known Issues

#### AI Foundry Hub 없을 때
현재 Resource Group에 AI Foundry Hub가 없으면 Agent 모드 사용 불가
- **해결 방법**: Azure Portal에서 AI Foundry Hub 생성

#### CognitiveServices (Azure OpenAI) 선택 시
Agent 조회 시 404 발생 (정상 동작)
- **이유**: Azure OpenAI는 Agent 지원 안 함
- **해결 방법**: AI Foundry Hub 선택

---

### Next Steps

**v3.0.55 예정:**
- Resource 타입 필터링 개선
- Agent 생성 UI 추가
- 설정 내보내기/가져오기

---

## 감사합니다!

이 업데이트로 UX가 대폭 개선되었습니다:
- ✅ 라디오 버튼 항상 보임
- ✅ 자동 팝업 제거
- ✅ 듀얼 모드 정보 수집
- ✅ 모드 전환 시 즉시 반응

**버그 리포트**: [GitHub Issues](https://github.com/asomi7007/azure-ai-chatbot-wordpress/issues)
