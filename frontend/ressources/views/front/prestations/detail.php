<?php if (empty($prestation) || empty($prestation['titre'])): ?>
<section class="max-w-3xl mx-auto px-6 lg:px-10 py-24 text-center">
    <div class="text-6xl mb-6">🔍</div>
    <h1 class="text-3xl md:text-4xl font-bold mb-4"><?= t('prestdet_not_found', 'Prestation introuvable') ?></h1>
    <p class="text-base-content/70 mb-8"><?= t('prestdet_not_found_desc', 'Cette prestation n\'existe pas ou n\'est plus disponible.') ?></p>
    <a href="/prestations"
        class="inline-block bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
        <?= t('prestdet_cta_back', 'Retour aux prestations') ?>
    </a>
</section>
<?php else: ?>
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="grid lg:grid-cols-2 gap-12 items-start">

        <div class="bg-base-100 rounded-3xl shadow-sm overflow-hidden">
            <img src="<?= uc_image('prestation', $prestation['id'] ?? ($prestation['titre'] ?? '')) ?>"
                alt="<?= htmlspecialchars($prestation['titre']) ?>" class="w-full h-full object-cover min-h-[420px]">
        </div>

        <div>
            <div class="text-sm text-base-content/60 mb-2"><?= htmlspecialchars($prestation['categorie'] ?? t('prestdet_breadcrumb_fallback', 'Prestation')) ?></div>
            <h1 class="text-4xl md:text-5xl font-bold mb-6"><?= htmlspecialchars($prestation['titre']) ?></h1>
            <?php if (!empty($prestation['description'])): ?>
            <p class="text-base-content/70 text-lg leading-relaxed mb-8 whitespace-pre-line">
                <?= htmlspecialchars($prestation['description']) ?>
            </p>
            <?php endif; ?>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <?php if (!empty($prestation['categorie'])): ?>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('prestdet_info_category', 'Catégorie') ?></span>
                    <span class="text-base-content/70"><?= htmlspecialchars($prestation['categorie']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($prestation['duree'])): ?>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('prestdet_info_duration', 'Durée') ?></span>
                    <span class="text-base-content/70"><?= htmlspecialchars($prestation['duree']) ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('prestdet_info_price', 'Tarif indicatif') ?></span>
                    <span class="text-base-content/70 font-semibold"><?= formatPrix($prestation['prix'] ?? 0) ?></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/demande-prestation"
                    class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                    <?= t('prestdet_cta_request', 'Faire une demande') ?>
                </a>
                <a href="/prestations"
                    class="bg-base-200 border border-base-300 px-8 py-3 rounded-xl font-medium hover:bg-base-300 transition text-center">
                    <?= t('prestdet_cta_back', 'Retour aux prestations') ?>
                </a>
            </div>
        </div>

    </div>
</section>

<section class="bg-base-100 border-t border-base-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
        <h2 class="text-3xl font-bold mb-8 text-center"><?= t('prestdet_includes_title', 'Ce que comprend cette prestation') ?></h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-base-200 rounded-2xl p-6">
                <h3 class="text-xl font-semibold mb-3"><?= t('prestdet_feat1_title', 'Diagnostic de l\'objet') ?></h3>
                <p class="text-base-content/70">
                    <?= t('prestdet_feat1_desc', 'Le prestataire analyse l\'état de l\'appareil et identifie la cause du problème avant toute intervention.') ?>
                </p>
            </div>
            <div class="bg-base-200 rounded-2xl p-6">
                <h3 class="text-xl font-semibold mb-3"><?= t('prestdet_feat2_title', 'Réparation adaptée') ?></h3>
                <p class="text-base-content/70">
                    <?= t('prestdet_feat2_desc', 'Une solution est proposée selon la panne rencontrée afin de remettre l\'objet en état de fonctionnement.') ?>
                </p>
            </div>
            <div class="bg-base-200 rounded-2xl p-6">
                <h3 class="text-xl font-semibold mb-3"><?= t('prestdet_feat3_title', 'Conseil d\'entretien') ?></h3>
                <p class="text-base-content/70">
                    <?= t('prestdet_feat3_desc', 'Des recommandations sont fournies pour prolonger la durée de vie de l\'appareil après réparation.') ?>
                </p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>