<div class="page-header mb-4"><h2 class="page-title">Dashboard Administration</h2></div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="kpi-card"><div class="kpi-icon bg-primary-light"><i class="bi bi-building text-primary"></i></div><div class="kpi-info"><div class="kpi-value"><?= $stats['total_companies'] ?></div><div class="kpi-label">Entreprises</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="kpi-card"><div class="kpi-icon bg-success-light"><i class="bi bi-people text-success"></i></div><div class="kpi-info"><div class="kpi-value"><?= $stats['total_users'] ?></div><div class="kpi-label">Utilisateurs</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="kpi-card"><div class="kpi-icon bg-warning-light"><i class="bi bi-receipt text-warning"></i></div><div class="kpi-info"><div class="kpi-value"><?= $stats['total_invoices'] ?></div><div class="kpi-label">Factures</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="kpi-card"><div class="kpi-icon bg-danger-light"><i class="bi bi-cash-stack text-danger"></i></div><div class="kpi-info"><div class="kpi-value"><?= number_format($stats['total_payments'], 0, ',', ' ') ?></div><div class="kpi-label">Total paiements</div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Distribution par plan</h6></div>
            <div class="card-body p-0">
                <table class="table mb-0"><tbody>
                <?php foreach ($stats['plan_distribution'] as $pd): ?>
                    <tr><td><?= htmlspecialchars($pd['name']) ?></td><td class="text-end fw-semibold"><?= $pd['count'] ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Entreprises récentes</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0"><tbody>
                <?php foreach ($stats['recent_companies'] as $c): ?>
                    <tr><td><strong><?= htmlspecialchars($c['name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($c['email']) ?></small></td><td class="text-muted"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>
</div>
