<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<div class="page-header mb-4"><h2 class="page-title">Paramètres entreprise</h2></div>

<div class="row">
    <div class="col-md-3 mb-3">
        <div class="list-group">
            <a href="/settings/company" class="list-group-item list-group-item-action active">Entreprise</a>
            <a href="/settings/users" class="list-group-item list-group-item-action">Utilisateurs</a>
            <a href="/settings/profile" class="list-group-item list-group-item-action">Mon profil</a>
            <a href="/settings/subscription" class="list-group-item list-group-item-action">Abonnement</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="/settings/company" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nom *</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($company['name'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Raison sociale</label><input type="text" class="form-control" name="legal_name" value="<?= htmlspecialchars($company['legal_name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Type</label><select class="form-select" name="type">
                            <?php foreach (['tpe'=>'TPE','pme'=>'PME','grande_entreprise'=>'Grande Entreprise','association'=>'Association','ong'=>'ONG','entrepreneur_individuel'=>'Entrepreneur Individuel'] as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= ($company['type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($company['email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Téléphone</label><input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($company['phone'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">WhatsApp</label><input type="tel" class="form-control" name="whatsapp" value="<?= htmlspecialchars($company['whatsapp'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Adresse</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($company['address'] ?? '') ?></textarea></div>
                        <div class="col-md-4"><label class="form-label">Ville</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars($company['city'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Pays</label><input type="text" class="form-control" name="country" value="<?= htmlspecialchars($company['country'] ?? 'Cameroun') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Devise</label><select class="form-select" name="default_currency">
                            <?php foreach (['XAF','XOF','USD','EUR','GBP'] as $c): ?><option value="<?= $c ?>" <?= ($company['default_currency'] ?? 'XAF') === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label">N° Contribuable</label><input type="text" class="form-control" name="tax_id" value="<?= htmlspecialchars($company['tax_id'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">RCCM</label><input type="text" class="form-control" name="rccm" value="<?= htmlspecialchars($company['rccm'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Site web</label><input type="url" class="form-control" name="website" value="<?= htmlspecialchars($company['website'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Préfixe facture</label><input type="text" class="form-control" name="invoice_prefix" value="<?= htmlspecialchars($company['invoice_prefix'] ?? 'FAC') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Préfixe devis</label><input type="text" class="form-control" name="quote_prefix" value="<?= htmlspecialchars($company['quote_prefix'] ?? 'DEV') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Délai paiement (jours)</label><input type="number" class="form-control" name="payment_terms" value="<?= $company['payment_terms'] ?? 30 ?>"></div>
                        <div class="col-md-6"><label class="form-label">Logo</label><input type="file" class="form-control" name="logo" accept="image/*"></div>
                        <div class="col-12"><label class="form-label">Mentions légales</label><textarea class="form-control" name="legal_mentions" rows="3"><?= htmlspecialchars($company['legal_mentions'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label">Conditions de paiement</label><textarea class="form-control" name="payment_conditions" rows="3"><?= htmlspecialchars($company['payment_conditions'] ?? '') ?></textarea></div>
                    </div>
                    <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
