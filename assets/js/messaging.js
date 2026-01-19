/**
 * Messaging JavaScript - AJAX polling and real-time message updates
 * Handles message sending, receiving, and real-time polling for new messages
 */

class MessagingManager {
    constructor(otherUserId, currentUsername) {
        this.otherUserId = otherUserId;
        this.currentUsername = currentUsername;
        this.pollingInterval = 10000; // 10 seconds
        this.maxPollingInterval = 120000; // 2 minutes max
        this.pollingTimer = null;
        this.lastMessageId = null;
        this.isPolling = false;
        this.errorCount = 0;

        this.init();
    }

    init() {
        this.setupFormSubmission();
        this.setupTextareaAutoResize();
        this.scrollToBottom();
        this.startPolling();
        this.trackLastMessageId();

        // Pause polling when tab is not visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPolling();
            } else {
                this.startPolling();
            }
        });
    }

    /**
     * Track the ID of the last message in the conversation
     */
    trackLastMessageId() {
        const messages = document.querySelectorAll('[data-message-id]');
        if (messages.length > 0) {
            const lastMessage = messages[messages.length - 1];
            this.lastMessageId = parseInt(lastMessage.dataset.messageId);
        }
    }

    /**
     * Setup form submission with AJAX
     */
    setupFormSubmission() {
        const form = document.getElementById('send-message-form');
        const textarea = document.getElementById('message-input');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.sendMessage(form, textarea);
        });

        // Send message with Ctrl+Enter or Cmd+Enter
        textarea.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    }

    /**
     * Setup textarea auto-resize
     */
    setupTextareaAutoResize() {
        const textarea = document.getElementById('message-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        });
    }

    /**
     * Send a message via AJAX
     */
    async sendMessage(form, textarea) {
        const formData = new FormData(form);
        const content = formData.get('content').trim();

        if (!content) return;

        // Disable form during submission
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonContent = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const response = await fetch(form.action || '/messages/send', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();

                // Add message to UI
                this.addMessageToUI({
                    id: data.message.id,
                    content: data.message.content,
                    sentAt: 'Just now',
                    isCurrentUser: true,
                    isRead: false
                });

                // Update last message ID
                this.lastMessageId = data.message.id;

                // Clear form and reset height
                form.reset();
                textarea.style.height = 'auto';

                // Reset error count on success
                this.errorCount = 0;
                this.pollingInterval = 10000;
            } else {
                const error = await response.json();
                this.showError(error.error || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            this.showError('Failed to send message. Please try again.');
        } finally {
            // Re-enable form
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonContent;
        }
    }

    /**
     * Add a message to the UI
     */
    addMessageToUI(message) {
        const messagesContainer = document.getElementById('messages-container');

        // Check if empty state exists and remove it
        const emptyState = messagesContainer.querySelector('.text-center.py-8');
        if (emptyState) {
            emptyState.remove();
        }

        const messageHtml = message.isCurrentUser
            ? this.renderCurrentUserMessage(message)
            : this.renderOtherUserMessage(message);

        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        this.scrollToBottom();
    }

    /**
     * Render current user's message
     */
    renderCurrentUserMessage(message) {
        return `
            <div class="flex justify-end" data-message-id="${message.id}">
                <div class="flex flex-row-reverse max-w-[80%] sm:max-w-[70%]">
                    <div class="ml-2">
                        <div class="rounded-lg px-4 py-2 bg-yellow-600 text-white">
                            <p class="text-sm break-words whitespace-pre-wrap">${this.escapeHtml(message.content)}</p>
                        </div>
                        <div class="flex items-center mt-1 space-x-2 justify-end">
                            <span class="text-xs text-gray-500">${message.sentAt}</span>
                            ${message.isRead
                                ? '<i class="fas fa-check-double text-xs text-blue-500" title="Read"></i>'
                                : '<i class="fas fa-check text-xs text-gray-400" title="Sent"></i>'}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render other user's message
     */
    renderOtherUserMessage(message) {
        return `
            <div class="flex justify-start" data-message-id="${message.id}">
                <div class="flex flex-row max-w-[80%] sm:max-w-[70%]">
                    <div class="flex-shrink-0 mr-2">
                        ${message.avatar
                            ? `<img src="${message.avatar}" alt="${message.senderUsername}" class="w-8 h-8 rounded-full object-cover">`
                            : `<div class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center">
                                   <span class="text-white font-bold text-xs">${message.senderUsername.charAt(0).toUpperCase()}</span>
                               </div>`}
                    </div>
                    <div class="mr-2">
                        <div class="rounded-lg px-4 py-2 bg-gray-100 text-gray-900">
                            <p class="text-sm break-words whitespace-pre-wrap">${this.escapeHtml(message.content)}</p>
                        </div>
                        <div class="flex items-center mt-1 space-x-2 justify-start">
                            <span class="text-xs text-gray-500">${message.sentAt}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Start polling for new messages
     */
    startPolling() {
        if (this.isPolling) return;

        this.isPolling = true;
        this.pollingTimer = setInterval(() => {
            this.pollNewMessages();
        }, this.pollingInterval);
    }

    /**
     * Stop polling
     */
    stopPolling() {
        if (this.pollingTimer) {
            clearInterval(this.pollingTimer);
            this.pollingTimer = null;
        }
        this.isPolling = false;
    }

    /**
     * Poll for new messages from the API
     */
    async pollNewMessages() {
        if (!document.hasFocus() && document.hidden) {
            return; // Skip polling if tab is not active
        }

        try {
            const response = await fetch(`/api/social/messages/conversation/${this.otherUserId}/new`);

            if (response.ok) {
                const data = await response.json();

                if (data.count > 0 && data.messages.length > 0) {
                    // Add new messages to UI
                    data.messages.forEach(msg => {
                        // Only add if we haven't seen this message yet
                        if (!this.lastMessageId || msg.id > this.lastMessageId) {
                            this.addMessageToUI({
                                id: msg.id,
                                content: msg.content,
                                sentAt: this.formatTime(msg.sentAt),
                                isCurrentUser: false,
                                senderUsername: msg.sender.username,
                                avatar: null // Could be added to API response
                            });

                            // Update last message ID
                            this.lastMessageId = msg.id;

                            // Mark message as read
                            this.markMessageAsRead(msg.id);
                        }
                    });

                    // Update badge count in navigation
                    if (window.loadSocialBadges) {
                        window.loadSocialBadges();
                    }
                }

                // Reset error count on success
                this.errorCount = 0;
                this.pollingInterval = 10000; // Reset to 10 seconds
            } else {
                this.handlePollingError();
            }
        } catch (error) {
            console.error('Error polling for new messages:', error);
            this.handlePollingError();
        }
    }

    /**
     * Handle polling errors with exponential backoff
     */
    handlePollingError() {
        this.errorCount++;

        // Exponential backoff: double interval on each error, up to max
        this.pollingInterval = Math.min(
            this.pollingInterval * 2,
            this.maxPollingInterval
        );

        // Restart polling with new interval
        this.stopPolling();
        this.startPolling();

        console.log(`Polling error count: ${this.errorCount}, new interval: ${this.pollingInterval}ms`);
    }

    /**
     * Mark message as read via AJAX
     */
    async markMessageAsRead(messageId) {
        try {
            await fetch(`/messages/${messageId}/mark-read`, {
                method: 'POST'
            });
        } catch (error) {
            console.error('Error marking message as read:', error);
        }
    }

    /**
     * Scroll messages container to bottom
     */
    scrollToBottom() {
        const messagesContainer = document.getElementById('messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    /**
     * Format timestamp for display
     */
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffMinutes = Math.floor((now - date) / 1000 / 60);

        if (diffMinutes < 1) return 'Just now';
        if (diffMinutes < 60) return `${diffMinutes}m ago`;
        if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)}h ago`;

        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Show error message
     */
    showError(message) {
        // Create a temporary error toast
        const errorToast = document.createElement('div');
        errorToast.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg z-50';
        errorToast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(errorToast);

        // Remove after 5 seconds
        setTimeout(() => {
            errorToast.remove();
        }, 5000);
    }
}

// Initialize messaging manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMessaging);
} else {
    initializeMessaging();
}

function initializeMessaging() {
    // Only initialize on conversation pages
    const conversationPage = document.querySelector('[data-conversation-user-id]');
    if (conversationPage) {
        const otherUserId = parseInt(conversationPage.dataset.conversationUserId);
        const currentUsername = conversationPage.dataset.currentUsername || 'You';

        window.messagingManager = new MessagingManager(otherUserId, currentUsername);
    }
}
