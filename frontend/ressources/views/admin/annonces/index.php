<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Annonces</h2>
        <p class="text-gray-600">Gérez et validez les annonces des particuliers</p>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Particulier</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contenu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($annonces)): ?>
                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucune annonce</td></tr>
            <?php else: ?>
                <?php foreach ($annonces as $a): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($a['email']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars(substr($a['contenu'], 0, 80)) ?>...</td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($a['date_publication']) ?></td>
                    <td class="px-6 py-4">
                        <?php
                            $sc = match($a['statut']) {
                                'validé' => 'bg-green-100 text-green-700',
                                'refusé' => 'bg-red-100 text-red-700',
                                default  => 'bg-yellow-100 text-yellow-700'
                            };
                        ?>
                        <span class="px-3 py-1 rounded-full text-sm <?= $sc ?>"><?= htmlspecialchars($a['statut']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/annonces/<?= $a['id'] ?>/valider">
                                <button class="text-green-600 hover:text-green-800" title="Valider"><i class="fas fa-check"></i></button>
                            </form>
                            <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/annonces/<?= $a['id'] ?>/refuser">
                                <button class="text-red-600 hover:text-red-800" title="Refuser"><i class="fas fa-times"></i></button>
                            </form>
                            <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/annonces/<?= $a['id'] ?>/supprimer">
                                <button class="text-gray-600 hover:text-gray-800" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>