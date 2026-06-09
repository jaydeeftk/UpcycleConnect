<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800"><?= t('adm_conseils_title', 'Modération des Conseils') ?></h2>
        <p class="text-slate-500"><?= t('adm_conseils_subtitle', 'Gestion des articles et astuces d\'upcycling') ?></p>
    </div>
    <input type="text" id="filter-conseils" onkeyup="filterTable('filter-conseils','table-conseils')"
        placeholder="<?= t('adm_filter_titre_auteur', 'Filtrer par titre, auteur...') ?>"
        class="border border-slate-200 rounded-lg px-4 py-2 text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-emerald-300">
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table id="table-conseils" class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold"><?= t('adm_conseils_col_article', 'Article & Contenu') ?></th>
                <th class="p-4 font-semibold"><?= t('adm_col_author', 'Auteur') ?></th>
                <th class="p-4 font-semibold"><?= t('adm_conseils_col_categorie', 'Catégorie') ?></th>
                <th class="p-4 font-semibold"><?= t('adm_col_status', 'Statut') ?></th>
                <th class="p-4 font-semibold text-right"><?= t('adm_col_actions', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($conseils)) { ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400 italic"><?= t('adm_conseils_empty', 'Aucun conseil en attente.') ?></td>
                </tr>
            <?php } else { ?>
                <?php foreach ($conseils as $conseil) {
                    $statut = strtolower($conseil['statut'] ?? 'en_attente');
                    $statutBadge = match($statut) {
                        'valide', 'validé' => ['bg-emerald-100 text-emerald-700 border-emerald-200', t('adm_conseils_status_validated', 'Validé')],
                        'refuse', 'refusé' => ['bg-rose-100 text-rose-700 border-rose-200', t('adm_conseils_status_refused', 'Refusé')],
                        default => ['bg-amber-100 text-amber-700 border-amber-200', t('adm_conseils_status_pending', 'En attente')],
                    };
                    $role = $conseil['role'] ?? 'salarie';
                    $roleLabel = match(strtolower($role)) {
                        'admin' => t('adm_role_admin', 'Administrateur'),
                        'salarie' => t('adm_role_salarie', 'Salarié'),
                        'particulier' => t('adm_role_particulier', 'Particulier'),
                        'professionnel' => t('adm_role_professionnel', 'Professionnel'),
                        default => ucfirst($role),
                    };
                ?>
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="p-4">
                        <p class="font-bold text-slate-800"><?= htmlspecialchars($conseil['titre'] ?? t('adm_conseils_default_title', 'Sans titre')) ?></p>
                        <p class="text-sm text-slate-500 truncate max-w-md"><?= htmlspecialchars(substr($conseil['contenu'] ?? '', 0, 100)) ?></p>
                        <p class="text-xs text-slate-400 mt-1"><i class="far fa-clock mr-1"></i><?= formatDate($conseil['date'] ?? '') ?></p>
                    </td>
                    <td class="p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold mr-2 text-xs">
                                <?= strtoupper(substr($conseil['auteur'] ?? 'S', 0, 1)) ?>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($conseil['auteur'] ?? t('adm_conseils_default_author', 'Salarié')) ?></span>
                                <div class="text-xs text-slate-400"><?= $roleLabel ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-medium border border-slate-200">
                            <?= htmlspecialchars(ucfirst($conseil['categorie'] ?? t('adm_conseils_default_categorie', 'Général'))) ?>
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $statutBadge[0] ?> uppercase">
                            <?= $statutBadge[1] ?>
                        </span>
                    </td>
                    <td class="p-4 text-right space-x-2">
                        <form method="POST" action="/admin/conseils/<?= $conseil['id'] ?>/valider" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-500 hover:bg-emerald-500 hover:text-white transition-colors" title="<?= t('adm_btn_validate', 'Valider') ?>">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form method="POST" action="/admin/conseils/<?= $conseil['id'] ?>/rejeter" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-colors" title="<?= t('adm_btn_reject', 'Rejeter') ?>">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <form method="POST" action="/admin/conseils/<?= $conseil['id'] ?>/delete" class="inline"
                           onsubmit="return ucConfirm(this, '<?= t('adm_conseils_confirm_delete', 'Supprimer définitivement ce conseil ?') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors" title="<?= t('adm_btn_delete', 'Supprimer') ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
