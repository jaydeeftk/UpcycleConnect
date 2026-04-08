<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="grid lg:grid-cols-2 gap-12 items-start">

        <div class="bg-base-100 rounded-3xl shadow-sm flex items-center justify-center min-h-[420px]">
            <i class="fas fa-calendar-alt text-8xl text-base-content/20"></i>
        </div>

        <div>
            <div class="text-sm text-base-content/60 mb-2">
                <?= htmlspecialchars($evenement['date'] ?? '') ?> • <?= htmlspecialchars($evenement['lieu'] ?? '') ?>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-6"><?= htmlspecialchars($evenement['titre'] ?? 'Événement') ?></h1>
            <p class="text-base-content/70 text-lg leading-relaxed mb-8">
                <?= htmlspecialchars($evenement['description'] ?? '') ?>
            </p>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <div class="flex justify-between">
                    <span class="font-medium">Date</span>
                    <span class="text-base-content/70"><?= htmlspecialchars($evenement['date'] ?? '') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Lieu</span>
                    <span class="text-base-content/70"><?= htmlspecialchars($evenement['lieu'] ?? '') ?></span>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle"></i>
                    <span>Vous êtes bien inscrit à cet événement !</span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($_GET['error']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success_desinscription'])): ?>
                <div class="alert alert-info mb-6">
                    <i class="fas fa-info-circle"></i>
                    <span>Vous avez été désinscrit de cet événement.</span>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-4">
                <?php if (isset($_SESSION['user'])): ?>
                    <?php if ($evenement['est_inscrit'] ?? false): ?>
                        <form method="POST" action="/evenements/<?= $evenement['id'] ?? '' ?>/desinscrire">
                            <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-xl font-medium hover:bg-red-700 transition">
                                <i class="fas fa-times mr-2"></i>Se désinscrire
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="/evenements/<?= $evenement['id'] ?? '' ?>/participer">
                            <button type="submit" class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                                S'inscrire à l'événement
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/login" class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                        Connectez-vous pour s'inscrire
                    </a>
                <?php endif; ?>
                <a href="/evenements" class="bg-base-200 border border-base-300 px-8 py-3 rounded-xl font-medium hover:bg-base-300 transition text-center">
                    Retour aux événements
                </a>
            </div>
        </div>

    </div>
</section>