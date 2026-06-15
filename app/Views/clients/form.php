<?php
$csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf;
$_SESSION['csrf_token_time'] = time();
$isEdit = isset($client);
$action = $isEdit ? "/clients/{$client['id']}" : '/clients';
?>
<div class="page-header mb-4">
    <h2 class="page-title"><?= $isEdit ? 'Modifier le client' : 'Nouveau client' ?></h2>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= $action ?>">
            <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="individual" <?= ($client['type'] ?? '') === 'individual' ? 'selected' : '' ?>>Particulier</option>
                        <option value="company" <?= ($client['type'] ?? '') === 'company' ? 'selected' : '' ?>>Entreprise</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom / Raison sociale *</label>
                    <input type="text" class="form-control" name="name" required value="<?= htmlspecialchars($client['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact</label>
                    <input type="text" class="form-control" name="contact_name" value="<?= htmlspecialchars($client['contact_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($client['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">WhatsApp</label>
                    <input type="tel" class="form-control" name="whatsapp" value="<?= htmlspecialchars($client['whatsapp'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Adresse</label>
                    <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($client['address'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ville</label>
                    <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($client['city'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pays</label>
                    <input type="text" class="form-control" name="country" value="<?= htmlspecialchars($client['country'] ?? 'Cameroun') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">N° Contribuable</label>
                    <input type="text" class="form-control" name="tax_id" value="<?= htmlspecialchars($client['tax_id'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">RCCM</label>
                    <input type="text" class="form-control" name="rccm" value="<?= htmlspecialchars($client['rccm'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="/clients" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
