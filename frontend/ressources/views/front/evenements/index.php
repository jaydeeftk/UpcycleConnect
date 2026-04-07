<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Événements à venir</h1>
        <p class="text-lg text-base-content/70 max-w-2xl">
            Découvrez les ateliers, rencontres et événements organisés autour de la réparation,
            de la transformation et du recyclage.
        </p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">

        <?php if (!empty($evenements)): ?>
            <?php foreach ($evenements as $evenement): ?>
                <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="w-full h-64 bg-base-200 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-4xl text-base-content/40"></i>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-base-content/60 mb-2">
                            <?= htmlspecialchars($evenement['date'] ?? '') ?> • <?= htmlspecialchars($evenement['lieu'] ?? '') ?>
                        </div>
                        <h3 class="text-xl font-semibold mb-3"><?= htmlspecialchars($evenement['titre'] ?? '') ?></h3>
                        <p class="text-base-content/70 mb-4"><?= htmlspecialchars($evenement['description'] ?? '') ?></p>
                        <a href="/evenements/<?= $evenement['id'] ?>"
                            class="text-sm font-medium hover:underline">
                            Voir l'événement →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-3 text-center text-base-content/60 py-12">
                Aucun événement disponible pour le moment.
            </div>
        <?php endif; ?>

    </div>
</section>

<section class="bg-base-100 border-t border-base-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 text-center">
        <h2 class="text-3xl font-bold mb-4">Participez à la communauté UpcycleConnect</h2>
        <p class="text-base-content/70 max-w-2xl mx-auto mb-6">
            Rejoignez des événements près de chez vous pour apprendre, rencontrer des prestataires
            et découvrir de nouvelles façons de donner une seconde vie à vos objets.
        </p>
        <a href="/login"
            class="inline-block bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
            S'inscrire à un événement
        </a>
    </div>
</section>