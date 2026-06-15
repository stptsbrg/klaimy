<?php
$currency = $_SESSION['company']['default_currency'] ?? 'XAF';
$statusColors = ['draft' => 'secondary', 'sent' => 'info', 'viewed' => 'info', 'paid' => 'success', 'partial' => 'warning', 'overdue' => 'danger', 'cancelled' => 'dark'];
$statusLabels = ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'viewed' => 'Vue', 'paid' => 'Payée', 'partial' => 'Partiel', 'overdue' => 'En retard', 'cancelled' => 'Annulée'];
?>
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h2 class="page-title mb-0">Factures</h2>
    <a href="/invoices/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle facture</a>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="/invoices" class="btn btn-sm <?= empty($status) ? 'btn-primary' : 'btn-outline-primary' ?>">Toutes</a>
            <?php foreach ($statusLabels as $key => $label): ?>
            <a href="/invoices?status=<?= $key ?>" class="btn btn-sm <?= ($status ?? '') === $key ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th class="d-none d-md-table-cell">Échéance</th>
                        <th class="text-end">Total</th>
                        <th class="text-end d-none d-lg-table-cell">Restant</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucune facture</td></tr>
                <?php else: foreach ($invoices as $inv): ?>
                    <tr>
                        <td><a href="/invoices/<?= $inv['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($inv['invoice_number']) ?></a></td>
                        <td><?= htmlspecialchars($inv['client_name']) ?></td>
                        <td class="d-none d-md-table-cell text-muted"><?= date('d/m/Y', strtotime($inv['issue_date'])) ?></td>
                        <td class="d-none d-md-table-cell <?= strtotime($inv['due_date']) < time() && !in_array($inv['status'], ['paid', 'cancelled']) ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= date('d/m/Y', strtotime($inv['due_date'])) ?></td>
                        <td class="text-end fw-semibold"><?= number_format($inv['total'], 0, ',', ' ') ?> <?= $currency ?></td>
                        <td class="text-end d-none d-lg-table-cell"><?= $inv['amount_due'] > 0 ? number_format($inv['amount_due'], 0, ',', ' ') . ' ' . $currency : '-' ?></td>
                        <td class="text-center"><span class="badge bg-<?= $statusColors[$inv['status']] ?? 'secondary' ?>"><?= $statusLabels[$inv['status']] ?? $inv['status'] ?></span></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="/invoices/<?= $inv['id'] ?>" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <?php if ($inv['status'] === 'draft'): ?>
                                <a href="/invoices/<?= $inv['id'] ?>/edit" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
        <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link" href="/invoices?page=<?= $i ?>&status=<?= $status ?? '' ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
