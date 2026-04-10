<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Événements</h2>
        <p class="text-gray-600">Gestion des événements et ateliers</p>
    </div>
    <a href="/UpcycleConnect-PA2526/frontend/public/admin/evenements/create"
       class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Créer un événement
    </a>
</div>

<?php if (empty($evenements)): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        <i class="fas fa-calendar text-4xl mb-3"></i>
        <p>Aucun événement pour le moment.</p>
    </div>
<?php else: ?>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Lieu</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($evenements as $e): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($e['titre'] ?? '-') ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($e['lieu'] ?? '-') ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $e['date_evenement'] ?? '-' ?></td>
                <td class="px-6 py-4">
                    <a href="/UpcycleConnect-PA2526/frontend/public/admin/evenements/<?= $e['id'] ?>/delete"
                       onclick="return confirm('Supprimer cet événement ?')"
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