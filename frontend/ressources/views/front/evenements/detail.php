<?php
$imagesByType = [
    'Marché'        => 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=1200&q=80',
    'Atelier'       => 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1200&q=80',
    'Conférence'    => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80',
    'Exposition'    => 'https://images.unsplash.com/photo-1531058020387-3be344556be6?auto=format&fit=crop&w=1200&q=80',
    'Communautaire' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?auto=format&fit=crop&w=1200&q=80',
    'default'       => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&w=1200&q=80',
];
$type   = $evenement['type'] ?? $evenement['statut'] ?? '';
$imgUrl = $evenement['image_url'] ?? ($imagesByType[$type] ?? $imagesByType['default']);
?>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-8">
        <a href="/catalogue/evenements" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition">
            <i class="fas fa-arrow-left"></i> Retour aux événements
        </a>
    </div>

    <div class="grid lg:grid-cols-2 gap-12 items-start">

        <div class="rounded-3xl overflow-hidden shadow-sm">
            <img src="<?= htmlspecialchars($imgUrl) ?>"
                 alt="<?= htmlspecialchars($evenement['titre'] ?? '') ?>"
                 class="w-full h-[420px] object-cover">
        </div>

        <div>
            <?php if ($type): ?>
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">
                    <?= htmlspecialchars($type) ?>
                </span>
            <?php endif; ?>

            <div class="text-sm text-base-content/60 mb-2">
                <?= htmlspecialchars($evenement['date'] ?? '') ?> • <?= htmlspecialchars($evenement['lieu'] ?? '') ?>
            </div>

            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                <?= htmlspecialchars($evenement['titre'] ?? 'Événement') ?>
            </h1>

            <p class="text-base-content/70 text-lg leading-relaxed mb-8">
                <?= htmlspecialchars($evenement['description'] ?? '') ?>
            </p>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <div class="flex justify-between items-center py-2 border-b border-base-200">
                    <span class="font-medium flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-blue-500 w-4"></i> Date
                    </span>
                    <span class="text-base-content/70"><?= htmlspecialchars($evenement['date'] ?? '') ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-base-200">
                    <span class="font-medium flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-red-500 w-4"></i> Lieu
                    </span>
                    <span class="text-base-content/70"><?= htmlspecialchars($evenement['lieu'] ?? '') ?></span>
                </div>
                <?php if (!empty($evenement['capacite'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-base-200">
                    <span class="font-medium flex items-center gap-2">
                        <i class="fas fa-users text-purple-500 w-4"></i> Capacité
                    </span>
                    <span class="text-base-content/70"><?= htmlspecialchars($evenement['capacite']) ?> places</span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between items-center py-2">
                    <span class="font-medium flex items-center gap-2">
                        <i class="fas fa-tag text-green-500 w-4"></i> Prix
                    </span>
                    <span class="font-bold text-lg <?= ($evenement['prix'] ?? 0) > 0 ? 'text-green-600' : 'text-base-content/70' ?>">
                        <?= ($evenement['prix'] ?? 0) > 0 ? number_format($evenement['prix'], 2) . ' €' : 'Gratuit' ?>
                    </span>
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
                    <?php elseif (($evenement['prix'] ?? 0) > 0): ?>
                        <a href="/payer?type=evenement&id_item=<?= $evenement['id'] ?? '' ?>&montant=<?= $evenement['prix'] ?? 0 ?>&titre=<?= urlencode($evenement['titre'] ?? '') ?>"
                           class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                            <i class="fas fa-credit-card mr-2"></i>S'inscrire — <?= number_format($evenement['prix'], 2) ?>€
                        </a>
                    <?php else: ?>
                        <form method="POST" action="/evenements/<?= $evenement['id'] ?? '' ?>/participer">
                            <button type="submit" class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                                <i class="fas fa-check mr-2"></i>S'inscrire à l'événement
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/login" class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                        Connectez-vous pour s'inscrire
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>