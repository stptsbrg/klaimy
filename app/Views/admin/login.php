<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<h4 class="mb-3 text-center fw-semibold">Administration Klaimy</h4>
<form method="POST" action="/admin/login">
    <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
    <div class="mb-3"><label class="form-label">Mot de passe</label><input type="password" class="form-control" name="password" required></div>
    <button type="submit" class="btn btn-warning w-100 btn-lg">Connexion Admin</button>
</form>
