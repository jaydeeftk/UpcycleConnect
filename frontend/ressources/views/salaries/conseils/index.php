<?php
// ressources/views/salarie/conseils/index.php

$success = $_SESSION['success'] ?? null;
$error_session = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Conseils</h2>
        <p class="text-gray-600">Gérez les conseils publiés sur le site</p>
    </div>
    <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Ajouter un conseil
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
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total conseils</p>
                <p class="text-3xl font-bold"><?= count($conseils) ?></p>
            </div>
            <i class="fas fa-lightbulb text-4xl text-yellow-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Mes conseils</p>
                <p class="text-3xl font-bold text-green-600">
                    <?= count(array_filter($conseils, fn($c) => ($c['id_salaries'] ?? 0) == ($_SESSION['user']['id'] ?? -1))) ?>
                </p>
            </div>
            <i class="fas fa-user-check text-4xl text-green-500"></i>
        </div>
    </div>
</div>

<!-- Tableau des conseils -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date d'ajout</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contenu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Auteur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($conseils)): ?>
            <tr>
                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                    <i class="fas fa-lightbulb text-4xl mb-3 text-gray-300"></i>
                    <p>Aucun conseil pour le moment.</p>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($conseils as $conseil): ?>
            <tr>
                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                    <?= htmlspecialchars($conseil['date_d_ajout'] ?? '') ?>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-gray-800 max-w-md truncate">
                        <?= htmlspecialchars($conseil['contenu'] ?? '') ?>
                    </p>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <?= htmlspecialchars($conseil['auteur'] ?? 'Inconnu') ?>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <?php if (($conseil['id_salaries'] ?? 0) == ($_SESSION['user']['id'] ?? -1)): ?>
                        <button onclick="openEditModal(<?= $conseil['id_conseils'] ?>, `<?= addslashes($conseil['contenu']) ?>`)"
                                class="text-green-600 hover:text-green-800" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/conseils/<?= $conseil['id_conseils'] ?>/delete"
                           onclick="return confirm('Supprimer ce conseil ?')"
                           class="text-red-600 hover:text-red-800" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php else: ?>
                        <span class="text-gray-400 text-xs italic">Non modifiable</span>
                        <?php endif; ?>
                    </div>
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
            <h3 class="text-lg font-bold">Ajouter un conseil</h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/salaries/conseils/store">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contenu du conseil</label>
                <textarea name="contenu" rows="5" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                          placeholder="Rédigez votre conseil..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i>Publier
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal édition -->
<div id="modal-edit" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Modifier le conseil</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" id="form-edit" action="">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contenu du conseil</label>
                <textarea name="contenu" id="edit-contenu" rows="5" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
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
function openEditModal(id, contenu) {
    document.getElementById('edit-contenu').value = contenu;
    document.getElementById('form-edit').action =
        '/UpcycleConnect-PA2526/frontend/public/salaries/conseils/' + id + '/update';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>