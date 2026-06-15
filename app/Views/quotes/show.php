<?php
$csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time();
$currency = $quote['currency'] ?? 'XAF';
$statusColors = ['draft' => 'secondary', 'sent' => 'info', 'viewed' => 'info', 'accepted' => 'success', 'rejected' => 'danger', 'converted' => 'primary'];
$statusLabels = ['draft' => 'Brouillon', 'sent' => 'Envoyé', 'viewed' => 'Consulté', 'accepted' => 'Accepté', 'rejected' => 'Refusé', 'converted' => 'Converti'];
?>
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h2 class="page-title mb-0"><?= htmlspecialchars($quote['quote_number']) ?></h2>
        <span class="badge bg-<?= $statusColors[$quote['status']] ?? 'secondary' ?>"><?= $statusLabels[$quote['status']] ?? $quote['status'] ?></span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($quote['status'] === 'draft'): ?>
        <form method="POST" action="/quotes/<?= $quote['id'] ?>/send" class="d-inline"><input type="hidden" name="_csrf_token" value="<?= $csrf ?>"><button class="btn btn-primary"><i class="bi bi-send me-1"></i>Envoyer</button></form>
        <?php endif; ?>
        <?php if (in_array($quote['status'], ['sent', 'viewed', 'accepted'])): ?>
        <form method="POST" action="/quotes/<?= $quote['id'] ?>/convert" class="d-inline"><input type="hidden" name="_csrf_token" value="<?= $csrf ?>"><button class="btn btn-success"><i class="bi bi-receipt me-1"></i>Convertir en facture</button></form>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h5><?= htmlspecialchars($quote['company_name']) ?></h5>
                <p class="text-muted small mb-0"><?= htmlspecialchars($quote['company_email'] ?? '') ?> | <?= htmlspecialchars($quote['company_phone'] ?? '') ?></p>
            </div>
            <div class="text-end">
                <h3 class="text-primary">DEVIS</h3>
                <p class="mb-1"><strong><?= htmlspecialchars($quote['quote_number']) ?></strong></p>
                <p class="small text-muted mb-0">Date: <?= date('d/m/Y', strtotime($quote['issue_date'])) ?></p>
                <?php if ($quote['expiry_date']): ?><p class="small text-muted mb-0">Validité: <?= date('d/m/Y', strtotime($quote['expiry_date'])) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="bg-light p-3 rounded mb-4">
            <h6 class="text-muted mb-1">Client</h6>
            <strong><?= htmlspecialchars($quote['client_name']) ?></strong><br>
            <small class="text-muted"><?= htmlspecialchars($quote['client_email'] ?? '') ?></small>
        </div>
        <div class="table-responsive mb-4">
            <table class="table">
                <thead class="table-light"><tr><th>Description</th><th class="text-center">Qté</th><th class="text-end">Prix unit.</th><th class="text-end">TVA</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                <?php foreach ($quote['items'] ?? [] as $item): ?>
                <tr><td><?= htmlspecialchars($item['description']) ?></td><td class="text-center"><?= $item['quantity'] ?></td><td class="text-end"><?= number_format($item['unit_price'], 0, ',', ' ') ?></td><td class="text-end"><?= $item['tax_rate'] ?>%</td><td class="text-end fw-semibold"><?= number_format($item['total'], 0, ',', ' ') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="row justify-content-end">
            <div class="col-md-5">
                <table class="table table-sm">
                    <tr><td>Sous-total</td><td class="text-end"><?= number_format($quote['subtotal'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                    <tr><td>TVA</td><td class="text-end"><?= number_format($quote['tax_amount'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                    <tr class="table-primary"><td class="fw-bold fs-5">Total</td><td class="text-end fw-bold fs-5"><?= number_format($quote['total'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
