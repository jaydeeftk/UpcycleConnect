<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['success']) ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['error']) ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-lightbulb text-green-600"></i>
            </div>
            <span class="text-sm font-medium text-green-600 uppercase tracking-wide"><?= t('considx_eyebrow', 'Espace Conseils') ?></span>
        </div>
        <h1 class="text-3xl font-bold"><?= t('considx_title', 'Conseils & Forum communautaire') ?></h1>
        <p class="text-base-content/60 mt-2">
            <?= t('considx_subtitle', 'Retrouvez les conseils de nos experts et échangez avec la communauté UpcycleConnect.') ?>
        </p>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">

        <aside class="lg:col-span-1">
            <div class="bg-base-100 rounded-2xl shadow-sm p-5 sticky top-24">
                <h2 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4"><?= t('considx_categories', 'Catégories') ?></h2>
                <ul class="space-y-1">
                    <?php
                    $categories = [
                        ['slug' => 'tous',            'label' => 'Tous',                      'icon' => 'fa-th-large',     'color' => 'text-base-content'],
                        ['slug' => 'general',         'label' => 'Général',                   'icon' => 'fa-comment',      'color' => 'text-slate-500'],
                        ['slug' => 'recyclage',       'label' => 'Recyclage',                 'icon' => 'fa-recycle',      'color' => 'text-green-500'],
                        ['slug' => 'entretien',       'label' => 'Entretien des matériaux',   'icon' => 'fa-tools',        'color' => 'text-yellow-500'],
                        ['slug' => 'upcycling',       'label' => 'Upcycling créatif',         'icon' => 'fa-paint-brush',  'color' => 'text-purple-500'],
                        ['slug' => 'durable',         'label' => 'Développement durable',     'icon' => 'fa-leaf',         'color' => 'text-emerald-500'],
                        ['slug' => 'bricolage',       'label' => 'Bricolage & Réparation',    'icon' => 'fa-wrench',       'color' => 'text-orange-500'],
                        ['slug' => 'bonnes-pratiques','label' => 'Bonnes pratiques',          'icon' => 'fa-check-circle', 'color' => 'text-blue-500'],
                    ];

                    $categorieActive = $onglet === 'forum' ? $catForum : $catConseil;
                    foreach ($categories as $cat):
                    ?>
                        <li>
                            <a href="?onglet=<?= $onglet ?>&cat_conseil=<?= $onglet === 'conseils' ? $cat['slug'] : $catConseil ?>&cat_forum=<?= $onglet === 'forum' ? $cat['slug'] : $catForum ?>"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition <?= $categorieActive === $cat['slug'] ? 'bg-base-200 font-semibold' : 'hover:bg-base-200' ?>">
                                <i class="fas <?= $cat['icon'] ?> <?= $cat['color'] ?> w-4"></i>
                                <?= $cat['label'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (isset($_SESSION['user'])): ?>
                    <div class="border-t border-base-300 mt-5 pt-5">
                        <a href="/conseils/forum/create"
                           class="btn btn-neutral btn-sm w-full">
                            <i class="fas fa-plus mr-2"></i>
                            <?= t('considx_new_topic', 'Nouveau sujet') ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <div class="lg:col-span-3 space-y-8">

            <div class="tabs tabs-boxed bg-base-100 p-1 rounded-2xl shadow-sm w-fit">
                <a href="?onglet=conseils&cat_conseil=<?= $catConseil ?>&cat_forum=<?= $catForum ?>"
                   class="tab <?= $onglet === 'conseils' ? 'tab-active' : '' ?>">
                    <i class="fas fa-lightbulb mr-2"></i> <?= t('considx_tab_advice', 'Conseils') ?>
                </a>
                <a href="?onglet=forum&cat_conseil=<?= $catConseil ?>&cat_forum=<?= $catForum ?>"
                   class="tab <?= $onglet === 'forum' ? 'tab-active' : '' ?>">
                    <i class="fas fa-comments mr-2"></i> <?= t('considx_tab_forum', 'Forum') ?>
                </a>
            </div>

            <?php if ($onglet === 'conseils'): ?>

                <?php if (empty($conseils)): ?>
                    <div class="text-center py-16 text-base-content/40">
                        <i class="fas fa-lightbulb text-4xl mb-3 block"></i>
                        <p><?= t('considx_empty_advice', 'Aucun conseil disponible pour le moment.') ?></p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($conseils as $conseil):
                            $catInfo = array_values(array_filter($categories, fn($c) => $c['slug'] === $conseil['categorie']))[0] ?? $categories[1];
                        ?>
                            <article class="bg-base-100 rounded-2xl shadow-sm p-6 hover:shadow-md transition">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="badge badge-ghost text-xs gap-1">
                                                <i class="fas <?= $catInfo['icon'] ?> <?= $catInfo['color'] ?>"></i>
                                                <?= $catInfo['label'] ?>
                                            </span>
                                        </div>

                                        <h3 class="text-lg font-semibold mb-2">
                                            <?= htmlspecialchars($conseil['titre']) ?>
                                        </h3>

                                        <p class="text-base-content/60 text-sm leading-relaxed line-clamp-2 mb-4">
                                            <?= htmlspecialchars($conseil['contenu']) ?>
                                        </p>

                                        <?php if (!empty($conseil['tags'])): ?>
                                            <div class="flex flex-wrap gap-2 mb-4">
                                                <?php foreach (explode(',', $conseil['tags']) as $tag): ?>
                                                    <?php if (trim($tag)): ?>
                                                        <span class="badge badge-outline badge-sm">#<?= htmlspecialchars(trim($tag)) ?></span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2 text-sm text-base-content/50">
                                                <i class="fas fa-user-circle text-lg"></i>
                                                <span class="font-medium text-base-content/70"><?= htmlspecialchars($conseil['auteur'] ?? '') ?></span>
                                                <span class="badge badge-sm badge-ghost"><?= htmlspecialchars($conseil['role'] ?? '') ?></span>
                                                <span>· <?= formatDate($conseil['date'] ?? '') ?></span>
                                            </div>
                                            <a href="/conseils/<?= $conseil['id'] ?>" class="btn btn-ghost btn-sm">
                                                <?= t('considx_read_more', 'Lire la suite') ?> <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>

                <div class="flex items-center justify-between">
                    <p class="text-base-content/60 text-sm"><?= t('considx_forum_intro', 'Échangez avec la communauté UpcycleConnect') ?></p>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="/conseils/forum/create"
                           class="btn btn-neutral btn-sm">
                            <i class="fas fa-plus mr-2"></i>
                            <?= t('considx_new_topic', 'Nouveau sujet') ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($sujets)): ?>
                    <div class="text-center py-16 text-base-content/40">
                        <i class="fas fa-comments text-4xl mb-3 block"></i>
                        <p><?= t('considx_empty_forum', 'Aucun sujet pour le moment. Soyez le premier à poster !') ?></p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($sujets as $sujet):
                            $catInfo = array_values(array_filter($categories, fn($c) => $c['slug'] === $sujet['categorie']))[0] ?? $categories[1];
                        ?>
                            <a href="/conseils/forum/<?= $sujet['id'] ?>"
                               class="block bg-base-100 rounded-2xl shadow-sm p-5 hover:shadow-md transition">
                                <div class="flex items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <?php if ($sujet['resolu'] ?? false): ?>
                                                <span class="badge badge-success badge-sm gap-1">
                                                    <i class="fas fa-check"></i> <?= t('considx_resolved', 'Résolu') ?>
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
                                            <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($sujet['auteur'] ?? '') ?></span>
                                            <span><i class="fas fa-clock mr-1"></i><?= formatDate($sujet['date'] ?? '') ?></span>
                                        </div>
                                    </div>

                                    <div class="flex gap-6 text-center text-sm text-base-content/50 flex-shrink-0">
                                        <div>
                                            <div class="font-semibold text-base-content text-lg"><?= $sujet['nb_reponses'] ?? 0 ?></div>
                                            <div class="text-xs"><?= t('considx_replies', 'réponses') ?></div>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-base-content text-lg"><?= $sujet['vues'] ?? 0 ?></div>
                                            <div class="text-xs"><?= t('considx_views', 'vues') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</section>