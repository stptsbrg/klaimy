<?php $currentPlan = $company['plan_slug'] ?? 'free'; ?>
<div class="page-header mb-4"><h2 class="page-title">Abonnement</h2></div>
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="list-group">
            <a href="/settings/company" class="list-group-item list-group-item-action">Entreprise</a>
            <a href="/settings/users" class="list-group-item list-group-item-action">Utilisateurs</a>
            <a href="/settings/profile" class="list-group-item list-group-item-action">Mon profil</a>
            <a href="/settings/subscription" class="list-group-item list-group-item-action active">Abonnement</a>
        </div>
    </div>
    <div class="col-md-9">
        <!-- Current Plan -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h6 class="text-muted">Plan actuel</h6>
                <h4 class="text-primary"><?= htmlspecialchars($company['plan_name'] ?? 'Gratuit') ?></h4>
                <p class="text-muted mb-0">
                    Statut: <span class="badge bg-<?= ($company['subscription_status'] ?? 'trial') === 'active' ? 'success' : 'warning' ?>"><?= ucfirst($company['subscription_status'] ?? 'trial') ?></span>
                    <?php if ($company['subscription_end']): ?> | Expire le <?= date('d/m/Y', strtotime($company['subscription_end'])) ?><?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Plans -->
        <div class="row g-3">
            <?php foreach ($plans as $plan): ?>
            <div class="col-md-4">
                <div class="card shadow-sm h-100 <?= $plan['slug'] === $currentPlan ? 'border-primary' : '' ?>">
                    <div class="card-body text-center">
                        <h5 class="fw-bold"><?= htmlspecialchars($plan['name']) ?></h5>
                        <div class="my-3">
                            <span class="display-6 fw-bold"><?= number_format($plan['price_monthly'], 0, ',', ' ') ?></span>
                            <span class="text-muted"><?= $plan['currency'] ?>/mois</span>
                        </div>
                        <ul class="list-unstyled text-start small">
                            <li class="mb-1"><i class="bi bi-check text-success me-1"></i><?= $plan['max_invoices_per_month'] == -1 ? 'Factures illimitées' : $plan['max_invoices_per_month'] . ' factures/mois' ?></li>
                            <li class="mb-1"><i class="bi bi-check text-success me-1"></i><?= $plan['max_users'] == -1 ? 'Utilisateurs illimités' : $plan['max_users'] . ' utilisateur(s)' ?></li>
                            <li class="mb-1"><i class="bi bi-<?= $plan['has_auto_recovery'] ? 'check text-success' : 'x text-muted' ?> me-1"></i>Relances auto</li>
                            <li class="mb-1"><i class="bi bi-<?= $plan['has_priority_support'] ? 'check text-success' : 'x text-muted' ?> me-1"></i>Support prioritaire</li>
                            <li class="mb-1"><i class="bi bi-<?= !$plan['has_watermark'] ? 'check text-success' : 'x text-muted' ?> me-1"></i>Sans watermark</li>
                        </ul>
                        <?php if ($plan['slug'] === $currentPlan): ?>
                            <button class="btn btn-outline-primary w-100" disabled>Plan actuel</button>
                        <?php else: ?>
                            <button class="btn btn-primary w-100">Choisir</button>
                        <?php endif; ?>
                    </div>
                    <?php if ($plan['price_yearly'] > 0): ?>
                    <div class="card-footer bg-success-subtle text-center small">
                        <strong>Annuel: <?= number_format($plan['price_yearly'], 0, ',', ' ') ?> <?= $plan['currency'] ?></strong> (2 mois offerts)
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
