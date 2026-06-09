<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold"><?= t('adm_categories_title', 'Catégories') ?></h2>
        <p class="text-gray-600"><?= t('adm_categories_subtitle', 'Gérez les catégories de prestations') ?></p>
    </div>
</div>

<div class="mb-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-4"><?= t('adm_categories_add_title', 'Ajouter une catégorie') ?></h3>
    <form method="POST" action="/admin/categories/store" class="flex gap-4">
        <input type="text" name="nom" placeholder="<?= t('adm_categories_name_ph', 'Nom de la catégorie') ?>" required
            class="flex-1 border rounded-lg px-4 py-2">
        <input type="text" name="description" placeholder="<?= t('adm_categories_desc_ph', 'Description (optionnel)') ?>"
            class="flex-1 border rounded-lg px-4 py-2">
        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
            <i class="fas fa-plus mr-2"></i><?= t('adm_categories_add', 'Ajouter') ?>
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_col_id', 'ID') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_col_name', 'Nom') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_col_description', 'Description') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_col_actions', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500"><?= t('adm_categories_empty', 'Aucune catégorie') ?></td></tr>
            <?php else: ?>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td class="px-6 py-4 text-gray-500">#<?= $c['id'] ?></td>
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($c['nom'] ?? '') ?></td>
                    <td class="px-6 py-4 text-gray-600 text-sm"><?= htmlspecialchars($c['description'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <a href="/admin/categories/<?= $c['id'] ?>/delete"
                            onclick="return ucConfirm(this, '<?= t('adm_categories_confirm_delete', 'Supprimer cette catégorie ?') ?>')"
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