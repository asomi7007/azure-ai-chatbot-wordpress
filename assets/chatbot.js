(function($) {
    'use strict';
    
    class AzureChatbot {
        constructor() {
            this.threadId = localStorage.getItem('azure_thread_id');
            this.isProcessing = false;
            this.init();
        }
        
        init() {
            this.$toggle = $('#azure-chatbot-toggle');
            this.$window = $('#azure-chatbot-window');
            this.$close = $('#azure-chatbot-close');
            this.$messages = $('#azure-chatbot-messages');
            this.$input = $('#azure-chatbot-input');
            this.$send = $('#azure-chatbot-send');
            this.$loading = $('.chatbot-loading');
            
            this.bindEvents();
        }
        
        bindEvents() {
            this.$toggle.on('click', () => this.toggleChat());
            this.$close.on('click', () => this.closeChat());
            this.$send.on('click', () => this.sendMessage());
            
            this.$input.on('keypress', (e) => {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }
        
        toggleChat() {
            this.$window.toggleClass('active');
            if (this.$window.hasClass('active')) {
                this.$input.focus();
            }
        }
        
        closeChat() {
            this.$window.removeClass('active');
        }
        
        async sendMessage() {
            const message = this.$input.val().trim();
            
            if (!message || this.isProcessing) return;
            
            this.addMessage(message, 'user');
            this.$input.val('');
            this.setProcessing(true);
            
            try {
                const response = await fetch(azureChatbot.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': azureChatbot.nonce
                    },
                    body: JSON.stringify({
                        message: message,
                        thread_id: this.threadId
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.threadId = data.thread_id;
                    localStorage.setItem('azure_thread_id', this.threadId);
                    this.addMessage(data.reply, 'bot');
                } else {
                    throw new Error(data.message || '알 수 없는 오류');
                }
                
            } catch (error) {
                console.error('Chat error:', error);
                this.addMessage('죄송합니다. 일시적인 오류가 발생했습니다. 잠시 후 다시 시도해주세요.', 'bot');
            } finally {
                this.setProcessing(false);
            }
        }
        
        addMessage(text, type) {
            const avatar = type === 'bot' ? '🤖' : '👤';
            const messageClass = type === 'bot' ? 'bot-message' : 'user-message';
            
            const html = `
                <div class="message ${messageClass}">
                    <div class="message-content">
                        <span class="message-avatar">${avatar}</span>
                        <div class="message-text">${this.escapeHtml(text)}</div>
                    </div>
                </div>
            `;
            
            this.$messages.append(html);
            this.scrollToBottom();
        }
        
        setProcessing(state) {
            this.isProcessing = state;
            this.$send.prop('disabled', state);
            this.$loading.toggle(state);
            
            if (!state) {
                this.scrollToBottom();
            }
        }
        
        scrollToBottom() {
            this.$messages.scrollTop(this.$messages[0].scrollHeight);
        }
        
        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    }
    
    $(document).ready(function() {
        new AzureChatbot();
    });
    
})(jQuery);
