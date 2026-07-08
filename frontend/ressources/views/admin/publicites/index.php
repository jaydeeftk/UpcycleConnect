<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold"><?= t('adm_pub_title', 'Campagnes publicitaires') ?></h2>
        <p class="text-gray-600"><?= t('adm_pub_subtitle', 'Campagnes publicitaires souscrites par les professionnels') ?></p>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_pub_col_pro', 'Professionnel') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_pub_col_company', 'Entreprise') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_pub_col_type', 'Type') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_pub_col_price', 'Prix') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_pub_col_period', 'Période') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('adm_pub_col_status', 'Statut') ?></th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"><?= t('adm_pub_col_actions', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($publicites)): ?>
                <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500"><?= t('adm_pub_empty', 'Aucune campagne publicitaire') ?></td></tr>
            <?php else: ?>
                <?php foreach ($publicites as $p): ?>
                <tr>
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($p['professionnel'] ?? '') ?: '—' ?></td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($p['entreprise'] ?? '') ?: '—' ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"><?= htmlspecialchars($p['type'] ?? '') ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= formatPrix($p['prix'] ?? 0) ?></td>
                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap"><?= formatDate($p['date_debut'] ?? '') ?> → <?= formatDate($p['date_fin'] ?? '') ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase" style="background-color: color-mix(in srgb, <?= statutCouleur($p['statut'] ?? '') ?> 15%, white); color: <?= statutCouleur($p['statut'] ?? '') ?>;">
                            <?= formatStatut($p['statut'] ?? '') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <?php if (!empty($p['annulable'])): ?>
                        <form method="POST" action="/admin/publicites/<?= urlencode($p['id'] ?? '') ?>/annuler" onsubmit="return confirm('<?= t('adm_pub_confirm_cancel', 'Annuler cette campagne ?') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                                <i class="fas fa-ban mr-1"></i><?= t('adm_pub_cancel', 'Annuler') ?>
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-gray-300">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
