<div class="mb-6">
    <a href="/admin/finances" class="text-sm text-slate-500 hover:text-slate-700"><i class="fas fa-arrow-left mr-1"></i> <?= t('adm_comm_back', 'Retour aux finances') ?></a>
    <h1 class="text-xl font-bold text-slate-800 mt-2"><?= t('adm_comm_title', 'Détail des commissions') ?></h1>
    <p class="text-sm text-slate-500"><?= t('adm_comm_subtitle', 'Répartition entre UpcycleConnect et les vendeurs/prestataires.') ?></p>
</div>

<?php
$totalCommission = 0;
$totalVendeur = 0;
foreach ($commissions as $c) {
    $totalCommission += (float)($c['montant_commission'] ?? 0);
    $totalVendeur += (float)($c['montant_vendeur'] ?? 0);
}
?>

<div class="grid md:grid-cols-2 gap-4 mb-6">
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
        <p class="text-xs text-purple-600 uppercase font-bold"><?= t('adm_comm_total_platform', 'Total reversé à UpcycleConnect') ?></p>
        <p class="text-2xl font-bold text-purple-800"><?= number_format($totalCommission, 2, ',', ' ') ?> €</p>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5">
        <p class="text-xs text-emerald-600 uppercase font-bold"><?= t('adm_comm_total_sellers', 'Total reversé aux vendeurs/prestataires') ?></p>
        <p class="text-2xl font-bold text-emerald-800"><?= number_format($totalVendeur, 2, ',', ' ') ?> €</p>
    </div>
</div>

<?php if (empty($commissions)): ?>
    <div class="bg-white rounded-xl border border-slate-200 text-center py-16 text-slate-400">
        <i class="fas fa-hand-holding-usd text-4xl mb-3 block"></i>
        <p><?= t('adm_comm_empty', 'Aucune commission générée pour le moment.') ?></p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left"><?= t('adm_comm_col_date', 'Date') ?></th>
                    <th class="px-4 py-3 text-left"><?= t('adm_comm_col_type', 'Type') ?></th>
                    <th class="px-4 py-3 text-left"><?= t('adm_comm_col_desc', 'Objet') ?></th>
                    <th class="px-4 py-3 text-left"><?= t('adm_comm_col_seller', 'Vendeur / prestataire') ?></th>
                    <th class="px-4 py-3 text-right"><?= t('adm_comm_col_total', 'Prix total') ?></th>
                    <th class="px-4 py-3 text-right"><?= t('adm_comm_col_rate', 'Taux') ?></th>
                    <th class="px-4 py-3 text-right"><?= t('adm_comm_col_commission', 'Commission') ?></th>
                    <th class="px-4 py-3 text-right"><?= t('adm_comm_col_seller_amount', 'Reversé au vendeur') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($commissions as $c): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500"><?= htmlspecialchars($c['date'] ?? '') ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= ($c['type'] ?? '') === 'annonce' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' ?>">
                                <?= ($c['type'] ?? '') === 'annonce' ? t('adm_comm_type_annonce', 'Annonce') : t('adm_comm_type_devis', 'Prestation') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($c['description'] ?? '') ?></td>
                        <td class="px-4 py-3 text-slate-500"><?= htmlspecialchars($c['nom_vendeur'] ?? '—') ?></td>
                        <td class="px-4 py-3 text-right text-slate-700"><?= number_format((float)($c['prix_total'] ?? 0), 2, ',', ' ') ?> €</td>
                        <td class="px-4 py-3 text-right text-slate-500"><?= number_format((float)($c['taux'] ?? 0), 2, ',', ' ') ?>%</td>
                        <td class="px-4 py-3 text-right font-semibold text-purple-700"><?= number_format((float)($c['montant_commission'] ?? 0), 2, ',', ' ') ?> €</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-700"><?= number_format((float)($c['montant_vendeur'] ?? 0), 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
