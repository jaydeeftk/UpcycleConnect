<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Prestations disponibles</h1>
        <p class="text-lg text-base-content/70 max-w-2xl">
            Découvrez les prestations proposées par les professionnels pour réparer,
            transformer ou recycler vos objets du quotidien.
        </p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">

        <?php if (!empty($prestations)): ?>
            <?php foreach ($prestations as $prestation): ?>
                <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="w-full h-64 bg-base-200 flex items-center justify-center">
                        <span class="text-4xl">
                            <?php
    $cat = $prestation['categorie'] ?? '';
    if ($cat === 'Réparation') echo '<i class="fas fa-wrench text-4xl text-base-content/40"></i>';
    elseif ($cat === 'Transformation') echo '<i class="fas fa-hammer text-4xl text-base-content/40"></i>';
    elseif ($cat === 'Recyclage') echo '<i class="fas fa-recycle text-4xl text-base-content/40"></i>';
    else echo '<i class="fas fa-box text-4xl text-base-content/40"></i>';
?>
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-base-content/60 mb-2"><?= htmlspecialchars($prestation['categorie'] ?? '') ?></div>
                        <h3 class="text-xl font-semibold mb-3"><?= htmlspecialchars($prestation['titre'] ?? '') ?></h3>
                        <p class="text-base-content/70 mb-2"><?= htmlspecialchars($prestation['description'] ?? '') ?></p>
                        <p class="font-semibold mb-4">À partir de <?= htmlspecialchars($prestation['prix'] ?? '') ?>€</p>
                        <a href="/prestations/<?= $prestation['id'] ?>"
                            class="text-sm font-medium hover:underline">
                            Voir la prestation →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-3 text-center text-base-content/60 py-12">
                Aucune prestation disponible pour le moment.
            </div>
        <?php endif; ?>

    </div>
</section>

<section class="bg-base-100 border-t border-base-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 text-center">
        <h2 class="text-3xl font-bold mb-4">Vous êtes un professionnel ?</h2>
        <p class="text-base-content/70 max-w-xl mx-auto mb-6">
            Rejoignez la plateforme UpcycleConnect et proposez vos prestations pour aider
            les particuliers à donner une seconde vie à leurs objets.
        </p>
        <a href="/devenir-prestataire"
            class="inline-block bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
            Devenir prestataire
        </a>
    </div>
</section>