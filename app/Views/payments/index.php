<?php $currency = $_SESSION['company']['default_currency'] ?? 'XAF'; ?>
<div class="page-header mb-4"><h2 class="page-title">Paiements</h2></div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Facture</th><th>Client</th><th class="text-end">Montant</th><th>Méthode</th><th>Statut</th><th>Date</th></tr></thead>
                <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun paiement</td></tr>
                <?php else: foreach ($payments as $p): ?>
                    <tr>
                        <td><a href="/invoices/<?= $p['invoice_id'] ?? '' ?>"><?= htmlspecialchars($p['invoice_number'] ?? '-') ?></a></td>
                        <td><?= htmlspecialchars($p['client_name'] ?? '') ?></td>
                        <td class="text-end fw-semibold text-success"><?= number_format($p['amount'], 0, ',', ' ') ?> <?= $currency ?></td>
                        <td><?php
                            $methods = ['cash'=>'Espèces','mobile_money_mtn'=>'MTN MoMo','mobile_money_orange'=>'Orange Money','card'=>'Carte','bank_transfer'=>'Virement','wallet'=>'Wallet','other'=>'Autre'];
                            echo $methods[$p['payment_method']] ?? $p['payment_method'];
                        ?></td>
                        <td><span class="badge bg-<?= $p['status'] === 'completed' ? 'success' : ($p['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $p['status'] ?></span></td>
                        <td class="text-muted"><?= $p['paid_at'] ? date('d/m/Y H:i', strtotime($p['paid_at'])) : '-' ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
