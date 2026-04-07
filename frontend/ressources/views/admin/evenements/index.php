<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Événements</h2>
        <p class="text-gray-600">Gérez les événements et ateliers</p>
    </div>
    <a href="/admin/evenements/create"
        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Créer un événement
    </a>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Événement</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacité</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($evenements)): ?>
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Aucun événement</td></tr>
            <?php else: ?>
                <?php foreach ($evenements as $e): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium"><?= htmlspecialchars($e['titre']) ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($e['description'] ?? '', 0, 50)) ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($e['date'] ?? '') ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($e['lieu'] ?? '') ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($e['capacite'] ?? '') ?></td>
                    <td class="px-6 py-4">
                        <?php
                            $sc = match($e['statut'] ?? '') {
                                'en cours' => 'bg-green-100 text-green-700',
                                'terminé'  => 'bg-gray-100 text-gray-600',
                                default    => 'bg-blue-100 text-blue-700'
                            };
                        ?>
                        <span class="px-3 py-1 rounded-full text-sm <?= $sc ?>"><?= htmlspecialchars($e['statut'] ?? '') ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="/admin/evenements/<?= $e['id'] ?>/delete"
                                onclick="return confirm('Supprimer cet événement ?')"
                                class="text-red-600 hover:text-red-800">
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