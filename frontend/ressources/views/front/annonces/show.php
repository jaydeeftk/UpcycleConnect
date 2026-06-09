<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <a href="/annonces" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-8">
        <i class="fas fa-arrow-left"></i> <?= t('annshow_back', 'Retour aux annonces') ?>
    </a>

    <div class="grid lg:grid-cols-2 gap-10 items-start">

        <div class="bg-base-100 rounded-3xl shadow-sm flex items-center justify-center min-h-[360px]">
            <i class="fas fa-bullhorn text-8xl text-base-content/20"></i>
        </div>

        <div>
            <div class="flex items-center gap-2 mb-4">
                <?php if (($annonce['type_annonce'] ?? '') === 'vente'): ?>
                    <span class="badge badge-ghost gap-1"><i class="fas fa-tag text-blue-500"></i> <?= t('annshow_sale', 'Vente') ?></span>
                    <span class="text-2xl font-bold text-blue-500"><?= htmlspecialchars(formatPrix($annonce['prix'] ?? 0)) ?></span>
                <?php else: ?>
                    <span class="badge badge-ghost gap-1"><i class="fas fa-heart text-green-500"></i> <?= t('annshow_free_gift', 'Don gratuit') ?></span>
                <?php endif; ?>
                <span class="badge badge-ghost"><?= htmlspecialchars($annonce['categorie'] ?? '') ?></span>
            </div>

            <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($annonce['titre'] ?? '') ?></h1>
            <p class="text-base-content/70 leading-relaxed mb-8"><?= htmlspecialchars($annonce['description'] ?? '') ?></p>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('annshow_condition', 'État') ?></span>
                    <span class="text-base-content/70"><?= htmlspecialchars($annonce['etat'] ?? '') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('annshow_location', 'Localisation') ?></span>
                    <span class="text-base-content/70"><?= htmlspecialchars($annonce['ville'] ?? '') ?> <?= htmlspecialchars($annonce['code_postal'] ?? '') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('annshow_published_on', 'Publié le') ?></span>
                    <span class="text-base-content/70"><?= formatDate($annonce['date'] ?? '') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('annshow_posted_by', 'Déposé par') ?></span>
                    <span class="text-base-content/70"><?= htmlspecialchars($annonce['auteur'] ?? '') ?></span>
                </div>
            </div>

            <?php if (isset($_SESSION['user'])): ?>
                <a href="mailto:<?= htmlspecialchars($annonce['email'] ?? '') ?>"
                   class="btn btn-neutral w-full">
                    <i class="fas fa-envelope mr-2"></i> <?= t('annshow_contact_seller', 'Contacter le déposant') ?>
                </a>
                <?php if (($_SESSION['user']['role'] ?? '') === 'professionnel'): ?>
                    <form method="POST" action="/professionnels/favoris/<?= $annonce['id'] ?>/toggle" class="mt-2">
                    <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline btn-pink w-full gap-2">
                            <i class="fas fa-heart text-pink-500"></i> <?= t('annshow_add_favorite', 'Ajouter aux favoris') ?>
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <a href="/login" class="btn btn-neutral w-full">
                    <i class="fas fa-sign-in-alt mr-2"></i> <?= t('annshow_login_to_contact', 'Connectez-vous pour contacter') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
