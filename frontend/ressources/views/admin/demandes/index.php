<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Gestion des dépôts (Objets)</h1>
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Objet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type d'objet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code de dépôt</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($demandes as $demande): ?>
                <tr>
                    <td class="px-6 py-4"><?= htmlspecialchars($demande['id']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($demande['type_objet']) ?></td>
                    <td class="px-6 py-4">
                        <?php if(strtolower($demande['statut']) === 'en_attente'): ?>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">En attente</span>
                        <?php elseif(strtolower($demande['statut']) === 'validee'): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Validée</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm"><?= htmlspecialchars(ucfirst($demande['statut'])) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 font-mono font-bold text-blue-600">
                        <?= !empty($demande['code_ouverture']) ? htmlspecialchars($demande['code_ouverture']) : '-' ?>
                    </td>
                    <td class="px-6 py-4 flex gap-2">
                        <?php if(strtolower($demande['statut']) === 'en_attente'): ?>
                            <form action="/UpcycleConnect-PA2526/frontend/public/admin/demandes/valider/<?= $demande['id'] ?>" method="POST">
                                <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm">Valider</button>
                            </form>
                            <form action="/UpcycleConnect-PA2526/frontend/public/admin/demandes/refuser/<?= $demande['id'] ?>" method="POST">
                                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">Refuser</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>