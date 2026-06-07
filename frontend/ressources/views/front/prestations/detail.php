<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="grid lg:grid-cols-2 gap-12 items-start">

        <div class="bg-base-100 rounded-3xl shadow-sm overflow-hidden">
            <img src="https://images.unsplash.com/photo-1581093458791-9d09c86d1f79?auto=format&fit=crop&w=1200&q=80"
                alt="<?= t('prestdet_img_alt', 'Réparation d\'appareils') ?>" class="w-full h-full object-cover min-h-[420px]">
        </div>

        <div>
            <div class="text-sm text-base-content/60 mb-2"><?= t('prestdet_breadcrumb', 'Réparation • Électroménager • Paris') ?></div>
            <h1 class="text-4xl md:text-5xl font-bold mb-6"><?= t('prestdet_title', 'Réparation d\'appareils électroménagers') ?></h1>
            <p class="text-base-content/70 text-lg leading-relaxed mb-6">
                <?= t('prestdet_intro1', 'Faites réparer vos petits appareils électroménagers par un prestataire qualifié. Cette prestation permet de redonner vie à vos équipements du quotidien plutôt que de les remplacer.') ?>
            </p>
            <p class="text-base-content/70 leading-relaxed mb-8">
                <?= t('prestdet_intro2', 'Que ce soit pour un grille-pain, un mixeur, une cafetière ou un autre appareil, le prestataire réalise un diagnostic et vous accompagne dans la remise en état de votre objet de manière responsable et durable.') ?>
            </p>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('prestdet_info_category', 'Catégorie') ?></span>
                    <span class="text-base-content/70"><?= t('prestdet_info_category_val', 'Réparation') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('prestdet_info_type', 'Type d\'objet') ?></span>
                    <span class="text-base-content/70"><?= t('prestdet_info_type_val', 'Électroménager') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('prestdet_info_location', 'Localisation') ?></span>
                    <span class="text-base-content/70"><?= t('prestdet_info_location_val', 'Paris') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('prestdet_info_price', 'Tarif indicatif') ?></span>
                    <span class="text-base-content/70"><?= t('prestdet_info_price_val', 'À partir de 25€') ?></span>
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