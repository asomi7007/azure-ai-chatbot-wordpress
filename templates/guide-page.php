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
    
    <div class="guide-container">
        <div class="guide-sidebar">
            <div class="postbox">
                <h2 class="hndle">📑 목차</h2>
                <div class="inside">
                    <nav id="guide-toc">
                        <ul>
                            <li><a href="#introduction">소개</a></li>
                            <li><a href="#installation">설치 방법</a></li>
                            <li><a href="#configuration">설정</a></li>
                            <li><a href="#features">주요 기능</a></li>
                            <li><a href="#customization">커스터마이징</a></li>
                            <li><a href="#troubleshooting">문제 해결</a></li>
                            <li><a href="#faq">자주 묻는 질문</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle">🔧 빠른 작업</h2>
                <div class="inside">
                    <ul class="quick-actions">
                        <li>
                            <a href="admin.php?page=azure-ai-chatbot" class="button button-primary" style="width: 100%;">
                                ⚙️ 설정 페이지
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=azure-ai-chatbot-guide&action=edit'); ?>" class="button button-secondary" style="width: 100%;">
                                ✏️ 가이드 편집
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=azure-ai-chatbot-guide&action=download'); ?>" class="button button-secondary" style="width: 100%;">
                                ⬇️ 가이드 다운로드
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="guide-content">
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
