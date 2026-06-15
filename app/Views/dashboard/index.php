<?php
$currency = $company['default_currency'] ?? 'XAF';
$formatMoney = function($amount) use ($currency) {
    return number_format($amount, 0, ',', ' ') . ' ' . $currency;
};
?>
<div class="page-header mb-4">
    <h2 class="page-title">Tableau de bord</h2>
    <p class="text-muted">Bienvenue, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? '') ?></p>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon bg-primary-light"><i class="bi bi-graph-up text-primary"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?= $formatMoney($stats['revenue']) ?></div>
                <div class="kpi-label">Chiffre d'affaires</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon bg-success-light"><i class="bi bi-cash-stack text-success"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?= $formatMoney($stats['collected']) ?></div>
                <div class="kpi-label">Encaissements</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon bg-warning-light"><i class="bi bi-hourglass-split text-warning"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?= $formatMoney($stats['outstanding']) ?></div>
                <div class="kpi-label">Créances en attente</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon bg-danger-light"><i class="bi bi-exclamation-triangle text-danger"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?= $stats['invoices_unpaid'] ?></div>
                <div class="kpi-label">Factures impayées</div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['invoices_total'] ?></div>
            <div class="stat-label">Factures émises</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-value text-success"><?= $stats['invoices_paid'] ?></div>
            <div class="stat-label">Factures payées</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-value text-warning"><?= $stats['quotes_pending'] ?></div>
            <div class="stat-label">Devis en attente</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <a href="/invoices/create" class="btn btn-primary w-100">
                <i class="bi bi-plus-lg me-2"></i>Nouvelle facture
            </a>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">CA Mensuel</h6></div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Statut factures</h6></div>
            <div class="card-body">
                <canvas id="invoiceStatusChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Paiements récents</h6>
        <a href="/payments" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Client</th>
                        <th>Facture</th>
                        <th>Montant</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['recent_payments'])): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun paiement récent</td></tr>
                    <?php else: ?>
                    <?php foreach ($stats['recent_payments'] as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars($payment['client_name']) ?></td>
                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($payment['invoice_number']) ?></span></td>
                        <td class="fw-semibold text-success"><?= $formatMoney($payment['amount']) ?></td>
                        <td class="text-muted"><?= date('d/m/Y', strtotime($payment['paid_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const monthlyData = <?= json_encode($stats['monthly_revenue']) ?>;
    const labels = monthlyData.map(d => d.month);
    const values = monthlyData.map(d => parseFloat(d.amount));

    if (document.getElementById('revenueChart')) {
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Aucune donnée'],
                datasets: [{
                    label: 'CA Mensuel (<?= $currency ?>)',
                    data: values.length ? values : [0],
                    backgroundColor: 'rgba(30, 64, 175, 0.8)',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') } }
                }
            }
        });
    }

    // Invoice Status Chart
    if (document.getElementById('invoiceStatusChart')) {
        new Chart(document.getElementById('invoiceStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Payées', 'Impayées', 'En attente'],
                datasets: [{
                    data: [<?= $stats['invoices_paid'] ?>, <?= $stats['invoices_unpaid'] ?>, <?= $stats['quotes_pending'] ?>],
                    backgroundColor: ['#10B981', '#EF4444', '#F59E0B'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
