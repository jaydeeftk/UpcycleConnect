<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_svc_page_title', 'Mes prestations créées') ?> - UpcycleConnect</title>
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
                    <a href="/professionnel/services" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-gray-700 text-white">
                        <i class="fas fa-store w-5"></i><span><?= t('pro_nav_services', 'Mes prestations créées') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/prestations" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-tools w-5"></i><span><?= t('pro_nav_prestations', 'Demandes reçues') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/commissions" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
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
            <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_svc_heading', 'Mes prestations créées') ?></h2>
            <p class="text-gray-600 text-sm"><?= t('pro_svc_subtitle', 'Prestations catalogue visibles par tous les particuliers, avec paiement direct.') ?></p>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-4xl mx-auto space-y-8">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold mb-4"><?= t('pro_svc_new_title', 'Créer une nouvelle prestation') ?></h3>
                <form method="POST" action="/professionnel/services" class="grid md:grid-cols-2 gap-4">
                    <?= csrf_field() ?>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1"><?= t('pro_svc_label_title', 'Titre') ?></label>
                        <input type="text" name="titre" required class="input input-bordered w-full" placeholder="<?= t('pro_svc_title_ph', 'Ex : Réparation de vélo') ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1"><?= t('pro_svc_label_desc', 'Description') ?></label>
                        <textarea name="description" rows="3" class="textarea textarea-bordered w-full" placeholder="<?= t('pro_svc_desc_ph', 'Ce que comprend la prestation...') ?>"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1"><?= t('pro_svc_label_category', 'Catégorie') ?></label>
                        <select name="categorie" class="select select-bordered w-full">
                            <option value="reparation"><?= t('pro_svc_cat_reparation', 'Réparation') ?></option>
                            <option value="transformation"><?= t('pro_svc_cat_transformation', 'Transformation') ?></option>
                            <option value="recyclage"><?= t('pro_svc_cat_recyclage', 'Recyclage') ?></option>
                            <option value="upcycling"><?= t('pro_svc_cat_upcycling', 'Upcycling créatif') ?></option>
                            <option value="nettoyage"><?= t('pro_svc_cat_nettoyage', 'Nettoyage') ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1"><?= t('pro_svc_label_duration', 'Durée (minutes, optionnel)') ?></label>
                        <input type="number" name="duree" min="0" class="input input-bordered w-full">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1"><?= t('pro_svc_label_price', 'Prix fixe (€)') ?></label>
                        <input type="number" name="prix" min="1" step="0.01" required class="input input-bordered w-full">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="btn btn-neutral">
                            <i class="fas fa-plus mr-2"></i><?= t('pro_svc_create_btn', 'Créer la prestation') ?>
                        </button>
                    </div>
                </form>
            </div>

            <div>
                <h3 class="font-semibold mb-4"><?= t('pro_svc_list_title', 'Mes prestations') ?> (<?= count($services) ?>)</h3>
                <?php if (empty($services)): ?>
                    <div class="bg-white rounded-lg shadow text-center py-10 text-gray-400">
                        <?= t('pro_svc_list_empty', "Vous n'avez pas encore créé de prestation.") ?>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($services as $svc): ?>
                            <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($svc['titre'] ?? '') ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($svc['categorie'] ?? '') ?> · <?= number_format((float)($svc['prix'] ?? 0), 2) ?> €</div>
                                </div>
                                <form method="POST" action="/professionnel/services/<?= (int)($svc['id'] ?? 0) ?>/supprimer"
                                      onsubmit="return confirm('<?= t('pro_svc_confirm_delete', 'Supprimer cette prestation ?') ?>')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h3 class="font-semibold mb-4"><?= t('pro_svc_orders_title', 'Commandes reçues') ?> (<?= count($commandes) ?>)</h3>
                <?php if (empty($commandes)): ?>
                    <div class="bg-white rounded-lg shadow text-center py-10 text-gray-400">
                        <?= t('pro_svc_orders_empty', "Aucune commande pour l'instant.") ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left"><?= t('pro_svc_col_date', 'Date') ?></th>
                                    <th class="px-4 py-3 text-left"><?= t('pro_svc_col_client', 'Client') ?></th>
                                    <th class="px-4 py-3 text-left"><?= t('pro_svc_col_service', 'Prestation') ?></th>
                                    <th class="px-4 py-3 text-left"><?= t('pro_svc_col_object', 'Objet') ?></th>
                                    <th class="px-4 py-3 text-right"><?= t('pro_svc_col_price', 'Prix') ?></th>
                                    <th class="px-4 py-3 text-left"><?= t('pro_svc_col_status', 'Statut') ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($commandes as $c): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars($c['date_creation'] ?? '') ?></td>
                                        <td class="px-4 py-3 font-medium"><?= htmlspecialchars($c['nom_client'] ?? '') ?></td>
                                        <td class="px-4 py-3"><?= htmlspecialchars($c['titre_service'] ?? '') ?></td>
                                        <td class="px-4 py-3 text-gray-600">
                                            <?= htmlspecialchars($c['nom_objet'] ?? '') ?>
                                            <?php if (!empty($c['photo_url'])): ?>
                                                <a href="<?= htmlspecialchars($c['photo_url']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700 ml-2" title="<?= t('pro_svc_photo_view', 'Voir la photo') ?>">
                                                    <i class="fas fa-image"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold"><?= number_format((float)($c['prix'] ?? 0), 2) ?> €</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                <?= htmlspecialchars(formatStatut($c['statut'] ?? '')) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
        </main>
    </div>
</div>

</body>
</html>
