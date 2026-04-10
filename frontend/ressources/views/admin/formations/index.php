<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Formations</h2>
        <p class="text-gray-600">Gestion des formations et ateliers</p>
    </div>
    <a href="/UpcycleConnect-PA2526/frontend/public/admin/formations/create"
       class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Ajouter une formation
    </a>
</div>

<?php if (empty($formations)): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        <i class="fas fa-graduation-cap text-4xl mb-3"></i>
        <p>Aucune formation disponible.</p>
    </div>
<?php else: ?>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Prix</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Places</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($formations as $f): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($f['titre'] ?? '-') ?></td>
                <td class="px-6 py-4 text-sm"><?= number_format($f['prix'] ?? 0, 2) ?> €</td>
                <td class="px-6 py-4 text-sm"><?= $f['places_dispo'] ?? 0 ?> / <?= $f['places_total'] ?? 0 ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $f['date'] ?? '-' ?></td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><?= $f['statut'] ?? '-' ?></span>
                </td>
                <td class="px-6 py-4">
                    <a href="/UpcycleConnect-PA2526/frontend/public/admin/formations/<?= $f['id'] ?>/delete"
                       onclick="return confirm('Supprimer cette formation ?')"
                       class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>