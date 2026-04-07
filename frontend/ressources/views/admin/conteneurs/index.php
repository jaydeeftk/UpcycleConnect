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

<!-- Modal ajout conteneur -->
<div id="modal-conteneur" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Ajouter un conteneur</h3>
        <form method="POST" action="/admin/conteneurs/store">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Localisation</label>
                    <input type="text" name="localisation" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Capacité</label>
                    <input type="text" name="capacite" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Statut</label>
                    <select name="statut" class="w-full border rounded-lg px-4 py-2">
                        <option value="disponible">Disponible</option>
                        <option value="plein">Plein</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <input type="hidden" name="id_administrateurs" value="<?= $_SESSION['user']['id'] ?? 1 ?>">
            </div>
            <div class="flex gap-4 mt-6">
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">Ajouter</button>
                <button type="button" onclick="document.getElementById('modal-conteneur').classList.add('hidden')"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Annuler</button>
            </div>
        </form>
    </div>
</div>

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
                        <a href="/admin/conteneurs/<?= $c['id'] ?>/delete"
                            onclick="return confirm('Supprimer ce conteneur ?')"
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