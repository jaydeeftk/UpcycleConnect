<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Profil utilisateur</h2>
        <p class="text-gray-600">Détails complets du compte</p>
    </div>
    <a href="/admin/utilisateurs"
        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
        <i class="fas fa-arrow-left mr-2"></i>Retour
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (empty($utilisateur)): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">Utilisateur introuvable.</div>
<?php else: ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
            <?= strtoupper(substr($utilisateur['prenom'] ?? 'U', 0, 1)) ?><?= strtoupper(substr($utilisateur['nom'] ?? '', 0, 1)) ?>
        </div>
        <h3 class="text-xl font-bold"><?= htmlspecialchars(($utilisateur['prenom'] ?? '') . ' ' . ($utilisateur['nom'] ?? '')) ?></h3>
        <p class="text-gray-500"><?= htmlspecialchars($utilisateur['email'] ?? '') ?></p>
        <div class="mt-4">
            <?php
                $rc = match($utilisateur['role'] ?? 'particulier') {
                    'admin'         => 'bg-red-100 text-red-700',
                    'salarie'       => 'bg-purple-100 text-purple-700',
                    'professionnel' => 'bg-yellow-100 text-yellow-700',
                    default         => 'bg-blue-100 text-blue-700'
                };
            ?>
            <span class="px-3 py-1 rounded-full text-sm <?= $rc ?>"><?= htmlspecialchars(ucfirst($utilisateur['role'] ?? 'particulier')) ?></span>
        </div>
        <div class="mt-6 flex gap-2 justify-center">
            <a href="/admin/utilisateurs/<?= $utilisateur['id'] ?>/delete"
                class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm"
                onclick="return confirm('Supprimer cet utilisateur ?')">
                <i class="fas fa-trash mr-1"></i>Supprimer
            </a>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-bold mb-4">Informations personnelles</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Nom</p>
                    <p class="font-medium"><?= htmlspecialchars($utilisateur['nom'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Prénom</p>
                    <p class="font-medium"><?= htmlspecialchars($utilisateur['prenom'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium"><?= htmlspecialchars($utilisateur['email'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Téléphone</p>
                    <p class="font-medium"><?= htmlspecialchars($utilisateur['telephone'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Adresse</p>
                    <p class="font-medium"><?= htmlspecialchars($utilisateur['adresse'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Statut</p>
                    <p class="font-medium"><?= htmlspecialchars(ucfirst($utilisateur['statut'] ?? '-')) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-bold mb-4">Modifier le statut</h4>
            <form method="POST" action="/admin/utilisateurs/<?= $utilisateur['id'] ?>/statut" class="flex gap-4">
                <select name="statut" class="border rounded-lg px-4 py-2 flex-1">
                    <option value="actif" <?= ($utilisateur['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="inactif" <?= ($utilisateur['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                    <option value="suspendu" <?= ($utilisateur['statut'] ?? '') === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                </select>
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
                    Mettre à jour
                </button>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>