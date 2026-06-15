<?php $currency = $_SESSION['company']['default_currency'] ?? 'XAF'; ?>
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h2 class="page-title mb-0">Produits & Services</h2>
    <a href="/products/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Ajouter</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="/products" class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Rechercher...">
            </div>
            <button type="submit" class="btn btn-outline-primary">Rechercher</button>
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
                        <th>Type</th>
                        <th class="d-none d-md-table-cell">Catégorie</th>
                        <th class="text-end">Prix</th>
                        <th class="text-center d-none d-md-table-cell">Stock</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun produit</td></tr>
                <?php else: foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($product['name']) ?></div>
                            <?php if ($product['sku']): ?><small class="text-muted"><?= htmlspecialchars($product['sku']) ?></small><?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= $product['type'] === 'service' ? 'info' : 'primary' ?>-subtle text-<?= $product['type'] === 'service' ? 'info' : 'primary' ?>"><?= $product['type'] === 'service' ? 'Service' : 'Produit' ?></span></td>
                        <td class="d-none d-md-table-cell text-muted"><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                        <td class="text-end fw-semibold"><?= number_format($product['price'], 0, ',', ' ') ?> <?= $currency ?></td>
                        <td class="text-center d-none d-md-table-cell">
                            <?php if ($product['type'] === 'product'): ?>
                                <span class="badge <?= $product['stock_quantity'] <= $product['stock_alert_threshold'] ? 'bg-danger' : 'bg-success' ?>"><?= $product['stock_quantity'] ?></span>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="/products/<?= $product['id'] ?>/edit" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="/products/<?= $product['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Supprimer ce produit ?')">
                                    <input type="hidden" name="_csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
