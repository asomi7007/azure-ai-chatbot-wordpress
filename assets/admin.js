/**
 * Azure AI Chatbot 관리자 스크립트
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // 색상 선택기 초기화
        if ($.fn.wpColorPicker) {
            $('.color-picker').wpColorPicker({
                change: function(event, ui) {
                    // 색상 변경 시 미리보기 업데이트 (선택사항)
                    const color = ui.color.toString();
                    console.log('Color changed:', color);
                }
            });
        }
        
        // 폼 제출 전 검증
        $('form').on('submit', function(e) {
            const apiKey = $('#api_key').val().trim();
            const endpoint = $('#endpoint').val().trim();
            const agentId = $('#agent_id').val().trim();
            
            if (!apiKey || !endpoint || !agentId) {
                alert('필수 항목(API Key, 엔드포인트, 에이전트 ID)을 모두 입력해주세요.');
                e.preventDefault();
                return false;
            }
            
            // 엔드포인트 형식 검증
            if (!endpoint.includes('services.ai.azure.com/api/projects/')) {
                if (!confirm('엔드포인트 형식이 올바르지 않을 수 있습니다. 계속하시겠습니까?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // 에이전트 ID 형식 검증
            if (!agentId.startsWith('asst_')) {
                if (!confirm('에이전트 ID가 "asst_"로 시작하지 않습니다. 계속하시겠습니까?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        
        // 목차 스크롤 하이라이트
        if ($('#guide-toc').length) {
            const headings = $('.markdown-content h2, .markdown-content h3');
            
            $(window).on('scroll', function() {
                let current = '';
                
                headings.each(function() {
                    const top = $(this).offset().top;
                    if ($(window).scrollTop() >= top - 100) {
                        current = '#' + $(this).attr('id');
                    }
                });
                
                $('#guide-toc a').removeClass('active');
                $('#guide-toc a[href="' + current + '"]').addClass('active');
            });
            
            // 부드러운 스크롤
            $('#guide-toc a').on('click', function(e) {
                e.preventDefault();
                const target = $(this).attr('href');
                
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 80
                }, 500);
            });
        }
        
        // 설정 저장 성공 메시지
        if (window.location.search.includes('settings-updated=true')) {
            setTimeout(function() {
                $('.notice-success').fadeOut();
            }, 3000);
        }
        
        // 툴팁 초기화
        $('[data-tooltip]').on('mouseenter', function() {
            const tooltip = $(this).data('tooltip');
            const $tooltip = $('<div class="admin-tooltip">' + tooltip + '</div>');
            
            $('body').append($tooltip);
            
            const offset = $(this).offset();
            $tooltip.css({
                top: offset.top - $tooltip.outerHeight() - 10,
                left: offset.left + ($(this).outerWidth() / 2) - ($tooltip.outerWidth() / 2)
            }).fadeIn(200);
        }).on('mouseleave', function() {
            $('.admin-tooltip').fadeOut(200, function() {
                $(this).remove();
            });
        });
        
        // 가이드 편집기 개선
        if ($('#guide-editor').length) {
            const editor = $('#guide-editor');
            
            // Tab 키 지원
            editor.on('keydown', function(e) {
                if (e.keyCode === 9) { // Tab
                    e.preventDefault();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const value = $(this).val();
                    
                    $(this).val(value.substring(0, start) + '    ' + value.substring(end));
                    this.selectionStart = this.selectionEnd = start + 4;
                }
            });
            
            // 자동 저장 (5분마다)
            let autoSaveTimer;
            editor.on('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(function() {
                    const content = editor.val();
                    localStorage.setItem('azure_chatbot_guide_autosave', content);
                    console.log('가이드 자동 저장됨');
                }, 300000); // 5분
            });
            
            // 자동 저장된 내용 복원
            const autosaved = localStorage.getItem('azure_chatbot_guide_autosave');
            if (autosaved && autosaved !== editor.val()) {
                if (confirm('자동 저장된 내용이 있습니다. 복원하시겠습니까?')) {
                    editor.val(autosaved);
                }
            }
        }
        
        // API 키 표시/숨김 토글
        $('.toggle-api-key').on('click', function() {
            const $button = $(this);
            const $input = $('#api_key');
            const currentType = $input.attr('type');
            
            if (currentType === 'password') {
                $input.attr('type', 'text');
                $button.text('🙈 숨기기');
            } else {
                $input.attr('type', 'password');
                $button.text('👁️ 표시');
            }
        });
        
        // API Key 입력 시 마스킹 제거
        $('#api_key').on('focus', function() {
            const currentValue = $(this).val();
            if (currentValue.indexOf('•') !== -1) {
                $(this).val('').attr('placeholder', '새 API Key를 입력하세요');
            }
        });
        
        // API Key 표시/숨김 버튼
        $('#toggle-api-key').on('click', function() {
            const $input = $('#api_key');
            const $icon = $(this).find('.dashicons');
            
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        });
        
        // 연결 테스트
        $('#test-connection').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $result = $('#test-result');
            
            // 버튼 비활성화
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 테스트 중...');
            $result.html('');
            
            // AJAX 요청
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'azure_chatbot_test_connection',
                    nonce: $('#_wpnonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        $result.html('<span style="color: #46b450;">✓ ' + response.data.message + '</span>');
                    } else {
                        $result.html('<span style="color: #dc3232;">✗ ' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    $result.html('<span style="color: #dc3232;">✗ 연결 테스트 중 오류가 발생했습니다.</span>');
                },
                complete: function() {
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-arrow-right-alt"></span> 연결 테스트');
                }
            });
        });
        
        // 실시간 미리보기 업데이트
        function updateWidgetPreview() {
            const primaryColor = $('#primary_color').val();
            const secondaryColor = $('#secondary_color').val();
            const position = $('#widget_position').val();
            
            const $preview = $('.preview-toggle');
            if ($preview.length) {
                $preview.css({
                    'background': `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`
                });
            }
            
            // 위치 업데이트
            const $previewWidget = $('.preview-widget');
            if ($previewWidget.length) {
                $previewWidget.css({
                    'position': 'absolute',
                    'bottom': position.includes('bottom') ? '20px' : 'auto',
                    'top': position.includes('top') ? '20px' : 'auto',
                    'right': position.includes('right') ? '20px' : 'auto',
                    'left': position.includes('left') ? '20px' : 'auto'
                });
            }
        }
        
        // 색상 및 위치 변경 시 미리보기 업데이트
        $('#primary_color, #secondary_color, #widget_position').on('change', updateWidgetPreview);
        
        // 초기 미리보기 설정
        if ($('.preview-toggle').length) {
            updateWidgetPreview();
        }
        
        // 설정 내보내기/가져오기 (선택사항)
        if ($('#export-settings').length) {
            $('#export-settings').on('click', function() {
                const settings = {
                    endpoint: $('#endpoint').val(),
                    agent_id: $('#agent_id').val(),
                    widget_position: $('#widget_position').val(),
                    primary_color: $('#primary_color').val(),
                    secondary_color: $('#secondary_color').val(),
                    welcome_message: $('#welcome_message').val(),
                    chat_title: $('#chat_title').val()
                };
                
                const dataStr = JSON.stringify(settings, null, 2);
                const dataBlob = new Blob([dataStr], {type: 'application/json'});
                const url = URL.createObjectURL(dataBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'azure-chatbot-settings.json';
                link.click();
            });
        }
        
        // 실시간 미리보기 (색상)
        $('.color-picker').on('change', function() {
            updatePreview();
        });
        
        function updatePreview() {
            const primaryColor = $('#primary_color').val();
            const secondaryColor = $('#secondary_color').val();
            
            // 미리보기 요소가 있다면 업데이트
            if ($('#chatbot-preview').length) {
                $('#chatbot-preview').find('.preview-button').css({
                    'background': `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`
                });
            }
        }
        
        console.log('Azure AI Chatbot 관리자 스크립트 로드됨');
    });
    
})(jQuery);
