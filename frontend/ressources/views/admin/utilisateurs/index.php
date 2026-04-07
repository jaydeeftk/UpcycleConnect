<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Utilisateurs</h2>
        <p class="text-gray-600">Gérez tous les utilisateurs de la plateforme</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($utilisateurs)): ?>
                <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Aucun utilisateur trouvé.</td></tr>
            <?php else: ?>
                <?php foreach ($utilisateurs as $user): ?>
                <tr>
                    <td class="px-6 py-4">#<?= str_pad($user['id'] ?? '', 3, '0', STR_PAD_LEFT) ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1)) ?><?= strtoupper(substr($user['nom'] ?? '', 0, 1)) ?>
                            </div>
                            <span class="font-medium"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($user['email'] ?? '') ?></td>
                    <td class="px-6 py-4">
                        <?php
                            $rc = match($user['role'] ?? 'particulier') {
                                'admin'         => 'bg-red-100 text-red-700',
                                'salarie'       => 'bg-purple-100 text-purple-700',
                                'professionnel' => 'bg-yellow-100 text-yellow-700',
                                default         => 'bg-blue-100 text-blue-700'
                            };
                        ?>
                        <span class="px-3 py-1 rounded-full text-sm <?= $rc ?>"><?= htmlspecialchars(ucfirst($user['role'] ?? 'particulier')) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <?php $sc = ($user['statut'] ?? '') === 'actif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'; ?>
                        <span class="px-3 py-1 rounded-full text-sm <?= $sc ?>"><?= htmlspecialchars(ucfirst($user['statut'] ?? '')) ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        <?= !empty($user['date_inscription']) ? date('d/m/Y', strtotime($user['date_inscription'])) : '-' ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="/admin/utilisateurs/<?= $user['id'] ?>"
                                class="text-blue-600 hover:text-blue-800" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/admin/utilisateurs/<?= $user['id'] ?>/delete"
                                class="text-red-600 hover:text-red-800" title="Supprimer"
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