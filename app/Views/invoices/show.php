<?php
$csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf;
$_SESSION['csrf_token_time'] = time();
$currency = $invoice['currency'] ?? 'XAF';
$statusColors = ['draft' => 'secondary', 'sent' => 'info', 'viewed' => 'info', 'paid' => 'success', 'partial' => 'warning', 'overdue' => 'danger', 'cancelled' => 'dark'];
$statusLabels = ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'viewed' => 'Vue', 'paid' => 'Payée', 'partial' => 'Partiel', 'overdue' => 'En retard', 'cancelled' => 'Annulée'];
?>
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h2 class="page-title mb-0"><?= htmlspecialchars($invoice['invoice_number']) ?></h2>
        <span class="badge bg-<?= $statusColors[$invoice['status']] ?? 'secondary' ?> fs-6"><?= $statusLabels[$invoice['status']] ?? $invoice['status'] ?></span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($invoice['status'] === 'draft'): ?>
            <a href="/invoices/<?= $invoice['id'] ?>/edit" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Modifier</a>
            <form method="POST" action="/invoices/<?= $invoice['id'] ?>/send" class="d-inline">
                <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
                <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Envoyer</button>
            </form>
        <?php endif; ?>
        <?php if (in_array($invoice['status'], ['sent', 'viewed', 'partial', 'overdue'])): ?>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="bi bi-cash me-1"></i>Enregistrer paiement</button>
        <?php endif; ?>
        <a href="/invoices/<?= $invoice['id'] ?>/pdf" class="btn btn-outline-secondary" target="_blank"><i class="bi bi-file-pdf me-1"></i>PDF</a>
        <?php if (!in_array($invoice['status'], ['paid', 'cancelled'])): ?>
            <form method="POST" action="/invoices/<?= $invoice['id'] ?>/cancel" class="d-inline" onsubmit="return confirm('Annuler cette facture ?')">
                <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
                <button class="btn btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Annuler</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Invoice Preview -->
        <div class="card shadow-sm" id="invoicePreview">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <?php if ($invoice['company_logo']): ?>
                            <img src="<?= htmlspecialchars($invoice['company_logo']) ?>" alt="Logo" style="max-height:60px" class="mb-2">
                        <?php endif; ?>
                        <h5 class="mb-1"><?= htmlspecialchars($invoice['company_name']) ?></h5>
                        <p class="text-muted mb-0 small">
                            <?= htmlspecialchars($invoice['company_address'] ?? '') ?><br>
                            <?= htmlspecialchars(($invoice['company_city'] ?? '') . ', ' . ($invoice['company_country'] ?? '')) ?><br>
                            <?= htmlspecialchars($invoice['company_email'] ?? '') ?> | <?= htmlspecialchars($invoice['company_phone'] ?? '') ?>
                        </p>
                        <?php if ($invoice['company_tax_id']): ?><small>N° Contrib: <?= htmlspecialchars($invoice['company_tax_id']) ?></small><br><?php endif; ?>
                        <?php if ($invoice['company_rccm']): ?><small>RCCM: <?= htmlspecialchars($invoice['company_rccm']) ?></small><?php endif; ?>
                    </div>
                    <div class="text-end">
                        <h3 class="text-primary mb-2">FACTURE</h3>
                        <p class="mb-1"><strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong></p>
                        <p class="mb-1 small text-muted">Date: <?= date('d/m/Y', strtotime($invoice['issue_date'])) ?></p>
                        <p class="mb-0 small text-muted">Échéance: <?= date('d/m/Y', strtotime($invoice['due_date'])) ?></p>
                    </div>
                </div>

                <!-- Client Info -->
                <div class="bg-light p-3 rounded mb-4">
                    <h6 class="text-muted mb-1">Facturé à</h6>
                    <strong><?= htmlspecialchars($invoice['client_name']) ?></strong><br>
                    <span class="small text-muted">
                        <?= htmlspecialchars($invoice['client_address'] ?? '') ?><br>
                        <?= htmlspecialchars(($invoice['client_city'] ?? '') . ', ' . ($invoice['client_country'] ?? '')) ?><br>
                        <?= htmlspecialchars($invoice['client_email'] ?? '') ?>
                    </span>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table">
                        <thead class="table-light">
                            <tr><th>Description</th><th class="text-center">Qté</th><th class="text-end">Prix unit.</th><th class="text-end">TVA</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($invoice['items'] ?? [] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end"><?= number_format($item['unit_price'], 0, ',', ' ') ?></td>
                                <td class="text-end"><?= $item['tax_rate'] ?>%</td>
                                <td class="text-end fw-semibold"><?= number_format($item['total'], 0, ',', ' ') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-sm">
                            <tr><td>Sous-total HT</td><td class="text-end"><?= number_format($invoice['subtotal'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                            <tr><td>TVA</td><td class="text-end"><?= number_format($invoice['tax_amount'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                            <?php if ($invoice['discount_amount'] > 0): ?>
                            <tr><td>Remise</td><td class="text-end text-danger">-<?= number_format($invoice['discount_amount'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                            <?php endif; ?>
                            <tr class="table-primary"><td class="fw-bold fs-5">Total TTC</td><td class="text-end fw-bold fs-5"><?= number_format($invoice['total'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                            <?php if ($invoice['amount_paid'] > 0): ?>
                            <tr><td>Payé</td><td class="text-end text-success"><?= number_format($invoice['amount_paid'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                            <tr class="table-warning"><td class="fw-bold">Reste à payer</td><td class="text-end fw-bold"><?= number_format($invoice['amount_due'], 0, ',', ' ') ?> <?= $currency ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <?php if ($invoice['notes']): ?>
                <div class="mt-3"><strong>Notes:</strong> <?= nl2br(htmlspecialchars($invoice['notes'])) ?></div>
                <?php endif; ?>
                <?php if ($invoice['terms']): ?>
                <div class="mt-2"><strong>Conditions:</strong> <?= nl2br(htmlspecialchars($invoice['terms'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Payment Link / QR -->
        <?php if ($invoice['payment_link']): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body text-center">
                <h6 class="mb-3">Lien de paiement</h6>
                <div class="bg-light p-3 rounded mb-2">
                    <code class="small"><?= htmlspecialchars($invoice['payment_link']) ?></code>
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($invoice['payment_link']) ?>')">
                    <i class="bi bi-clipboard me-1"></i>Copier le lien
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payments History -->
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Historique paiements</h6></div>
            <div class="card-body p-0">
                <?php if (empty($invoice['payments'])): ?>
                    <p class="text-center text-muted py-3 mb-0">Aucun paiement</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                    <?php foreach ($invoice['payments'] as $pay): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <span class="badge bg-<?= $pay['status'] === 'completed' ? 'success' : 'warning' ?>"><?= $pay['status'] ?></span>
                                <small class="text-muted ms-1"><?= $pay['paid_at'] ? date('d/m/Y', strtotime($pay['paid_at'])) : '-' ?></small>
                            </div>
                            <strong><?= number_format($pay['amount'], 0, ',', ' ') ?> <?= $currency ?></strong>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/payments/<?= $invoice['id'] ?>">
                <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Enregistrer un paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Montant</label>
                        <input type="number" class="form-control" name="amount" value="<?= $invoice['amount_due'] ?>" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Méthode de paiement</label>
                        <select class="form-select" name="payment_method">
                            <option value="cash">Espèces</option>
                            <option value="mobile_money_mtn">MTN Mobile Money</option>
                            <option value="mobile_money_orange">Orange Money</option>
                            <option value="card">Carte bancaire</option>
                            <option value="bank_transfer">Virement bancaire</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
