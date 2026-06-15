<?php
$csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time();
$activeId = $activeConversation ?? null;
?>
<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <h2 class="page-title mb-0">Messagerie</h2>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newConvModal"><i class="bi bi-plus-lg me-1"></i>Nouvelle conversation</button>
</div>

<div class="card shadow-sm" style="height: calc(100vh - 220px); min-height:400px;">
    <div class="row g-0 h-100">
        <!-- Conversations list -->
        <div class="col-md-4 border-end h-100 overflow-auto">
            <?php if (empty($conversations)): ?>
                <div class="text-center text-muted py-5"><i class="bi bi-chat-dots" style="font-size:2rem"></i><p class="mt-2">Aucune conversation</p></div>
            <?php else: foreach ($conversations as $conv): ?>
                <a href="/messages/<?= $conv['id'] ?>" class="d-block p-3 border-bottom text-decoration-none <?= $activeId == $conv['id'] ? 'bg-primary-subtle' : 'bg-white' ?>" style="transition:background .2s">
                    <div class="d-flex justify-content-between">
                        <strong class="text-dark"><?= htmlspecialchars($conv['subject'] ?: ($conv['client_name'] ?? 'Conversation')) ?></strong>
                        <?php if (($conv['unread_count'] ?? 0) > 0): ?>
                            <span class="badge bg-primary rounded-pill"><?= $conv['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted d-block text-truncate"><?= htmlspecialchars(substr($conv['last_message'] ?? '', 0, 50)) ?></small>
                    <small class="text-muted"><?= $conv['type'] === 'client' ? '<i class="bi bi-person"></i> Client' : '<i class="bi bi-people"></i> Interne' ?></small>
                </a>
            <?php endforeach; endif; ?>
        </div>

        <!-- Messages area -->
        <div class="col-md-8 d-flex flex-column h-100">
            <?php if ($activeId && !empty($messages)): ?>
            <div class="flex-grow-1 overflow-auto p-3" id="messagesArea">
                <?php foreach ($messages as $msg): ?>
                <div class="mb-3 <?= $msg['sender_id'] == ($_SESSION['user_id'] ?? 0) ? 'text-end' : '' ?>">
                    <div class="d-inline-block p-2 px-3 rounded-3 <?= $msg['sender_id'] == ($_SESSION['user_id'] ?? 0) ? 'bg-primary text-white' : 'bg-light' ?>" style="max-width:75%">
                        <?php if ($msg['sender_id'] != ($_SESSION['user_id'] ?? 0)): ?>
                            <small class="fw-semibold d-block"><?= htmlspecialchars(($msg['first_name'] ?? '') . ' ' . ($msg['last_name'] ?? '')) ?></small>
                        <?php endif; ?>
                        <?= nl2br(htmlspecialchars($msg['body'])) ?>
                        <small class="d-block <?= $msg['sender_id'] == ($_SESSION['user_id'] ?? 0) ? 'text-white-50' : 'text-muted' ?>" style="font-size:.7rem"><?= date('H:i', strtotime($msg['created_at'])) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="border-top p-3">
                <form method="POST" action="/messages/<?= $activeId ?>/send" class="d-flex gap-2">
                    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
                    <input type="text" class="form-control" name="body" placeholder="Écrire un message..." required autofocus>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
                </form>
            </div>
            <?php else: ?>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted">
                <div class="text-center"><i class="bi bi-chat-dots" style="font-size:3rem"></i><p class="mt-2">Sélectionnez une conversation</p></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- New Conversation Modal -->
<div class="modal fade" id="newConvModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/messages/create">
                <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
                <div class="modal-header"><h5 class="modal-title">Nouvelle conversation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Type</label><select class="form-select" name="type"><option value="internal">Interne (équipe)</option><option value="client">Client</option></select></div>
                    <div class="mb-3"><label class="form-label">Sujet</label><input type="text" class="form-control" name="subject" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-primary">Créer</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const area = document.getElementById('messagesArea');
    if (area) area.scrollTop = area.scrollHeight;
});
</script>
