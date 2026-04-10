<div class="mb-6">
    <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs" class="text-gray-500 hover:text-black text-sm">
        <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
            <?= strtoupper(substr($utilisateur['prenom'] ?? 'U', 0, 1)) ?><?= strtoupper(substr($utilisateur['nom'] ?? '', 0, 1)) ?>
        </div>
        <h3 class="text-xl font-bold"><?= htmlspecialchars(($utilisateur['prenom'] ?? '') . ' ' . ($utilisateur['nom'] ?? '')) ?></h3>
        <p class="text-gray-500 text-sm"><?= htmlspecialchars($utilisateur['email'] ?? '') ?></p>
        <span class="mt-2 inline-block px-3 py-1 rounded-full text-sm font-medium
            <?= ($utilisateur['statut'] ?? '') === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' ?>">
            <?= ucfirst($utilisateur['statut'] ?? 'actif') ?>
        </span>
        <div class="mt-4 text-sm text-gray-500">
            <i class="fas fa-calendar mr-1"></i>
            Inscrit le <?= $utilisateur['date_inscription'] ? date('d/m/Y', strtotime($utilisateur['date_inscription'])) : '-' ?>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4">Modifier l'utilisateur</h3>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs/<?= $utilisateur['id'] ?>/update">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($utilisateur['nom'] ?? '') ?>"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prénom</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>"
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Rôle / Statut</label>
                    <select name="statut" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="actif" <?= ($utilisateur['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif (Particulier)</option>
                        <option value="inactif" <?= ($utilisateur['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        <option value="admin" <?= ($utilisateur['statut'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                        <option value="salarie" <?= ($utilisateur['statut'] ?? '') === 'salarie' ? 'selected' : '' ?>>Salarié</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 text-sm mb-3">
                <i class="fas fa-save mr-2"></i>Enregistrer les modifications
            </button>
        </form>
        <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs/<?= $utilisateur['id'] ?>/delete"
           onclick="return confirm('Supprimer définitivement cet utilisateur ?')"
           class="w-full block text-center bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm">
            <i class="fas fa-trash mr-2"></i>Supprimer l'utilisateur
        </a>
    </div>
</div>