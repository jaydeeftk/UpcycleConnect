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

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/UpcycleConnect-PA2526/frontend/public/login"
                    class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                    S'inscrire à l'événement
                </a>
                <a href="/UpcycleConnect-PA2526/frontend/public/evenements"
                    class="bg-base-200 border border-base-300 px-8 py-3 rounded-xl font-medium hover:bg-base-300 transition text-center">
                    Retour aux événements
                </a>
            </div>
        </div>

    </div>
</section>