<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold"><?= t('adm_abo_title', 'Abonnements Pro') ?></h2>
        <p class="text-gray-600"><?= t('adm_abo_subtitle', 'Abonnements Premium souscrits par les professionnels') ?></p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_abo_col_pro', 'Professionnel') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_abo_col_company', 'Entreprise') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_abo_col_type', 'Type') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_abo_col_price', 'Prix') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_abo_col_start', 'Début') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_abo_col_end', 'Fin') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_abo_col_ads', 'Annonces gratuites') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_abo_col_status', 'Statut') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($abonnements)): ?>
                <tr><td colspan="8" class="px-6 py-4 text-center text-gray-500"><?= t('adm_abo_empty', 'Aucun abonnement') ?></td></tr>
            <?php else: ?>
                <?php foreach ($abonnements as $a): ?>
                <tr>
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars(trim(($a['prenom'] ?? '') . ' ' . ($a['nom'] ?? ''))) ?: '—' ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($a['nom_entreprise'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"><?= htmlspecialchars($a['type'] ?? '') ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= formatPrix($a['prix'] ?? 0) ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= formatDate($a['date_debut'] ?? '') ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= formatDate($a['date_fin'] ?? '') ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= (int)($a['annonces_gratuites_restantes'] ?? 0) ?> / <?= (int)($a['annonces_gratuites_incluses'] ?? 0) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase" style="background-color: color-mix(in srgb, <?= statutCouleur($a['statut'] ?? '') ?> 15%, white); color: <?= statutCouleur($a['statut'] ?? '') ?>;">
                            <?= formatStatut($a['statut'] ?? '') ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
