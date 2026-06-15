<?php
$csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf;
$_SESSION['csrf_token_time'] = time();
?>
<div class="page-header mb-4"><h2 class="page-title">Nouveau devis</h2></div>

<form method="POST" action="/quotes" id="quoteForm">
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
                                <option value="">Sélectionner...</option>
                                <?php foreach ($clients ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Devise</label>
                            <select class="form-select" name="currency">
                                <?php foreach (['XAF','XOF','USD','EUR','GBP'] as $cur): ?>
                                <option value="<?= $cur ?>"><?= $cur ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="issue_date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Validité</label>
                            <input type="date" class="form-control" name="expiry_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Lignes du devis</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn"><i class="bi bi-plus-lg me-1"></i>Ligne</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0"><thead class="table-light"><tr><th>Description</th><th style="width:80px">Qté</th><th style="width:120px">Prix</th><th style="width:80px">TVA%</th><th style="width:110px" class="text-end">Total</th><th style="width:40px"></th></tr></thead>
                        <tbody id="itemsBody">
                            <tr class="item-row">
                                <td><input type="hidden" name="item_product_id[]" value=""><input type="text" class="form-control form-control-sm" name="item_description[]" required></td>
                                <td><input type="number" class="form-control form-control-sm item-qty" name="item_quantity[]" value="1" min="0.01" step="0.01"></td>
                                <td><input type="number" class="form-control form-control-sm item-price" name="item_price[]" value="0" min="0" step="0.01"></td>
                                <td><input type="number" class="form-control form-control-sm item-tax" name="item_tax_rate[]" value="19.25" min="0" step="0.01"></td>
                                <td class="text-end fw-semibold item-total">0</td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        </tbody></table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Produits</h6></div>
                <div class="card-body">
                    <select class="form-select" id="productSelect">
                        <option value="">Ajouter...</option>
                        <?php foreach ($products ?? [] as $p): ?>
                        <option data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= $p['price'] ?>" data-tax="<?= $p['tax_rate'] ?>" value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Sous-total</span><span id="subtotal" class="fw-semibold">0</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>TVA</span><span id="totalTax" class="fw-semibold">0</span></div>
                    <div class="mb-3"><label class="form-label small">Remise</label><input type="number" class="form-control form-control-sm" name="discount_amount" id="discountAmount" value="0" min="0" step="0.01"><input type="hidden" name="discount_type" value="fixed"></div>
                    <hr><div class="d-flex justify-content-between"><span class="fs-5 fw-bold">Total</span><span class="fs-5 fw-bold text-primary" id="grandTotal">0</span></div>
                </div>
            </div>
            <div class="card shadow-sm mb-4"><div class="card-body"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea><label class="form-label mt-2">Conditions</label><textarea class="form-control" name="terms" rows="2"></textarea></div></div>
            <div class="d-grid gap-2"><button type="submit" class="btn btn-lg btn-primary"><i class="bi bi-check-lg me-1"></i>Créer le devis</button><a href="/quotes" class="btn btn-outline-secondary">Annuler</a></div>
        </div>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function addLine(d='',q=1,p=0,t=19.25,pid='') {
        const r = document.createElement('tr'); r.className='item-row';
        r.innerHTML=`<td><input type="hidden" name="item_product_id[]" value="${pid}"><input type="text" class="form-control form-control-sm" name="item_description[]" value="${d}" required></td><td><input type="number" class="form-control form-control-sm item-qty" name="item_quantity[]" value="${q}" min="0.01" step="0.01"></td><td><input type="number" class="form-control form-control-sm item-price" name="item_price[]" value="${p}" min="0" step="0.01"></td><td><input type="number" class="form-control form-control-sm item-tax" name="item_tax_rate[]" value="${t}" min="0" step="0.01"></td><td class="text-end fw-semibold item-total">0</td><td><button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="bi bi-trash"></i></button></td>`;
        document.getElementById('itemsBody').appendChild(r); calc();
    }
    document.getElementById('addLineBtn').addEventListener('click',()=>addLine());
    document.getElementById('productSelect').addEventListener('change',function(){const o=this.options[this.selectedIndex];if(o.value){addLine(o.dataset.name,1,o.dataset.price,o.dataset.tax,o.value);this.value='';}});
    document.getElementById('itemsBody').addEventListener('click',function(e){if(e.target.closest('.remove-line')){e.target.closest('tr').remove();calc();}});
    document.getElementById('itemsBody').addEventListener('input',calc);
    document.getElementById('discountAmount').addEventListener('input',calc);
    function calc(){let s=0,tx=0;document.querySelectorAll('.item-row').forEach(r=>{const q=parseFloat(r.querySelector('.item-qty').value)||0,p=parseFloat(r.querySelector('.item-price').value)||0,t=parseFloat(r.querySelector('.item-tax').value)||0,l=q*p;r.querySelector('.item-total').textContent=l.toLocaleString('fr-FR');s+=l;tx+=l*(t/100);});const d=parseFloat(document.getElementById('discountAmount').value)||0;document.getElementById('subtotal').textContent=s.toLocaleString('fr-FR');document.getElementById('totalTax').textContent=tx.toLocaleString('fr-FR');document.getElementById('grandTotal').textContent=(s+tx-d).toLocaleString('fr-FR');}
    calc();
});
</script>
