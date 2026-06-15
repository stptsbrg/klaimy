<?php $currency = $_SESSION['company']['default_currency'] ?? 'XAF'; ?>
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h2 class="page-title mb-0"><?= htmlspecialchars($client['name']) ?></h2>
        <span class="badge bg-<?= $client['type'] === 'company' ? 'primary' : 'secondary' ?>-subtle text-<?= $client['type'] === 'company' ? 'primary' : 'secondary' ?>"><?= $client['type'] === 'company' ? 'Entreprise' : 'Particulier' ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Modifier</a>
        <a href="/invoices/create?client_id=<?= $client['id'] ?>" class="btn btn-primary"><i class="bi bi-receipt me-1"></i>Créer facture</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-3">Informations</h6>
                <p><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($client['email'] ?: '-') ?></p>
                <p><i class="bi bi-phone me-2"></i><?= htmlspecialchars($client['phone'] ?: '-') ?></p>
                <p><i class="bi bi-whatsapp me-2"></i><?= htmlspecialchars($client['whatsapp'] ?: '-') ?></p>
                <p><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars(($client['city'] ?: '') . ', ' . ($client['country'] ?: '')) ?></p>
                <?php if ($client['tax_id']): ?><p><strong>N° Contrib:</strong> <?= htmlspecialchars($client['tax_id']) ?></p><?php endif; ?>
                <?php if ($client['rccm']): ?><p><strong>RCCM:</strong> <?= htmlspecialchars($client['rccm']) ?></p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="row g-3">
            <div class="col-sm-4">
                <div class="kpi-card"><div class="kpi-info"><div class="kpi-value"><?= number_format($client['total_invoiced'] ?? 0, 0, ',', ' ') ?> <?= $currency ?></div><div class="kpi-label">Total facturé</div></div></div>
            </div>
            <div class="col-sm-4">
                <div class="kpi-card"><div class="kpi-info"><div class="kpi-value text-success"><?= number_format($client['total_paid'] ?? 0, 0, ',', ' ') ?> <?= $currency ?></div><div class="kpi-label">Total payé</div></div></div>
            </div>
            <div class="col-sm-4">
                <div class="kpi-card"><div class="kpi-info"><div class="kpi-value text-danger"><?= number_format(($client['total_invoiced'] ?? 0) - ($client['total_paid'] ?? 0), 0, ',', ' ') ?> <?= $currency ?></div><div class="kpi-label">Solde dû</div></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Invoices -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0">Factures</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>N°</th><th>Date</th><th>Total</th><th>Statut</th></tr></thead>
                <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">Aucune facture</td></tr>
                <?php else: foreach ($invoices as $inv): ?>
                    <tr>
                        <td><a href="/invoices/<?= $inv['id'] ?>"><?= htmlspecialchars($inv['invoice_number']) ?></a></td>
                        <td class="text-muted"><?= date('d/m/Y', strtotime($inv['issue_date'])) ?></td>
                        <td class="fw-semibold"><?= number_format($inv['total'], 0, ',', ' ') ?> <?= $currency ?></td>
                        <td><?php
                            $statusColors = ['draft' => 'secondary', 'sent' => 'info', 'viewed' => 'info', 'paid' => 'success', 'partial' => 'warning', 'overdue' => 'danger', 'cancelled' => 'dark'];
                            $statusLabels = ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'viewed' => 'Vue', 'paid' => 'Payée', 'partial' => 'Partiel', 'overdue' => 'En retard', 'cancelled' => 'Annulée'];
                            $s = $inv['status'];
                        ?><span class="badge bg-<?= $statusColors[$s] ?? 'secondary' ?>"><?= $statusLabels[$s] ?? $s ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payments -->
<div class="card shadow-sm">
    <div class="card-header bg-white"><h6 class="mb-0">Paiements</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Facture</th><th>Montant</th><th>Méthode</th><th>Date</th></tr></thead>
                <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">Aucun paiement</td></tr>
                <?php else: foreach ($payments as $pay): ?>
                    <tr>
                        <td><?= htmlspecialchars($pay['invoice_number'] ?? '-') ?></td>
                        <td class="fw-semibold text-success"><?= number_format($pay['amount'], 0, ',', ' ') ?> <?= $currency ?></td>
                        <td><?= htmlspecialchars($pay['payment_method']) ?></td>
                        <td class="text-muted"><?= $pay['paid_at'] ? date('d/m/Y', strtotime($pay['paid_at'])) : '-' ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
