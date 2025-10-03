# Azure AI Chatbot for WordPress

Azure AI Foundry의 강력한 AI 에이전트를 WordPress 웹사이트에 쉽게 통합하는 플러그인입니다.

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)

## ✨ 주요 기능

- ✅ **쉬운 설정**: wp-config.php 수정 없이 관리자 페이지에서 모든 설정
- 🎨 **완전한 커스터마이징**: 색상, 위치, 메시지 자유롭게 변경
- 🤖 **Azure AI 완벽 지원**: Function Calling, RAG, 파일 업로드 등
- 📱 **반응형 디자인**: 데스크톱과 모바일 완벽 지원
- 🔒 **보안**: API 키는 서버에서만 관리
- 📖 **마크다운 가이드**: 편집 가능한 상세 가이드 제공
- 🧪 **연결 테스트**: 설정 페이지에서 즉시 테스트 가능

## 📦 설치 방법

### 자동 설치 (WordPress.org에서 배포 시)

1. WordPress 관리자 페이지 → **플러그인** → **새로 추가**
2. "Azure AI Chatbot" 검색
3. **지금 설치** → **활성화**

### 수동 설치

1. 이 저장소를 다운로드하거나 복제
2. `azure-ai-chatbot` 폴더를 `/wp-content/plugins/`에 업로드
3. WordPress 관리자 페이지에서 플러그인 활성화

## 🚀 빠른 시작

### 1단계: Azure AI Foundry 정보 확인

Azure Portal에서 다음 정보를 확인하세요:

- **API Key**: AI Foundry 리소스 → "키 및 엔드포인트"
- **프로젝트 엔드포인트**: `https://[리소스명].services.ai.azure.com/api/projects/[프로젝트명]`
- **에이전트 ID**: AI Foundry에서 생성한 에이전트 ID (예: `asst_xxxxx`)

### 2단계: 플러그인 설정

1. WordPress 관리자 → **AI Chatbot** → **설정**
2. Azure 정보 입력
3. **위젯 활성화** 체크
4. **설정 저장**

### 3단계: 테스트

- **연결 테스트** 버튼으로 Azure 연결 확인
- 웹사이트 방문하여 채팅 버튼 확인

## 📁 파일 구조

```
azure-ai-chatbot/
├── azure-ai-chatbot.php      # 메인 플러그인 파일
├── README.md                  # 이 파일
├── assets/                    # CSS/JS 리소스
│   ├── chatbot.css           # 프론트엔드 스타일
│   ├── chatbot.js            # 프론트엔드 스크립트
│   ├── admin.css             # 관리자 스타일
│   └── admin.js              # 관리자 스크립트
├── templates/                 # PHP 템플릿
│   ├── settings-page.php     # 설정 페이지
│   └── guide-page.php        # 가이드 페이지
└── docs/                      # 문서
    └── USER_GUIDE.md         # 사용자 가이드 (편집 가능)
```

## ⚙️ 설정 옵션

### Azure 연결

| 설정 | 설명 | 필수 |
|------|------|------|
| API Key | Azure AI API 키 | ✅ |
| 프로젝트 엔드포인트 | Azure AI 프로젝트 URL | ✅ |
| 에이전트 ID | 사용할 에이전트 ID | ✅ |

### 위젯 설정

| 설정 | 설명 | 기본값 |
|------|------|--------|
| 위젯 활성화 | 채팅 위젯 표시 여부 | 비활성화 |
| 위젯 위치 | 버튼 위치 (오른쪽/왼쪽 하단) | 오른쪽 하단 |
| 채팅 제목 | 채팅창 제목 | "AI 도우미" |
| 환영 메시지 | 첫 메시지 | "안녕하세요! ..." |

### 디자인

| 설정 | 설명 | 기본값 |
|------|------|--------|
| 주 색상 | 버튼 및 사용자 메시지 색상 | #667eea |
| 보조 색상 | 그라데이션 두 번째 색상 | #764ba2 |

## 🎨 커스터마이징

### CSS 커스터마이징

테마의 `style.css`에 추가:

```css
/* 채팅 버튼 크기 */
.chatbot-toggle {
    width: 70px !important;
    height: 70px !important;
}

/* 채팅창 크기 */
.chatbot-window {
    width: 400px !important;
    height: 650px !important;
}
```

### Function Calling 추가

`functions.php`에 추가:

```php
add_filter('azure_chatbot_function_call', function($result, $function_name, $arguments) {
    if ($function_name === 'my_custom_function') {
        // 커스텀 로직
        return ['result' => 'success'];
    }
    return $result;
}, 10, 3);
```

## 🔧 개발자 가이드

### 훅 (Hooks)

**필터:**
- `azure_chatbot_function_call` - Function calling 처리
- `azure_chatbot_before_send` - 메시지 전송 전
- `azure_chatbot_response_format` - 응답 포맷 변경

**액션:**
- `azure_chatbot_after_response` - 응답 받은 후
- `azure_chatbot_widget_loaded` - 위젯 로드 완료

### API 엔드포인트

```
POST /wp-json/azure-chatbot/v1/chat
```

**요청 본문:**
```json
{
    "message": "사용자 메시지",
    "thread_id": "thread_xxxxx" (선택)
}
```

**응답:**
```json
{
    "success": true,
    "reply": "AI 응답",
    "thread_id": "thread_xxxxx"
}
```

## 📊 시스템 요구사항

- **WordPress**: 6.0 이상
- **PHP**: 7.4 이상
- **Azure 구독**: Active Azure subscription
- **메모리**: 최소 128MB PHP memory limit
- **SSL**: HTTPS 권장 (API 보안)

## 🐛 문제 해결

### 채팅 버튼이 보이지 않음

1. **설정** → **위젯 활성화** 체크 확인
2. API Key, 엔드포인트, 에이전트 ID 모두 입력 확인
3. 브라우저 캐시 삭제 및 새로고침

### API 오류 발생

1. Azure Portal에서 API Key 재확인
2. **연결 테스트** 버튼으로 진단
3. `/wp-content/debug.log` 확인

### 디버그 모드 활성화

`wp-config.php`에 추가:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 💰 비용 안내

### Azure AI Foundry 가격 (2025년 기준)

**GPT-4o 모델:**
- 입력: $2.50 per 1M tokens
- 출력: $10.00 per 1M tokens

**예상 비용:**
- 월 1,000건 대화 (평균 500토큰) ≈ $3-5
- 월 10,000건 대화 ≈ $30-50

자세한 요금: [Azure 가격 계산기](https://azure.microsoft.com/pricing/calculator/)

## 🔐 보안 고려사항

### API Key 암호화 저장

**중요**: 이 플러그인은 API Key를 평문으로 저장하지 않습니다!

#### 암호화 상세
- **알고리즘**: AES-256-CBC
- **키 생성**: WordPress 보안 상수 조합 (SHA-256 해시)
- **IV**: 랜덤 생성 (매번 다름)
- **요구사항**: OpenSSL PHP 확장 (대부분의 서버에 기본 설치)

#### 자동 보안 키 생성 ✨

**플러그인 활성화 시 자동으로:**

1. `wp-config.php`의 보안 키 확인
2. 보안 키가 없거나 기본값(`put your unique phrase here`)이면:
   - WordPress.org API에서 새 보안 키 자동 생성
   - `wp-config.php`에 자동 추가/업데이트
   - 기존 파일 백업 (`wp-config.php.backup-YYYYMMDD-HHMMSS`)
   - 성공 여부를 관리자 화면에 알림
3. 파일 쓰기 권한이 없으면 수동 설정 안내

**성공 시 표시:**
```
✅ WordPress 보안 키가 자동으로 생성되어 wp-config.php에 추가되었습니다!
백업 파일: wp-config.php.backup-2025-01-15-143022
```

**수동 설정 (필요 시):**
```php
// wp-config.php에 추가
define('AUTH_KEY', 'your-unique-phrase');
define('SECURE_AUTH_KEY', 'your-unique-phrase');
define('LOGGED_IN_KEY', 'your-unique-phrase');
define('NONCE_KEY', 'your-unique-phrase');
define('AUTH_SALT', 'your-unique-phrase');
define('SECURE_AUTH_SALT', 'your-unique-phrase');
define('LOGGED_IN_SALT', 'your-unique-phrase');
define('NONCE_SALT', 'your-unique-phrase');
```

보안 키 생성: https://api.wordpress.org/secret-key/1.1/salt/

### 보안 기능

- ✅ **AES-256 암호화**: API Key 데이터베이스 암호화
- ✅ **API Key 마스킹**: 설정 페이지에서 전체 키 숨김 (예: ab12••••••••xy89)
- ✅ **서버 사이드 처리**: API Key는 서버에서만 사용, 클라이언트 노출 없음
- ✅ **WordPress Nonce**: CSRF 공격 방어
- ✅ **입력 검증**: 모든 입력 sanitization
- ✅ **권한 확인**: 관리자만 설정 변경 가능
- ❌ **Rate Limiting**: 향후 업데이트 예정

### 보안 권장사항

1. **HTTPS 사용**: SSL 인증서 필수
2. **WordPress 업데이트**: 최신 버전 유지
3. **강력한 비밀번호**: 관리자 계정 보안
4. **2FA 활성화**: 2단계 인증 사용
5. **정기 백업**: 데이터베이스 및 파일 백업
6. **보안 플러그인**: Wordfence, iThemes Security 등 사용

## 📈 성능 최적화

### 권장 설정

1. **캐싱 플러그인 사용**: WP Rocket, W3 Total Cache
2. **CDN 활용**: Cloudflare, Amazon CloudFront
3. **이미지 최적화**: Imagify, ShortPixel
4. **데이터베이스 최적화**: WP-Optimize

### 속도 개선 팁

- Thread ID를 로컬 스토리지에 저장하여 불필요한 생성 방지
- 에이전트 프롬프트를 간결하게 유지
- Function calling 응답 캐싱 고려

## 🌍 다국어 지원

현재 한국어로 제공되며, 향후 영어 등 추가 언어를 지원할 예정입니다.

### 번역 기여

번역에 참여하고 싶으신가요?
- `.pot` 파일: `languages/azure-ai-chatbot.pot`
- 연락처: admin@edueldensolution.kr

## 🤝 기여하기

### 버그 리포트

다음 정보와 함께 이메일 보내주세요:
- WordPress 버전
- PHP 버전
- 플러그인 버전
- 오류 메시지
- 재현 단계

### 기능 제안

새 기능 아이디어가 있으신가요?
- 이메일: admin@edueldensolution.kr
- 제목: [Feature Request] 기능 제목

### 코드 기여

1. Fork this repository
2. Create your feature branch: `git checkout -b feature/AmazingFeature`
3. Commit your changes: `git commit -m 'Add some AmazingFeature'`
4. Push to the branch: `git push origin feature/AmazingFeature`
5. Open a Pull Request

## 📝 변경 로그

### 2.0.0 (2025-01-XX)

**추가:**
- ✨ 관리자 페이지에서 모든 설정 가능
- ✨ 마크다운 가이드 편집 기능
- ✨ 색상 및 위치 커스터마이징
- ✨ 연결 테스트 기능
- ✨ Function calling 확장 포인트

**개선:**
- 🎨 향상된 UI/UX
- 🔒 보안 강화 (Nonce 검증)
- 📱 모바일 반응형 개선
- ⚡ 성능 최적화

**수정:**
- 🐛 Thread ID 저장 버그 수정
- 🐛 색상 선택기 버그 수정

### 1.0.0 (초기 릴리스)
- 기본 채팅 기능
- Azure AI Foundry 연동

## 📚 추가 리소스

### 공식 문서
- [Azure AI Foundry](https://learn.microsoft.com/azure/ai-foundry/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)

### 튜토리얼
- [Azure AI Agent 생성하기](https://ai.azure.com)
- [Function Calling 가이드](https://learn.microsoft.com/azure/ai-foundry/agents/)

### 커뮤니티
- **이메일 지원**: admin@edueldensolution.kr
- **웹사이트**: https://edueldensolution.kr

## 📄 라이선스

이 프로젝트는 GPL-2.0+ 라이선스 하에 배포됩니다.

```
Copyright (C) 2025 허석 (Heo Seok)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
```

## 👤 제작자

**허석 (Heo Seok)**
- 이메일: admin@edueldensolution.kr
- 웹사이트: https://edueldensolution.kr
- 위치: 대한민국

## 🙏 감사의 말

이 플러그인을 만드는 데 도움을 주신 분들:
- Azure AI Foundry 팀
- WordPress 커뮤니티
- 모든 베타 테스터분들

## 💡 향후 계획

### v2.1.0 (예정)
- [ ] 실시간 스트리밍 응답
- [ ] 대화 내역 대시보드
- [ ] 다중 에이전트 지원
- [ ] 음성 입력/출력

### v2.2.0 (예정)
- [ ] 완전한 다국어 지원
- [ ] 고급 분석 대시보드
- [ ] A/B 테스트 기능
- [ ] 화이트라벨 옵션

### v3.0.0 (장기)
- [ ] AI 학습 데이터 관리
- [ ] 멀티사이트 지원
- [ ] REST API 확장
- [ ] Webhook 통합

## ❓ FAQ

**Q: 무료인가요?**  
A: 플러그인은 무료이지만, Azure AI Foundry 사용료는 별도로 발생합니다.

**Q: 다른 AI 서비스를 지원하나요?**  
A: 현재는 Azure AI Foundry만 지원하며, 향후 OpenAI API 등 추가 예정입니다.

**Q: 상업적 이용이 가능한가요?**  
A: 네, GPL 라이선스 하에 자유롭게 사용 가능합니다.

**Q: 업데이트는 어떻게 받나요?**  
A: WordPress 관리자 페이지에서 자동으로 업데이트 알림을 받습니다.

**Q: 기술 지원은 어떻게 받나요?**  
A: admin@edueldensolution.kr로 문의하시거나 사용 가이드를 참고하세요.

---

⭐ 이 플러그인이 유용하다면 GitHub에서 Star를 눌러주세요!

🐛 버그를 발견하셨나요? Issue를 등록해주세요.

💬 질문이 있으신가요? admin@edueldensolution.kr로 연락주세요.
