<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Paiements</h1>
        <p class="text-lg text-base-content/70 max-w-2xl">
            Consultez vos paiements à venir et l'historique de vos règlements.
        </p>
    </div>

    <?php if (!isset($_SESSION['user'])): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-8 text-center">
            <p class="text-base-content/70 mb-4">Vous devez être connecté pour voir vos paiements.</p>
            <a href="/login"
                class="inline-block bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                Se connecter
            </a>
        </div>
    <?php elseif (empty($paiements)): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-8 text-center">
            <p class="text-base-content/70">Aucun paiement pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($paiements as $paiement): ?>
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                        <div>
                            <h2 class="text-2xl font-semibold"><?= htmlspecialchars($paiement['montant'] ?? '') ?>€</h2>
                            <div class="text-sm text-base-content/60"><?= htmlspecialchars($paiement['date'] ?? '') ?></div>
                        </div>
                        <?php $statutPaye = in_array($paiement['statut'] ?? '', ['paye', 'payé', 'success', '1', 'completed']); ?>
                        <?php if ($statutPaye): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Payé
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                À payer
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!$statutPaye): ?>
                        <a href="/payer"
                            class="inline-block bg-black text-white px-6 py-2 rounded-xl text-sm font-medium hover:bg-neutral-800 transition">
                            Régler maintenant
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>