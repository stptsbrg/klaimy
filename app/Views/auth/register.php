<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<h4 class="mb-3 text-center fw-semibold">Créer un compte</h4>
<form method="POST" action="/register">
    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
    <div class="mb-3">
        <label for="company_name" class="form-label">Nom de l'entreprise</label>
        <input type="text" class="form-control" id="company_name" name="company_name" required placeholder="Mon Entreprise SARL">
    </div>
    <div class="mb-3">
        <label for="company_type" class="form-label">Type</label>
        <select class="form-select" id="company_type" name="company_type">
            <option value="tpe">TPE</option>
            <option value="pme">PME</option>
            <option value="grande_entreprise">Grande Entreprise</option>
            <option value="association">Association</option>
            <option value="ong">ONG</option>
            <option value="entrepreneur_individuel">Entrepreneur Individuel</option>
        </select>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="first_name" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="last_name" class="form-label">Nom</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required placeholder="votre@email.com">
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Téléphone</label>
        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+237 6XX XXX XXX">
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="password" required minlength="8" placeholder="Minimum 8 caractères">
    </div>
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Créer mon compte</button>
</form>
<div class="text-center mt-3">
    <span class="text-muted">Déjà un compte ?</span>
    <a href="/login" class="text-decoration-none ms-1">Se connecter</a>
</div>
