<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<h4 class="mb-3 text-center fw-semibold">Réinitialiser le mot de passe</h4>
<form method="POST" action="/reset-password">
    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    <div class="mb-3">
        <label for="password" class="form-label">Nouveau mot de passe</label>
        <input type="password" class="form-control" id="password" name="password" required minlength="8">
    </div>
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmer</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">Réinitialiser</button>
</form>
