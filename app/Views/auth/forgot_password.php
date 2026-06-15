<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<h4 class="mb-3 text-center fw-semibold">Mot de passe oublié</h4>
<p class="text-muted text-center mb-4">Entrez votre email pour recevoir un lien de réinitialisation.</p>
<form method="POST" action="/forgot-password">
    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus placeholder="votre@email.com">
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Envoyer le lien</button>
</form>
<div class="text-center mt-3">
    <a href="/login" class="text-decoration-none small">Retour à la connexion</a>
</div>
