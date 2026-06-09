<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <h1 class="text-4xl md:text-5xl font-bold mb-6"><?= t('apropos_title', 'À propos d\'UpcycleConnect') ?></h1>
            <p class="text-lg text-base-content/70 leading-relaxed mb-6">
                <?= t('apropos_intro_1', 'UpcycleConnect est une plateforme qui met en relation des particuliers et des prestataires autour d\'un objectif commun : donner une seconde vie aux objets du quotidien grâce à la réparation, la transformation et le recyclage.') ?>
            </p>
            <p class="text-base-content/70 leading-relaxed">
                <?= t('apropos_intro_2', 'Notre ambition est de promouvoir une consommation plus responsable, de limiter les déchets et de valoriser les savoir-faire locaux en facilitant l\'accès à des services utiles, concrets et engagés.') ?>
            </p>
        </div>
        <div class="bg-base-100 rounded-3xl shadow-sm overflow-hidden">
            <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80"
                alt="<?= t('apropos_img_alt', 'À propos UpcycleConnect') ?>" class="w-full h-full object-cover min-h-[420px]">
        </div>
    </div>
</section>

<section class="bg-base-100 border-y border-base-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
        <div class="grid md:grid-cols-3 gap-8">
            <div class="rounded-2xl p-6 bg-base-200">
                <h2 class="text-2xl font-semibold mb-3"><?= t('apropos_mission_title', 'Notre mission') ?></h2>
                <p class="text-base-content/70 leading-relaxed">
                    <?= t('apropos_mission_text', 'Encourager l\'économie circulaire en facilitant l\'accès à des prestations de réparation, de transformation et de recyclage pour tous.') ?>
                </p>
            </div>
            <div class="rounded-2xl p-6 bg-base-200">
                <h2 class="text-2xl font-semibold mb-3"><?= t('apropos_vision_title', 'Notre vision') ?></h2>
                <p class="text-base-content/70 leading-relaxed">
                    <?= t('apropos_vision_text', 'Construire une plateforme utile et accessible qui aide chacun à réduire le gaspillage et à prolonger la durée de vie des objets.') ?>
                </p>
            </div>
            <div class="rounded-2xl p-6 bg-base-200">
                <h2 class="text-2xl font-semibold mb-3"><?= t('apropos_values_title', 'Nos valeurs') ?></h2>
                <p class="text-base-content/70 leading-relaxed">
                    <?= t('apropos_values_text', 'Responsabilité, proximité, entraide et innovation au service d\'une consommation plus durable.') ?>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl md:text-4xl font-bold mb-4"><?= t('apropos_why_title', 'Pourquoi choisir UpcycleConnect ?') ?></h2>
        <p class="text-base-content/70">
            <?= t('apropos_why_subtitle', 'Une plateforme pensée pour répondre à des besoins concrets tout en favorisant des pratiques plus durables.') ?>
        </p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-base-100 rounded-2xl shadow-sm p-6">
            <h3 class="text-xl font-semibold mb-3"><?= t('apropos_feature_simple_title', 'Simple d\'utilisation') ?></h3>
            <p class="text-base-content/70">
                <?= t('apropos_feature_simple_text', 'Recherchez facilement une prestation, découvrez des événements ou entrez en contact avec des professionnels qualifiés.') ?>
            </p>
        </div>
        <div class="bg-base-100 rounded-2xl shadow-sm p-6">
            <h3 class="text-xl font-semibold mb-3"><?= t('apropos_feature_local_title', 'Engagée localement') ?></h3>
            <p class="text-base-content/70">
                <?= t('apropos_feature_local_text', 'La plateforme valorise les prestataires de proximité et soutient les initiatives locales autour du réemploi.') ?>
            </p>
        </div>
        <div class="bg-base-100 rounded-2xl shadow-sm p-6">
            <h3 class="text-xl font-semibold mb-3"><?= t('apropos_feature_daily_title', 'Utile au quotidien') ?></h3>
            <p class="text-base-content/70">
                <?= t('apropos_feature_daily_text', 'Donnez une seconde vie à vos objets sans perdre de temps et en profitant d\'un accompagnement adapté.') ?>
            </p>
        </div>
    </div>
</section>

<section class="bg-base-100 border-t border-base-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 text-center">
        <h2 class="text-3xl font-bold mb-4"><?= t('apropos_cta_title', 'Rejoignez l\'aventure UpcycleConnect') ?></h2>
        <p class="text-base-content/70 max-w-2xl mx-auto mb-6">
            <?= t('apropos_cta_text', 'Que vous soyez un particulier à la recherche d\'une solution ou un prestataire souhaitant proposer ses services, UpcycleConnect vous accompagne vers une consommation plus responsable.') ?>
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="/prestations"
                class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                <?= t('apropos_cta_prestations', 'Voir les prestations') ?>
            </a>
            <a href="/register"
                class="bg-base-200 border border-base-300 px-8 py-3 rounded-xl font-medium hover:bg-base-300 transition">
                <?= t('apropos_cta_become', 'Devenir prestataire') ?>
            </a>
        </div>
    </div>
</section>