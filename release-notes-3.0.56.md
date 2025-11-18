# v3.0.56 - UX 개선 + AI Foundry 리소스 감지 강화

## 🎯 주요 개선사항

### 1. 🔒 Auto Setup 페이지 모드 선택 비활성화

**변경 이유:**
- Auto Setup 페이지에서 모드를 변경하면 이미 저장된 리소스 설정과 불일치 발생
- 사용자 혼란 방지 및 일관된 설정 유지

**개선 효과:**
- Auto Setup: 리소스 선택에만 집중 (모드는 읽기 전용)
- Manual Settings: 모드 변경 + 수동 설정 가능
- 명확한 역할 분리 → 사용자 혼란 제거

### 2. 🔍 AI Foundry 리소스 감지 로직 강화

**문제 상황:**
- Azure OpenAI 리소스가 Agent 모드 리소스 목록에 표시됨
- `.openai.azure.com` endpoint를 가진 리소스가 AI Foundry로 오인됨

**해결 방법:**
- 3단계 검증 로직 추가:
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

### 3. 📊 Agent 리소스 조회 로깅 강화

**추가된 로그:**
- 리소스 조회 과정 가시화
- OpenAI vs AI Foundry 판별 과정 추적
- 문제 발생 시 빠른 원인 파악

**트러블슈팅 개선:**
```
[Azure OAuth] Agent 리소스 조회 시작 - RG: myResourceGroup
[Azure OAuth] MachineLearningServices 조회 성공: 2개
[Azure OAuth] ML Workspace 발견: myWorkspace
[Azure OAuth] CognitiveServices 리소스: myOpenAI | Kind: OpenAI | Is OpenAI: YES
[Azure OAuth] ❌ Azure OpenAI 제외: myOpenAI
[Azure OAuth] ✅ Agent 리소스로 추가: myAIFoundry
```

## 📋 변경 요약

| 항목 | 개선 내용 |
|------|-----------|
| **UX** | Auto Setup 모드 선택 비활성화 → 역할 명확화 |
| **로직** | AI Foundry 감지 강화 → Azure OpenAI 제외 |
| **디버깅** | 상세 로그 추가 → 트러블슈팅 용이 |

## 🔧 기술 세부사항

### 변경된 파일
- `templates/oauth-auto-setup.php`: 모드 선택 라디오 버튼 disabled 처리
- `includes/class-azure-oauth.php`: AI Foundry 리소스 감지 로직 + 로깅 추가
- `azure-ai-chatbot.php`: 버전 3.0.56으로 업데이트
- `CHANGELOG.md`: 상세 변경 이력 추가
- `readme.txt`: Stable tag 및 changelog 업데이트
- `README.md`, `README-ko.md`: 버전 배지 업데이트

## 📦 설치 방법

1. WordPress 관리자 페이지에서 플러그인 → 새로 추가
2. ZIP 파일 업로드
3. 활성화

또는 기존 버전에서 자동 업데이트

## 📖 관련 문서

- [사용자 가이드](docs/USER_GUIDE.md)
- [Azure 설정 가이드](docs/AZURE_SETUP.md)
- [전체 변경 이력](CHANGELOG.md)

---

**Full Changelog**: https://github.com/asomi7007/azure-ai-chatbot-wordpress/compare/v3.0.55...v3.0.56
