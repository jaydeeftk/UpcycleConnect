<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Contrats & Abonnements</h2>
        <p class="text-gray-600">Gérez les contrats des professionnels</p>
    </div>
    <button onclick="document.getElementById('modal-contrat').classList.remove('hidden')"
        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Nouveau contrat
    </button>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div id="modal-contrat" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-bold mb-4">Nouveau contrat</h3>
        <form method="POST" action="/admin/contrats/store">
            <input type="hidden" name="date_signature" value="<?= date('Y-m-d') ?>">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Type</label>
                    <select name="type" class="w-full border rounded-lg px-4 py-2">
                        <option value="prestation">Prestation</option>
                        <option value="abonnement">Abonnement</option>
                        <option value="partenariat">Partenariat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">ID Professionnel</label>
                    <input type="number" name="id_professionnels" required class="w-full border rounded-lg px-4 py-2" placeholder="Ex: 1">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Date début</label>
                    <input type="date" name="date_debut" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Date fin</label>
                    <input type="date" name="date_fin" required class="w-full border rounded-lg px-4 py-2">
                </div>
            </div>
            <div class="flex gap-4 mt-6">
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">Créer</button>
                <button type="button" onclick="document.getElementById('modal-contrat').classList.add('hidden')"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Annuler</button>
            </div>
        </form>
    </div>
</div>

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
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? '')) ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['nom_entreprise'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"><?= htmlspecialchars($c['type'] ?? '') ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['date_debut'] ?? '') ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['date_fin'] ?? '') ?></td>
                    <td class="px-6 py-4">
                        <a href="/admin/contrats/<?= $c['id'] ?>/delete"
                            onclick="return confirm('Supprimer ce contrat ?')"
                            class="text-red-600 hover:text-red-800" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>