<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_nav_annonces', 'Annonces') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_nav_annonces', 'Annonces') ?></h2>
                <p class="text-gray-600 text-sm"><?= t('pro_ann_subtitle', 'Parcourez toutes les annonces ou gérez les vôtres.') ?></p>
            </div>
            <a href="/professionnel/annonces/create"
               class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold">
                <i class="fas fa-plus"></i> <?= t('pro_ann_new', 'Déposer une annonce') ?>
            </a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i><?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i><?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Mes annonces -->
            <?php if (!empty($mesAnnonces)): ?>
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-user-circle text-green-500"></i>
                        <?= t('pro_ann_mine_title', 'Mes annonces') ?>
                        <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold"><?= count($mesAnnonces) ?></span>
                    </h3>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($mesAnnonces as $a):
                        $type = $a['type_annonce'] ?? 'don';
                        $typeColor = $type === 'vente' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700';
                        $typeLabel = $type === 'vente' ? t('ann_type_sale', 'Vente') : t('ann_type_gift', 'Don');
                        $st = $a['statut'] ?? '';
                        $stCls = match($st) {
                            'validee'     => 'bg-green-100 text-green-700',
                                            'vendue'      => 'bg-blue-100 text-blue-700',
                            'en_attente'           => 'bg-yellow-100 text-yellow-700',
                            'retiree'     => 'bg-red-100 text-red-600',
                                            'refusee'     => 'bg-gray-100 text-gray-600',
                            default                => 'bg-gray-100 text-gray-600',
                        };
                    ?>
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($a['titre'] ?? '—') ?></p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <?= htmlspecialchars($a['ville'] ?? '') ?>
                                    <?php if (!empty($a['prix']) && $type === 'vente'): ?>
                                        · <span class="font-medium"><?= htmlspecialchars(formatPrix($a['prix'])) ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $typeColor ?>"><?= $typeLabel ?></span>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $stCls ?>"><?= htmlspecialchars(formatStatut($st)) ?></span>
                            <a href="/annonces/<?= (int)($a['id'] ?? 0) ?>"
                               class="text-blue-500 hover:text-blue-700 text-sm" title="<?= t('pro_ann_view', 'Voir') ?>">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (!in_array($st, ['retiree', 'refusee', 'vendue'])): ?>
                            <form method="POST" action="/professionnel/annonces/<?= (int)($a['id'] ?? 0) ?>/annuler"
                                  onsubmit="return confirm('<?= t('pro_ann_cancel_confirm', 'Annuler cette annonce ?') ?>')">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm" title="<?= t('pro_ann_cancel', 'Annuler') ?>">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Toutes les annonces -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-bullhorn text-gray-400"></i>
                        <?= t('pro_ann_all_title', 'Toutes les annonces') ?>
                        <span class="ml-2 px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold"><?= count($annonces) ?></span>
                    </h3>

                    <form method="GET" class="flex items-center gap-3">
                        <?php $type = $_GET['type'] ?? 'tous'; ?>
                        <select name="type" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="tous" <?= $type === 'tous' ? 'selected' : '' ?>><?= t('ann_filter_all', 'Tous') ?></option>
                            <option value="don" <?= $type === 'don' ? 'selected' : '' ?>><?= t('ann_type_gift', 'Dons') ?></option>
                            <option value="vente" <?= $type === 'vente' ? 'selected' : '' ?>><?= t('ann_type_sale', 'Ventes') ?></option>
                        </select>
                        <div class="relative">
                            <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="localisation"
                                   value="<?= htmlspecialchars($localisation ?? '') ?>"
                                   placeholder="<?= t('ann_filter_location', 'Ville ou code postal') ?>"
                                   class="border border-gray-300 rounded-lg pl-8 pr-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 w-48">
                        </div>
                        <button type="submit" class="bg-gray-700 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-gray-800 transition">
                            <i class="fas fa-filter mr-1"></i><?= t('ann_filter_btn', 'Filtrer') ?>
                        </button>
                        <?php if (!empty($localisation) || $type !== 'tous'): ?>
                        <a href="/professionnel/annonces" class="text-gray-500 hover:text-gray-700 text-sm"><?= t('ann_filter_reset', 'Réinitialiser') ?></a>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if (empty($annonces)): ?>
                <div class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-bullhorn text-5xl mb-4 block text-gray-300"></i>
                    <p><?= t('pro_ann_empty', 'Aucune annonce disponible.') ?></p>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php
                    $userId = (int)($_SESSION['user']['id'] ?? 0);
                    foreach ($annonces as $a):
                        $type      = $a['type_annonce'] ?? 'don';
                        $typeColor = $type === 'vente' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700';
                        $typeLabel = $type === 'vente' ? t('ann_type_sale', 'Vente') : t('ann_type_gift', 'Don');
                        $isOwner   = (int)($a['user_id'] ?? $a['id_utilisateur'] ?? 0) === $userId;
                        $st = $a['statut'] ?? '';
                        $stCls = match($st) {
                            'validee'     => 'bg-green-100 text-green-700',
                                            'vendue'      => 'bg-blue-100 text-blue-700',
                            'en_attente'           => 'bg-yellow-100 text-yellow-700',
                            'retiree'     => 'bg-red-100 text-red-600',
                                            'refusee'     => 'bg-gray-100 text-gray-600',
                            default                => 'bg-gray-100 text-gray-600',
                        };
                    ?>
                    <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($a['titre'] ?? '—') ?></p>
                                    <?php if ($isOwner): ?>
                                        <span class="px-1.5 py-0.5 bg-green-50 text-green-600 rounded text-xs font-semibold border border-green-200"><?= t('pro_ann_badge_mine', 'Moi') ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-500">
                                    <?= htmlspecialchars($a['ville'] ?? '') ?>
                                    <?php if (!empty($a['prix']) && $type === 'vente'): ?>
                                        · <span class="font-medium text-gray-700"><?= htmlspecialchars(formatPrix($a['prix'])) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($a['categorie'])): ?>
                                        · <?= htmlspecialchars($a['categorie']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $typeColor ?>"><?= $typeLabel ?></span>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $stCls ?>"><?= htmlspecialchars(formatStatut($st)) ?></span>
                            <a href="/annonces/<?= (int)($a['id'] ?? 0) ?>"
                               class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i><?= t('pro_ann_view', 'Voir') ?>
                            </a>
                            <?php if ($isOwner && !in_array($st, ['retiree', 'refusee', 'vendue'])): ?>
                            <form method="POST" action="/professionnel/annonces/<?= (int)($a['id'] ?? 0) ?>/annuler"
                                  onsubmit="return confirm('<?= t('pro_ann_cancel_confirm', 'Annuler cette annonce ?') ?>')">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm" title="<?= t('pro_ann_cancel', 'Annuler') ?>">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

</body>
</html>
