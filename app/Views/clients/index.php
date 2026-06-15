<?php $csrf = $_SESSION['csrf_token'] ?? ''; ?>
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h2 class="page-title mb-0">Clients</h2>
    <a href="/clients/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau client</a>
</div>

<!-- Search -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="/clients" class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Rechercher un client...">
            </div>
            <button type="submit" class="btn btn-outline-primary">Rechercher</button>
            <?php if (!empty($search)): ?>
                <a href="/clients" class="btn btn-outline-secondary">Effacer</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th class="d-none d-md-table-cell">Email</th>
                        <th class="d-none d-md-table-cell">Téléphone</th>
                        <th class="d-none d-lg-table-cell">Ville</th>
                        <th class="text-end">Total facturé</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun client trouvé</td></tr>
                    <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td>
                            <a href="/clients/<?= $client['id'] ?>" class="text-decoration-none fw-semibold">
                                <?= htmlspecialchars($client['name']) ?>
                            </a>
                            <?php if ($client['type'] === 'company'): ?>
                                <span class="badge bg-primary-subtle text-primary ms-1">Entreprise</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-md-table-cell text-muted"><?= htmlspecialchars($client['email'] ?? '-') ?></td>
                        <td class="d-none d-md-table-cell text-muted"><?= htmlspecialchars($client['phone'] ?? '-') ?></td>
                        <td class="d-none d-lg-table-cell text-muted"><?= htmlspecialchars($client['city'] ?? '-') ?></td>
                        <td class="text-end fw-semibold"><?= number_format($client['total_invoiced'], 0, ',', ' ') ?> FCFA</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="/clients/<?= $client['id'] ?>" class="btn btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                                <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-outline-secondary" title="Modifier"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
        <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link" href="/clients?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
