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

<!-- Modal ajout formation -->
<div id="modal-formation" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-bold mb-4">Créer une formation</h3>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/formations/store">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Titre</label>
                    <input type="text" name="titre" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border rounded-lg px-4 py-2"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prix (€)</label>
                    <input type="number" step="0.01" name="prix" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Durée (h)</label>
                    <input type="number" name="duree" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Statut</label>
                    <select name="statut" class="w-full border rounded-lg px-4 py-2">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">ID Salarié</label>
                    <input type="number" name="id_salaries" value="1" required class="w-full border rounded-lg px-4 py-2">
                </div>
            </div>
            <div class="flex gap-4 mt-6">
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">Créer</button>
                <button type="button" onclick="document.getElementById('modal-formation').classList.add('hidden')"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Annuler</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durée</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($formations)): ?>
                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucune formation</td></tr>
            <?php else: ?>
                <?php foreach ($formations as $f): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium"><?= htmlspecialchars($f['titre']) ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($f['description'] ?? '', 0, 60)) ?></div>
                    </td>
                    <td class="px-6 py-4"><?= htmlspecialchars($f['prix']) ?>€</td>
                    <td class="px-6 py-4"><?= htmlspecialchars($f['duree']) ?>h</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"><?= htmlspecialchars($f['statut']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="/UpcycleConnect-PA2526/frontend/public/admin/formations/<?= $f['id'] ?>/delete"
                            onclick="return confirm('Supprimer cette formation ?')"
                            class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>