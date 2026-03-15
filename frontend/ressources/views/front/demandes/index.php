<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Mes demandes</h1>
        <p class="text-lg text-base-content/70 max-w-2xl">
            Retrouvez ici les demandes de prestations que vous avez déposées.
        </p>
    </div>

    <?php if (!isset($_SESSION['user'])): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-8 text-center">
            <p class="text-base-content/70 mb-4">Vous devez être connecté pour voir vos demandes.</p>
            <a href="/UpcycleConnect-PA2526/frontend/public/login"
                class="inline-block bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                Se connecter
            </a>
        </div>
    <?php elseif (empty($demandes)): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-8 text-center">
            <p class="text-base-content/70 mb-4">Vous n'avez pas encore de demandes.</p>
            <a href="/UpcycleConnect-PA2526/frontend/public/demande-prestation"
                class="inline-block bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                Faire une demande
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($demandes as $demande): ?>
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                        <h2 class="text-xl font-semibold"><?= htmlspecialchars($demande['contenu'] ?? '') ?></h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            En attente
                        </span>
                    </div>
                    <div class="text-sm text-base-content/70">
                        <span class="font-medium text-base-content">Date :</span> <?= htmlspecialchars($demande['date'] ?? '') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>