<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800"><?= t('adm_conteneurs_label', 'Conteneur') ?> #<?= htmlspecialchars($conteneur['id']) ?></h2>
        <p class="text-slate-500"><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($conteneur['localisation'] ?? '') ?></p>
    </div>
    <a href="/admin/conteneurs" class="text-sm px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors text-slate-600">
        <i class="fas fa-arrow-left mr-2"></i><?= t('adm_btn_back', 'Retour') ?>
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-xs uppercase font-bold text-slate-400 mb-2"><?= t('adm_col_status', 'Statut') ?></p>
        <?php $sc = statutCouleur($conteneur['statut'] ?? ''); ?>
        <span class="px-3 py-1 rounded-full text-sm font-semibold" style="background:<?= $sc ?>22;color:<?= $sc ?>"><?= formatStatut($conteneur['statut'] ?? '') ?></span>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-xs uppercase font-bold text-slate-400 mb-2"><?= t('adm_conteneurs_slots_label', 'Casiers') ?></p>
        <p class="text-2xl font-bold text-slate-800"><?= (int)($conteneur['nb_standard'] ?? 0) + (int)($conteneur['nb_encombrant'] ?? 0) ?></p>
        <p class="text-xs text-slate-400 mt-1"><?= (int)($conteneur['nb_standard'] ?? 0) ?> <?= t('adm_conteneurs_std', 'standard') ?> · <?= (int)($conteneur['nb_encombrant'] ?? 0) ?> <?= t('adm_conteneurs_enc', 'encombrant') ?> · <?= (int)($conteneur['capacite_totale'] ?? 0) ?> <?= t('adm_conteneurs_obj_max', 'objets max') ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <?php $fr = (int)($conteneur['fill_rate'] ?? 0); $bar = $fr >= 90 ? '#ef4444' : ($fr >= 70 ? '#f59e0b' : '#22c55e'); ?>
        <div class="flex justify-between text-xs font-semibold mb-1">
            <span class="text-slate-500"><?= t('adm_conteneurs_fill_rate', 'Taux de remplissage') ?></span>
            <span class="text-slate-700"><?= $fr ?>%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2.5">
            <div class="h-2.5 rounded-full" style="width:<?= $fr ?>%;background:<?= $bar ?>"></div>
        </div>
        <p class="text-xs text-slate-400 mt-2"><?= (int)($conteneur['occupation'] ?? 0) ?> <?= t('adm_conteneurs_objects_stock', 'objet(s) en stock') ?> · <?= (int)($conteneur['nb_demandes'] ?? 0) ?> <?= t('adm_conteneurs_deposits_validated', 'dépôt(s) validé(s)') ?></p>
    </div>
</div>

<?php if (!empty($conteneur['boxes'])): ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="font-bold text-slate-800 flex items-center gap-2">
            <i class="fas fa-th-large text-emerald-500"></i>
            <?= t('adm_conteneurs_boxes_title', 'Casiers (UpcycleBox)') ?>
            <span class="ml-2 px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs font-semibold"><?= count($conteneur['boxes']) ?></span>
        </h3>
        <p class="text-xs text-slate-400"><?= t('adm_box_dimensions_hint', 'Cliquez sur "Modifier" pour renseigner les dimensions d\'un casier.') ?></p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-semibold"><?= t('adm_box_col_ref', 'Référence') ?></th>
                    <th class="p-4 font-semibold"><?= t('adm_box_col_taille', 'Taille') ?></th>
                    <th class="p-4 font-semibold"><?= t('adm_col_status', 'Statut') ?></th>
                    <th class="p-4 font-semibold"><?= t('adm_box_col_hauteur', 'Hauteur (cm)') ?></th>
                    <th class="p-4 font-semibold"><?= t('adm_box_col_largeur', 'Largeur (cm)') ?></th>
                    <th class="p-4 font-semibold"><?= t('adm_box_col_longueur', 'Longueur (cm)') ?></th>
                    <th class="p-4 font-semibold text-right"><?= t('adm_col_actions', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($conteneur['boxes'] as $box):
                    $bSc = statutCouleur($box['statut'] ?? '');
                ?>
                <tr class="hover:bg-slate-50 transition-colors" id="box-row-<?= (int)$box['id'] ?>">
                    <td class="p-4 font-mono text-xs text-slate-600"><?= htmlspecialchars($box['reference'] ?? '') ?></td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= ($box['taille'] ?? '') === 'encombrant' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600' ?>">
                            <?= ($box['taille'] ?? '') === 'encombrant' ? t('adm_box_taille_large', 'Encombrant') : t('adm_box_taille_std', 'Standard') ?>
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="background:<?= $bSc ?>22;color:<?= $bSc ?>">
                            <?= formatStatut($box['statut'] ?? '') ?>
                        </span>
                    </td>
                    <td class="p-4 text-slate-700" id="box-h-<?= (int)$box['id'] ?>"><?= isset($box['hauteur_cm']) ? number_format((float)$box['hauteur_cm'], 1) : '—' ?></td>
                    <td class="p-4 text-slate-700" id="box-l-<?= (int)$box['id'] ?>"><?= isset($box['largeur_cm']) ? number_format((float)$box['largeur_cm'], 1) : '—' ?></td>
                    <td class="p-4 text-slate-700" id="box-lo-<?= (int)$box['id'] ?>"><?= isset($box['longueur_cm']) ? number_format((float)$box['longueur_cm'], 1) : '—' ?></td>
                    <td class="p-4 text-right">
                        <button onclick='openBoxDimModal(<?= htmlspecialchars(json_encode($box), ENT_QUOTES) ?>, <?= (int)$conteneur['id'] ?>)'
                                class="text-emerald-600 hover:text-emerald-800 text-xs font-semibold transition-colors">
                            <i class="fas fa-ruler-combined mr-1"></i><?= t('adm_box_btn_dimensions', 'Dimensions') ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div id="boxDimModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-base font-bold text-slate-800">
                <i class="fas fa-ruler-combined mr-2 text-emerald-500"></i>
                <?= t('adm_box_modal_title', 'Dimensions du casier') ?>
                <span id="boxDimRef" class="ml-2 font-mono text-xs text-slate-400"></span>
            </h3>
            <button onclick="document.getElementById('boxDimModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form id="boxDimForm" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id_conteneur" id="boxDimIdConteneur">
            <div class="p-6 space-y-4">
                <p class="text-xs text-slate-400"><?= t('adm_box_dimensions_note', 'Laissez vide si les dimensions ne sont pas encore connues.') ?></p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1"><?= t('adm_box_col_hauteur', 'Hauteur') ?> (cm)</label>
                        <input type="number" name="hauteur_cm" id="boxDimH" min="0" step="0.1" placeholder="—"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1"><?= t('adm_box_col_largeur', 'Largeur') ?> (cm)</label>
                        <input type="number" name="largeur_cm" id="boxDimL" min="0" step="0.1" placeholder="—"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1"><?= t('adm_box_col_longueur', 'Longueur') ?> (cm)</label>
                        <input type="number" name="longueur_cm" id="boxDimLo" min="0" step="0.1" placeholder="—"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                <button type="button" onclick="document.getElementById('boxDimModal').classList.add('hidden')"
                        class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg text-sm"><?= t('adm_btn_cancel', 'Annuler') ?></button>
                <button type="submit" class="px-4 py-2 bg-emerald-500 text-white font-medium rounded-lg hover:bg-emerald-600 transition-colors text-sm">
                    <?= t('adm_btn_save', 'Enregistrer') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openBoxDimModal(box, idConteneur) {
    document.getElementById('boxDimRef').textContent = box.reference || '';
    document.getElementById('boxDimForm').action = '/admin/box/' + box.id + '/dimensions';
    document.getElementById('boxDimIdConteneur').value = idConteneur;
    document.getElementById('boxDimH').value  = box.hauteur_cm  != null ? box.hauteur_cm  : '';
    document.getElementById('boxDimL').value  = box.largeur_cm  != null ? box.largeur_cm  : '';
    document.getElementById('boxDimLo').value = box.longueur_cm != null ? box.longueur_cm : '';
    document.getElementById('boxDimModal').classList.remove('hidden');
}
</script>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200">
        <h3 class="font-bold text-slate-800"><?= t('adm_conteneurs_deposits_in_box', 'Dépôts dans cette box') ?></h3>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold"><?= t('adm_conteneurs_col_particulier', 'Particulier') ?></th>
                <th class="p-4 font-semibold"><?= t('adm_col_object', 'Objet') ?></th>
                <th class="p-4 font-semibold"><?= t('adm_col_status', 'Statut') ?></th>
                <th class="p-4 font-semibold"><?= t('adm_col_code', 'Code') ?></th>
                <th class="p-4 font-semibold"><?= t('adm_col_date', 'Date') ?></th>
                <th class="p-4 font-semibold text-right"><?= t('adm_col_actions', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($demandes)): ?>
                <tr><td colspan="6" class="p-8 text-center text-slate-400 italic"><?= t('adm_conteneurs_empty_box', 'Aucun dépôt dans cette box.') ?></td></tr>
            <?php else: foreach ($demandes as $d):
                $st = strtolower($d['statut'] ?? 'en_attente');
                $col = statutCouleur($st);
            ?>
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4">
                    <div class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '')) ?></div>
                    <div class="text-xs text-slate-400"><?= htmlspecialchars($d['email'] ?? '') ?></div>
                </td>
                <td class="p-4 text-sm text-slate-600"><?= htmlspecialchars($d['type_objet'] ?? '-') ?></td>
                <td class="p-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:<?= $col ?>22;color:<?= $col ?>"><?= formatStatut($d['statut'] ?? '') ?></span></td>
                <td class="p-4 font-mono text-xs text-slate-500"><?= !empty($d['code_acces']) ? htmlspecialchars($d['code_acces']) : '—' ?></td>
                <td class="p-4 text-xs text-slate-400"><?= formatDate($d['date'] ?? '') ?></td>
                <td class="p-4 text-right">
                    <?php if ($st === 'en_attente'): ?>
                    <div class="flex justify-end gap-2">
                        <form method="POST" action="/admin/demandes/valider/<?= $d['id'] ?>">
                        <?= csrf_field() ?>
                            <button type="submit" class="w-8 h-8 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-600 hover:text-white transition-colors" title="<?= t('adm_btn_validate', 'Valider') ?>"><i class="fas fa-check text-xs"></i></button>
                        </form>
                        <form method="POST" action="/admin/demandes/refuser/<?= $d['id'] ?>">
                        <?= csrf_field() ?>
                            <button type="submit" class="w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition-colors" title="<?= t('adm_btn_refuse', 'Refuser') ?>"><i class="fas fa-times text-xs"></i></button>
                        </form>
                    </div>
                    <?php else: ?>
                    <span class="text-xs text-slate-400 italic"><?= formatStatut($d['statut'] ?? '') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>
