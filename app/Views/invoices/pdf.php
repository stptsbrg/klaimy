<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; padding: 30px; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .company-info h2 { color: #1E40AF; margin-bottom: 5px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { color: #1E40AF; font-size: 28px; }
        .client-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #1E40AF; color: white; padding: 10px; text-align: left; }
        td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .totals { width: 300px; margin-left: auto; }
        .totals td { padding: 6px 10px; }
        .totals .grand-total { background: #1E40AF; color: white; font-weight: bold; font-size: 16px; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #666; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center;margin-bottom:20px;">
        <button onclick="window.print()" style="padding:10px 30px;background:#1E40AF;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;">Imprimer / Télécharger PDF</button>
    </div>

    <div class="invoice-header">
        <div class="company-info">
            <h2><?= htmlspecialchars($invoice['company_name']) ?></h2>
            <p><?= htmlspecialchars($invoice['company_address'] ?? '') ?></p>
            <p><?= htmlspecialchars(($invoice['company_city'] ?? '') . ', ' . ($invoice['company_country'] ?? '')) ?></p>
            <p><?= htmlspecialchars($invoice['company_email'] ?? '') ?> | <?= htmlspecialchars($invoice['company_phone'] ?? '') ?></p>
            <?php if ($invoice['company_tax_id']): ?><p>N° Contrib: <?= htmlspecialchars($invoice['company_tax_id']) ?></p><?php endif; ?>
            <?php if ($invoice['company_rccm']): ?><p>RCCM: <?= htmlspecialchars($invoice['company_rccm']) ?></p><?php endif; ?>
        </div>
        <div class="invoice-title">
            <h1>FACTURE</h1>
            <p><strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong></p>
            <p>Date: <?= date('d/m/Y', strtotime($invoice['issue_date'])) ?></p>
            <p>Échéance: <?= date('d/m/Y', strtotime($invoice['due_date'])) ?></p>
        </div>
    </div>

    <div class="client-box">
        <strong>Facturé à:</strong><br>
        <?= htmlspecialchars($invoice['client_name']) ?><br>
        <?= htmlspecialchars($invoice['client_address'] ?? '') ?><br>
        <?= htmlspecialchars(($invoice['client_city'] ?? '') . ', ' . ($invoice['client_country'] ?? '')) ?><br>
        <?= htmlspecialchars($invoice['client_email'] ?? '') ?>
        <?php if ($invoice['client_tax_id']): ?><br>N° Contrib: <?= htmlspecialchars($invoice['client_tax_id']) ?><?php endif; ?>
    </div>

    <table>
        <thead>
            <tr><th>Description</th><th style="text-align:center">Qté</th><th style="text-align:right">Prix unit.</th><th style="text-align:right">TVA</th><th style="text-align:right">Total</th></tr>
        </thead>
        <tbody>
        <?php foreach ($invoice['items'] ?? [] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['description']) ?></td>
                <td style="text-align:center"><?= $item['quantity'] ?></td>
                <td style="text-align:right"><?= number_format($item['unit_price'], 0, ',', ' ') ?></td>
                <td style="text-align:right"><?= $item['tax_rate'] ?>%</td>
                <td style="text-align:right"><?= number_format($item['total'], 0, ',', ' ') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Sous-total HT</td><td style="text-align:right"><?= number_format($invoice['subtotal'], 0, ',', ' ') ?> <?= $invoice['currency'] ?></td></tr>
        <tr><td>TVA</td><td style="text-align:right"><?= number_format($invoice['tax_amount'], 0, ',', ' ') ?> <?= $invoice['currency'] ?></td></tr>
        <?php if ($invoice['discount_amount'] > 0): ?>
        <tr><td>Remise</td><td style="text-align:right;color:red">-<?= number_format($invoice['discount_amount'], 0, ',', ' ') ?> <?= $invoice['currency'] ?></td></tr>
        <?php endif; ?>
        <tr class="grand-total"><td>Total TTC</td><td style="text-align:right"><?= number_format($invoice['total'], 0, ',', ' ') ?> <?= $invoice['currency'] ?></td></tr>
    </table>

    <?php if ($invoice['notes']): ?>
    <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
    <?php endif; ?>

    <?php if ($invoice['payment_link']): ?>
    <div style="margin-top:20px;padding:15px;background:#EEF2FF;border-radius:8px;text-align:center;">
        <strong>Payez en ligne:</strong> <?= htmlspecialchars($invoice['payment_link']) ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        <?php if ($invoice['company_legal_mentions']): ?>
        <p><?= nl2br(htmlspecialchars($invoice['company_legal_mentions'])) ?></p>
        <?php endif; ?>
        <p style="text-align:center;margin-top:10px;">Généré par Klaimy - Facturation Intelligente</p>
    </div>
</body>
</html>
