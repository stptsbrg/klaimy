<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Klaimy') ?> - Klaimy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/dashboard" class="sidebar-brand">
                    <span class="logo-text">Klaimy</span>
                </a>
                <button class="sidebar-toggle d-lg-none" id="sidebarClose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="sidebar-body">
                <nav class="sidebar-nav">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="/dashboard">
                                <i class="bi bi-grid-1x2"></i><span>Tableau de bord</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'clients' ? 'active' : '' ?>" href="/clients">
                                <i class="bi bi-people"></i><span>Clients</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'products' ? 'active' : '' ?>" href="/products">
                                <i class="bi bi-box-seam"></i><span>Produits & Services</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'quotes' ? 'active' : '' ?>" href="/quotes">
                                <i class="bi bi-file-earmark-text"></i><span>Devis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'invoices' ? 'active' : '' ?>" href="/invoices">
                                <i class="bi bi-receipt"></i><span>Factures</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'payments' ? 'active' : '' ?>" href="/payments">
                                <i class="bi bi-credit-card"></i><span>Paiements</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'messages' ? 'active' : '' ?>" href="/messages">
                                <i class="bi bi-chat-dots"></i><span>Messagerie</span>
                            </a>
                        </li>

                        <li class="nav-divider"></li>

                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'notifications' ? 'active' : '' ?>" href="/notifications">
                                <i class="bi bi-bell"></i><span>Notifications</span>
                                <span class="badge bg-danger ms-auto notification-badge" id="notifBadge" style="display:none;">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>" href="/settings/company">
                                <i class="bi bi-gear"></i><span>Paramètres</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user']['first_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? '')) ?></div>
                        <div class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['user']['role'] ?? '')) ?></div>
                    </div>
                    <a href="/logout" class="btn-logout" title="Déconnexion">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="topbar">
                <button class="sidebar-toggle d-lg-none" id="sidebarOpen">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-right">
                    <span class="company-name"><?= htmlspecialchars($_SESSION['company']['name'] ?? '') ?></span>
                    <a href="/settings/profile" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="bi bi-person"></i>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <?php
                $flash = $_SESSION['flash'] ?? null;
                unset($_SESSION['flash']);
                if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>
