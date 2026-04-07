<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <!-- En-tête -->
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

    <!-- Filtres -->
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
                <a href="/UpcycleConnect-PA2526/frontend/public/catalogue/services" class="btn btn-ghost btn-sm">Réinitialiser</a>
                <button type="submit" class="btn btn-neutral btn-sm">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <?php
    $services = $services ?? [
        ['id' => 1, 'titre' => 'Réparation de meubles anciens',       'categorie' => 'Réparation',      'description' => 'Restauration complète de meubles en bois anciens, ponçage, traitement et finition.', 'prix' => 80,  'localisation' => 'Paris 10ème', 'note' => 4.8, 'avis' => 24, 'icon' => 'fa-couch'],
        ['id' => 2, 'titre' => 'Transformation de palettes',           'categorie' => 'Transformation',  'description' => 'Création de meubles design à partir de palettes récupérées.', 'prix' => 120, 'localisation' => 'Paris 11ème', 'note' => 4.6, 'avis' => 18, 'icon' => 'fa-layer-group'],
        ['id' => 3, 'titre' => 'Upcycling vêtements & textiles',       'categorie' => 'Upcycling',       'description' => 'Donnez une seconde vie à vos vêtements grâce à des techniques créatives.', 'prix' => 45,  'localisation' => 'Paris 13ème', 'note' => 4.9, 'avis' => 31, 'icon' => 'fa-tshirt'],
        ['id' => 4, 'titre' => 'Recyclage équipements électroniques',  'categorie' => 'Recyclage',       'description' => 'Collecte et recyclage responsable de vos appareils électroniques.', 'prix' => 30,  'localisation' => 'Montreuil',   'note' => 4.5, 'avis' => 12, 'icon' => 'fa-laptop'],
        ['id' => 5, 'titre' => 'Réparation électroménager',            'categorie' => 'Réparation',      'description' => 'Diagnostic et réparation de vos appareils électroménagers.', 'prix' => 60,  'localisation' => 'Paris 16ème', 'note' => 4.7, 'avis' => 42, 'icon' => 'fa-blender'],
        ['id' => 6, 'titre' => 'Customisation mobilier',               'categorie' => 'Upcycling',       'description' => 'Personnalisation unique de vos meubles avec peintures et matériaux éco.', 'prix' => 95,  'localisation' => 'Ivry',        'note' => 4.4, 'avis' => 9,  'icon' => 'fa-paint-brush'],
    ];
    ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-base-content/50"><?= count($services) ?> service(s) trouvé(s)</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($services as $service): ?>
            <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition group">
                <div class="w-full h-40 bg-orange-50 flex items-center justify-center">
                    <i class="fas <?= $service['icon'] ?> text-5xl text-orange-300 group-hover:text-orange-400 transition"></i>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($service['categorie']) ?></span>
                        <div class="flex items-center gap-1 text-xs text-yellow-500">
                            <i class="fas fa-star"></i>
                            <span class="font-semibold text-base-content"><?= $service['note'] ?></span>
                            <span class="text-base-content/40">(<?= $service['avis'] ?>)</span>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($service['titre']) ?></h3>
                    <p class="text-sm text-base-content/60 mb-4 line-clamp-2"><?= htmlspecialchars($service['description']) ?></p>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xl font-bold">À partir de <?= $service['prix'] ?>€</span>
                            <div class="text-xs text-base-content/40 mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i><?= $service['localisation'] ?></div>
                        </div>
                        <a href="/UpcycleConnect-PA2526/frontend/public/prestations/<?= $service['id'] ?>" class="btn btn-neutral btn-sm">
                            Voir
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</section>