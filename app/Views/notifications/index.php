<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <h2 class="page-title mb-0">Notifications</h2>
    <form method="POST" action="/notifications/read-all"><input type="hidden" name="_csrf_token" value="<?= $csrf ?>"><button class="btn btn-outline-primary btn-sm">Tout marquer comme lu</button></form>
</div>

<div class="card shadow-sm">
    <div class="list-group list-group-flush">
        <?php if (empty($notifications)): ?>
            <div class="list-group-item text-center text-muted py-5">Aucune notification</div>
        <?php else: foreach ($notifications as $notif): ?>
            <div class="list-group-item <?= $notif['is_read'] ? '' : 'bg-primary-subtle' ?>">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1"><?= htmlspecialchars($notif['title']) ?></h6>
                        <p class="text-muted mb-0 small"><?= htmlspecialchars($notif['body'] ?? '') ?></p>
                    </div>
                    <small class="text-muted"><?= date('d/m H:i', strtotime($notif['created_at'])) ?></small>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
