<div class="page-header mb-4"><h2 class="page-title">Entreprises</h2></div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Entreprise</th><th>Email</th><th>Plan</th><th class="text-center">Utilisateurs</th><th class="text-center">Factures</th><th>Inscrite le</th></tr></thead>
                <tbody>
                <?php foreach ($companies ?? [] as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($c['name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($c['email']) ?></td>
                    <td><span class="badge bg-primary"><?= htmlspecialchars($c['plan_name'] ?? 'Gratuit') ?></span></td>
                    <td class="text-center"><?= $c['user_count'] ?></td>
                    <td class="text-center"><?= $c['invoice_count'] ?></td>
                    <td class="text-muted"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
