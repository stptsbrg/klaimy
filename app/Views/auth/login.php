<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<h4 class="mb-3 text-center fw-semibold">Connexion</h4>
<form method="POST" action="/login">
    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="email" name="email" required autofocus placeholder="votre@email.com">
        </div>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" required placeholder="Votre mot de passe">
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="/forgot-password" class="text-decoration-none small">Mot de passe oublié ?</a>
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Se connecter</button>
</form>
<div class="text-center mt-3">
    <span class="text-muted">Pas encore de compte ?</span>
    <a href="/register" class="text-decoration-none ms-1">Créer un compte</a>
</div>
