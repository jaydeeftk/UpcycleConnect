<section class="max-w-6xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                </div>
                <span class="text-sm font-medium text-blue-600 uppercase tracking-wide">Mon espace</span>
            </div>
            <h1 class="text-3xl font-bold">Mon Planning</h1>
            <p class="text-base-content/60 mt-2">
                Retrouvez tous vos cours, événements et activités en cours et à venir.
            </p>
        </div>

        <div class="tabs tabs-boxed bg-base-100 p-1 rounded-2xl shadow-sm">
            <a href="?vue=jour" class="tab <?= ($vue ?? 'semaine') === 'jour' ? 'tab-active' : '' ?>">Jour</a>
            <a href="?vue=semaine" class="tab <?= ($vue ?? 'semaine') === 'semaine' ? 'tab-active' : '' ?>">Semaine</a>
            <a href="?vue=mois" class="tab <?= ($vue ?? 'semaine') === 'mois' ? 'tab-active' : '' ?>">Mois</a>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">

        <aside class="lg:col-span-1 space-y-6">

            <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                <h2 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4">Résumé</h2>
                <div class="space-y-3">
                    <?php foreach ([
                        ['label' => 'Formations à venir',  'count' => $stats['formations'] ?? 2,  'icon' => 'fa-graduation-cap', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50'],
                        ['label' => 'Événements à venir',  'count' => $stats['evenements'] ?? 1,  'icon' => 'fa-calendar-check', 'color' => 'text-blue-500',   'bg' => 'bg-blue-50'],
                        ['label' => 'Services en cours',   'count' => $stats['services'] ?? 3,    'icon' => 'fa-tools',          'color' => 'text-orange-500', 'bg' => 'bg-orange-50'],
                    ] as $item): ?>
                        <div class="flex items-center gap-3 p-3 <?= $item['bg'] ?> rounded-xl">
                            <i class="fas <?= $item['icon'] ?> <?= $item['color'] ?>"></i>
                            <div class="flex-1 text-sm"><?= $item['label'] ?></div>
                            <span class="font-bold <?= $item['color'] ?>"><?= $item['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                <h2 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4">Prochain rendez-vous</h2>
                <?php
                $prochain = $evenements[0] ?? [
                    'titre'  => 'Atelier Upcycling Mobilier',
                    'date'   => '08 avril 2026',
                    'heure'  => '14h00',
                    'lieu'   => 'Paris 10ème',
                    'type'   => 'formation',
                ];
                ?>
                <div class="bg-blue-50 rounded-xl p-4">
                    <div class="badge badge-primary badge-sm mb-2"><?= ucfirst($prochain['type']) ?></div>
                    <div class="font-semibold text-sm mb-1"><?= htmlspecialchars($prochain['titre']) ?></div>
                    <div class="text-xs text-base-content/60 space-y-1">
                        <div><i class="fas fa-clock mr-1"></i><?= $prochain['date'] ?> à <?= $prochain['heure'] ?></div>
                        <div><i class="fas fa-map-marker-alt mr-1"></i><?= $prochain['lieu'] ?></div>
                    </div>
                </div>
            </div>

        </aside>

        <div class="lg:col-span-3">

            <?php $vue = $_GET['vue'] ?? 'semaine'; ?>

            <div class="bg-base-100 rounded-2xl shadow-sm p-4 mb-6 flex items-center justify-between">
                <button class="btn btn-ghost btn-sm">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="font-semibold">
                    <?php
                    if ($vue === 'jour') echo date('d F Y');
                    elseif ($vue === 'semaine') echo 'Semaine du 7 au 13 avril 2026';
                    else echo 'Avril 2026';
                    ?>
                </span>
                <button class="btn btn-ghost btn-sm">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <?php if ($vue === 'semaine'): ?>

                <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
                    <?php
                    $jours = ['Lun 07', 'Mar 08', 'Mer 09', 'Jeu 10', 'Ven 11', 'Sam 12', 'Dim 13'];
                    $evenementsSemaine = $evenements ?? [
                        ['jour' => 1, 'heure' => '14h00', 'fin' => '16h00', 'titre' => 'Atelier Upcycling Mobilier', 'type' => 'formation',  'lieu' => 'Paris 10ème'],
                        ['jour' => 3, 'heure' => '10h00', 'fin' => '11h30', 'titre' => 'Formation Développement Durable', 'type' => 'formation', 'lieu' => 'Paris 11ème'],
                        ['jour' => 4, 'heure' => '18h00', 'fin' => '19h30', 'titre' => 'Événement Communautaire', 'type' => 'evenement', 'lieu' => 'Paris 13ème'],
                        ['jour' => 6, 'heure' => '09h00', 'fin' => '12h00', 'titre' => 'Service Réparation Meuble', 'type' => 'service',   'lieu' => 'À domicile'],
                    ];
                    $typeColors = [
                        'formation' => 'bg-purple-100 text-purple-700 border-purple-300',
                        'evenement' => 'bg-blue-100 text-blue-700 border-blue-300',
                        'service'   => 'bg-orange-100 text-orange-700 border-orange-300',
                    ];
                    ?>

                    <div class="grid grid-cols-7 border-b border-base-300">
                        <?php foreach ($jours as $i => $jour): ?>
                            <div class="p-3 text-center text-sm <?= $i === 1 ? 'bg-primary/10 font-bold text-primary' : 'text-base-content/50' ?>">
                                <?= $jour ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="grid grid-cols-7 min-h-64 divide-x divide-base-300">
                        <?php for ($i = 0; $i < 7; $i++): ?>
                            <div class="p-2 space-y-2 min-h-32">
                                <?php foreach ($evenementsSemaine as $ev):
                                    if ($ev['jour'] !== $i) continue;
                                    $colorClass = $typeColors[$ev['type']] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                                ?>
                                    <div class="<?= $colorClass ?> border rounded-lg p-2 text-xs cursor-pointer hover:opacity-80 transition">
                                        <div class="font-semibold"><?= $ev['heure'] ?></div>
                                        <div class="mt-0.5 leading-tight"><?= htmlspecialchars($ev['titre']) ?></div>
                                        <div class="mt-1 text-xs opacity-70"><i class="fas fa-map-marker-alt mr-1"></i><?= $ev['lieu'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

            <?php elseif ($vue === 'jour'): ?>

                <div class="bg-base-100 rounded-2xl shadow-sm p-6 space-y-3">
                    <?php
                    $heures = ['08h00', '09h00', '10h00', '11h00', '12h00', '13h00', '14h00', '15h00', '16h00', '17h00', '18h00', '19h00'];
                    foreach ($heures as $h):
                    ?>
                        <div class="flex gap-4 items-start">
                            <span class="text-xs text-base-content/40 w-12 pt-1 flex-shrink-0"><?= $h ?></span>
                            <div class="flex-1 border-t border-base-200 pt-1 min-h-8">
                                <?php if ($h === '14h00'): ?>
                                    <div class="bg-purple-100 text-purple-700 border border-purple-300 rounded-lg p-3 text-sm">
                                        <div class="font-semibold">Atelier Upcycling Mobilier</div>
                                        <div class="text-xs mt-1 opacity-70">14h00 - 16h00 · Paris 10ème</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>

                <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="grid grid-cols-7 border-b border-base-300">
                        <?php foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $j): ?>
                            <div class="p-3 text-center text-xs font-semibold text-base-content/50"><?= $j ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="grid grid-cols-7 divide-x divide-y divide-base-300">
                        <?php
                        $joursVides = 2; 
                        for ($i = 0; $i < $joursVides; $i++):
                        ?>
                            <div class="p-2 min-h-20 bg-base-200/50"></div>
                        <?php endfor; ?>
                        <?php for ($d = 1; $d <= 30; $d++): ?>
                            <div class="p-2 min-h-20 <?= $d === 8 ? 'bg-primary/5' : '' ?>">
                                <span class="text-sm <?= $d === 8 ? 'font-bold text-primary' : 'text-base-content/60' ?>"><?= $d ?></span>
                                <?php if ($d === 8): ?>
                                    <div class="mt-1 bg-purple-100 text-purple-700 rounded text-xs p-1 leading-tight">Atelier</div>
                                <?php endif; ?>
                                <?php if ($d === 10): ?>
                                    <div class="mt-1 bg-blue-100 text-blue-700 rounded text-xs p-1 leading-tight">Événement</div>
                                <?php endif; ?>
                                <?php if ($d === 12): ?>
                                    <div class="mt-1 bg-orange-100 text-orange-700 rounded text-xs p-1 leading-tight">Service</div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

            <?php endif; ?>

            <div class="flex gap-4 mt-4 text-xs text-base-content/50">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-purple-200 inline-block"></span> Formation</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-200 inline-block"></span> Événement</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-orange-200 inline-block"></span> Service</span>
            </div>

        </div>
    </div>
</section>