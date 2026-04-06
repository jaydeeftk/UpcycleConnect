<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Conteneurs</h2>
        <p class="text-gray-600">Suivi en temps réel des conteneurs</p>
    </div>
    <button onclick="document.getElementById('modal-conteneur').classList.remove('hidden')"
        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Ajouter un conteneur
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Localisation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacité</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($conteneurs)): ?>
                <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Aucun conteneur</td></tr>
            <?php else: ?>
                <?php foreach ($conteneurs as $c): ?>
                <tr>
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($c['localisation']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($c['capacite']) ?></td>
                    <td class="px-6 py-4">
                        <?php $sc = $c['statut'] === 'disponible' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>
                        <span class="px-3 py-1 rounded-full text-sm <?= $sc ?>"><?= htmlspecialchars($c['statut']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <button class="text-green-600 hover:text-green-800"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/conteneurs/<?= $c['id'] ?>/supprimer">
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