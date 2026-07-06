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
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-xl font-bold text-green-400">UpcycleConnect</h1>
            <p class="text-xs text-gray-400 mt-1"><?= t('pro_space', 'Espace Professionnel') ?></p>
        </div>
        <nav class="flex-1 p-4">
            <ul class="space-y-1">
                <li>
                    <a href="/professionnel" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-tachometer-alt w-5"></i><span><?= t('pro_nav_dashboard', 'Tableau de bord') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/recuperation" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-recycle w-5"></i><span><?= t('pro_nav_recuperation', 'Récupération') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/projets/create" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-project-diagram w-5"></i><span><?= t('pro_nav_new_project', 'Nouveau projet') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/annonces" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-bullhorn w-5"></i><span><?= t('pro_nav_annonces', 'Annonces') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/services" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-store w-5"></i><span><?= t('pro_nav_services', 'Mes prestations créées') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/prestations" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-tools w-5"></i><span><?= t('pro_nav_prestations', 'Demandes reçues') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/commissions" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-gray-700 text-white">
                        <i class="fas fa-hand-holding-usd w-5"></i><span><?= t('pro_nav_commissions', 'Mes commissions') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/abonnement" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-crown w-5"></i><span><?= t('pro_nav_abonnement', 'Abonnement Premium') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/publicites" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-ad w-5"></i><span><?= t('pro_nav_publicites', 'Campagnes publicitaires') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/messagerie" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-comment-dots w-5"></i><span><?= t('pro_nav_messagerie', 'Messagerie') ?></span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <a href="/logout" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
                <i class="fas fa-sign-out-alt w-5"></i><span><?= t('pro_nav_logout', 'Déconnexion') ?></span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_comm_heading', 'Mes commissions') ?></h2>
            <p class="text-gray-600 text-sm"><?= t('pro_comm_subtitle', "Détail de ce qu'UpcycleConnect prélève sur vos ventes et prestations.") ?></p>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-4xl mx-auto space-y-6">

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
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= ($c['type'] ?? '') === 'annonce' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' ?>">
                                            <?= ($c['type'] ?? '') === 'annonce' ? t('pro_comm_type_annonce', 'Annonce') : t('pro_comm_type_devis', 'Prestation') ?>
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
