<?php
$csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf;
$_SESSION['csrf_token_time'] = time();
$isEdit = isset($invoice);
$action = $isEdit ? "/invoices/{$invoice['id']}" : '/invoices';
?>
<div class="page-header mb-4">
    <h2 class="page-title"><?= $isEdit ? 'Modifier la facture' : 'Nouvelle facture' ?></h2>
</div>

<form method="POST" action="<?= $action ?>" id="invoiceForm">
    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Informations</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Client *</label>
                            <select class="form-select" name="client_id" required>
                                <option value="">Sélectionner un client</option>
                                <?php foreach ($clients ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($invoice['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Devise</label>
                            <select class="form-select" name="currency">
                                <?php foreach (['XAF', 'XOF', 'USD', 'EUR', 'GBP'] as $cur): ?>
                                <option value="<?= $cur ?>" <?= ($invoice['currency'] ?? 'XAF') === $cur ? 'selected' : '' ?>><?= $cur ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date d'émission</label>
                            <input type="date" class="form-control" name="issue_date" value="<?= $invoice['issue_date'] ?? date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date d'échéance</label>
                            <input type="date" class="form-control" name="due_date" value="<?= $invoice['due_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Lignes de facture</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn"><i class="bi bi-plus-lg me-1"></i>Ajouter une ligne</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:200px">Description</th>
                                    <th style="width:100px">Qté</th>
                                    <th style="width:130px">Prix unitaire</th>
                                    <th style="width:100px">TVA %</th>
                                    <th style="width:130px" class="text-end">Total</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <?php
                                $items = $invoice['items'] ?? [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 19.25, 'product_id' => '']];
                                foreach ($items as $idx => $item):
                                ?>
                                <tr class="item-row">
                                    <td>
                                        <input type="hidden" name="item_product_id[]" value="<?= $item['product_id'] ?? '' ?>">
                                        <input type="text" class="form-control form-control-sm" name="item_description[]" value="<?= htmlspecialchars($item['description'] ?? '') ?>" required placeholder="Description">
                                    </td>
                                    <td><input type="number" class="form-control form-control-sm item-qty" name="item_quantity[]" value="<?= $item['quantity'] ?? 1 ?>" min="0.01" step="0.01"></td>
                                    <td><input type="number" class="form-control form-control-sm item-price" name="item_price[]" value="<?= $item['unit_price'] ?? 0 ?>" min="0" step="0.01"></td>
                                    <td><input type="number" class="form-control form-control-sm item-tax" name="item_tax_rate[]" value="<?= $item['tax_rate'] ?? 19.25 ?>" min="0" step="0.01"></td>
                                    <td class="text-end fw-semibold item-total">0</td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Product selector -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Produits rapides</h6></div>
                <div class="card-body">
                    <select class="form-select mb-2" id="productSelect">
                        <option value="">Ajouter un produit...</option>
                        <?php foreach ($products ?? [] as $p): ?>
                        <option value="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= $p['price'] ?>" data-tax="<?= $p['tax_rate'] ?>"><?= htmlspecialchars($p['name']) ?> - <?= number_format($p['price'], 0, ',', ' ') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Totals -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Totaux</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total HT</span>
                        <span class="fw-semibold" id="subtotal">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>TVA</span>
                        <span class="fw-semibold" id="totalTax">0</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Remise</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control" name="discount_amount" id="discountAmount" value="<?= $invoice['discount_amount'] ?? 0 ?>" min="0" step="0.01">
                            <select class="form-select" name="discount_type" style="max-width:80px">
                                <option value="fixed" <?= ($invoice['discount_type'] ?? 'fixed') === 'fixed' ? 'selected' : '' ?>>Fixe</option>
                                <option value="percentage" <?= ($invoice['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>%</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fs-5 fw-bold">Total TTC</span>
                        <span class="fs-5 fw-bold text-primary" id="grandTotal">0</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($invoice['notes'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Conditions</label>
                        <textarea class="form-control" name="terms" rows="2"><?= htmlspecialchars($invoice['terms'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="status" value="draft" class="btn btn-lg btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="/invoices" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productsData = <?= json_encode(array_map(function($p) {
        return ['id' => $p['id'], 'name' => $p['name'], 'price' => $p['price'], 'tax_rate' => $p['tax_rate']];
    }, $products ?? [])) ?>;

    function addLine(desc = '', qty = 1, price = 0, tax = 19.25, productId = '') {
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td><input type="hidden" name="item_product_id[]" value="${productId}"><input type="text" class="form-control form-control-sm" name="item_description[]" value="${desc}" required placeholder="Description"></td>
            <td><input type="number" class="form-control form-control-sm item-qty" name="item_quantity[]" value="${qty}" min="0.01" step="0.01"></td>
            <td><input type="number" class="form-control form-control-sm item-price" name="item_price[]" value="${price}" min="0" step="0.01"></td>
            <td><input type="number" class="form-control form-control-sm item-tax" name="item_tax_rate[]" value="${tax}" min="0" step="0.01"></td>
            <td class="text-end fw-semibold item-total">0</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(row);
        calculateTotals();
    }

    document.getElementById('addLineBtn').addEventListener('click', () => addLine());

    document.getElementById('productSelect').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.value) {
            addLine(opt.dataset.name, 1, opt.dataset.price, opt.dataset.tax, opt.value);
            this.value = '';
        }
    });

    document.getElementById('itemsBody').addEventListener('click', function(e) {
        if (e.target.closest('.remove-line')) {
            e.target.closest('tr').remove();
            calculateTotals();
        }
    });

    document.getElementById('itemsBody').addEventListener('input', calculateTotals);
    document.getElementById('discountAmount').addEventListener('input', calculateTotals);

    function calculateTotals() {
        let subtotal = 0, totalTax = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const taxRate = parseFloat(row.querySelector('.item-tax').value) || 0;
            const lineTotal = qty * price;
            const lineTax = lineTotal * (taxRate / 100);
            row.querySelector('.item-total').textContent = lineTotal.toLocaleString('fr-FR');
            subtotal += lineTotal;
            totalTax += lineTax;
        });

        const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
        const grandTotal = subtotal + totalTax - discount;

        document.getElementById('subtotal').textContent = subtotal.toLocaleString('fr-FR');
        document.getElementById('totalTax').textContent = totalTax.toLocaleString('fr-FR');
        document.getElementById('grandTotal').textContent = grandTotal.toLocaleString('fr-FR');
    }

    calculateTotals();
});
</script>
