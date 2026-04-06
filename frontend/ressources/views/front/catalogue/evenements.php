<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-blue-600"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 uppercase tracking-wide">Catalogue</span>
        </div>
        <h1 class="text-3xl font-bold">Événements</h1>
        <p class="text-base-content/60 mt-2">Participez à des rencontres, expositions et marchés autour de l'upcycling et du développement durable.</p>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Type</label>
                <select name="type" class="select select-bordered w-full select-sm">
                    <option value="">Tous</option>
                    <option value="atelier">Atelier</option>
                    <option value="marche">Marché</option>
                    <option value="conference">Conférence</option>
                    <option value="exposition">Exposition</option>
                    <option value="communautaire">Communautaire</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Tarif</label>
                <select name="tarif" class="select select-bordered w-full select-sm">
                    <option value="">Tous</option>
                    <option value="gratuit">Gratuit</option>
                    <option value="payant">Payant</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Date</label>
                <input type="date" name="date" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Localisation</label>
                <input type="text" name="localisation" placeholder="Ville ou arrondissement" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase">Trier par</label>
                <select name="tri" class="select select-bordered w-full select-sm">
                    <option value="date">Date</option>
                    <option value="prix_asc">Prix croissant</option>
                    <option value="popularite">Popularité</option>
                </select>
            </div>
            <div class="md:col-span-5 flex justify-end gap-3">
                <a href="/UpcycleConnect-PA2526/frontend/public/catalogue/evenements" class="btn btn-ghost btn-sm">Réinitialiser</a>
                <button type="submit" class="btn btn-neutral btn-sm">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <?php
    if (empty($evenements)) {
        $evenements = [
            ['id'=>1,'titre'=>'Marché de l\'upcycling','type'=>'Marché','description'=>'Venez découvrir et acheter des créations uniques réalisées à partir d\'objets recyclés.','prix'=>0,'date'=>'11 avr. 2026','heure'=>'10h - 18h','lieu'=>'Paris 11ème','participants'=>234,'icon'=>'fa-store'],
            ['id'=>2,'titre'=>'Atelier couture collective','type'=>'Atelier','description'=>'Rejoignez notre atelier collaboratif pour apprendre à recoudre et transformer vos vêtements.','prix'=>15,'date'=>'13 avr. 2026','heure'=>'14h - 17h','lieu'=>'Paris 10ème','participants'=>18,'icon'=>'fa-cut'],
            ['id'=>3,'titre'=>'Conférence : L\'économie circulaire','type'=>'Conférence','description'=>'Comprenez les enjeux de l\'économie circulaire avec nos experts invités.','prix'=>0,'date'=>'15 avr. 2026','heure'=>'19h - 21h','lieu'=>'Paris 13ème','participants'=>87,'icon'=>'fa-microphone'],
            ['id'=>4,'titre'=>'Expo : Objets réinventés','type'=>'Exposition','description'=>'Découvrez les œuvres de nos artisans qui ont transformé des objets du quotidien en pièces d\'art.','prix'=>5,'date'=>'17 avr. 2026','heure'=>'11h - 19h','lieu'=>'Paris 16ème','participants'=>156,'icon'=>'fa-palette'],
            ['id'=>5,'titre'=>'Repair Café','type'=>'Communautaire','description'=>'Apportez vos objets cassés, nos bénévoles vous aident à les réparer gratuitement.','prix'=>0,'date'=>'19 avr. 2026','heure'=>'09h - 13h','lieu'=>'Montreuil','participants'=>45,'icon'=>'fa-wrench'],
            ['id'=>6,'titre'=>'Soirée communauté UpcycleConnect','type'=>'Communautaire','description'=>'Rencontrez d\'autres membres de la communauté et partagez vos expériences d\'upcycling.','prix'=>10,'date'=>'22 avr. 2026','heure'=>'19h - 22h','lieu'=>'Paris 10ème','participants'=>63,'icon'=>'fa-users'],
        ];
    }
    $typeColors = [
        'Marché'        => 'bg-green-50 text-green-600',
        'Atelier'       => 'bg-purple-50 text-purple-600',
        'Conférence'    => 'bg-blue-50 text-blue-600',
        'Exposition'    => 'bg-pink-50 text-pink-600',
        'Communautaire' => 'bg-orange-50 text-orange-600',
    ];
    ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-base-content/50"><?= count($evenements) ?> événement(s) trouvé(s)</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($evenements as $ev):
            $icon         = $ev['icon']         ?? 'fa-calendar-alt';
            $type         = $ev['type']         ?? ($ev['statut'] ?? '');
            $titre        = $ev['titre']        ?? '';
            $description  = $ev['description']  ?? '';
            $prix         = isset($ev['prix'])   ? $ev['prix'] : null;
            $date         = $ev['date']         ?? '';
            $heure        = $ev['heure']        ?? '';
            $lieu         = $ev['lieu']         ?? '';
            $participants = $ev['participants'] ?? ($ev['capacite'] ?? '?');
            $colorClass   = $typeColors[$type]  ?? 'bg-gray-50 text-gray-600';
        ?>
            <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition">
                <div class="w-full h-36 bg-blue-50 flex items-center justify-center relative">
                    <i class="fas <?= $icon ?> text-5xl text-blue-200"></i>
                    <div class="absolute top-3 left-3">
                        <span class="badge badge-sm <?= $colorClass ?> border-0"><?= htmlspecialchars($type) ?></span>
                    </div>
                    <div class="absolute top-3 right-3">
                        <?php if ($prix === 0 || $prix === null): ?>
                            <span class="badge badge-success badge-sm">Gratuit</span>
                        <?php else: ?>
                            <span class="badge badge-ghost badge-sm"><?= $prix ?>€</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($titre) ?></h3>
                    <p class="text-sm text-base-content/60 mb-4 line-clamp-2"><?= htmlspecialchars($description) ?></p>
                    <div class="space-y-2 mb-4 text-xs text-base-content/50">
                        <div><i class="fas fa-calendar-alt mr-2"></i><?= htmlspecialchars($date) ?><?= $heure ? ' · ' . htmlspecialchars($heure) : '' ?></div>
                        <div><i class="fas fa-map-marker-alt mr-2"></i><?= htmlspecialchars($lieu) ?></div>
                        <div><i class="fas fa-users mr-2"></i><?= htmlspecialchars((string)$participants) ?> participant(s)</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold"><?= ($prix === 0 || $prix === null) ? 'Gratuit' : $prix . '€' ?></span>
                        <a href="/UpcycleConnect-PA2526/frontend/public/evenements/<?= $ev['id'] ?>" class="btn btn-neutral btn-sm">Participer</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</section>