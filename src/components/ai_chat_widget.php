<?php
/**
 * AI Chat Widget Component
 * Floating chat button and panel for public pages
 */
?>
<!-- AI Chat Widget -->
<div id="ai-chat-widget" x-data="aiChat()" x-init="init()" 
     x-show="isEnabled"
     x-cloak
     class="ai-chat-widget">
    
    <!-- Chat Toggle Button -->
    <button @click="toggleChat()" 
            class="ai-chat-toggle"
            :class="{ 'has unread': unreadCount > 0 }"
            :title="isOpen ? 'Close chat' : 'Chat with AI'">
        <svg x-show="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <svg x-show="isOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span x-show="unreadCount > 0" class="ai-chat-badge" x-text="unreadCount"></span>
    </button>
    
    <!-- Chat Panel -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="ai-chat-panel"
         @click.away="isOpen = false">
        
        <!-- Header -->
        <div class="ai-chat-header">
            <div class="flex items-center gap-2">
                <div class="ai-avatar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-sm">AI Assistant</div>
                    <div class="text-xs opacity-80" x-text="providerName"></div>
                </div>
            </div>
            <button @click="isOpen = false" class="ai-chat-close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Messages -->
        <div class="ai-chat-messages" x-ref="messages">
            <template x-for="(msg, i) in messages" :key="i">
                <div class="ai-message" :class="msg.role">
                    <div class="ai-message-content" x-html="formatMessage(msg.content)"></div>
                    <div class="ai-message-time" x-text="msg.time"></div>
                </div>
            </template>
            
            <!-- Typing indicator -->
            <div x-show="isTyping" class="ai-message ai-message-bot">
                <div class="ai-typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div x-show="messages.length === 0" class="ai-quick-actions">
            <div class="text-xs font-medium mb-2 opacity-70">Quick Actions:</div>
            <div class="flex flex-wrap gap-1">
                <button @click="sendQuickMessage('Latest news in Nepal')" class="ai-quick-btn">
                    🇳🇪 Latest News
                </button>
                <button @click="sendQuickMessage('Economy news')" class="ai-quick-btn">
                    💰 Economy
                </button>
                <button @click="sendQuickMessage('Politics news')" class="ai-quick-btn">
                    🏛️ Politics
                </button>
                <button @click="sendQuickMessage('Technology news')" class="ai-quick-btn">
                    💻 Technology
                </button>
            </div>
        </div>
        
        <!-- Article Summary (shown when on article page) -->
        <?php if (isset($article) && !empty($article['content'])): ?>
        <div x-show="messages.length === 0" class="ai-article-summary">
            <button @click="summarizeArticle()" 
                    class="w-full py-2 px-3 rounded-lg text-sm font-medium flex items-center justify-center gap-2"
                    style="background: var(--c-primary); color: white;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Summarize This Article
            </button>
        </div>
        <?php endif; ?>
        
        <!-- Input -->
        <div class="ai-chat-input">
            <input type="text" 
                   x-model="inputMessage"
                   @keydown.enter="sendMessage()"
                   placeholder="Ask me anything..."
                   class="ai-chat-text-input">
            <button @click="sendMessage()" 
                    :disabled="!inputMessage.trim() || isTyping"
                    class="ai-chat-send">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
/* AI Chat Widget Styles */
.ai-chat-widget {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: inherit;
}

.ai-chat-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--c-primary, #2563EB), var(--c-primary-lt, #3B82F6));
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}

.ai-chat-toggle:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
}

.ai-chat-toggle.has {
    animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
    0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(37, 99, 235, 0); }
    100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
}

.ai-chat-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #EF4444;
    color: white;
    font-size: 11px;
    font-weight: 700;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
}

.ai-chat-panel {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    max-width: calc(100vw - 48px);
    height: 520px;
    max-height: calc(100vh - 120px);
    background: var(--c-surface, #ffffff);
    border-radius: 16px;
    box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid var(--c-border, #e5e7eb);
}

[data-theme="dark"] .ai-chat-panel {
    background: var(--c-surface, #1f2937);
    border-color: var(--c-border, #374151);
}

.ai-chat-header {
    padding: 16px;
    background: linear-gradient(135deg, var(--c-primary, #2563EB), var(--c-primary-lt, #3B82F6));
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ai-avatar {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-chat-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: background 0.15s;
}

.ai-chat-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ai-message {
    display: flex;
    flex-direction: column;
    max-width: 85%;
}

.ai-message-user {
    align-self: flex-end;
}

.ai-message-bot {
    align-self: flex-start;
}

.ai-message-content {
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
}

.ai-message-user .ai-message-content {
    background: var(--c-primary, #2563EB);
    color: white;
    border-bottom-right-radius: 4px;
}

.ai-message-bot .ai-message-content {
    background: var(--c-surface2, #f3f4f6);
    color: var(--c-text, #1f2937);
    border-bottom-left-radius: 4px;
}

[data-theme="dark"] .ai-message-bot .ai-message-content {
    background: var(--c-surface2, #374151);
    color: var(--c-text, #f3f4f6);
}

.ai-message-time {
    font-size: 10px;
    opacity: 0.6;
    margin-top: 4px;
    padding: 0 4px;
}

.ai-typing {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
}

.ai-typing span {
    width: 8px;
    height: 8px;
    background: var(--c-muted, #9ca3af);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.ai-typing span:nth-child(2) { animation-delay: 0.2s; }
.ai-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-8px); opacity: 1; }
}

.ai-quick-actions {
    padding: 12px 16px;
    border-bottom: 1px solid var(--c-border, #e5e7eb);
}

.ai-quick-btn {
    padding: 6px 12px;
    background: var(--c-surface2, #f3f4f6);
    border: 1px solid var(--c-border, #e5e7eb);
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.15s;
}

.ai-quick-btn:hover {
    background: var(--c-primary, #2563EB);
    color: white;
    border-color: var(--c-primary, #2563EB);
}

.ai-article-summary {
    padding: 12px 16px;
    border-top: 1px solid var(--c-border, #e5e7eb);
}

.ai-chat-input {
    padding: 12px 16px;
    border-top: 1px solid var(--c-border, #e5e7eb);
    display: flex;
    gap: 8px;
    background: var(--c-surface, #ffffff);
}

[data-theme="dark"] .ai-chat-input {
    background: var(--c-surface, #1f2937);
}

.ai-chat-text-input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid var(--c-border, #e5e7eb);
    border-radius: 24px;
    font-size: 14px;
    outline: none;
    background: var(--c-surface2, #f3f4f6);
    color: var(--c-text, #1f2937);
}

.ai-chat-text-input:focus {
    border-color: var(--c-primary, #2563EB);
}

.ai-chat-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--c-primary, #2563EB);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, transform 0.15s;
}

.ai-chat-send:hover:not(:disabled) {
    background: var(--c-primary-lt, #3B82F6);
    transform: scale(1.05);
}

.ai-chat-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Scrollbar */
.ai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.ai-chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.ai-chat-messages::-webkit-scrollbar-thumb {
    background: var(--c-border, #d1d5db);
    border-radius: 3px;
}

/* Mobile */
@media (max-width: 480px) {
    .ai-chat-widget {
        bottom: 16px;
        right: 16px;
    }
    
    .ai-chat-panel {
        width: calc(100vw - 32px);
        height: calc(100vh - 100px);
        max-height: 600px;
    }
}
</style>

<script>
function aiChat() {
    return {
        isOpen: false,
        isEnabled: false,
        isTyping: false,
        unreadCount: 0,
        inputMessage: '',
        messages: [],
        providerName: 'AI Assistant',
        
        init() {
            // Check if AI is enabled
            fetch('/api/ai/status')
                .then(r => r.json())
                .then(data => {
                    this.isEnabled = data.enabled;
                    if (data.provider === 'gemini') {
                        this.providerName = 'Google Gemini';
                    } else if (data.provider === 'deepseek') {
                        this.providerName = 'DeepSeek';
                    } else {
                        this.providerName = 'ChatGPT';
                    }
                })
                .catch(() => {
                    this.isEnabled = false;
                });
            
            // Load saved messages
            const saved = localStorage.getItem('ai_chat_messages');
            if (saved) {
                try {
                    this.messages = JSON.parse(saved);
                } catch (e) {}
            }
        },
        
        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.unreadCount = 0;
                this.$nextTick(() => {
                    if (this.$refs.messages) {
                        this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                    }
                });
            }
        },
        
        sendMessage() {
            const msg = this.inputMessage.trim();
            if (!msg || this.isTyping) return;
            
            // Add user message
            this.messages.push({
                role: 'user',
                content: msg,
                time: this.formatTime(new Date())
            });
            
            this.inputMessage = '';
            this.isTyping = true;
            this.saveMessages();
            this.scrollToBottom();
            
            // Send to API
            fetch('/api/ai/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg })
            })
            .then(r => r.json())
            .then(data => {
                this.isTyping = false;
                if (data.success) {
                    this.messages.push({
                        role: 'bot',
                        content: data.response,
                        time: this.formatTime(new Date())
                    });
                } else {
                    this.messages.push({
                        role: 'bot',
                        content: data.error || 'Sorry, I encountered an error.',
                        time: this.formatTime(new Date())
                    });
                }
                this.saveMessages();
                this.scrollToBottom();
            })
            .catch(err => {
                this.isTyping = false;
                this.messages.push({
                    role: 'bot',
                    content: 'Sorry, I could not connect to the server.',
                    time: this.formatTime(new Date())
                });
                this.saveMessages();
                this.scrollToBottom();
            });
        },
        
        sendQuickMessage(msg) {
            this.inputMessage = msg;
            this.sendMessage();
        },
        
        summarizeArticle() {
            const content = document.querySelector('.article-content')?.innerText || '';
            const title = document.querySelector('h1')?.textContent || '';
            
            if (!content) return;
            
            this.isTyping = true;
            this.messages.push({
                role: 'user',
                content: 'Please summarize this article: ' + title,
                time: this.formatTime(new Date())
            });
            this.saveMessages();
            
            fetch('/api/ai/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    message: 'Summarize this article',
                    action: 'summarize',
                    content: content,
                    title: title
                })
            })
            .then(r => r.json())
            .then(data => {
                this.isTyping = false;
                if (data.success) {
                    this.messages.push({
                        role: 'bot',
                        content: data.response,
                        time: this.formatTime(new Date())
                    });
                } else {
                    this.messages.push({
                        role: 'bot',
                        content: data.error || 'Could not summarize article.',
                        time: this.formatTime(new Date())
                    });
                }
                this.saveMessages();
                this.scrollToBottom();
            })
            .catch(() => {
                this.isTyping = false;
                this.messages.push({
                    role: 'bot',
                    content: 'Could not connect to summarize article.',
                    time: this.formatTime(new Date())
                });
                this.saveMessages();
            });
        },
        
        formatMessage(content) {
            // Convert URLs to links
            content = content.replace(
                /(https?:\/\/[^\s]+)/g, 
                '<a href="$1" target="_blank" style="color:inherit;text-decoration:underline">$1</a>'
            );
            
            // Convert newlines to <br>
            content = content.replace(/\n/g, '<br>');
            
            // Convert article slugs to links
            content = content.replace(
                /\/article\/([a-z0-9-]+)/gi,
                '<a href="/article/$1" target="_blank" style="color:var(--c-primary);text-decoration:underline">$1</a>'
            );
            
            return content;
        },
        
        formatTime(date) {
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.messages) {
                    this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                }
            });
        },
        
        saveMessages() {
            // Keep only last 20 messages
            const toSave = this.messages.slice(-20);
            localStorage.setItem('ai_chat_messages', JSON.stringify(toSave));
        }
    };
}
</script>
