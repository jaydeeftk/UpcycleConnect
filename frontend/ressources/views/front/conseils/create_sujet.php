<section class="max-w-3xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-comments text-green-600"></i>
            </div>
            <span class="text-sm font-medium text-green-600 uppercase tracking-wide"><?= t('conscre_forum_badge', 'Forum communautaire') ?></span>
        </div>
        <h1 class="text-3xl font-bold"><?= t('conscre_title', 'Nouveau sujet') ?></h1>
        <p class="text-base-content/60 mt-2"><?= t('conscre_subtitle', 'Posez votre question à la communauté UpcycleConnect.') ?></p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-base-100 rounded-2xl shadow-sm p-8">
        <form method="POST" action="/conseils/forum/store">
        <?= csrf_field() ?>

            <div class="space-y-6">

                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('conscre_label_titre', 'Titre') ?> <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="titre"
                        placeholder="<?= t('conscre_ph_titre', 'Ex : Comment réparer une chaise en bois ?') ?>"
                        class="input input-bordered w-full"
                        required
                        value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('conscre_label_cat', 'Catégorie') ?> <span class="text-red-500">*</span></label>
                    <select name="categorie" class="select select-bordered w-full" required>
                        <option value="" disabled selected><?= t('conscre_cat_placeholder', 'Sélectionnez une catégorie') ?></option>
                        <option value="general" <?= ($_POST['categorie'] ?? '') === 'general' ? 'selected' : '' ?>><?= t('conscre_cat_general', 'Général') ?></option>
                        <option value="recyclage" <?= ($_POST['categorie'] ?? '') === 'recyclage' ? 'selected' : '' ?>><?= t('conscre_cat_recyclage', 'Recyclage') ?></option>
                        <option value="entretien" <?= ($_POST['categorie'] ?? '') === 'entretien' ? 'selected' : '' ?>><?= t('conscre_cat_entretien', 'Entretien des matériaux') ?></option>
                        <option value="upcycling" <?= ($_POST['categorie'] ?? '') === 'upcycling' ? 'selected' : '' ?>><?= t('conscre_cat_upcycling', 'Upcycling créatif') ?></option>
                        <option value="durable" <?= ($_POST['categorie'] ?? '') === 'durable' ? 'selected' : '' ?>><?= t('conscre_cat_durable', 'Développement durable') ?></option>
                        <option value="bricolage" <?= ($_POST['categorie'] ?? '') === 'bricolage' ? 'selected' : '' ?>><?= t('conscre_cat_bricolage', 'Bricolage & Réparation') ?></option>
                        <option value="bonnes-pratiques" <?= ($_POST['categorie'] ?? '') === 'bonnes-pratiques' ? 'selected' : '' ?>><?= t('conscre_cat_bonnes_pratiques', 'Bonnes pratiques') ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('conscre_label_desc', 'Description') ?> <span class="text-red-500">*</span></label>
                    <textarea
                        name="contenu"
                        rows="6"
                        placeholder="<?= t('conscre_ph_desc', 'Décrivez votre question en détail...') ?>"
                        class="textarea textarea-bordered w-full resize-none"
                        required
                    ><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-6 mt-6 border-t border-base-300">
                <button type="submit" class="btn btn-neutral flex-1">
                    <i class="fas fa-paper-plane mr-2"></i> <?= t('conscre_submit', 'Publier le sujet') ?>
                </button>
                <a href="/conseils?onglet=forum" class="btn btn-ghost flex-1">
                    <?= t('conscre_cancel', 'Annuler') ?>
                </a>
            </div>

        </form>
    </div>

</section>
