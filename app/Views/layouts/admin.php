<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> - Klaimy Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
    <div class="app-wrapper">
        <aside class="sidebar admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/admin/dashboard" class="sidebar-brand">
                    <span class="logo-text">Klaimy</span>
                    <span class="badge bg-warning text-dark ms-2">Admin</span>
                </a>
            </div>
            <div class="sidebar-body">
                <nav class="sidebar-nav">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'admin_dashboard' ? 'active' : '' ?>" href="/admin/dashboard">
                                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'admin_companies' ? 'active' : '' ?>" href="/admin/companies">
                                <i class="bi bi-building"></i><span>Entreprises</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar bg-warning text-dark">A</div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['super_admin']['name'] ?? 'Admin') ?></div>
                        <div class="user-role">Super Admin</div>
                    </div>
                    <a href="/admin/logout" class="btn-logout"><i class="bi bi-box-arrow-right"></i></a>
                </div>
            </div>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <button class="sidebar-toggle d-lg-none" id="sidebarOpen"><i class="bi bi-list"></i></button>
                <div class="topbar-right"><span class="text-muted">Administration Klaimy</span></div>
            </header>
            <div class="page-content">
                <?php
                $flash = $_SESSION['flash'] ?? null;
                unset($_SESSION['flash']);
                if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                        <?= htmlspecialchars($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?= $content ?>
            </div>
        </main>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>
