<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Utilisateurs</h2>
        <p class="text-gray-600">Gérez tous les utilisateurs de la plateforme</p>
    </div>
    <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs/create"
       class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Ajouter un utilisateur
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($utilisateurs)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        Aucun utilisateur trouvé.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($utilisateurs as $user): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">#<?= str_pad($user['id'] ?? '', 3, '0', STR_PAD_LEFT) ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-3 text-sm">
                                <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1)) ?><?= strtoupper(substr($user['nom'] ?? '', 0, 1)) ?>
                            </div>
                            <span class="font-medium"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-sm"><?= htmlspecialchars($user['email'] ?? '') ?></td>
                    <td class="px-6 py-4">
                        <?php
                        $s = $user['statut'] ?? 'actif';
                        $colors = [
                            'admin'    => 'bg-purple-100 text-purple-800',
                            'actif'    => 'bg-green-100 text-green-800',
                            'inactif'  => 'bg-red-100 text-red-800',
                            'salarie'  => 'bg-blue-100 text-blue-800',
                        ];
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $colors[$s] ?? 'bg-gray-100 text-gray-800' ?>">
                            <?= ucfirst($s) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-sm">
                        <?= !empty($user['date_inscription']) ? date('d/m/Y', strtotime($user['date_inscription'])) : '-' ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs/<?= $user['id'] ?>"
                               class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600" title="Voir / Modifier">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs/<?= $user['id'] ?>/delete"
                               class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600"
                               onclick="return confirm('Supprimer cet utilisateur définitivement ?')" title="Supprimer">
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

<div class="mt-4 text-sm text-gray-500 text-right">
    <?= count($utilisateurs) ?> utilisateur(s) au total
</div>