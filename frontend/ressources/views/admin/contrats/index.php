<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold"><?= t('adm_contrats_title', 'Contrats & Abonnements') ?></h2>
        <p class="text-gray-600"><?= t('adm_contrats_subtitle', 'Gérez les contrats des professionnels') ?></p>
    </div>
    <button onclick="document.getElementById('modal-contrat').classList.remove('hidden')"
        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i><?= t('adm_contrats_new', 'Nouveau contrat') ?>
    </button>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div id="modal-contrat" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-bold mb-4"><?= t('adm_contrats_new', 'Nouveau contrat') ?></h3>
        <form method="POST" action="/admin/contrats/store">
        <?= csrf_field() ?>
            <input type="hidden" name="date_signature" value="<?= date('Y-m-d') ?>">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1"><?= t('adm_col_type', 'Type') ?></label>
                    <select name="type" class="w-full border rounded-lg px-4 py-2">
                        <option value="prestation"><?= t('adm_contrats_type_prestation', 'Prestation') ?></option>
                        <option value="abonnement"><?= t('adm_contrats_type_abonnement', 'Abonnement') ?></option>
                        <option value="partenariat"><?= t('adm_contrats_type_partenariat', 'Partenariat') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1"><?= t('adm_contrats_label_id_pro', 'ID Professionnel') ?></label>
                    <input type="number" name="id_professionnels" required class="w-full border rounded-lg px-4 py-2" placeholder="<?= t('adm_contrats_id_pro_ph', 'Ex: 1') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1"><?= t('adm_contrats_label_date_start', 'Date début') ?></label>
                    <input type="date" name="date_debut" min="<?= dateProgrammationMin(false) ?>" max="<?= dateProgrammationMax(false) ?>" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1"><?= t('adm_contrats_label_date_end', 'Date fin') ?></label>
                    <input type="date" name="date_fin" min="<?= dateProgrammationMin(false) ?>" max="<?= dateProgrammationMax(false) ?>" required class="w-full border rounded-lg px-4 py-2">
                </div>
            </div>
            <div class="flex gap-4 mt-6">
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600"><?= t('adm_btn_create', 'Créer') ?></button>
                <button type="button" onclick="document.getElementById('modal-contrat').classList.add('hidden')"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300"><?= t('adm_btn_cancel', 'Annuler') ?></button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_contrats_col_pro', 'Professionnel') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_contrats_col_company', 'Entreprise') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_col_type', 'Type') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_contrats_col_start', 'Début') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_contrats_col_end', 'Fin') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_col_actions', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($contrats)): ?>
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500"><?= t('adm_contrats_empty', 'Aucun contrat') ?></td></tr>
            <?php else: ?>
                <?php foreach ($contrats as $c): ?>
                <tr>
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? '')) ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['nom_entreprise'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"><?= htmlspecialchars($c['type'] ?? '') ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= formatDate($c['date_debut'] ?? '') ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= formatDate($c['date_fin'] ?? '') ?></td>
                    <td class="px-6 py-4">
                        <form method="POST" action="/admin/contrats/<?= $c['id'] ?>/supprimer" class="inline"
                            onsubmit="return ucConfirm(this, '<?= t('adm_contrats_confirm_delete', 'Supprimer ce contrat ?') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-red-600 hover:text-red-800" title="<?= t('adm_btn_delete', 'Supprimer') ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>