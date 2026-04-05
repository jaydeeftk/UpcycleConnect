<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-lightbulb text-green-600"></i>
            </div>
            <span class="text-sm font-medium text-green-600 uppercase tracking-wide">Espace Conseils</span>
        </div>
        <h1 class="text-3xl font-bold">Conseils & Forum communautaire</h1>
        <p class="text-base-content/60 mt-2">
            Retrouvez les conseils de nos experts et échangez avec la communauté UpcycleConnect.
        </p>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">

        <aside class="lg:col-span-1">
            <div class="bg-base-100 rounded-2xl shadow-sm p-5 sticky top-24">
                <h2 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4">Catégories</h2>
                <ul class="space-y-1">
                    <?php
                    $categories = [
                        ['slug' => 'tous',          'label' => 'Tous les conseils',         'icon' => 'fa-th-large',        'color' => 'text-base-content'],
                        ['slug' => 'recyclage',     'label' => 'Recyclage',                 'icon' => 'fa-recycle',         'color' => 'text-green-500'],
                        ['slug' => 'entretien',     'label' => 'Entretien des matériaux',   'icon' => 'fa-tools',           'color' => 'text-yellow-500'],
                        ['slug' => 'upcycling',     'label' => 'Upcycling créatif',         'icon' => 'fa-paint-brush',     'color' => 'text-purple-500'],
                        ['slug' => 'durable',       'label' => 'Développement durable',     'icon' => 'fa-leaf',            'color' => 'text-emerald-500'],
                        ['slug' => 'bricolage',     'label' => 'Bricolage & Réparation',    'icon' => 'fa-wrench',          'color' => 'text-orange-500'],
                        ['slug' => 'bonnes-pratiques', 'label' => 'Bonnes pratiques',       'icon' => 'fa-check-circle',    'color' => 'text-blue-500'],
                    ];
                    $categorieActive = $_GET['categorie'] ?? 'tous';
                    foreach ($categories as $cat):
                    ?>
                        <li>
                            <a href="?categorie=<?= $cat['slug'] ?>"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition <?= $categorieActive === $cat['slug'] ? 'bg-base-200 font-semibold' : 'hover:bg-base-200' ?>">
                                <i class="fas <?= $cat['icon'] ?> <?= $cat['color'] ?> w-4"></i>
                                <?= $cat['label'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (isset($_SESSION['user'])): ?>
                    <div class="border-t border-base-300 mt-5 pt-5">
                        <a href="/UpcycleConnect-PA2526/frontend/public/conseils/create"
                           class="btn btn-neutral btn-sm w-full">
                            <i class="fas fa-plus mr-2"></i>
                            Partager un conseil
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <div class="lg:col-span-3 space-y-8">

        
            <div class="tabs tabs-boxed bg-base-100 p-1 rounded-2xl shadow-sm w-fit">
                <a href="?onglet=conseils&categorie=<?= $categorieActive ?>"
                   class="tab <?= ($onglet ?? 'conseils') === 'conseils' ? 'tab-active' : '' ?>">
                    <i class="fas fa-lightbulb mr-2"></i> Conseils
                </a>
                <a href="?onglet=forum&categorie=<?= $categorieActive ?>"
                   class="tab <?= ($onglet ?? 'conseils') === 'forum' ? 'tab-active' : '' ?>">
                    <i class="fas fa-comments mr-2"></i> Forum
                </a>
            </div>

            <?php $onglet = $_GET['onglet'] ?? 'conseils'; ?>

            <?php if ($onglet === 'conseils'): ?>

                <div class="bg-base-100 rounded-2xl shadow-sm p-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                        <input type="text" placeholder="Rechercher un conseil..." class="input input-bordered w-full pl-10">
                    </div>
                </div>

                <div class="space-y-4">
                    <?php
                    $conseils = $conseils ?? [
                        [
                            'titre'      => 'Comment préparer un meuble avant de le peindre ?',
                            'contenu'    => 'Avant toute chose, il est important de poncer la surface pour que la peinture accroche correctement. Utilisez un papier de verre grain 120 puis 240...',
                            'categorie'  => 'entretien',
                            'auteur'     => 'Marie Lambert',
                            'role'       => 'Formatrice',
                            'date'       => '28 mars 2026',
                            'tags'       => ['meuble', 'peinture', 'préparation'],
                        ],
                        [
                            'titre'      => '5 idées pour upcycler des palettes en bois',
                            'contenu'    => 'Les palettes en bois sont un matériau de choix pour l\'upcycling. Voici 5 idées originales pour leur donner une seconde vie : table basse, jardinière verticale...',
                            'categorie'  => 'upcycling',
                            'auteur'     => 'Thomas Durand',
                            'role'       => 'Animateur',
                            'date'       => '25 mars 2026',
                            'tags'       => ['palette', 'bois', 'DIY'],
                        ],
                        [
                            'titre'      => 'Réduire ses déchets textiles : les bonnes pratiques',
                            'contenu'    => 'Chaque année, des millions de tonnes de vêtements finissent en décharge. Voici comment agir à votre niveau pour réduire cet impact...',
                            'categorie'  => 'durable',
                            'auteur'     => 'Sophie Martin',
                            'role'       => 'Experte',
                            'date'       => '20 mars 2026',
                            'tags'       => ['textile', 'déchets', 'écologie'],
                        ],
                    ];
                    foreach ($conseils as $conseil):
                    ?>
                        <article class="bg-base-100 rounded-2xl shadow-sm p-6 hover:shadow-md transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-3">
                                        <?php
                                        $catInfo = array_filter($categories, fn($c) => $c['slug'] === $conseil['categorie']);
                                        $catInfo = array_values($catInfo)[0] ?? $categories[0];
                                        ?>
                                        <span class="badge badge-ghost text-xs gap-1">
                                            <i class="fas <?= $catInfo['icon'] ?> <?= $catInfo['color'] ?>"></i>
                                            <?= $catInfo['label'] ?>
                                        </span>
                                    </div>

                                    <h3 class="text-lg font-semibold mb-2 hover:text-primary cursor-pointer">
                                        <?= htmlspecialchars($conseil['titre']) ?>
                                    </h3>

                                    <p class="text-base-content/60 text-sm leading-relaxed line-clamp-2 mb-4">
                                        <?= htmlspecialchars($conseil['contenu']) ?>
                                    </p>

                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <?php foreach ($conseil['tags'] as $tag): ?>
                                            <span class="badge badge-outline badge-sm">#<?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-sm text-base-content/50">
                                            <i class="fas fa-user-circle text-lg"></i>
                                            <span class="font-medium text-base-content/70"><?= htmlspecialchars($conseil['auteur']) ?></span>
                                            <span class="badge badge-sm badge-ghost"><?= htmlspecialchars($conseil['role']) ?></span>
                                            <span>· <?= htmlspecialchars($conseil['date']) ?></span>
                                        </div>

                                        <a href="#" class="btn btn-ghost btn-sm">
                                            Lire la suite <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>

                <div class="flex items-center justify-between">
                    <p class="text-base-content/60 text-sm">Échangez avec la communauté UpcycleConnect</p>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="/UpcycleConnect-PA2526/frontend/public/conseils/forum/create"
                           class="btn btn-neutral btn-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Nouveau sujet
                        </a>
                    <?php endif; ?>
                </div>

                
                <div class="space-y-3">
                    <?php
                    $sujets = $sujets ?? [
                        [
                            'titre'     => 'Où trouver des palettes gratuitement à Paris ?',
                            'auteur'    => 'Jean Dupont',
                            'date'      => '03 avr. 2026',
                            'reponses'  => 12,
                            'vues'      => 148,
                            'categorie' => 'bricolage',
                            'resolu'    => false,
                        ],
                        [
                            'titre'     => 'Quelle peinture utiliser sur du plastique ?',
                            'auteur'    => 'Claire Petit',
                            'date'      => '01 avr. 2026',
                            'reponses'  => 8,
                            'vues'      => 94,
                            'categorie' => 'entretien',
                            'resolu'    => true,
                        ],
                        [
                            'titre'     => 'Comment renforcer une chaise en bois ancienne ?',
                            'auteur'    => 'Paul Moreau',
                            'date'      => '29 mars 2026',
                            'reponses'  => 5,
                            'vues'      => 67,
                            'categorie' => 'bricolage',
                            'resolu'    => false,
                        ],
                        [
                            'titre'     => 'Vos meilleures idées pour recycler des bouteilles en verre ?',
                            'auteur'    => 'Lucie Bernard',
                            'date'      => '27 mars 2026',
                            'reponses'  => 23,
                            'vues'      => 312,
                            'categorie' => 'upcycling',
                            'resolu'    => false,
                        ],
                    ];
                    foreach ($sujets as $sujet):
                        $catInfo = array_filter($categories, fn($c) => $c['slug'] === $sujet['categorie']);
                        $catInfo = array_values($catInfo)[0] ?? $categories[0];
                    ?>
                        <div class="bg-base-100 rounded-2xl shadow-sm p-5 hover:shadow-md transition cursor-pointer">
                            <div class="flex items-center gap-4">

                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <?php if ($sujet['resolu']): ?>
                                            <span class="badge badge-success badge-sm gap-1">
                                                <i class="fas fa-check"></i> Résolu
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge badge-ghost badge-sm gap-1">
                                            <i class="fas <?= $catInfo['icon'] ?> <?= $catInfo['color'] ?>"></i>
                                            <?= $catInfo['label'] ?>
                                        </span>
                                    </div>

                                    <h3 class="font-semibold hover:text-primary transition">
                                        <?= htmlspecialchars($sujet['titre']) ?>
                                    </h3>

                                    <div class="flex items-center gap-3 mt-2 text-xs text-base-content/50">
                                        <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($sujet['auteur']) ?></span>
                                        <span><i class="fas fa-clock mr-1"></i><?= htmlspecialchars($sujet['date']) ?></span>
                                    </div>
                                </div>

                                <div class="flex gap-6 text-center text-sm text-base-content/50 flex-shrink-0">
                                    <div>
                                        <div class="font-semibold text-base-content text-lg"><?= $sujet['reponses'] ?></div>
                                        <div class="text-xs">réponses</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-base-content text-lg"><?= $sujet['vues'] ?></div>
                                        <div class="text-xs">vues</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>