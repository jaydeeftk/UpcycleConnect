<section class="max-w-5xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-leaf text-emerald-600"></i>
            </div>
            <span class="text-sm font-medium text-emerald-600 uppercase tracking-wide">Mon impact</span>
        </div>
        <h1 class="text-3xl font-bold">Mon Upcycling Score</h1>
        <p class="text-base-content/60 mt-2">
            Suivez votre impact environnemental et progressez dans la communauté UpcycleConnect.
        </p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-8">

        <div class="lg:col-span-2 bg-base-100 rounded-2xl shadow-sm p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold">Votre score actuel</h2>
                <span class="text-xs text-base-content/40">Mis à jour en temps réel</span>
            </div>

            <?php $score = $score ?? 420; $scoreMax = 1000; $pct = min(100, round($score / $scoreMax * 100)); ?>

            <div class="flex items-end gap-4 mb-6">
                <div class="text-7xl font-extrabold text-emerald-500"><?= $score ?></div>
                <div class="text-base-content/40 text-lg mb-2">/ <?= $scoreMax ?> pts</div>
            </div>

            <div class="w-full bg-base-200 rounded-full h-4 mb-2">
                <div class="bg-gradient-to-r from-emerald-400 to-green-500 h-4 rounded-full transition-all duration-500"
                     style="width: <?= $pct ?>%"></div>
            </div>
            <div class="flex justify-between text-xs text-base-content/40 mb-6">
                <span>0</span>
                <span><?= $pct ?>% vers le prochain niveau</span>
                <span><?= $scoreMax ?></span>
            </div>

            <div class="border-t border-base-300 pt-6">
                <h3 class="font-semibold mb-4">Comment améliorer votre score ?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ([
                        ['icon' => 'fa-box-open',    'color' => 'text-blue-500',    'bg' => 'bg-blue-50',    'label' => 'Donner ou vendre',  'desc' => '+50 pts par objet déposé'],
                        ['icon' => 'fa-calendar-alt','color' => 'text-purple-500',  'bg' => 'bg-purple-50',  'label' => 'Participer',        'desc' => '+30 pts par événement'],
                        ['icon' => 'fa-comments',    'color' => 'text-orange-500',  'bg' => 'bg-orange-50',  'label' => 'Partager un conseil','desc' => '+20 pts par conseil'],
                    ] as $action): ?>
                        <div class="<?= $action['bg'] ?> rounded-xl p-4 text-center">
                            <i class="fas <?= $action['icon'] ?> <?= $action['color'] ?> text-2xl mb-2 block"></i>
                            <div class="font-medium text-sm"><?= $action['label'] ?></div>
                            <div class="text-xs text-base-content/50 mt-1"><?= $action['desc'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center text-center">
            <h2 class="text-lg font-semibold mb-6">Votre badge</h2>

            <?php
            $badges = [
                ['min' => 0,   'max' => 100,  'icon' => '🌱', 'label' => 'Éco-Débutant',      'color' => 'text-green-500',   'bg' => 'bg-green-50'],
                ['min' => 100, 'max' => 300,  'icon' => '♻️', 'label' => 'Recycleur Actif',    'color' => 'text-blue-500',    'bg' => 'bg-blue-50'],
                ['min' => 300, 'max' => 600,  'icon' => '🌍', 'label' => 'Éco-Engagé',         'color' => 'text-purple-500',  'bg' => 'bg-purple-50'],
                ['min' => 600, 'max' => 1000, 'icon' => '🏆', 'label' => 'Phénix Vert',        'color' => 'text-yellow-500',  'bg' => 'bg-yellow-50'],
            ];
            $badgeActuel = $badges[0];
            $badgeSuivant = $badges[1] ?? null;
            foreach ($badges as $i => $badge) {
                if ($score >= $badge['min'] && $score < $badge['max']) {
                    $badgeActuel = $badge;
                    $badgeSuivant = $badges[$i + 1] ?? null;
                    break;
                }
            }
            ?>

            <div class="w-28 h-28 <?= $badgeActuel['bg'] ?> rounded-full flex items-center justify-center text-6xl mb-4 shadow-inner">
                <?= $badgeActuel['icon'] ?>
            </div>
            <div class="text-xl font-bold <?= $badgeActuel['color'] ?>"><?= $badgeActuel['label'] ?></div>

            <?php if ($badgeSuivant): ?>
                <div class="mt-4 text-xs text-base-content/50">
                    Prochain badge : <span class="font-medium"><?= $badgeSuivant['label'] ?></span>
                    <br>dans <?= $badgeSuivant['min'] - $score ?> points
                </div>
            <?php else: ?>
                <div class="mt-4 text-xs text-emerald-600 font-medium">🎉 Niveau maximum atteint !</div>
            <?php endif; ?>

            <div class="mt-6 w-full border-t border-base-300 pt-4 text-left">
                <p class="text-xs font-semibold text-base-content/50 mb-2">Vos avantages :</p>
                <ul class="text-xs text-base-content/60 space-y-1">
                    <li><i class="fas fa-check text-emerald-500 mr-1"></i> Accès aux événements de base</li>
                    <li><i class="fas fa-check text-emerald-500 mr-1"></i> Publication d'annonces</li>
                    <?php if ($score >= 100): ?>
                        <li><i class="fas fa-check text-emerald-500 mr-1"></i> Réduction 5% sur les formations</li>
                    <?php endif; ?>
                    <?php if ($score >= 300): ?>
                        <li><i class="fas fa-check text-emerald-500 mr-1"></i> Accès prioritaire aux annonces</li>
                    <?php endif; ?>
                    <?php if ($score >= 600): ?>
                        <li><i class="fas fa-check text-emerald-500 mr-1"></i> Profil mis en avant</li>
                        <li><i class="fas fa-check text-emerald-500 mr-1"></i> Offres partenaires exclusives</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm p-8 mb-8">
        <h2 class="text-lg font-semibold mb-6">Tous les badges</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($badges as $badge): ?>
                <?php $debloque = $score >= $badge['min']; ?>
                <div class="text-center p-4 rounded-xl border-2 <?= $debloque ? 'border-emerald-200 ' . $badge['bg'] : 'border-base-300 opacity-40' ?>">
                    <div class="text-4xl mb-2"><?= $badge['icon'] ?></div>
                    <div class="font-semibold text-sm <?= $debloque ? $badge['color'] : '' ?>"><?= $badge['label'] ?></div>
                    <div class="text-xs text-base-content/50 mt-1"><?= $badge['min'] ?> pts</div>
                    <?php if ($debloque): ?>
                        <span class="badge badge-success badge-xs mt-2">Débloqué</span>
                    <?php else: ?>
                        <span class="badge badge-ghost badge-xs mt-2">Verrouillé</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm p-8">
        <h2 class="text-lg font-semibold mb-6">Historique des points</h2>
        <div class="space-y-3">
            <?php
            $historique = $historique ?? [
                ['action' => 'Dépôt d\'un objet dans un conteneur', 'points' => '+50', 'date' => '02 avr. 2026', 'icon' => 'fa-box-open',     'color' => 'text-blue-500'],
                ['action' => 'Participation à un atelier',           'points' => '+30', 'date' => '28 mars 2026', 'icon' => 'fa-calendar-alt', 'color' => 'text-purple-500'],
                ['action' => 'Conseil partagé dans l\'espace conseils','points' => '+20','date' => '25 mars 2026','icon' => 'fa-comments',    'color' => 'text-orange-500'],
                ['action' => 'Don d\'un objet via annonce',           'points' => '+50', 'date' => '20 mars 2026', 'icon' => 'fa-heart',        'color' => 'text-red-500'],
            ];
            foreach ($historique as $item):
            ?>
                <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-base-200 transition">
                    <div class="w-10 h-10 bg-base-200 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas <?= $item['icon'] ?> <?= $item['color'] ?>"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-sm"><?= htmlspecialchars($item['action']) ?></div>
                        <div class="text-xs text-base-content/50"><?= htmlspecialchars($item['date']) ?></div>
                    </div>
                    <div class="font-bold text-emerald-500"><?= $item['points'] ?> pts</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</section>