<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= t('prestidx_title', 'Prestations disponibles') ?></h1>
        <p class="text-lg text-base-content/70 max-w-2xl">
            <?= t('prestidx_intro', 'Découvrez les prestations proposées par les professionnels pour réparer, transformer ou recycler vos objets du quotidien.') ?>
        </p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">

        <?php if (!empty($prestations)): ?>
            <?php foreach ($prestations as $prestation): ?>
                <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="w-full h-64 overflow-hidden">
                        <img src="<?= uc_image('prestation', $prestation['id'] ?? ($prestation['titre'] ?? '')) ?>"
                             alt="<?= htmlspecialchars($prestation['titre'] ?? '') ?>"
                             class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-base-content/60 mb-2"><?= htmlspecialchars($prestation['categorie'] ?? '') ?></div>
                        <h3 class="text-xl font-semibold mb-3"><?= htmlspecialchars($prestation['titre'] ?? '') ?></h3>
                        <p class="text-base-content/70 mb-2"><?= htmlspecialchars($prestation['description'] ?? '') ?></p>
                        <p class="font-semibold mb-4"><?= t('prestidx_from_price', 'À partir de') ?> <?= htmlspecialchars(formatPrix($prestation['prix'] ?? 0)) ?></p>
                        <a href="/prestations/<?= $prestation['id'] ?? 0 ?>" class="text-sm font-medium hover:underline">
                            <?= t('prestidx_view_link', 'Voir la prestation') ?> →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-3 text-center text-base-content/60 py-12">
                <?= t('prestidx_empty', 'Aucune prestation disponible pour le moment.') ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<section class="bg-base-100 border-t border-base-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 text-center">
        <h2 class="text-3xl font-bold mb-4"><?= t('prestidx_cta_title', 'Vous êtes un professionnel ?') ?></h2>
        <p class="text-base-content/70 max-w-xl mx-auto mb-6">
            <?= t('prestidx_cta_text', 'Rejoignez la plateforme UpcycleConnect et proposez vos prestations pour aider les particuliers à donner une seconde vie à leurs objets.') ?>
        </p>
        <a href="/register"
            class="inline-block bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
            <?= t('prestidx_cta_btn', 'Devenir prestataire') ?>
        </a>
    </div>
</section>