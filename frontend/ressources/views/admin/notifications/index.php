<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Notifications</h2>
        <p class="text-gray-600">Gérez les notifications envoyées aux utilisateurs</p>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destinataire</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contenu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($notifications)): ?>
                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucune notification</td></tr>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium"><?= htmlspecialchars($n['prenom'] . ' ' . $n['nom']) ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($n['email']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars(substr($n['contenu'], 0, 80)) ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($n['date_envoi']) ?></td>
                    <td class="px-6 py-4">
                        <?php if ($n['statut']): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Lu</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Non lu</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/notifications/<?= $n['id'] ?>/supprimer">
                            <button class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>