<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800"><?= t('adm_finances_title', 'Finances & Statistiques') ?></h2>
        <p class="text-slate-500"><?= t('adm_finances_subtitle', 'Vue d\'ensemble de vos revenus générés') ?></p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider"><?= t('adm_finances_ca_ttc', 'Chiffre d\'Affaires TTC') ?></h3>
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center"><i class="fas fa-euro-sign text-lg"></i></div>
            </div>
            <p class="text-3xl font-bold text-slate-800"><?= number_format($finances['total_ttc'] ?? 0, 2, ',', ' ') ?> €</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider"><?= t('adm_finances_total_ht', 'Total HT') ?></h3>
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center"><i class="fas fa-file-invoice-dollar text-lg"></i></div>
            </div>
            <p class="text-3xl font-bold text-slate-800"><?= number_format($finances['total_ht'] ?? 0, 2, ',', ' ') ?> €</p>
        </div>
    </div>

    <a href="/admin/commissions" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition block">
        <div class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider"><?= t('adm_finances_commissions', 'Commissions générées') ?></h3>
                <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center"><i class="fas fa-hand-holding-usd text-lg"></i></div>
            </div>
            <p class="text-3xl font-bold text-slate-800"><?= number_format($finances['total_commissions'] ?? 0, 2, ',', ' ') ?> €</p>
            <p class="text-xs text-purple-600 mt-2"><?= t('adm_finances_commissions_link', 'Voir le détail') ?> <i class="fas fa-arrow-right ml-1"></i></p>
        </div>
    </a>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider"><?= t('adm_finances_total_factures', 'Total Factures') ?></h3>
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center"><i class="fas fa-receipt text-lg"></i></div>
            </div>
            <p class="text-3xl font-bold text-slate-800"><?= intval($finances['nb_factures'] ?? 0) ?></p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-slate-800"><?= t('adm_finances_split_source', 'Répartition du chiffre d\'affaires') ?></h3>
        <span class="text-sm text-slate-400"><?= number_format($finances['total_ttc'] ?? 0, 2, ',', ' ') ?> € <?= t('adm_finances_total_label', 'au total') ?></span>
    </div>
    <?php
    $src = $finances['ca_par_source'] ?? [];
    $caTotal = array_sum($src) ?: 1;
    $sources = [
        [t('adm_finances_src_abo', 'Abonnements'), (float)($src['abonnements'] ?? 0), '/admin/abonnements', 'fa-id-card',          'text-emerald-500', 'bg-emerald-500'],
        [t('adm_finances_src_pub', 'Publicités'),  (float)($src['publicites'] ?? 0),  '/admin/publicites',  'fa-ad',              'text-blue-500',    'bg-blue-500'],
        [t('adm_finances_src_com', 'Commissions'), (float)($src['commissions'] ?? 0), '/admin/commissions', 'fa-hand-holding-usd','text-purple-500',  'bg-purple-500'],
    ];
    ?>
    <div class="space-y-5">
        <?php foreach ($sources as [$label, $montant, $lien, $icon, $txtColor, $barColor]):
            $pct = round($montant / $caTotal * 100);
        ?>
        <a href="<?= $lien ?>" class="block group">
            <div class="flex items-center justify-between mb-1.5">
                <span class="flex items-center gap-2 text-sm font-medium text-slate-600">
                    <i class="fas <?= $icon ?> <?= $txtColor ?>"></i><?= $label ?>
                    <i class="fas fa-arrow-right text-xs text-slate-300 group-hover:text-slate-500 transition"></i>
                </span>
                <span class="text-sm font-bold text-slate-800"><?= number_format($montant, 2, ',', ' ') ?> € <span class="text-slate-400 font-normal">(<?= $pct ?>%)</span></span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5">
                <div class="<?= $barColor ?> h-2.5 rounded-full" style="width: <?= $pct ?>%"></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mt-6">
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fas fa-rotate-left text-amber-500"></i>
            <?= t('adm_finances_remb_title', 'Demandes de remboursement') ?>
        </h3>
        <?php
        $rembEnAttente = array_filter($remboursements ?? [], fn($r) => ($r['statut'] ?? '') === 'en_attente');
        $nbAttente = count($rembEnAttente);
        ?>
        <?php if ($nbAttente > 0): ?>
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                <i class="fas fa-clock"></i> <?= $nbAttente ?> <?= t('adm_finances_remb_pending', 'en attente') ?>
            </span>
        <?php else: ?>
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                <i class="fas fa-check-circle"></i> <?= t('adm_finances_remb_none_pending', 'Aucun en attente') ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if (empty($remboursements)): ?>
        <p class="text-slate-400 text-sm italic text-center py-8"><?= t('adm_finances_remb_empty', 'Aucune demande de remboursement enregistrée.') ?></p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase pb-3 pr-4"><?= t('adm_finances_remb_col_demandeur', 'Demandeur') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase pb-3 pr-4"><?= t('adm_finances_remb_col_montant', 'Montant') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase pb-3 pr-4"><?= t('adm_finances_remb_col_motif', 'Motif') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase pb-3 pr-4"><?= t('adm_finances_remb_col_date', 'Date demande') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase pb-3"><?= t('adm_finances_remb_col_statut', 'Statut') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($remboursements as $r):
                    $st  = $r['statut'] ?? '';
                    $cls = match ($st) {
                        'remboursee' => 'bg-green-100 text-green-700',
                        'en_attente' => 'bg-yellow-100 text-yellow-700',
                        'approuvee'  => 'bg-blue-100 text-blue-700',
                        'refusee'    => 'bg-gray-100 text-gray-600',
                        'echouee'    => 'bg-red-100 text-red-700',
                        default      => 'bg-gray-100 text-gray-600',
                    };
                ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 pr-4 text-slate-800 font-medium"><?= htmlspecialchars(trim(($r['prenom'] ?? '') . ' ' . ($r['nom'] ?? '')) ?: '—') ?></td>
                    <td class="py-3 pr-4 font-semibold"><?= htmlspecialchars(formatPrix($r['montant'] ?? 0)) ?></td>
                    <td class="py-3 pr-4 text-slate-600"><?= htmlspecialchars($r['motif'] ?? '—') ?: '—' ?></td>
                    <td class="py-3 pr-4 text-slate-500 whitespace-nowrap"><?= formatDate($r['date_demande'] ?? '', true) ?></td>
                    <td class="py-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $cls ?>"><?= htmlspecialchars(formatStatut($st)) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>