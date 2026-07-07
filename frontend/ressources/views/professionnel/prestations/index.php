<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_presta_page_title', 'Demandes reçues') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_presta_heading', 'Demandes reçues') ?></h2>
            <p class="text-gray-600 text-sm"><?= t('pro_presta_subtitle', 'Proposez un devis sur les demandes des particuliers.') ?></p>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-3xl mx-auto space-y-4">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (empty($demandes)): ?>
                <div class="bg-white rounded-lg shadow text-center py-16 text-gray-400">
                    <i class="fas fa-tools text-4xl mb-3 block"></i>
                    <p><?= t('pro_presta_empty', 'Aucune demande ouverte pour le moment.') ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($demandes as $d): ?>
                    <?php $monDevisId = (int)($d['mon_devis_id'] ?? 0); ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($d['nom_objet'] ?? '') ?></h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= htmlspecialchars($d['categorie'] ?? '') ?>
                                    <?php if (!empty($d['type_objet'])): ?> · <?= htmlspecialchars($d['type_objet']) ?><?php endif; ?>
                                    <?php if (!empty($d['etat'])): ?> · <?= t('pro_presta_state', 'État') ?> : <?= htmlspecialchars($d['etat']) ?><?php endif; ?>
                                </p>
                            </div>
                            <?php if (!empty($d['budget'])): ?>
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600"><?= t('pro_presta_budget', 'Budget indicatif') ?> : <?= htmlspecialchars($d['budget']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($d['description'])): ?>
                            <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($d['description']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($d['localisation'])): ?>
                            <p class="text-xs text-gray-400 mb-3"><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($d['localisation']) ?></p>
                        <?php endif; ?>

                        <?php if ($monDevisId > 0 && ($d['mon_devis_statut'] ?? '') === 'propose'): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-800 mb-3">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <?= t('pro_presta_my_quote', 'Votre devis en attente') ?> :
                                    <strong><?= htmlspecialchars(number_format((float)($d['mon_devis_prix'] ?? 0), 2)) ?> €</strong>
                                </p>
                                <details>
                                    <summary class="text-xs text-blue-600 cursor-pointer mb-2"><?= t('pro_presta_edit_quote', 'Modifier mon devis') ?></summary>
                                    <form method="POST" action="/professionnel/prestations/devis" class="space-y-2 mt-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_demande" value="<?= (int)($d['id'] ?? 0) ?>">
                                        <input type="number" name="prix" min="1" step="0.01" required
                                               value="<?= htmlspecialchars((string)($d['mon_devis_prix'] ?? '')) ?>"
                                               class="input input-bordered w-full text-sm" placeholder="Prix (€)">
                                        <textarea name="message" rows="2" required class="textarea textarea-bordered w-full text-sm" placeholder="Message"></textarea>
                                        <button type="submit" class="btn btn-sm btn-primary"><?= t('pro_presta_update_btn', 'Mettre à jour') ?></button>
                                    </form>
                                </details>
                                <form method="POST" action="/professionnel/prestations/devis/<?= (int)$monDevisId ?>/retirer" class="mt-2"
                                      onsubmit="return confirm('<?= t('pro_presta_confirm_withdraw', 'Retirer ce devis ?') ?>')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">
                                        <i class="fas fa-times mr-1"></i><?= t('pro_presta_withdraw_btn', 'Retirer mon devis') ?>
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="/professionnel/prestations/devis" class="flex flex-col sm:flex-row gap-2 items-start">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_demande" value="<?= (int)($d['id'] ?? 0) ?>">
                                <input type="number" name="prix" min="1" step="0.01" required
                                       class="input input-bordered text-sm w-32" placeholder="Prix (€)">
                                <input type="text" name="message" required
                                       class="input input-bordered text-sm flex-1" placeholder="<?= t('pro_presta_msg_placeholder', 'Votre message (délai, précisions...)') ?>">
                                <button type="submit" class="btn btn-sm btn-primary whitespace-nowrap">
                                    <i class="fas fa-paper-plane mr-1"></i><?= t('pro_presta_propose_btn', 'Proposer un devis') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
        </main>
    </div>
</div>

</body>
</html>
