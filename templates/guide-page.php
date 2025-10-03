<?php
if (!defined('ABSPATH')) exit;

// Parsedown 라이브러리 로드 (WordPress 내장)
if (!class_exists('Parsedown')) {
    require_once ABSPATH . 'wp-includes/class-parsedown.php';
}

// 가이드 마크다운 파일 경로
$guide_file = AZURE_CHATBOT_PLUGIN_DIR . 'docs/USER_GUIDE.md';

// 마크다운 파일이 없으면 기본 가이드 생성
if (!file_exists($guide_file)) {
    $guide_content = "가이드 파일을 찾을 수 없습니다.";
} else {
    $markdown_content = file_get_contents($guide_file);
    $parsedown = new Parsedown();
    $guide_content = $parsedown->text($markdown_content);
}
?>

<div class="wrap azure-chatbot-guide">
    <h1>
        <span class="dashicons dashicons-book"></span>
        Azure AI Chatbot 사용 가이드
    </h1>
    
    <div class="guide-container" style="display: flex; gap: 20px; align-items: flex-start;">
        <div class="guide-sidebar" style="position: sticky; top: 32px; flex: 0 0 280px; max-height: calc(100vh - 64px); overflow-y: auto;">
            <div class="postbox">
                <h2 class="hndle">📑 목차</h2>
                <div class="inside">
                    <nav id="guide-toc">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="margin-bottom: 8px;">
                                <a href="#-소개" style="text-decoration: none; color: #2271b1; display: block; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" 
                                   onmouseover="this.style.background='#f0f0f1'" 
                                   onmouseout="this.style.background='transparent'"
                                   onclick="event.preventDefault(); scrollToSection('#-소개');">
                                    소개
                                </a>
                            </li>
                            <li style="margin-bottom: 8px;">
                                <a href="#-설치-방법" style="text-decoration: none; color: #2271b1; display: block; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" 
                                   onmouseover="this.style.background='#f0f0f1'" 
                                   onmouseout="this.style.background='transparent'"
                                   onclick="event.preventDefault(); scrollToSection('#-설치-방법');">
                                    설치 방법
                                </a>
                            </li>
                            <li style="margin-bottom: 8px;">
                                <a href="#-설정-가이드" style="text-decoration: none; color: #2271b1; display: block; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" 
                                   onmouseover="this.style.background='#f0f0f1'" 
                                   onmouseout="this.style.background='transparent'"
                                   onclick="event.preventDefault(); scrollToSection('#-설정-가이드');">
                                    설정 가이드
                                </a>
                            </li>
                            <li style="margin-bottom: 8px;">
                                <a href="#-주요-기능" style="text-decoration: none; color: #2271b1; display: block; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" 
                                   onmouseover="this.style.background='#f0f0f1'" 
                                   onmouseout="this.style.background='transparent'"
                                   onclick="event.preventDefault(); scrollToSection('#-주요-기능');">
                                    주요 기능
                                </a>
                            </li>
                            <li style="margin-bottom: 8px;">
                                <a href="#-커스터마이징" style="text-decoration: none; color: #2271b1; display: block; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" 
                                   onmouseover="this.style.background='#f0f0f1'" 
                                   onmouseout="this.style.background='transparent'"
                                   onclick="event.preventDefault(); scrollToSection('#-커스터마이징');">
                                    커스터마이징
                                </a>
                            </li>
                            <li style="margin-bottom: 8px;">
                                <a href="#-문제-해결" style="text-decoration: none; color: #2271b1; display: block; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" 
                                   onmouseover="this.style.background='#f0f0f1'" 
                                   onmouseout="this.style.background='transparent'"
                                   onclick="event.preventDefault(); scrollToSection('#-문제-해결');">
                                    문제 해결
                                </a>
                            </li>
                            <li style="margin-bottom: 8px;">
                                <a href="#-자주-묻는-질문" style="text-decoration: none; color: #2271b1; display: block; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" 
                                   onmouseover="this.style.background='#f0f0f1'" 
                                   onmouseout="this.style.background='transparent'"
                                   onclick="event.preventDefault(); scrollToSection('#-자주-묻는-질문');">
                                    자주 묻는 질문
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle">🔧 빠른 작업</h2>
                <div class="inside">
                    <ul class="quick-actions" style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 8px;">
                            <a href="admin.php?page=azure-ai-chatbot" class="button button-primary" style="width: 100%; text-align: center;">
                                ⚙️ 설정 페이지
                            </a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <a href="<?php echo admin_url('admin.php?page=azure-ai-chatbot-guide&action=edit'); ?>" class="button button-secondary" style="width: 100%; text-align: center;">
                                ✏️ 가이드 편집
                            </a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <a href="<?php echo admin_url('admin.php?page=azure-ai-chatbot-guide&action=download'); ?>" class="button button-secondary" style="width: 100%; text-align: center;">
                                ⬇️ 가이드 다운로드
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="guide-content" style="flex: 1; min-width: 0;">
            <?php if (isset($_GET['action']) && $_GET['action'] === 'edit'): ?>
                <!-- 편집 모드 -->
                <div class="postbox">
                    <h2 class="hndle">✏️ 가이드 편집</h2>
                    <div class="inside">
                        <form method="post" action="">
                            <?php wp_nonce_field('azure_chatbot_edit_guide'); ?>
                            <p>
                                <label for="guide-editor"><strong>마크다운 편집:</strong></label>
                            </p>
                            <textarea id="guide-editor" 
                                      name="guide_content" 
                                      rows="30" 
                                      style="width: 100%; font-family: monospace;"><?php echo esc_textarea(file_get_contents($guide_file)); ?></textarea>
                            <p class="description">
                                마크다운 문법을 사용하여 가이드를 작성할 수 있습니다.
                                <a href="https://www.markdownguide.org/basic-syntax/" target="_blank">마크다운 문법 보기</a>
                            </p>
                            <p>
                                <button type="submit" name="save_guide" class="button button-primary">
                                    💾 저장
                                </button>
                                <a href="admin.php?page=azure-ai-chatbot-guide" class="button button-secondary">
                                    취소
                                </a>
                            </p>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- 읽기 모드 -->
                <div class="markdown-content">
                    <?php echo $guide_content; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function scrollToSection(selector) {
    const element = document.querySelector(selector);
    if (element) {
        const offset = 100; // 상단 여백
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;
        
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
}
</script>

<?php
// 가이드 저장 처리
if (isset($_POST['save_guide']) && check_admin_referer('azure_chatbot_edit_guide')) {
    $new_content = wp_unslash($_POST['guide_content']);
    
    if (file_put_contents($guide_file, $new_content) !== false) {
        echo '<div class="notice notice-success is-dismissible"><p>가이드가 성공적으로 저장되었습니다.</p></div>';
        echo '<script>setTimeout(function(){ window.location.href = "admin.php?page=azure-ai-chatbot-guide"; }, 1500);</script>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>가이드 저장에 실패했습니다. 파일 권한을 확인하세요.</p></div>';
    }
}

// 가이드 다운로드 처리
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    header('Content-Type: text/markdown');
    header('Content-Disposition: attachment; filename="azure-chatbot-guide.md"');
    readfile($guide_file);
    exit;
}
?>
