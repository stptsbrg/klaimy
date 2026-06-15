<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5" style="max-width:600px">
        <div class="text-center mb-4">
            <h1 class="logo-text" style="color:#1E40AF">Klaimy</h1>
            <p class="text-muted">Paiement sécurisé</p>
        </div>

        <?php if ($invoice['status'] === 'paid'): ?>
        <div class="card shadow-sm text-center p-5">
            <i class="bi bi-check-circle text-success" style="font-size:4rem"></i>
            <h3 class="mt-3">Facture déjà payée</h3>
            <p class="text-muted">Cette facture a été intégralement réglée.</p>
        </div>
        <?php elseif ($invoice['status'] === 'cancelled'): ?>
        <div class="card shadow-sm text-center p-5">
            <i class="bi bi-x-circle text-danger" style="font-size:4rem"></i>
            <h3 class="mt-3">Facture annulée</h3>
        </div>
        <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <h6 class="text-muted mb-1">Émetteur</h6>
                        <strong><?= htmlspecialchars($invoice['company_name']) ?></strong>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary"><?= htmlspecialchars($invoice['invoice_number']) ?></span>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Facturé à</h6>
                    <strong><?= htmlspecialchars($invoice['client_name']) ?></strong>
                </div>
                <hr>
                <!-- Items summary -->
                <?php foreach ($invoice['items'] ?? [] as $item): ?>
                <div class="d-flex justify-content-between mb-1">
                    <span><?= htmlspecialchars($item['description']) ?> × <?= $item['quantity'] ?></span>
                    <span><?= number_format($item['total'], 0, ',', ' ') ?></span>
                </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between mb-1"><span>Sous-total</span><span><?= number_format($invoice['subtotal'], 0, ',', ' ') ?> <?= $invoice['currency'] ?></span></div>
                <div class="d-flex justify-content-between mb-1"><span>TVA</span><span><?= number_format($invoice['tax_amount'], 0, ',', ' ') ?> <?= $invoice['currency'] ?></span></div>
                <hr>
                <div class="d-flex justify-content-between fs-4 fw-bold">
                    <span>À payer</span>
                    <span class="text-primary"><?= number_format($invoice['amount_due'], 0, ',', ' ') ?> <?= $invoice['currency'] ?></span>
                </div>

                <div class="mt-4">
                    <p class="text-muted text-center small mb-3">Payez en toute sécurité via CinetPay</p>
                    <button class="btn btn-primary btn-lg w-100" id="payBtn" onclick="initPayment()">
                        <i class="bi bi-credit-card me-2"></i>Payer maintenant
                    </button>
                    <div class="d-flex justify-content-center gap-3 mt-3">
                        <img src="https://www.cinetpay.com/assets/images/logo.png" alt="CinetPay" height="24" onerror="this.style.display='none'">
                        <span class="text-muted small">MTN MoMo • Orange Money • Carte</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function initPayment() {
        const btn = document.getElementById('payBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Initialisation...';

        fetch('/api/payment/init', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'invoice_uuid=<?= htmlspecialchars($invoice['uuid']) ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.data && data.data.payment_url) {
                window.location.href = data.data.payment_url;
            } else {
                alert('Erreur: ' + (data.message || 'Impossible d\'initialiser le paiement.'));
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>Payer maintenant';
            }
        })
        .catch(() => {
            alert('Erreur de connexion.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>Payer maintenant';
        });
    }
    </script>
</body>
</html>
