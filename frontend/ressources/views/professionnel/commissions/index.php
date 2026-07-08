<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_comm_page_title', 'Mes commissions') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_comm_heading', 'Mes commissions') ?></h2>
            <p class="text-gray-600 text-sm"><?= t('pro_comm_subtitle', "Détail de ce qu'UpcycleConnect prélève sur vos ventes et prestations.") ?></p>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-4xl mx-auto space-y-6">

            <?php if ($estPremium): ?>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-center gap-3">
                    <i class="fas fa-crown text-amber-500"></i>
                    <p class="text-sm text-amber-800"><?= t('pro_comm_rate_premium', 'Grâce à votre abonnement Premium, votre commission sur les ventes d\'annonces est réduite à 7% (au lieu de 10%).') ?></p>
                </div>
            <?php else: ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between gap-3">
                    <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-2"></i><?= t('pro_comm_rate_standard', "Taux standard : 10% sur les ventes d'annonces. Passez au Premium pour le réduire à 7%.") ?></p>
                    <a href="/professionnel/abonnement" class="text-xs font-semibold text-blue-700 hover:underline whitespace-nowrap"><?= t('pro_comm_rate_cta', 'En savoir plus') ?></a>
                </div>
            <?php endif; ?>

            <?php
            $totalCommission = 0; $totalNet = 0;
            foreach ($commissions as $c) {
                $totalCommission += (float)($c['montant_commission'] ?? 0);
                $totalNet += (float)($c['montant_vendeur'] ?? 0);
            }
            ?>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-xs text-gray-500 uppercase font-bold"><?= t('pro_comm_total_platform', 'Reversé à UpcycleConnect') ?></p>
                    <p class="text-2xl font-bold text-orange-600"><?= number_format($totalCommission, 2, ',', ' ') ?> €</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-xs text-gray-500 uppercase font-bold"><?= t('pro_comm_total_net', 'Vos revenus nets') ?></p>
                    <p class="text-2xl font-bold text-emerald-600"><?= number_format($totalNet, 2, ',', ' ') ?> €</p>
                </div>
            </div>

            <?php if (empty($commissions)): ?>
                <div class="bg-white rounded-lg shadow text-center py-16 text-gray-400">
                    <i class="fas fa-hand-holding-usd text-4xl mb-3 block"></i>
                    <p><?= t('pro_comm_empty', "Aucune commission pour l'instant.") ?></p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left"><?= t('pro_comm_col_date', 'Date') ?></th>
                                <th class="px-4 py-3 text-left"><?= t('pro_comm_col_type', 'Type') ?></th>
                                <th class="px-4 py-3 text-left"><?= t('pro_comm_col_desc', 'Objet') ?></th>
                                <th class="px-4 py-3 text-right"><?= t('pro_comm_col_total', 'Prix total') ?></th>
                                <th class="px-4 py-3 text-right"><?= t('pro_comm_col_commission', 'Commission') ?></th>
                                <th class="px-4 py-3 text-right"><?= t('pro_comm_col_net', 'Votre part') ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($commissions as $c): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars($c['date'] ?? '') ?></td>
                                    <td class="px-4 py-3">
                                        <?php
                                        $typeC = $c['type'] ?? '';
                                        $typeCls = ['annonce' => 'bg-blue-100 text-blue-700', 'devis' => 'bg-orange-100 text-orange-700', 'prestation_catalogue' => 'bg-purple-100 text-purple-700'][$typeC] ?? 'bg-gray-100 text-gray-600';
                                        $typeLabel = ['annonce' => t('pro_comm_type_annonce', 'Annonce'), 'devis' => t('pro_comm_type_devis', 'Prestation sur devis'), 'prestation_catalogue' => t('pro_comm_type_catalogue', 'Prestation catalogue')][$typeC] ?? $typeC;
                                        ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $typeCls ?>">
                                            <?= $typeLabel ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($c['description'] ?? '') ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700"><?= number_format((float)($c['prix_total'] ?? 0), 2, ',', ' ') ?> €</td>
                                    <td class="px-4 py-3 text-right font-semibold text-orange-600"><?= number_format((float)($c['montant_commission'] ?? 0), 2, ',', ' ') ?> €</td>
                                    <td class="px-4 py-3 text-right font-semibold text-emerald-600"><?= number_format((float)($c['montant_vendeur'] ?? 0), 2, ',', ' ') ?> €</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
        </main>
    </div>
</div>

</body>
</html>
