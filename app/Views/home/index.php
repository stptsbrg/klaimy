<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/"><span class="logo-text" style="font-size:1.5rem">Klaimy</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Fonctionnalités</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Tarifs</a></li>
                    <li class="nav-item ms-2"><a class="btn btn-outline-primary" href="/login">Connexion</a></li>
                    <li class="nav-item ms-2"><a class="btn btn-primary" href="/register">Essai gratuit</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section py-5">
        <div class="container">
            <div class="row align-items-center py-5">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3" style="font-family:Poppins;color:#1E40AF">La facturation intelligente pour l'Afrique</h1>
                    <p class="lead text-muted mb-4">Créez des factures professionnelles, recevez vos paiements via Mobile Money et automatisez vos relances clients. Simple, rapide et accessible.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="/register" class="btn btn-primary btn-lg px-5">Commencer gratuitement</a>
                        <a href="#features" class="btn btn-outline-primary btn-lg">En savoir plus</a>
                    </div>
                    <div class="mt-4 d-flex gap-4">
                        <div><strong class="text-primary fs-4">1000+</strong><br><small class="text-muted">Entreprises</small></div>
                        <div><strong class="text-primary fs-4">50K+</strong><br><small class="text-muted">Factures créées</small></div>
                        <div><strong class="text-primary fs-4">99.9%</strong><br><small class="text-muted">Disponibilité</small></div>
                    </div>
                </div>
                <div class="col-lg-6 text-center mt-4 mt-lg-0">
                    <div class="bg-primary bg-opacity-10 rounded-4 p-4" style="min-height:350px;display:flex;align-items:center;justify-content:center;">
                        <div>
                            <i class="bi bi-receipt text-primary" style="font-size:6rem"></i>
                            <h3 class="text-primary mt-3">Dashboard Klaimy</h3>
                            <p class="text-muted">Votre centre de contrôle financier</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="font-family:Poppins;color:#1E40AF">Tout ce dont vous avez besoin</h2>
                <p class="text-muted">Une plateforme complète pour gérer votre activité</p>
            </div>
            <div class="row g-4">
                <?php
                $features = [
                    ['bi-receipt', 'Facturation Pro', 'Créez des factures conformes OHADA avec numérotation automatique, PDF professionnel et QR code de paiement.'],
                    ['bi-phone', 'Paiement Mobile Money', 'Recevez vos paiements via MTN Mobile Money, Orange Money ou carte bancaire grâce à CinetPay.'],
                    ['bi-bell', 'Relances Automatiques', 'Automatisez vos rappels de paiement par email, SMS et WhatsApp avant et après échéance.'],
                    ['bi-file-earmark-text', 'Devis & Bons de livraison', 'Créez des devis en quelques clics et convertissez-les en factures automatiquement.'],
                    ['bi-graph-up', 'Tableau de bord', 'Suivez votre CA, encaissements, créances et évolution en temps réel avec des graphiques clairs.'],
                    ['bi-chat-dots', 'Messagerie intégrée', 'Communiquez avec votre équipe et vos clients directement depuis la plateforme.'],
                    ['bi-people', 'Multi-utilisateurs', 'Ajoutez votre équipe avec des rôles et permissions configurables.'],
                    ['bi-currency-exchange', 'Multi-devises', 'Facturez en XAF, XOF, USD, EUR ou GBP selon vos besoins.'],
                    ['bi-shield-check', 'Sécurité avancée', 'Double authentification, chiffrement des données et protection contre les attaques.'],
                ];
                foreach ($features as $f): ?>
                <div class="col-md-4">
                    <div class="card border-0 h-100 shadow-sm hover-shadow">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3"><i class="bi <?= $f[0] ?> text-primary" style="font-size:2rem"></i></div>
                            <h5 class="fw-semibold"><?= $f[1] ?></h5>
                            <p class="text-muted mb-0"><?= $f[2] ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-5" style="background:#F8FAFC">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="font-family:Poppins;color:#1E40AF">Tarifs simples et transparents</h2>
                <p class="text-muted">Commencez gratuitement, évoluez selon vos besoins</p>
                <p class="text-success fw-semibold"><i class="bi bi-gift me-1"></i>Abonnement annuel : 2 mois offerts</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php foreach ($plans as $plan): ?>
                <div class="col-md-4 col-lg">
                    <div class="card shadow-sm h-100 <?= $plan['slug'] === 'pro' ? 'border-primary border-2' : '' ?>">
                        <?php if ($plan['slug'] === 'pro'): ?><div class="card-header bg-primary text-white text-center fw-semibold">Populaire</div><?php endif; ?>
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bold"><?= htmlspecialchars($plan['name']) ?></h5>
                            <div class="my-3">
                                <span class="display-6 fw-bold"><?= $plan['price_monthly'] == 0 ? 'Gratuit' : number_format($plan['price_monthly'], 0, ',', ' ') ?></span>
                                <?php if ($plan['price_monthly'] > 0): ?><span class="text-muted">FCFA/mois</span><?php endif; ?>
                            </div>
                            <ul class="list-unstyled text-start">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= $plan['max_invoices_per_month'] == -1 ? 'Factures illimitées' : $plan['max_invoices_per_month'] . ' factures/mois' ?></li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= $plan['max_users'] == -1 ? 'Utilisateurs illimités' : $plan['max_users'] . ' utilisateur(s)' ?></li>
                                <li class="mb-2"><i class="bi bi-<?= $plan['has_auto_recovery'] ? 'check-circle-fill text-success' : 'x-circle text-muted' ?> me-2"></i>Relances automatiques</li>
                                <li class="mb-2"><i class="bi bi-<?= $plan['has_priority_support'] ? 'check-circle-fill text-success' : 'x-circle text-muted' ?> me-2"></i>Support prioritaire</li>
                                <li class="mb-2"><i class="bi bi-<?= !$plan['has_watermark'] ? 'check-circle-fill text-success' : 'x-circle text-muted' ?> me-2"></i>Sans watermark</li>
                            </ul>
                            <a href="/register" class="btn <?= $plan['slug'] === 'pro' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mt-3">
                                <?= $plan['price_monthly'] == 0 ? 'Commencer' : 'Choisir' ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5 bg-primary text-white text-center">
        <div class="container">
            <h2 class="fw-bold mb-3" style="font-family:Poppins">Prêt à digitaliser votre facturation ?</h2>
            <p class="lead mb-4">Rejoignez les milliers d'entrepreneurs africains qui utilisent Klaimy</p>
            <a href="/register" class="btn btn-light btn-lg px-5 fw-semibold">Créer mon compte gratuitement</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6"><span class="logo-text" style="color:white">Klaimy</span><span class="text-white-50 ms-2">Facturation intelligente pour l'Afrique</span></div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0"><small class="text-white-50">&copy; <?= date('Y') ?> Klaimy. Tous droits réservés.</small></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
