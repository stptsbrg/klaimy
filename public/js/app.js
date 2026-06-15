/**
 * Klaimy - JavaScript principal
 */
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle (mobile)
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openBtn = document.getElementById('sidebarOpen');
    const closeBtn = document.getElementById('sidebarClose');

    function toggleSidebar(show) {
        if (sidebar) sidebar.classList.toggle('show', show);
        if (overlay) overlay.classList.toggle('show', show);
    }

    if (openBtn) openBtn.addEventListener('click', () => toggleSidebar(true));
    if (closeBtn) closeBtn.addEventListener('click', () => toggleSidebar(false));
    if (overlay) overlay.addEventListener('click', () => toggleSidebar(false));

    // Notification badge polling
    function updateNotificationBadge() {
        fetch('/api/notifications/count')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(() => {});
    }

    // Poll every 30 seconds
    if (document.getElementById('notifBadge')) {
        updateNotificationBadge();
        setInterval(updateNotificationBadge, 30000);
    }

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // Format currency inputs
    document.querySelectorAll('[data-format="currency"]').forEach(input => {
        input.addEventListener('blur', function() {
            const val = parseFloat(this.value);
            if (!isNaN(val)) {
                this.value = val.toFixed(2);
            }
        });
    });

    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Êtes-vous sûr ?')) {
                e.preventDefault();
            }
        });
    });
});
