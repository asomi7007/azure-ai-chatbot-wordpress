# Azure AI Chatbot 설치 가이드

## 📦 전체 파일 구조

플러그인을 설치하기 전에 다음 파일 구조를 확인하세요:

```
/wp-content/plugins/azure-ai-chatbot/
│
├── azure-ai-chatbot.php          # 메인 플러그인 파일 ✅
├── README.md                      # 프로젝트 README
├── INSTALL.md                     # 이 파일
│
├── assets/                        # 리소스 파일
│   ├── chatbot.css               # 프론트엔드 스타일 ✅
│   ├── chatbot.js                # 프론트엔드 스크립트 ✅
│   ├── admin.css                 # 관리자 스타일 ✅
│   └── admin.js                  # 관리자 스크립트 ✅
│
├── templates/                     # PHP 템플릿
│   ├── settings-page.php         # 설정 페이지 템플릿 ✅
│   └── guide-page.php            # 가이드 페이지 템플릿 ✅
│
└── docs/                          # 문서 폴더
    └── USER_GUIDE.md             # 사용자 가이드 (편집 가능) ✅
```

**✅ 표시된 파일은 반드시 생성해야 하는 필수 파일입니다.**

---

## 🚀 단계별 설치 방법

### 1단계: 폴더 구조 생성

FTP 또는 파일 관리자로 WordPress 서버에 접속하여 다음 폴더를 생성합니다:

```bash
/wp-content/plugins/azure-ai-chatbot/
/wp-content/plugins/azure-ai-chatbot/assets/
/wp-content/plugins/azure-ai-chatbot/templates/
/wp-content/plugins/azure-ai-chatbot/docs/
```

### 2단계: 파일 업로드

다음 파일들을 각각의 위치에 업로드합니다:

#### 루트 파일
- `azure-ai-chatbot.php` → `/wp-content/plugins/azure-ai-chatbot/`
- `README.md` → `/wp-content/plugins/azure-ai-chatbot/`

#### assets 폴더
- `chatbot.css` → `/wp-content/plugins/azure-ai-chatbot/assets/`
- `chatbot.js` → `/wp-content/plugins/azure-ai-chatbot/assets/`
- `admin.css` → `/wp-content/plugins/azure-ai-chatbot/assets/`
- `admin.js` → `/wp-content/plugins/azure-ai-chatbot/assets/`

#### templates 폴더
- `settings-page.php` → `/wp-content/plugins/azure-ai-chatbot/templates/`
- `guide-page.php` → `/wp-content/plugins/azure-ai-chatbot/templates/`

#### docs 폴더
- `USER_GUIDE.md` → `/wp-content/plugins/azure-ai-chatbot/docs/`

### 3단계: 파일 권한 설정

올바른 파일 권한을 설정합니다:

```bash
# 폴더 권한
chmod 755 /wp-content/plugins/azure-ai-chatbot/
chmod 755 /wp-content/plugins/azure-ai-chatbot/assets/
chmod 755 /wp-content/plugins/azure-ai-chatbot/templates/
chmod 755 /wp-content/plugins/azure-ai-chatbot/docs/

# 파일 권한
chmod 644 /wp-content/plugins/azure-ai-chatbot/*.php
chmod 644 /wp-content/plugins/azure-ai-chatbot/assets/*
chmod 644 /wp-content/plugins/azure-ai-chatbot/templates/*
chmod 644 /wp-content/plugins/azure-ai-chatbot/docs/*
```

### 4단계: WordPress에서 플러그인 활성화

1. WordPress 관리자 페이지 로그인
2. 좌측 메뉴에서 **플러그인** 클릭
3. 설치된 플러그인 목록에서 **"Azure AI Chatbot"** 찾기
4. **활성화** 버튼 클릭

**✨ 자동 보안 키 생성**

플러그인 활성화 시 자동으로 다음 작업이 수행됩니다:

1. **보안 키 확인**: `wp-config.php`에 WordPress 보안 키가 있는지 확인
2. **자동 생성**: 없거나 기본값이면 WordPress.org에서 자동으로 새 보안 키 생성
3. **자동 추가**: 생성된 보안 키를 `wp-config.php`에 자동으로 추가
4. **백업 생성**: 원본 `wp-config.php`를 백업 (예: `wp-config.php.backup-2025-01-15-143022`)
5. **알림 표시**: 성공/실패 여부를 관리자 화면에 표시

**성공 시 표시되는 메시지:**
```
✅ Azure AI Chatbot: WordPress 보안 키가 자동으로 생성되어 
wp-config.php에 추가되었습니다! 🎉

백업 파일: wp-config.php.backup-2025-01-15-143022
업데이트 시간: 2025-01-15 14:30:22

이제 API Key가 AES-256 암호화로 안전하게 보호됩니다.
```

**경고 메시지가 표시되는 경우:**

`wp-config.php` 파일에 쓰기 권한이 없으면 다음 메시지가 표시됩니다:

```
⚠️ Azure AI Chatbot 보안 경고: WordPress 보안 키가 설정되지 
않았거나 기본값입니다.

wp-config.php 파일에 쓰기 권한이 없어 자동 업데이트할 수 없습니다.
```

이 경우 **6단계: 보안 키 수동 설정** 섹션을 참조하세요.

### 4-1단계: 보안 키 수동 설정 (필요 시)

자동 업데이트가 실패한 경우에만 이 단계를 진행하세요.

**방법 1: 온라인 생성기 사용 (권장)**

1. [WordPress 보안 키 생성기](https://api.wordpress.org/secret-key/1.1/salt/) 접속
2. 자동으로 생성된 8줄의 코드 전체 복사
3. FTP 또는 파일 관리자로 `wp-config.php` 열기
4. 다음 섹션을 찾아서 교체:

```php
/**#@+
 * Authentication unique keys and salts.
 */
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');
/**#@-*/
```

5. 복사한 8줄로 교체
6. 파일 저장
7. 플러그인 페이지로 돌아가서 새로고침

**방법 2: SSH 명령어 사용**

```bash
# wp-config.php 백업
cp wp-config.php wp-config.php.backup

# 새 보안 키 다운로드 및 임시 저장
curl https://api.wordpress.org/secret-key/1.1/salt/ > /tmp/wp-keys.txt

# 수동으로 wp-config.php 편집
nano wp-config.php

# /tmp/wp-keys.txt 내용을 복사하여 붙여넣기
```

### 5단계: Azure 정보 확인

활성화 후 설정하기 전에 Azure에서 다음 정보를 확인합니다:

#### API Key 확인 방법
1. [Azure Portal](https://portal.azure.com) 접속
2. 검색창에 AI Foundry 리소스 이름 입력 (예: `eduelden04-2296-resource`)
3. 리소스 선택 후 좌측 메뉴에서 **"키 및 엔드포인트"** 클릭
4. **KEY 1** 또는 **KEY 2** 복사

#### 프로젝트 엔드포인트 확인
- 형식: `https://[리소스명].services.ai.azure.com/api/projects/[프로젝트명]`
- 예시: `https://eduelden04-2296-resource.services.ai.azure.com/api/projects/eduelden04-2296`

#### 에이전트 ID 확인
1. [AI Foundry](https://ai.azure.com) 접속
2. 프로젝트 선택
3. 좌측 메뉴에서 **"Agents"** 클릭
4. 사용할 에이전트 선택
5. 에이전트 상세 페이지에서 ID 복사 (형식: `asst_xxxxxxxxxxxxx`)

### 6단계: 플러그인 설정

1. WordPress 관리자 페이지 좌측 메뉴에서 **"AI Chatbot"** 클릭
2. **Azure AI Foundry 연결 설정** 섹션에 다음 정보 입력:
   - **API Key**: Azure에서 복사한 API 키
   - **프로젝트 엔드포인트**: Azure AI 프로젝트 URL
   - **에이전트 ID**: 에이전트 ID
3. **위젯 설정** 섹션:
   - **위젯 활성화** 체크박스 선택
   - 원하는 위치, 제목, 환영 메시지 입력
4. **디자인 설정** 섹션:
   - 색상 선택 (기본값 사용 또는 커스터마이징)
5. 페이지 하단의 **"설정 저장"** 버튼 클릭

### 7단계: 연결 테스트

1. 설정 페이지 하단의 **"연결 테스트"** 버튼 클릭
2. "Azure AI 연결에 성공했습니다!" 메시지 확인
3. 오류 발생 시 오류 메시지를 확인하고 설정 재검토

### 8단계: 웹사이트에서 확인

1. 웹사이트 홈페이지 방문
2. 우측 하단(또는 설정한 위치)에 채팅 버튼 확인
3. 채팅 버튼 클릭하여 채팅창 열기
4. 테스트 메시지 입력하여 AI 응답 확인

---

## 🔍 설치 확인 체크리스트

설치가 완료되었다면 다음 항목들을 확인하세요:

- [ ] 모든 파일이 올바른 위치에 업로드됨
- [ ] WordPress 관리자 페이지에서 플러그인이 "활성화됨" 상태
- [ ] 좌측 메뉴에 "AI Chatbot" 메뉴가 표시됨
- [ ] 설정 페이지에서 모든 Azure 정보 입력 완료
- [ ] "위젯 활성화" 체크박스 선택됨
- [ ] "연결 테스트" 성공
- [ ] 웹사이트에서 채팅 버튼이 보임
- [ ] 채팅 버튼 클릭 시 채팅창이 열림
- [ ] 메시지 전송 시 AI 응답 수신

---

## ⚠️ 일반적인 설치 문제

### 문제 1: "플러그인에서 XXX 파일을 찾을 수 없습니다" 오류

**원인**: 파일이 올바른 위치에 없음

**해결**:
1. 파일 구조 재확인
2. 파일 이름 및 경로 확인 (대소문자 구분)
3. 누락된 파일 업로드

### 문제 2: 플러그인 목록에 나타나지 않음

**원인**: `azure-ai-chatbot.php` 파일 문제

**해결**:
1. 파일이 정확히 `/wp-content/plugins/azure-ai-chatbot/azure-ai-chatbot.php`에 있는지 확인
2. 파일 내용이 손상되지 않았는지 확인
3. PHP 문법 오류 확인 (서버 로그 참조)

### 문제 3: "설정" 메뉴가 보이지 않음

**원인**: 관리자 권한 부족 또는 플러그인 오류

**해결**:
1. 관리자 계정으로 로그인 확인
2. 플러그인 비활성화 후 재활성화
3. 브라우저 캐시 삭제

### 문제 4: CSS/JS 파일이 로드되지 않음

**원인**: 파일 경로 또는 권한 문제

**해결**:
1. assets 폴더 및 파일 권한 확인
2. 브라우저 개발자 도구에서 404 오류 확인
3. 파일 URL 직접 접속 테스트
4. WordPress 퍼머링크 재저장

---

## 🛠️ 수동 설치 스크립트 (SSH 접근 가능한 경우)

SSH로 서버에 접속 가능하다면 다음 스크립트를 사용할 수 있습니다:

```bash
#!/bin/bash

# WordPress 플러그인 디렉토리로 이동
cd /path/to/wordpress/wp-content/plugins/

# 플러그인 폴더 생성
mkdir -p azure-ai-chatbot/{assets,templates,docs}

# 파일 다운로드 (GitHub 등에서)
# wget 또는 curl 사용

# 권한 설정
chmod 755 azure-ai-chatbot/
chmod 755 azure-ai-chatbot/assets/
chmod 755 azure-ai-chatbot/templates/
chmod 755 azure-ai-chatbot/docs/
chmod 644 azure-ai-chatbot/*.php
chmod 644 azure-ai-chatbot/assets/*
chmod 644 azure-ai-chatbot/templates/*
chmod 644 azure-ai-chatbot/docs/*

# 소유자 설정 (필요한 경우)
chown -R www-data:www-data azure-ai-chatbot/

echo "설치 완료! WordPress 관리자 페이지에서 플러그인을 활성화하세요."
```

---

## 📞 설치 지원

설치 과정에서 문제가 발생했나요?

**이메일 지원**: admin@edueldensolution.kr

다음 정보를 포함하여 문의해주세요:
- WordPress 버전
- PHP 버전
- 서버 환경 (Apache/Nginx)
- 오류 메시지 또는 스크린샷
- 수행한 단계

---

## ✅ 설치 완료 후 다음 단계

설치가 성공적으로 완료되었다면:

1. **사용 가이드 읽기**: WordPress 관리자 → AI Chatbot → 사용 가이드
2. **디자인 커스터마이징**: 색상 및 메시지 변경
3. **Function Calling 설정**: 필요한 커스텀 함수 추가
4. **모니터링**: 사용자 피드백 수집 및 개선

**Happy Chatting! 🎉**
