<?php $csrf = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); $_SESSION['csrf_token'] = $csrf; $_SESSION['csrf_token_time'] = time(); ?>
<div class="page-header mb-4"><h2 class="page-title">Utilisateurs</h2></div>
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="list-group">
            <a href="/settings/company" class="list-group-item list-group-item-action">Entreprise</a>
            <a href="/settings/users" class="list-group-item list-group-item-action active">Utilisateurs</a>
            <a href="/settings/profile" class="list-group-item list-group-item-action">Mon profil</a>
            <a href="/settings/subscription" class="list-group-item list-group-item-action">Abonnement</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Équipe</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Dernière connexion</th></tr></thead>
                        <tbody>
                        <?php foreach ($users ?? [] as $u): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td><?php $roles = ['owner'=>'Propriétaire','admin'=>'Administrateur','accountant'=>'Comptable','commercial'=>'Commercial','cashier'=>'Caissier','collaborator'=>'Collaborateur']; ?><span class="badge bg-primary-subtle text-primary"><?= $roles[$u['role']] ?? $u['role'] ?></span></td>
                            <td><span class="badge bg-<?= $u['is_active'] ? 'success' : 'secondary' ?>"><?= $u['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                            <td class="text-muted small"><?= $u['last_login_at'] ? date('d/m/Y H:i', strtotime($u['last_login_at'])) : 'Jamais' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/settings/users">
                <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">
                <div class="modal-header"><h5 class="modal-title">Nouvel utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Prénom</label><input type="text" class="form-control" name="first_name" required></div>
                        <div class="col-6"><label class="form-label">Nom</label><input type="text" class="form-control" name="last_name" required></div>
                        <div class="col-12"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                        <div class="col-12"><label class="form-label">Mot de passe</label><input type="password" class="form-control" name="password" required minlength="8"></div>
                        <div class="col-12"><label class="form-label">Rôle</label><select class="form-select" name="role">
                            <option value="admin">Administrateur</option><option value="accountant">Comptable</option><option value="commercial">Commercial</option><option value="cashier">Caissier</option><option value="collaborator" selected>Collaborateur</option>
                        </select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-primary">Créer</button></div>
            </form>
        </div>
    </div>
</div>
