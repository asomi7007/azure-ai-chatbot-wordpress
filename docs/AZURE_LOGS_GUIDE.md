# Azure App Service WordPress 로그 확인 가이드

## 📍 Azure App Service 로그 위치

### 1. Application Logs (애플리케이션 로그)
Azure App Service에서 WordPress를 실행할 때, PHP 에러 로그는 다음 위치에 저장됩니다:

```
/home/LogFiles/Application/
```

특히 PHP 에러는:
```
/home/LogFiles/php_errors.log
```

### 2. Web Server Logs (웹 서버 로그)
```
/home/LogFiles/http/
```

### 3. Detailed Error Logs
```
/home/LogFiles/DetailedErrors/
```

---

## 🔧 로그 활성화 방법

### Azure Portal에서 설정:

1. **Azure Portal** → **App Service** 선택
2. 왼쪽 메뉴에서 **"App Service logs"** 클릭
3. 다음 설정 활성화:
   - **Application Logging (Filesystem)**: **On**
   - **Level**: **Verbose** (또는 **Information**)
   - **Web server logging**: **File System**
   - **Detailed error messages**: **On**
   - **Failed request tracing**: **On**

4. **Save** 클릭

---

## 📥 로그 확인 방법

### 방법 1: Azure Portal에서 직접 확인 (추천)

1. **Azure Portal** → **App Service** 선택
2. 왼쪽 메뉴에서 **"Log stream"** 클릭
3. 실시간으로 로그 확인 가능

**또는**

1. **Azure Portal** → **App Service** 선택
2. 왼쪽 메뉴에서 **"Advanced Tools"** (Kudu) 클릭
3. **"Go →"** 버튼 클릭
4. 상단 메뉴에서 **"Debug console"** → **"CMD"** 선택
5. 경로 이동:
   ```bash
   cd LogFiles
   ```
6. 로그 파일 확인:
   ```bash
   cat php_errors.log
   ```

### 방법 2: Azure CLI 사용

```bash
# 실시간 로그 스트림
az webapp log tail --name <app-name> --resource-group <resource-group>

# 로그 다운로드
az webapp log download --name <app-name> --resource-group <resource-group>
```

### 방법 3: FTP/FTPS로 다운로드

1. **Azure Portal** → **App Service** 선택
2. 왼쪽 메뉴에서 **"Deployment Center"** 클릭
3. **"FTPS credentials"** 탭에서 FTP 정보 확인
4. FTP 클라이언트로 `/home/LogFiles/` 접속
5. 로그 파일 다운로드

### 방법 4: SSH (추천 - 가장 편리)

1. **Azure Portal** → **App Service** 선택
2. 왼쪽 메뉴에서 **"SSH"** 클릭
3. **"Go →"** 버튼 클릭
4. SSH 터미널에서:
   ```bash
   cd /home/LogFiles
   tail -f php_errors.log  # 실시간 로그 확인
   
   # 또는 특정 로그 검색
   grep "Azure OAuth" php_errors.log
   grep "Client Secret" php_errors.log
   grep "Bearer Token" php_errors.log
   ```

---

## 🐛 WordPress Debug 로그 설정

WordPress의 `wp-config.php`에 다음 설정이 있어야 합니다:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

이 설정이 있으면 로그가 다음 위치에 저장됩니다:
```
/home/site/wwwroot/wp-content/debug.log
```

### wp-config.php 확인 방법:

1. **Kudu** 또는 **SSH**로 접속
2. 경로 이동:
   ```bash
   cd /home/site/wwwroot
   ```
3. wp-config.php 확인:
   ```bash
   cat wp-config.php | grep WP_DEBUG
   ```

---

## 📊 플러그인 로그 확인

우리 플러그인(`class-azure-oauth.php`)에서 사용하는 `error_log()` 함수는:

- **PHP 에러 로그**에 기록됨: `/home/LogFiles/php_errors.log`
- **또는** (WP_DEBUG_LOG가 true인 경우): `/home/site/wwwroot/wp-content/debug.log`

### 특정 로그 검색:

```bash
# SSH 또는 Kudu에서
cd /home/LogFiles
grep "Azure OAuth" php_errors.log

# 또는
cd /home/site/wwwroot/wp-content
grep "Azure OAuth" debug.log
```

---

## 🔍 실시간 디버깅

### 1. Log Stream 사용 (가장 편리)

Azure Portal → App Service → **Log stream** 에서 실시간으로 모든 로그 확인

### 2. SSH에서 tail 명령어

```bash
# SSH 접속 후
tail -f /home/LogFiles/php_errors.log

# 또는
tail -f /home/site/wwwroot/wp-content/debug.log
```

### 3. Application Insights 사용 (선택사항)

Application Insights를 연결하면 더 강력한 로그 분석 및 검색 가능

---

## 🎯 OAuth 디버그 로그 찾기

우리 플러그인에서 추가한 로그를 찾으려면:

```bash
# SSH 또는 Kudu에서
cd /home/LogFiles
grep -E "Azure OAuth|Client Secret|Bearer Token|ajax_get_agents" php_errors.log

# 최근 100줄만 확인
tail -100 php_errors.log | grep "Azure OAuth"

# 실시간 모니터링
tail -f php_errors.log | grep --line-buffered "Azure OAuth"
```

---

## 💡 문제 해결 팁

### 로그가 보이지 않는 경우:

1. **App Service Logs가 활성화되어 있는지 확인**
2. **WP_DEBUG** 설정 확인
3. **파일 권한** 확인:
   ```bash
   ls -la /home/LogFiles/
   ls -la /home/site/wwwroot/wp-content/
   ```
4. **PHP error_log 설정** 확인:
   ```bash
   php -i | grep error_log
   ```

### 로그가 너무 많은 경우:

```bash
# 최근 로그만 보기
tail -1000 php_errors.log > recent.log

# 날짜별로 필터링
grep "2025-11-12" php_errors.log

# 특정 키워드만 추출
grep "Azure OAuth" php_errors.log > azure_oauth.log
```

---

## 📞 빠른 참조

| 목적 | 명령어 |
|------|--------|
| 실시간 로그 | `tail -f /home/LogFiles/php_errors.log` |
| OAuth 로그 검색 | `grep "Azure OAuth" /home/LogFiles/php_errors.log` |
| 최근 100줄 | `tail -100 /home/LogFiles/php_errors.log` |
| 에러만 보기 | `grep -i error /home/LogFiles/php_errors.log` |
| 로그 파일 크기 | `du -h /home/LogFiles/php_errors.log` |
| 로그 파일 삭제 | `rm /home/LogFiles/php_errors.log` (주의!) |

---

## 🚀 추천 워크플로우

1. **Azure Portal** → **App Service** → **SSH** 클릭
2. SSH 터미널에서:
   ```bash
   cd /home/LogFiles
   tail -f php_errors.log | grep --line-buffered "Azure OAuth"
   ```
3. WordPress 사이트에서 **Auto Setting** 실행
4. 터미널에서 실시간으로 로그 확인! 🎉

