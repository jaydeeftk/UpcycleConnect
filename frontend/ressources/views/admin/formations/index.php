<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Formations</h2>
        <p class="text-gray-600">Gérez les formations proposées</p>
    </div>
    <button onclick="document.getElementById('modal-formation').classList.remove('hidden')"
        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Créer une formation
    </button>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durée</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salarié</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($formations)): ?>
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune formation</td></tr>
            <?php else: ?>
                <?php foreach ($formations as $f): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium"><?= htmlspecialchars($f['titre']) ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($f['description'], 0, 60)) ?>...</div>
                    </td>
                    <td class="px-6 py-4"><?= htmlspecialchars($f['prix']) ?>€</td>
                    <td class="px-6 py-4"><?= htmlspecialchars($f['duree']) ?>h</td>
                    <td class="px-6 py-4"><?= htmlspecialchars($f['prenom_salarie'] . ' ' . $f['nom_salarie']) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"><?= htmlspecialchars($f['statut']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <button class="text-green-600 hover:text-green-800"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/formations/<?= $f['id'] ?>/supprimer">
                                <button class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>