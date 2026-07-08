<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <a href="/annonces" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-8">
        <i class="fas fa-arrow-left"></i> <?= t('annshow_back', 'Retour aux annonces') ?>
    </a>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

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
                <?php if (empty($annonce['est_proprietaire'])): ?>
                    <?php if (($annonce['type_annonce'] ?? '') === 'vente' && ($annonce['statut'] ?? '') === 'validee'): ?>
                        <a href="/payer?type=annonce&id_item=<?= (int)$annonce['id'] ?>&montant=<?= htmlspecialchars((string)($annonce['prix'] ?? 0)) ?>&titre=<?= urlencode($annonce['titre'] ?? '') ?>"
                           class="btn btn-success w-full gap-2">
                            <i class="fas fa-cart-shopping"></i> <?= t('annshow_buy', 'Acheter') ?> <?= htmlspecialchars(formatPrix($annonce['prix'] ?? 0)) ?>
                        </a>
                    <?php endif; ?>
                    <?php if (($annonce['type_annonce'] ?? '') === 'don' && ($annonce['statut'] ?? '') === 'validee'): ?>
                        <form method="POST" action="/annonces/<?= (int)$annonce['id'] ?>/reserver"
                              onsubmit="return confirm('<?= t('annshow_confirm_reserve', 'Réserver ce don ? Le déposant sera prévenu.') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success w-full gap-2">
                                <i class="fas fa-hand-holding-heart"></i> <?= t('annshow_reserve', 'Je veux ce don') ?>
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="/messagerie/demarrer" class="mt-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_annonce" value="<?= (int)$annonce['id'] ?>">
                        <button type="submit" class="btn btn-neutral w-full gap-2">
                            <i class="fas fa-comment-dots"></i> <?= t('annshow_send_message', 'Envoyer un message') ?>
                        </button>
                    </form>
                <?php endif; ?>
                <?php if (($_SESSION['user']['role'] ?? '') === 'professionnel'): ?>
                    <form method="POST" action="/professionnels/favoris/<?= $annonce['id'] ?>/toggle" class="mt-2">
                    <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline w-full gap-2 border-pink-400 text-pink-500 hover:bg-pink-500 hover:text-white hover:border-pink-500">
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
