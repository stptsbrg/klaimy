<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<h4 class="mb-3 text-center fw-semibold">Vérification en deux étapes</h4>
<p class="text-muted text-center mb-4">Un code de vérification a été envoyé à votre adresse email. Ce code expire dans 10 minutes.</p>
<?php if (!empty($debugCode)): ?>
<div class="alert alert-info">
    <small><strong>Mode développement :</strong> Votre code est <strong><?= htmlspecialchars($debugCode) ?></strong></small>
</div>
<?php endif; ?>
<form method="POST" action="/verify-2fa">
    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
    <div class="mb-4">
        <label for="code" class="form-label">Code de vérification</label>
        <input type="text" class="form-control form-control-lg text-center" id="code" name="code"
               maxlength="6" pattern="[0-9]{6}" required autofocus placeholder="000000"
               style="letter-spacing: 0.5em; font-size: 1.5rem;">
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Vérifier</button>
</form>
<div class="text-center mt-3">
    <a href="/login" class="text-decoration-none small">Retour à la connexion</a>
</div>
