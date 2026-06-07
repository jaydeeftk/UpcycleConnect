<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= t('mesprest_title', 'Mes prestations') ?></h1>
        <p class="text-lg text-base-content/70 max-w-2xl">
            <?= t('mesprest_subtitle', 'Retrouvez ici les prestations réservées ou en cours.') ?>
        </p>
    </div>

    <div class="space-y-6">

        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <div class="text-sm text-base-content/60 mb-1"><?= t('mesprest_card1_meta', 'Réparation • Prestataire : Atelier RépareTout') ?></div>
                    <h2 class="text-2xl font-semibold"><?= t('mesprest_card1_title', 'Réparation de vélo') ?></h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <?= t('mesprest_status_reserved', 'Réservée') ?>
                </span>
            </div>
            <p class="text-base-content/70 mb-4">
                <?= t('mesprest_card1_desc', 'Révision générale, réglage des freins et remplacement de la chambre à air.') ?>
            </p>
            <div class="grid md:grid-cols-3 gap-4 text-sm text-base-content/70">
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_place', 'Lieu :') ?></span> Paris</div>
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_price', 'Tarif :') ?></span> 45€</div>
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_date', 'Date :') ?></span> 15 mars 2026</div>
            </div>
        </div>

        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <div class="text-sm text-base-content/60 mb-1"><?= t('mesprest_card2_meta', 'Transformation • Prestataire : WoodCraft') ?></div>
                    <h2 class="text-2xl font-semibold"><?= t('mesprest_card2_title', 'Transformation de meuble') ?></h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    <?= t('mesprest_status_inprogress', 'En cours') ?>
                </span>
            </div>
            <p class="text-base-content/70 mb-4">
                <?= t('mesprest_card2_desc', 'Transformation d\'une vieille commode en meuble TV moderne avec finitions en bois clair.') ?>
            </p>
            <div class="grid md:grid-cols-3 gap-4 text-sm text-base-content/70">
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_place', 'Lieu :') ?></span> Lyon</div>
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_price', 'Tarif :') ?></span> 95€</div>
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_date', 'Date :') ?></span> 18 mars 2026</div>
            </div>
        </div>

        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <div class="text-sm text-base-content/60 mb-1"><?= t('mesprest_card3_meta', 'Recyclage • Prestataire : EcoCycle') ?></div>
                    <h2 class="text-2xl font-semibold"><?= t('mesprest_card3_title', 'Collecte et recyclage d\'objets électroniques') ?></h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <?= t('mesprest_status_done', 'Terminée') ?>
                </span>
            </div>
            <p class="text-base-content/70 mb-4">
                <?= t('mesprest_card3_desc', 'Collecte de plusieurs petits appareils électroniques pour tri et recyclage responsable.') ?>
            </p>
            <div class="grid md:grid-cols-3 gap-4 text-sm text-base-content/70">
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_place', 'Lieu :') ?></span> Marseille</div>
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_price', 'Tarif :') ?></span> 15€</div>
                <div><span class="font-medium text-base-content"><?= t('mesprest_label_date', 'Date :') ?></span> 5 mars 2026</div>
            </div>
        </div>

    </div>

</section>