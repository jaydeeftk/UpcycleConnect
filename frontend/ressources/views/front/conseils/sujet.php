<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-8">
        <a href="/conseils?onglet=forum" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-6">
            <i class="fas fa-arrow-left"></i> Retour au forum
        </a>

        <div class="bg-base-100 rounded-2xl shadow-sm p-8">
            <div class="flex items-center gap-2 mb-4">
                <?php if ($sujet['resolu'] ?? false): ?>
                    <span class="badge badge-success gap-1"><i class="fas fa-check"></i> Résolu</span>
                <?php else: ?>
                    <span class="badge badge-ghost">Ouvert</span>
                <?php endif; ?>
                <span class="badge badge-ghost"><?= htmlspecialchars($sujet['categorie'] ?? '') ?></span>
            </div>

            <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($sujet['titre'] ?? '') ?></h1>

            <p class="text-base-content/70 leading-relaxed mb-6"><?= nl2br(htmlspecialchars($sujet['contenu'] ?? '')) ?></p>

            <div class="flex items-center justify-between pt-4 border-t border-base-300">
                <div class="flex items-center gap-2 text-sm text-base-content/50">
                    <i class="fas fa-user-circle text-lg"></i>
                    <span class="font-medium text-base-content/70"><?= htmlspecialchars($sujet['auteur'] ?? '') ?></span>
                    <span>·</span>
                    <span><?= htmlspecialchars($sujet['date'] ?? '') ?></span>
                </div>
                <div class="flex items-center gap-4 text-sm text-base-content/50">
                    <span><i class="fas fa-eye mr-1"></i><?= $sujet['vues'] ?? 0 ?> vues</span>
                    <span><i class="fas fa-comments mr-1"></i><?= count($sujet['reponses'] ?? []) ?> réponses</span>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($sujet['reponses'])): ?>
        <div class="space-y-4 mb-10">
            <h2 class="text-lg font-semibold"><?= count($sujet['reponses']) ?> réponse<?= count($sujet['reponses']) > 1 ? 's' : '' ?></h2>

            <?php foreach ($sujet['reponses'] as $reponse): ?>
                <div class="bg-base-100 rounded-2xl shadow-sm p-6 <?= ($reponse['est_solution'] ?? false) ? 'border-2 border-success' : '' ?>">
                    <?php if ($reponse['est_solution'] ?? false): ?>
                        <div class="flex items-center gap-2 text-success text-sm font-semibold mb-3">
                            <i class="fas fa-check-circle"></i> Meilleure réponse
                        </div>
                    <?php endif; ?>

                    <p class="text-base-content/80 leading-relaxed mb-4"><?= nl2br(htmlspecialchars($reponse['contenu'] ?? '')) ?></p>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-base-content/50">
                            <i class="fas fa-user-circle text-lg"></i>
                            <span class="font-medium text-base-content/70"><?= htmlspecialchars($reponse['auteur'] ?? '') ?></span>
                            <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($reponse['auteur_statut'] ?? '') ?></span>
                            <span>· <?= htmlspecialchars($reponse['date'] ?? '') ?></span>
                        </div>

                        <?php if (isset($_SESSION['user']) && ($_SESSION['user']['id'] ?? 0) == ($sujet['auteur_id'] ?? -1) && !($sujet['resolu'] ?? false)): ?>
                            <form method="POST" action="/conseils/forum/<?= $sujet['id'] ?>/solution/<?= $reponse['id'] ?>">
                                <button type="submit" class="btn btn-success btn-xs gap-1">
                                    <i class="fas fa-check"></i> Marquer comme solution
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-10 text-base-content/40 mb-10">
            <i class="fas fa-comments text-4xl mb-3 block"></i>
            <p>Aucune réponse pour l'instant. Soyez le premier à répondre !</p>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user'])): ?>
        <div class="bg-base-100 rounded-2xl shadow-sm p-8">
            <h2 class="text-lg font-semibold mb-6">Votre réponse</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error mb-4">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/conseils/forum/<?= $sujet['id'] ?>/repondre">
                <div class="mb-4">
                    <textarea
                        name="contenu"
                        rows="5"
                        placeholder="Partagez votre expérience ou votre conseil..."
                        class="textarea textarea-bordered w-full resize-none"
                        required
                    ></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-neutral">
                        <i class="fas fa-paper-plane mr-2"></i> Publier ma réponse
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="bg-base-100 rounded-2xl shadow-sm p-8 text-center">
            <p class="text-base-content/60 mb-4">Connectez-vous pour répondre à ce sujet.</p>
            <a href="/login" class="btn btn-neutral">
                <i class="fas fa-sign-in-alt mr-2"></i> Se connecter
            </a>
        </div>
    <?php endif; ?>

</section>
