<?php
if (!defined('ABSPATH')) exit;

// Parsedown 라이브러리 로드 (WordPress 내장)
if (!class_exists('Parsedown')) {
    require_once ABSPATH . 'wp-includes/class-parsedown.php';
}

// 현재 사용자 언어 설정 가져오기
$user_locale = get_user_locale();
$is_korean = (strpos($user_locale, 'ko') === 0);

// 언어에 따라 적절한 README 파일 선택
$guide_file = $is_korean 
    ? AZURE_CHATBOT_PLUGIN_DIR . 'README-ko.md' 
    : AZURE_CHATBOT_PLUGIN_DIR . 'README.md';

// 마크다운 파일이 없으면 기본 가이드 생성
if (!file_exists($guide_file)) {
    $guide_content = $is_korean 
        ? "가이드 파일을 찾을 수 없습니다." 
        : "Guide file not found.";
    $toc_items = [];
} else {
    $markdown_content = file_get_contents($guide_file);
    
    // 목차(TOC) 생성을 위해 헤딩 추출
    $toc_items = [];
    preg_match_all('/^##\s+(.+)$/m', $markdown_content, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $heading) {
            // 이모지와 특수문자 제거하여 ID 생성
            $clean_heading = trim($heading);
            $id = strtolower(preg_replace('/[^\w\s-]/u', '', $clean_heading));
            $id = preg_replace('/\s+/', '-', $id);
            
            $toc_items[] = [
                'title' => $clean_heading,
                'id' => $id
            ];
        }
    }
    
    // Parsedown으로 HTML 변환
    $parsedown = new Parsedown();
    $parsedown->setMarkupEscaped(false);
    $guide_content = $parsedown->text($markdown_content);
    
    // 생성된 HTML의 헤딩에 ID 추가 (Parsedown이 자동으로 ID를 생성하지 않으므로)
    $guide_content = preg_replace_callback(
        '/<h2>(.+?)<\/h2>/i',
        function($matches) {
            $heading = $matches[1];
            // 이모지와 특수문자 제거
            $clean_heading = strip_tags($heading);
            $id = strtolower(preg_replace('/[^\w\s-]/u', '', $clean_heading));
            $id = preg_replace('/\s+/', '-', $id);
            return '<h2 id="' . esc_attr($id) . '">' . $heading . '</h2>';
        },
        $guide_content
    );
}
?>

<div class="wrap azure-chatbot-guide">
    <h1>
        <span class="dashicons dashicons-book"></span>
        <?php echo $is_korean ? 'Azure AI Chatbot 사용 가이드' : 'Azure AI Chatbot User Guide'; ?>
    </h1>
    
    <div class="guide-container" style="display: flex; gap: 20px; align-items: flex-start;">
        <div class="guide-sidebar" style="position: sticky; top: 32px; flex: 0 0 280px; max-height: calc(100vh - 64px); overflow-y: auto;">
            <div class="postbox">
                <h2 class="hndle">📑 <?php echo $is_korean ? '목차' : 'Table of Contents'; ?></h2>
                <div class="inside">
                    <nav id="guide-toc">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php if (!empty($toc_items)): ?>
                                <?php foreach ($toc_items as $item): ?>
                                    <li style="margin-bottom: 8px;">
                                        <a href="#<?php echo esc_attr($item['id']); ?>" 
                                           style="text-decoration: none; color: #2271b1; display: block; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" 
                                           onmouseover="this.style.background='#f0f0f1'" 
                                           onmouseout="this.style.background='transparent'"
                                           onclick="event.preventDefault(); scrollToSection('#<?php echo esc_js($item['id']); ?>');">
                                            <?php echo esc_html($item['title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li style="padding: 8px; color: #666;">
                                    <?php echo $is_korean ? '목차가 없습니다' : 'No table of contents'; ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle">🔧 <?php echo $is_korean ? '빠른 작업' : 'Quick Actions'; ?></h2>
                <div class="inside">
                    <ul class="quick-actions" style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 8px;">
                            <a href="admin.php?page=azure-ai-chatbot" class="button button-primary" style="width: 100%; text-align: center;">
                                ⚙️ <?php echo $is_korean ? '설정 페이지' : 'Settings Page'; ?>
                            </a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <a href="https://github.com/asomi7007/azure-ai-chatbot-wordpress" target="_blank" class="button button-secondary" style="width: 100%; text-align: center;">
                                📖 GitHub
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="guide-content" style="flex: 1; min-width: 0;">
            <div class="postbox">
                <div class="inside">
                    <div class="markdown-content">
                        <?php echo $guide_content; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.markdown-content {
    font-size: 15px;
    line-height: 1.7;
    color: #1e1e1e;
}

.markdown-content h1 {
    font-size: 2em;
    font-weight: 600;
    margin-top: 24px;
    margin-bottom: 16px;
    padding-bottom: 0.3em;
    border-bottom: 1px solid #e1e4e8;
}

.markdown-content h2 {
    font-size: 1.5em;
    font-weight: 600;
    margin-top: 24px;
    margin-bottom: 16px;
    padding-bottom: 0.3em;
    border-bottom: 1px solid #e1e4e8;
}

.markdown-content h3 {
    font-size: 1.25em;
    font-weight: 600;
    margin-top: 24px;
    margin-bottom: 16px;
}

.markdown-content h4 {
    font-size: 1em;
    font-weight: 600;
    margin-top: 24px;
    margin-bottom: 16px;
}

.markdown-content p {
    margin-bottom: 16px;
}

.markdown-content ul,
.markdown-content ol {
    margin-bottom: 16px;
    padding-left: 2em;
}

.markdown-content li {
    margin-bottom: 0.25em;
}

.markdown-content code {
    background-color: rgba(175, 184, 193, 0.2);
    padding: 0.2em 0.4em;
    margin: 0;
    font-size: 85%;
    border-radius: 3px;
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
}

.markdown-content pre {
    background-color: #f6f8fa;
    padding: 16px;
    overflow: auto;
    font-size: 85%;
    line-height: 1.45;
    border-radius: 6px;
    margin-bottom: 16px;
}

.markdown-content pre code {
    background-color: transparent;
    padding: 0;
    margin: 0;
    font-size: 100%;
    border-radius: 0;
}

.markdown-content blockquote {
    padding: 0 1em;
    color: #656d76;
    border-left: 0.25em solid #d0d7de;
    margin-bottom: 16px;
}

.markdown-content table {
    border-spacing: 0;
    border-collapse: collapse;
    display: block;
    width: max-content;
    max-width: 100%;
    overflow: auto;
    margin-bottom: 16px;
}

.markdown-content table th {
    font-weight: 600;
    padding: 6px 13px;
    border: 1px solid #d0d7de;
    background-color: #f6f8fa;
}

.markdown-content table td {
    padding: 6px 13px;
    border: 1px solid #d0d7de;
}

.markdown-content table tr {
    background-color: #ffffff;
    border-top: 1px solid #d0d7de;
}

.markdown-content table tr:nth-child(2n) {
    background-color: #f6f8fa;
}

.markdown-content img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
    margin: 16px 0;
}

.markdown-content a {
    color: #0969da;
    text-decoration: none;
}

.markdown-content a:hover {
    text-decoration: underline;
}

.markdown-content hr {
    height: 0.25em;
    padding: 0;
    margin: 24px 0;
    background-color: #d0d7de;
    border: 0;
}

/* 배지 스타일 */
.markdown-content img[alt*="version"],
.markdown-content img[alt*="php"],
.markdown-content img[alt*="wordpress"],
.markdown-content img[alt*="license"] {
    display: inline-block;
    margin: 4px 4px 4px 0;
    vertical-align: middle;
}
</style>

<script>
function scrollToSection(selector) {
    const element = document.querySelector(selector);
    if (element) {
        const offset = 100;
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;
        
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
}
</script>


