<?php
$imagesByCategorie = [
    'reparation'     => 'https://images.unsplash.com/photo-1609205807107-2d29e8f53dd6?auto=format&fit=crop&w=900&q=80',
    'transformation' => 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=900&q=80',
    'recyclage'      => 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=900&q=80',
    'upcycling'      => 'https://images.unsplash.com/photo-1595407753234-0882f1e77954?auto=format&fit=crop&w=900&q=80',
    'nettoyage'      => 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?auto=format&fit=crop&w=900&q=80',
    'default'        => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=900&q=80',
];
?>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                <i class="fas fa-tools text-orange-600"></i>
            </div>
            <span class="text-sm font-medium text-orange-600 uppercase tracking-wide">Catalogue</span>
        </div>
        <h1 class="text-3xl font-bold">Services</h1>
        <p class="text-base-content/60 mt-2">Trouvez un professionnel pour réparer, transformer ou recycler vos objets.</p>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Catégorie</label>
                <select name="categorie" class="select select-bordered w-full select-sm">
                    <option value="">Toutes</option>
                    <option value="reparation" <?= ($_GET['categorie'] ?? '') === 'reparation' ? 'selected' : '' ?>>Réparation</option>
                    <option value="transformation" <?= ($_GET['categorie'] ?? '') === 'transformation' ? 'selected' : '' ?>>Transformation</option>
                    <option value="recyclage" <?= ($_GET['categorie'] ?? '') === 'recyclage' ? 'selected' : '' ?>>Recyclage</option>
                    <option value="upcycling" <?= ($_GET['categorie'] ?? '') === 'upcycling' ? 'selected' : '' ?>>Upcycling créatif</option>
                    <option value="nettoyage" <?= ($_GET['categorie'] ?? '') === 'nettoyage' ? 'selected' : '' ?>>Nettoyage</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Prix max (€)</label>
                <input type="number" name="prix_max" min="0" placeholder="Ex : 100" value="<?= htmlspecialchars($_GET['prix_max'] ?? '') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Localisation</label>
                <input type="text" name="localisation" placeholder="Ville ou code postal" value="<?= htmlspecialchars($_GET['localisation'] ?? '') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Trier par</label>
                <select name="tri" class="select select-bordered w-full select-sm">
                    <option value="pertinence">Pertinence</option>
                    <option value="prix_asc" <?= ($_GET['tri'] ?? '') === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                    <option value="prix_desc" <?= ($_GET['tri'] ?? '') === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                    <option value="note" <?= ($_GET['tri'] ?? '') === 'note' ? 'selected' : '' ?>>Mieux notés</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end gap-3">
                <a href="/catalogue/services" class="btn btn-ghost btn-sm">Réinitialiser</a>
                <button type="submit" class="btn btn-neutral btn-sm">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <?php $services = $services ?? []; ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-base-content/50"><?= count($services) ?> service(s) trouvé(s)</p>
    </div>

    <?php if (empty($services)): ?>
        <div class="text-center py-20 text-base-content/40">
            <i class="fas fa-tools text-5xl mb-4 block"></i>
            <p class="text-lg">Aucun service disponible pour le moment.</p>
        </div>
    <?php else: ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($services as $service):
            $cat    = strtolower($service['categorie'] ?? '');
            $imgUrl = $imagesByCategorie[$cat] ?? $imagesByCategorie['default'];
        ?>
            <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition group">
                <div class="w-full h-48 relative overflow-hidden">
                    <img src="<?= $imgUrl ?>"
                         alt="<?= htmlspecialchars($service['titre']) ?>"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                    <div class="absolute top-3 left-3">
                        <span class="badge badge-sm bg-white/90 text-gray-700 border-0"><?= htmlspecialchars($service['categorie'] ?? '') ?></span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($service['titre']) ?></h3>
                    <p class="text-sm text-base-content/60 mb-4 line-clamp-2"><?= htmlspecialchars($service['description']) ?></p>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xl font-bold">À partir de <?= $service['prix'] ?? 0 ?>€</span>
                            <div class="text-xs text-base-content/40 mt-0.5"><i class="fas fa-clock mr-1"></i><?= $service['duree'] ?? '' ?>h</div>
                        </div>
                        <a href="/services/<?= $service['id'] ?>" class="btn btn-neutral btn-sm">Voir</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</section>