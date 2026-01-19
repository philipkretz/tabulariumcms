/**
 * Notifications JavaScript - AJAX polling and real-time notification updates
 * Handles notification badge updates and dropdown content refresh
 */

class NotificationManager {
    constructor() {
        this.pollingInterval = 30000; // 30 seconds
        this.maxPollingInterval = 120000; // 2 minutes max
        this.pollingTimer = null;
        this.isPolling = false;
        this.errorCount = 0;
        this.lastNotificationCount = 0;

        this.init();
    }

    init() {
        this.startPolling();
        this.setupDropdownRefresh();

        // Pause polling when tab is not visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPolling();
            } else {
                this.startPolling();
            }
        });

        // Initial load
        this.pollNotifications();
    }

    /**
     * Start polling for notifications
     */
    startPolling() {
        if (this.isPolling) return;

        this.isPolling = true;
        this.pollingTimer = setInterval(() => {
            this.pollNotifications();
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
     * Poll for new notifications from the API
     */
    async pollNotifications() {
        if (!document.hasFocus() && document.hidden) {
            return; // Skip polling if tab is not active
        }

        try {
            const response = await fetch('/api/social/notifications/unread');

            if (response.ok) {
                const data = await response.json();

                // Update notification badge
                this.updateNotificationBadge(data.count);

                // Update dropdown if open
                if (data.notifications && data.notifications.length > 0) {
                    this.updateNotificationDropdown(data.notifications);
                }

                // Show browser notification if new notifications
                if (data.count > this.lastNotificationCount && this.lastNotificationCount > 0) {
                    this.showBrowserNotification(data.count - this.lastNotificationCount);
                }

                this.lastNotificationCount = data.count;

                // Reset error count on success
                this.errorCount = 0;
                this.pollingInterval = 30000; // Reset to 30 seconds
            } else {
                this.handlePollingError();
            }
        } catch (error) {
            console.error('Error polling for notifications:', error);
            this.handlePollingError();
        }
    }

    /**
     * Update notification badge in navigation
     */
    updateNotificationBadge(count) {
        const badge = document.getElementById('notifications-badge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    /**
     * Update notification dropdown content
     */
    updateNotificationDropdown(notifications) {
        const dropdownContainer = document.querySelector('[data-notifications-dropdown]');
        if (!dropdownContainer) return;

        // Only update if dropdown is currently open (has x-show="open" and is visible)
        const dropdown = dropdownContainer.closest('[x-data]');
        if (!dropdown) return;

        // Clear existing notifications
        const notificationsList = dropdownContainer.querySelector('[data-notifications-list]');
        if (!notificationsList) return;

        // Add new notifications
        notificationsList.innerHTML = '';
        notifications.forEach(notification => {
            const notificationHtml = this.renderNotification(notification);
            notificationsList.insertAdjacentHTML('beforeend', notificationHtml);
        });
    }

    /**
     * Render a notification item
     */
    renderNotification(notification) {
        const iconMap = {
            'friend_request': { icon: 'fa-user-plus', color: 'yellow' },
            'friend_accepted': { icon: 'fa-user-check', color: 'green' },
            'message_received': { icon: 'fa-envelope', color: 'blue' },
            'default': { icon: 'fa-bell', color: 'gray' }
        };

        const iconData = iconMap[notification.type] || iconMap.default;

        // Determine notification link
        let linkUrl = '/notifications';
        if (notification.type === 'message_received' && notification.relatedUserId) {
            linkUrl = `/messages/conversation/${notification.relatedUserId}`;
        } else if (notification.type === 'friend_request') {
            linkUrl = '/friends/requests';
        }

        return `
            <a href="${linkUrl}"
               class="block px-4 py-3 hover:bg-gray-50 transition bg-yellow-50"
               onclick="markNotificationRead(${notification.id})">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-${iconData.color}-100 rounded-full flex items-center justify-center">
                            <i class="fas ${iconData.icon} text-${iconData.color}-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-900 font-semibold line-clamp-2">
                            ${this.escapeHtml(notification.message)}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            ${this.formatTime(notification.createdAt)}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="inline-block w-2 h-2 bg-yellow-600 rounded-full"></span>
                    </div>
                </div>
            </a>
        `;
    }

    /**
     * Show browser notification (if permission granted)
     */
    async showBrowserNotification(count) {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            new Notification('TabulariumCMS', {
                body: `You have ${count} new notification${count > 1 ? 's' : ''}`,
                icon: '/favicon.ico',
                badge: '/favicon.ico'
            });
        } else if (Notification.permission !== 'denied') {
            // Request permission
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                this.showBrowserNotification(count);
            }
        }
    }

    /**
     * Setup dropdown refresh when opened
     */
    setupDropdownRefresh() {
        // Look for notification bell button
        const bellButton = document.getElementById('notifications-bell');
        if (!bellButton) return;

        bellButton.addEventListener('click', () => {
            // Refresh notifications when dropdown is opened
            setTimeout(() => {
                this.pollNotifications();
            }, 100);
        });
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

        console.log(`Notification polling error count: ${this.errorCount}, new interval: ${this.pollingInterval}ms`);
    }

    /**
     * Format timestamp for display
     */
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffMinutes = Math.floor((now - date) / 1000 / 60);
        const diffHours = Math.floor(diffMinutes / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffMinutes < 1) return 'Just now';
        if (diffMinutes < 60) return `${diffMinutes}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays} days ago`;

        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

/**
 * Mark notification as read (globally available)
 */
window.markNotificationRead = async function(notificationId) {
    try {
        await fetch(`/notifications/${notificationId}/mark-read`, {
            method: 'POST'
        });

        // Update badge count
        if (window.notificationManager) {
            window.notificationManager.pollNotifications();
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

/**
 * Mark all notifications as read (globally available)
 */
window.markAllNotificationsRead = async function() {
    try {
        await fetch('/notifications/mark-all-read', {
            method: 'POST'
        });

        // Refresh page to show updated state
        window.location.reload();
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
        alert('Failed to mark notifications as read. Please try again.');
    }
};

// Initialize notification manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNotifications);
} else {
    initializeNotifications();
}

function initializeNotifications() {
    // Initialize globally (works on all pages)
    window.notificationManager = new NotificationManager();
}
