<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600"></i>
            </div>
            <span class="text-sm font-medium text-purple-600 uppercase tracking-wide">Catalogue</span>
        </div>
        <h1 class="text-3xl font-bold">Formations</h1>
        <p class="text-base-content/60 mt-2">Apprenez les techniques d'upcycling et de développement durable avec nos formateurs experts.</p>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Catégorie</label>
                <select name="categorie" class="select select-bordered w-full select-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($categories ?? [] as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($_GET['categorie'] ?? '') === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Prix max (€)</label>
                <input type="number" name="prix_max" min="0" placeholder="Ex : 50" value="<?= htmlspecialchars($_GET['prix_max'] ?? '') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Date</label>
                <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Places dispo</label>
                <select name="places" class="select select-bordered w-full select-sm">
                    <option value="">Peu importe</option>
                    <option value="1" <?= ($_GET['places'] ?? '') === '1' ? 'selected' : '' ?>>Au moins 1 place</option>
                    <option value="5" <?= ($_GET['places'] ?? '') === '5' ? 'selected' : '' ?>>Au moins 5 places</option>
                    <option value="10" <?= ($_GET['places'] ?? '') === '10' ? 'selected' : '' ?>>Au moins 10 places</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Trier par</label>
                <select name="tri" class="select select-bordered w-full select-sm">
                    <option value="date" <?= ($_GET['tri'] ?? 'date') === 'date' ? 'selected' : '' ?>>Date</option>
                    <option value="prix_asc" <?= ($_GET['tri'] ?? '') === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                    <option value="prix_desc" <?= ($_GET['tri'] ?? '') === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                    <option value="places" <?= ($_GET['tri'] ?? '') === 'places' ? 'selected' : '' ?>>Places restantes</option>
                </select>
            </div>
            <div class="md:col-span-5 flex justify-end gap-3">
                <a href="/catalogue/formations" class="btn btn-ghost btn-sm">Réinitialiser</a>
                <button type="submit" class="btn btn-neutral btn-sm">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <?php $formations = $formations ?? []; ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-base-content/50"><?= count($formations) ?> formation(s) trouvée(s)</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($formations as $formation):
            $complet = ($formation['places_dispo'] ?? 0) === 0;
            $presque = ($formation['places_dispo'] ?? 0) > 0 && ($formation['places_dispo'] ?? 0) <= 3;
        ?>
            <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition <?= $complet ? 'opacity-70' : '' ?>">
                <div class="w-full h-36 bg-purple-50 flex items-center justify-center relative">
                    <i class="fas fa-graduation-cap text-5xl text-purple-200"></i>
                    <?php if ($complet): ?>
                        <div class="absolute inset-0 bg-base-300/80 flex items-center justify-center">
                            <span class="badge badge-error">Complet</span>
                        </div>
                    <?php elseif ($presque): ?>
                        <div class="absolute top-3 right-3">
                            <span class="badge badge-warning badge-sm">Plus que <?= $formation['places_dispo'] ?> place(s) !</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($formation['categorie'] ?? '') ?></span>
                        <span class="text-xs text-base-content/40"><i class="fas fa-clock mr-1"></i><?= ($formation['duree'] ?? '') ?>h</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($formation['titre'] ?? '') ?></h3>
                    <p class="text-sm text-base-content/60 mb-4 line-clamp-2"><?= htmlspecialchars($formation['description'] ?? '') ?></p>
                    <div class="space-y-2 mb-4 text-xs text-base-content/50">
                        <div><i class="fas fa-calendar-alt mr-2"></i><?= $formation['date'] ?? '' ?></div>
                        <div><i class="fas fa-map-marker-alt mr-2"></i><?= $formation['localisation'] ?? '' ?></div>
                        <div>
                            <i class="fas fa-users mr-2"></i>
                            <?php if ($complet): ?>
                                <span class="text-red-500 font-medium">Complet</span>
                            <?php else: ?>
                                <span class="<?= $presque ? 'text-orange-500 font-medium' : '' ?>"><?= $formation['places_dispo'] ?? 0 ?> / <?= $formation['places_total'] ?? 0 ?> places restantes</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="w-full bg-base-200 rounded-full h-1.5 mb-4">
                        <?php
                        $total = $formation['places_total'] ?? 1;
                        $dispo = $formation['places_dispo'] ?? 0;
                        $pct = $total > 0 ? round(($total - $dispo) / $total * 100) : 0;
                        ?>
                        <div class="h-1.5 rounded-full <?= $complet ? 'bg-red-400' : ($presque ? 'bg-orange-400' : 'bg-purple-400') ?>" style="width:<?= $pct ?>%"></div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold"><?= $formation['prix'] ?? 0 ?>€</span>
                        <?php if ($complet): ?>
                            <button class="btn btn-disabled btn-sm" disabled>Complet</button>
                        <?php else: ?>
                            <a href="#" class="btn btn-neutral btn-sm">S'inscrire</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</section>