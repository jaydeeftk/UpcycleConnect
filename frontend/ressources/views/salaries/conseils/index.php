<?php

$success = $_SESSION['success'] ?? null;
$error_session = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold"><?= t('sal_nav_conseils', 'Conseils') ?></h2>
        <p class="text-gray-600"><?= t('sal_conseils_subtitle', 'Gérez les conseils publiés sur le site') ?></p>
    </div>
    <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i><?= t('sal_conseils_add', 'Ajouter un conseil') ?>
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

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm"><?= t('sal_conseils_total', 'Total conseils') ?></p>
                <p class="text-3xl font-bold"><?= count($conseils) ?></p>
            </div>
            <i class="fas fa-lightbulb text-4xl text-yellow-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm"><?= t('sal_conseils_mine', 'Mes conseils') ?></p>
                <p class="text-3xl font-bold text-green-600">
                    <?= count($conseils) ?>
                </p>
            </div>
            <i class="fas fa-user-check text-4xl text-green-500"></i>
        </div>
    </div>
</div>

<!  Tableau des conseils  >
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_date_ajout', 'Date d\'ajout') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_contenu', 'Contenu') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_auteur', 'Auteur') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_actions', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($conseils)): ?>
            <tr>
                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                    <i class="fas fa-lightbulb text-4xl mb-3 text-gray-300"></i>
                    <p><?= t('sal_conseils_empty', 'Aucun conseil pour le moment.') ?></p>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($conseils as $conseil): ?>
            <tr>
                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                    <?= formatDate($conseil['date'] ?? '') ?>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-gray-800 max-w-md truncate">
                        <?= htmlspecialchars($conseil['contenu'] ?? '') ?>
                    </p>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <?= htmlspecialchars(trim(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? ''))) ?>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button onclick="openEditModal(<?= (int)($conseil['id'] ?? 0) ?>, <?= json_encode((string)($conseil['contenu'] ?? ''), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) ?>, <?= json_encode((string)($conseil['titre'] ?? ''), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) ?>, <?= json_encode((string)($conseil['categorie'] ?? ''), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) ?>, <?= json_encode((string)($conseil['tags'] ?? ''), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) ?>)"
                                class="text-green-600 hover:text-green-800" title="<?= t('sal_action_edit', 'Modifier') ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="/salaries/conseils/<?= $conseil['id'] ?>/delete" class="inline"
                           onsubmit="return ucConfirm(this, '<?= t('sal_conseils_delete_confirm', 'Supprimer ce conseil ?') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-red-600 hover:text-red-800" title="<?= t('sal_action_delete', 'Supprimer') ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!  Modal ajout  >
<div id="modal-add" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('sal_conseils_add', 'Ajouter un conseil') ?></h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="/salaries/conseils/store">
        <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= t('sal_field_conseil_contenu', 'Contenu du conseil') ?></label>
                <textarea name="contenu" rows="5" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                          placeholder="<?= t('sal_ph_conseil_contenu', 'Rédigez votre conseil...') ?>"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <?= t('sal_cancel', 'Annuler') ?>
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i><?= t('sal_publish', 'Publier') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!  Modal édition  >
<div id="modal-edit" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('sal_conseils_edit', 'Modifier le conseil') ?></h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" id="form-edit" action="">
        <?= csrf_field() ?>
            <input type="hidden" name="titre" id="edit-titre">
            <input type="hidden" name="categorie" id="edit-categorie">
            <input type="hidden" name="tags" id="edit-tags">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= t('sal_field_conseil_contenu', 'Contenu du conseil') ?></label>
                <textarea name="contenu" id="edit-contenu" rows="5" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <?= t('sal_cancel', 'Annuler') ?>
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i><?= t('sal_save', 'Enregistrer') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, contenu, titre, categorie, tags) {
    document.getElementById('edit-contenu').value = contenu;
    document.getElementById('edit-titre').value = titre || '';
    document.getElementById('edit-categorie').value = categorie || '';
    document.getElementById('edit-tags').value = tags || '';
    document.getElementById('form-edit').action = '/salaries/conseils/' + id + '/update';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>