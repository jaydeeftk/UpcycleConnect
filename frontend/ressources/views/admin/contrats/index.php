<div class="mb-6">
    <h2 class="text-2xl font-bold">Contrats & Abonnements</h2>
    <p class="text-gray-600">Gérez les contrats des professionnels</p>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Professionnel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entreprise</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Début</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fin</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($contrats)): ?>
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucun contrat</td></tr>
            <?php else: ?>
                <?php foreach ($contrats as $c): ?>
                <tr>
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['nom_entreprise']) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"><?= htmlspecialchars($c['type']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['date_debut']) ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['date_fin']) ?></td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <button class="text-green-600 hover:text-green-800"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/contrats/<?= $c['id'] ?>/supprimer">
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