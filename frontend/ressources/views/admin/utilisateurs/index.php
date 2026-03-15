<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Utilisateurs</h2>
        <p class="text-gray-600">Gérez tous les utilisateurs de la plateforme</p>
    </div>
    <button class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Ajouter un utilisateur
    </button>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" placeholder="Rechercher..." class="border rounded-lg px-4 py-2">
        <select class="border rounded-lg px-4 py-2">
            <option>Tous les types</option>
            <option>Particuliers</option>
            <option>Artisans</option>
            <option>Salariés</option>
        </select>
        <select class="border rounded-lg px-4 py-2">
            <option>Tous les statuts</option>
            <option>Actif</option>
            <option>Inactif</option>
        </select>
        <button class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
            <i class="fas fa-filter mr-2"></i>Filtrer
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
    <?php if (empty($utilisateurs)): ?>
        <tr>
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                Aucun utilisateur trouvé.
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($utilisateurs as $user): ?>
        <tr>
            <td class="px-6 py-4">#<?= str_pad($user['id'] ?? '', 3, '0', STR_PAD_LEFT) ?></td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                        <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1)) ?><?= strtoupper(substr($user['nom'] ?? '', 0, 1)) ?>
                    </div>
                    <span class="font-medium"><?= htmlspecialchars($user['prenom'] ?? '') ?> <?= htmlspecialchars($user['nom'] ?? '') ?></span>
                </div>
            </td>
            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($user['email'] ?? '') ?></td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                    <?= htmlspecialchars(ucfirst($user['statut'] ?? 'Particulier')) ?>
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                    Actif
                </span>
            </td>
            <td class="px-6 py-4 text-gray-600">
                <?php
                    $date = $user['created_at'] ?? null;
                    echo $date ? date('d/m/Y', strtotime($date)) : '-';
                ?>
            </td>
            <td class="px-6 py-4">
                <div class="flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-green-600 hover:text-green-800">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs/<?= $user['id'] ?>/delete"
                        class="text-red-600 hover:text-red-800"
                        onclick="return confirm('Supprimer cet utilisateur ?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
    </table>
</div>

<div class="mt-6 flex items-center justify-between">
    <div class="text-gray-600">
        Affichage de 1 à 10 sur 248 utilisateurs
    </div>
    <div class="flex gap-2">
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-50">Précédent</button>
        <button class="px-4 py-2 bg-green-500 text-white rounded-lg">1</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-50">2</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-50">3</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-50">Suivant</button>
    </div>
</div>