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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6"><?= t('adm_finances_ca_evolution', 'Évolution du CA (12 derniers mois)') ?></h3>
        <canvas id="caChart" height="120"></canvas>
    </div>
    <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6"><?= t('adm_finances_split_status', 'Répartition par Statut') ?></h3>
        <canvas id="statutChart" height="250"></canvas>
    </div>
</div>

<!-- Section remboursements (lecture seule) -->
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const finances = <?= json_encode($finances ?? []) ?>;

        const caData = finances.ca_par_mois || [];
        const labelsCA = caData.map(item => item.mois);
        const dataCA = caData.map(item => item.ca);

        new Chart(document.getElementById('caChart'), {
            type: 'line',
            data: {
                labels: labelsCA.length ? labelsCA : ['<?= t('adm_finances_js_no_data', 'Aucune donnée') ?>'],
                datasets: [{
                    label: '<?= t('adm_finances_js_ca_label', 'Chiffre d\'Affaires TTC (€)') ?>',
                    data: dataCA.length ? dataCA : [0],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#10b981',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });

        const statuts = finances.statuts || {};
        const labelsStatut = Object.keys(statuts);
        const dataStatut = Object.values(statuts);

        new Chart(document.getElementById('statutChart'), {
            type: 'doughnut',
            data: {
                labels: labelsStatut.length ? labelsStatut : ['<?= t('adm_finances_js_no_facture', 'Aucune facture') ?>'],
                datasets: [{
                    data: dataStatut.length ? dataStatut : [1],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                }
            }
        });
    });
</script>