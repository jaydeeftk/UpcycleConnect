<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Annonces</h2>
        <p class="text-gray-600">Modération des annonces déposées</p>
    </div>
</div>

<?php if (empty($annonces)): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        <i class="fas fa-bullhorn text-4xl mb-3"></i>
        <p>Aucune annonce pour le moment.</p>
    </div>
<?php else: ?>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($annonces as $a): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">#<?= $a['id'] ?? '-' ?></td>
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($a['titre'] ?? '-') ?></td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($a['description'] ?? '-') ?></td>
                <td class="px-6 py-4">
                    <?php $s = $a['statut'] ?? 'en_attente'; $colors = ['active' => 'bg-green-100 text-green-800', 'en_attente' => 'bg-yellow-100 text-yellow-800', 'rejetee' => 'bg-red-100 text-red-800']; ?>
                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $colors[$s] ?? 'bg-gray-100 text-gray-800' ?>"><?= ucfirst($s) ?></span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $a['date_publication'] ?? '-' ?></td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <a href="/UpcycleConnect-PA2526/frontend/public/admin/annonces/<?= $a['id'] ?>/validate"
                           class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600" title="Valider">
                            <i class="fas fa-check"></i>
                        </a>
                        <a href="/UpcycleConnect-PA2526/frontend/public/admin/annonces/<?= $a['id'] ?>/reject"
                           class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600" title="Rejeter">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>