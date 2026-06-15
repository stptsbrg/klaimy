<?php
$currency = $_SESSION['company']['default_currency'] ?? 'XAF';
$statusColors = ['draft' => 'secondary', 'sent' => 'info', 'viewed' => 'info', 'accepted' => 'success', 'rejected' => 'danger', 'converted' => 'primary'];
$statusLabels = ['draft' => 'Brouillon', 'sent' => 'Envoyé', 'viewed' => 'Consulté', 'accepted' => 'Accepté', 'rejected' => 'Refusé', 'converted' => 'Converti'];
?>
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h2 class="page-title mb-0">Devis</h2>
    <a href="/quotes/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau devis</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="/quotes" class="btn btn-sm <?= empty($status) ? 'btn-primary' : 'btn-outline-primary' ?>">Tous</a>
            <?php foreach ($statusLabels as $key => $label): ?>
            <a href="/quotes?status=<?= $key ?>" class="btn btn-sm <?= ($status ?? '') === $key ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>N° Devis</th><th>Client</th><th class="d-none d-md-table-cell">Date</th><th class="text-end">Total</th><th class="text-center">Statut</th><th class="text-center">Actions</th></tr>
                </thead>
                <tbody>
                <?php if (empty($quotes)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun devis</td></tr>
                <?php else: foreach ($quotes as $q): ?>
                    <tr>
                        <td><a href="/quotes/<?= $q['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($q['quote_number']) ?></a></td>
                        <td><?= htmlspecialchars($q['client_name']) ?></td>
                        <td class="d-none d-md-table-cell text-muted"><?= date('d/m/Y', strtotime($q['issue_date'])) ?></td>
                        <td class="text-end fw-semibold"><?= number_format($q['total'], 0, ',', ' ') ?> <?= $currency ?></td>
                        <td class="text-center"><span class="badge bg-<?= $statusColors[$q['status']] ?? 'secondary' ?>"><?= $statusLabels[$q['status']] ?? $q['status'] ?></span></td>
                        <td class="text-center">
                            <a href="/quotes/<?= $q['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
