<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <a href="/catalogue/services" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-8">
        <i class="fas fa-arrow-left"></i> Retour aux services
    </a>

    <div class="grid lg:grid-cols-2 gap-12 items-start">

        <div class="bg-base-100 rounded-3xl shadow-sm flex items-center justify-center min-h-[360px]">
            <i class="fas fa-tools text-8xl text-base-content/20"></i>
        </div>

        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="badge badge-ghost"><?= htmlspecialchars($service['categorie'] ?? '') ?></span>
            </div>

            <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($service['titre'] ?? '') ?></h1>
            <p class="text-base-content/70 leading-relaxed mb-8"><?= htmlspecialchars($service['description'] ?? '') ?></p>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <div class="flex justify-between">
                    <span class="font-medium">Durée estimée</span>
                    <span class="text-base-content/70"><?= $service['duree'] ?? '' ?>h</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Tarif</span>
                    <span class="text-2xl font-bold text-orange-500"><?= $service['prix'] ?? 0 ?>€</span>
                </div>
            </div>

            <?php if (isset($_SESSION['user'])): ?>
                <a href="mailto:contact@upcycleconnect.fr?subject=Demande de service : <?= htmlspecialchars($service['titre'] ?? '') ?>"
                   class="btn btn-neutral w-full">
                    <i class="fas fa-envelope mr-2"></i> Demander ce service
                </a>
            <?php else: ?>
                <a href="/login" class="btn btn-neutral w-full">
                    <i class="fas fa-sign-in-alt mr-2"></i> Connectez-vous pour demander ce service
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
