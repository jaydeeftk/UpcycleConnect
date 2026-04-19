<?php
$success = $_SESSION['success'] ?? null;
$error_session = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Formations</h2>
        <p class="text-gray-600">Gérez les formations publiées sur le site</p>
    </div>
    <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Ajouter une formation
    </button>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if ($error_session): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error_session) ?>
</div>
<?php endif; ?>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total formations</p>
                <p class="text-3xl font-bold"><?= count($formations) ?></p>
            </div>
            <i class="fas fa-graduation-cap text-4xl text-blue-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">En attente</p>
                <p class="text-3xl font-bold text-yellow-600">
                    <?= count(array_filter($formations, fn($f) => $f['statut'] === 'en_attente')) ?>
                </p>
            </div>
            <i class="fas fa-clock text-4xl text-yellow-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Validées</p>
                <p class="text-3xl font-bold text-green-600">
                    <?= count(array_filter($formations, fn($f) => $f['statut'] === 'valide')) ?>
                </p>
            </div>
            <i class="fas fa-check-circle text-4xl text-green-500"></i>
        </div>
    </div>
</div>

<!-- Tableau des formations -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durée (min)</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Auteur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($formations)): ?>
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                    <i class="fas fa-graduation-cap text-4xl mb-3 text-gray-300"></i>
                    <p>Aucune formation pour le moment.</p>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($formations as $formation): ?>
<tr>
    <td class="px-6 py-4 text-sm font-medium text-gray-800">
        <?= htmlspecialchars($formation['titre'] ?? '') ?>
    </td>
    <td class="px-6 py-4">
        <p class="text-sm text-gray-600 max-w-xs truncate">
            <?= htmlspecialchars($formation['description'] ?? '') ?>
        </p>
    </td>
    <td class="px-6 py-4 text-sm text-gray-600">
        <?= number_format($formation['prix'] ?? 0, 2) ?> €
    </td>
    <td class="px-6 py-4 text-sm text-gray-600">
        <?= htmlspecialchars($formation['duree'] ?? '') ?> min
    </td>
    <td class="px-6 py-4">
        <?php
        $statut = $formation['statut'] ?? '';
        $colors = [
            'en_attente' => 'bg-yellow-100 text-yellow-800',
            'valide'     => 'bg-green-100 text-green-800',
            'annule'     => 'bg-red-100 text-red-800',
        ];
        $color = $colors[$statut] ?? 'bg-gray-100 text-gray-800';
        ?>
        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $color ?>">
            <?= ucfirst(str_replace('_', ' ', $statut)) ?>
        </span>
    </td>
    <td class="px-6 py-4 text-sm text-gray-600">
        <?= htmlspecialchars($formation['auteur'] ?? 'Inconnu') ?>
    </td>
    <td class="px-6 py-4">
        <div class="flex gap-2">
            <button onclick="openEditModal(
                    <?= $formation['id_formations'] ?>,
                    `<?= addslashes($formation['titre']) ?>`,
                    `<?= addslashes($formation['description']) ?>`,
                    <?= $formation['prix'] ?>,
                    <?= $formation['duree'] ?>,
                    `<?= addslashes($formation['date'] ?? '') ?>`,
                    `<?= addslashes($formation['lieu'] ?? '') ?>`
                )"
                class="text-blue-600 hover:text-blue-800" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <a href="/UpcycleConnect-PA2526/frontend/public/salaries/formations/<?= $formation['id_formations'] ?>/delete"
               onclick="return confirm('Supprimer cette formation ?')"
               class="text-red-600 hover:text-red-800" title="Supprimer">
                <i class="fas fa-trash"></i>
            </a>
        </div>
    </td>
    <td class="px-6 py-4 text-sm text-gray-600">
        <?= $formation['date'] ? date('d/m/Y H:i', strtotime($formation['date'])) : '—' ?>
    </td>
    <td class="px-6 py-4 text-sm text-gray-600">
        <?= htmlspecialchars($formation['lieu'] ?? '—') ?>
    </td>
</tr>
<?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal ajout -->
<div id="modal-add" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Ajouter une formation</h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/salaries/formations/store">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                <input type="text" name="titre" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Titre de la formation">
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
        <input type="datetime-local" name="date"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Lieu</label>
        <input type="text" name="lieu"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
               placeholder="Lieu de la formation">
    </div>
</div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                          placeholder="Description de la formation..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prix (€)</label>
                    <input type="number" name="prix" min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durée (min)</label>
                    <input type="number" name="duree" min="1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="Ex: 60">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i>Créer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal édition -->
<div id="modal-edit" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Modifier la formation</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" id="form-edit" action="">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                <input type="text" name="titre" id="edit-titre" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="edit-description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
        <input type="datetime-local" name="date" id="edit-date"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Lieu</label>
        <input type="text" name="lieu" id="edit-lieu"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
               placeholder="Lieu de la formation">
    </div>
</div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prix (€)</label>
                    <input type="number" name="prix" id="edit-prix" min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durée (min)</label>
                    <input type="number" name="duree" id="edit-duree" min="1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, titre, description, prix, duree, date, lieu) {
    document.getElementById('edit-titre').value = titre;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-prix').value = prix;
    document.getElementById('edit-duree').value = duree;
    document.getElementById('edit-date').value = date ?? '';
    document.getElementById('edit-lieu').value = lieu ?? '';
    document.getElementById('form-edit').action =
        '/UpcycleConnect-PA2526/frontend/public/salaries/formations/' + id + '/update';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>