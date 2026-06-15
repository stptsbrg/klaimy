<?php
$csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf;
$_SESSION['csrf_token_time'] = time();
$isEdit = isset($product);
$action = $isEdit ? "/products/{$product['id']}" : '/products';
?>
<div class="page-header mb-4">
    <h2 class="page-title"><?= $isEdit ? 'Modifier' : 'Nouveau produit/service' ?></h2>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="product" <?= ($product['type'] ?? 'product') === 'product' ? 'selected' : '' ?>>Produit</option>
                        <option value="service" <?= ($product['type'] ?? '') === 'service' ? 'selected' : '' ?>>Service</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Catégorie</label>
                    <select class="form-select" name="category_id">
                        <option value="">Sans catégorie</option>
                        <?php foreach ($categories ?? [] as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom *</label>
                    <input type="text" class="form-control" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Code / SKU</label>
                    <input type="text" class="form-control" name="sku" value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prix de vente *</label>
                    <input type="number" class="form-control" name="price" required step="0.01" value="<?= $product['price'] ?? 0 ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prix de revient</label>
                    <input type="number" class="form-control" name="cost_price" step="0.01" value="<?= $product['cost_price'] ?? 0 ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Taux TVA (%)</label>
                    <input type="number" class="form-control" name="tax_rate" step="0.01" value="<?= $product['tax_rate'] ?? 19.25 ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unité</label>
                    <input type="text" class="form-control" name="unit" value="<?= htmlspecialchars($product['unit'] ?? 'unité') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stock</label>
                    <input type="number" class="form-control" name="stock_quantity" value="<?= $product['stock_quantity'] ?? 0 ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Seuil alerte stock</label>
                    <input type="number" class="form-control" name="stock_alert_threshold" value="<?= $product['stock_alert_threshold'] ?? 5 ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="/products" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
