<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Box #<?= htmlspecialchars($conteneur['id']) ?></h2>
        <p class="text-slate-500"><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($conteneur['localisation'] ?? '') ?></p>
    </div>
    <a href="/admin/conteneurs" class="text-sm px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors text-slate-600">
        <i class="fas fa-arrow-left mr-2"></i><?= t('adm_btn_back', 'Retour') ?>
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-xs uppercase font-bold text-slate-400 mb-2"><?= t('adm_col_status', 'Statut') ?></p>
        <?php $sc = statutCouleur($conteneur['statut'] ?? ''); ?>
        <span class="px-3 py-1 rounded-full text-sm font-semibold" style="background:<?= $sc ?>22;color:<?= $sc ?>"><?= formatStatut($conteneur['statut'] ?? '') ?></span>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-xs uppercase font-bold text-slate-400 mb-2"><?= t('adm_conteneurs_max_capacity', 'Capacité max') ?></p>
        <p class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($conteneur['capacite'] ?? 0) ?> <span class="text-sm font-normal text-slate-400">kg</span></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-xs uppercase font-bold text-slate-400 mb-2"><?= t('adm_conteneurs_dimensions', 'Dimensions') ?></p>
        <p class="text-2xl font-bold text-slate-800">
            <?= htmlspecialchars((string)($conteneur['hauteur'] ?? 0)) ?> × <?= htmlspecialchars((string)($conteneur['largeur'] ?? 0)) ?> × <?= htmlspecialchars((string)($conteneur['longueur'] ?? 0)) ?>
            <span class="text-sm font-normal text-slate-400">cm</span>
        </p>
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
