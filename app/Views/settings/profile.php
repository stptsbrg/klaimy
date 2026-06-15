<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<div class="page-header mb-4"><h2 class="page-title">Mon profil</h2></div>
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="list-group">
            <a href="/settings/company" class="list-group-item list-group-item-action">Entreprise</a>
            <a href="/settings/users" class="list-group-item list-group-item-action">Utilisateurs</a>
            <a href="/settings/profile" class="list-group-item list-group-item-action active">Mon profil</a>
            <a href="/settings/subscription" class="list-group-item list-group-item-action">Abonnement</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="/settings/profile">
                    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Prénom</label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Nom</label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled></div>
                        <div class="col-md-6"><label class="form-label">Téléphone</label><input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                        <div class="col-12"><hr><h6>Changer le mot de passe</h6></div>
                        <div class="col-md-6"><label class="form-label">Nouveau mot de passe</label><input type="password" class="form-control" name="password" minlength="8" placeholder="Laisser vide pour ne pas changer"></div>
                    </div>
                    <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
