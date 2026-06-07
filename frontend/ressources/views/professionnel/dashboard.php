<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Professionnel - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-xl font-bold text-green-400">UpcycleConnect</h1>
            <p class="text-xs text-gray-400 mt-1">Espace Professionnel</p>
        </div>
        <nav class="flex-1 p-4">
            <ul class="space-y-1">
                <li>
                    <a href="/professionnel" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-gray-700 text-white">
                        <i class="fas fa-tachometer-alt w-5"></i><span>Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/recuperation" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-recycle w-5"></i><span>Récupération</span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/projets/create" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-project-diagram w-5"></i><span>Nouveau projet</span>
                    </a>
                </li>
                <li>
                    <a href="/annonces" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-bullhorn w-5"></i><span>Annonces</span>
                    </a>
                </li>
                <li>
                    <a href="/catalogue/services" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-tools w-5"></i><span>Services</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <a href="/logout" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
                <i class="fas fa-sign-out-alt w-5"></i><span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Tableau de bord</h2>
                    <p class="text-gray-600 text-sm"><?= htmlspecialchars($profil['nom_entreprise'] ?? ($_SESSION['user']['prenom'] ?? '')) ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'P', 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($profil['type'] ?? 'Professionnel') ?></p>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-project-diagram text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Projets</p>
                        <p class="text-lg font-bold"><?= count($projets ?? []) ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-pink-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-heart text-2xl text-pink-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Favoris</p>
                        <p class="text-lg font-bold"><?= count($favoris ?? []) ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-contract text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contrats</p>
                        <p class="text-lg font-bold"><?= count($contrats ?? []) ?></p>
                    </div>
                </div>
            </div>

            <!-- Impact écologique (PR7/PR8) -->
            <?php
                $imObjets = (int)($impact['objets_recuperes'] ?? 0);
                $imPoids  = (float)($impact['poids_total_kg'] ?? 0);
                $imCo2    = (float)($impact['co2_estime_kg'] ?? 0);
                $imCo2f   = (float)($impact['co2_facteur'] ?? 0);
                $imProjT  = (int)($impact['projets_termines'] ?? 0);
                $imProjN  = (int)($impact['projets_total'] ?? 0);
                $imMat    = $impact['materiaux'] ?? [];
                $imMax    = 0; foreach ($imMat as $m) { $imMax = max($imMax, (int)($m['nombre'] ?? 0)); }
            ?>
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                    <h3 class="text-lg font-bold flex items-center gap-2"><i class="fas fa-leaf text-emerald-600"></i> Impact écologique</h3>
                    <a href="/professionnel/impact/pdf" target="_blank" class="inline-flex items-center gap-2 bg-emerald-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-emerald-700 transition">
                        <i class="fas fa-file-pdf"></i> Télécharger le bilan PDF
                    </a>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="rounded-lg border border-gray-100 p-4 text-center">
                        <p class="text-2xl font-extrabold text-emerald-600"><?= $imObjets ?></p>
                        <p class="text-xs text-gray-500 mt-1">Objets valorisés</p>
                    </div>
                    <div class="rounded-lg border border-gray-100 p-4 text-center">
                        <p class="text-2xl font-extrabold text-emerald-600"><?= number_format($imPoids, 1, ',', ' ') ?> kg</p>
                        <p class="text-xs text-gray-500 mt-1">Poids détourné</p>
                    </div>
                    <div class="rounded-lg border border-gray-100 p-4 text-center">
                        <p class="text-2xl font-extrabold text-emerald-600">~<?= number_format($imCo2, 1, ',', ' ') ?> kg</p>
                        <p class="text-xs text-gray-500 mt-1">CO₂ évité <span class="text-gray-300">(est.)</span></p>
                    </div>
                    <div class="rounded-lg border border-gray-100 p-4 text-center">
                        <p class="text-2xl font-extrabold text-emerald-600"><?= $imProjT ?>/<?= $imProjN ?></p>
                        <p class="text-xs text-gray-500 mt-1">Projets réalisés</p>
                    </div>
                </div>

                <h4 class="text-sm font-bold text-gray-700 mb-3">Répartition par matériau</h4>
                <?php if (empty($imMat)): ?>
                    <p class="text-sm text-gray-400 italic">Aucun objet récupéré pour le moment.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($imMat as $m):
                            $mn  = (int)($m['nombre'] ?? 0);
                            $pct = $imMax > 0 ? round($mn / $imMax * 100) : 0;
                        ?>
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span class="font-medium"><?= htmlspecialchars($m['type'] ?? 'Autre') ?></span>
                                <span><?= $mn ?> objet<?= $mn > 1 ? 's' : '' ?> · <?= number_format((float)($m['poids_kg'] ?? 0), 1, ',', ' ') ?> kg</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-emerald-500 h-2.5 rounded-full" style="width: <?= max($pct, 6) ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-4 italic">CO₂ évité : estimation indicative (<?= number_format($imCo2f, 1, ',', ' ') ?> kg CO₂ par kg valorisé), non une mesure certifiée.</p>
                <?php endif; ?>
            </div>

            <!-- Projets -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Mes projets upcycling</h3>
                    <a href="/professionnel/projets/create" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition text-sm">
                        <i class="fas fa-plus mr-2"></i>Nouveau projet
                    </a>
                </div>
                <?php if (empty($projets)): ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-project-diagram text-4xl mb-3 block"></i>
                        <p>Aucun projet pour l'instant.</p>
                        <a href="/professionnel/projets/create" class="text-blue-500 hover:underline text-sm mt-2 inline-block">Créer votre premier projet</a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($projets as $projet): ?>
                            <?php $aa = $projet['allowed_actions'] ?? []; ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div>
                                    <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($projet['titre'] ?? '') ?></h4>
                                    <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars(substr($projet['description'] ?? '', 0, 80)) ?><?= strlen($projet['description'] ?? '') > 80 ? '...' : '' ?></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <?php
                                        $statutColors = ['en_cours' => 'bg-blue-100 text-blue-700', 'termine' => 'bg-green-100 text-green-700', 'pause' => 'bg-yellow-100 text-yellow-700'];
                                        $sc = $statutColors[$projet['statut'] ?? ''] ?? 'bg-gray-100 text-gray-600';
                                        ?>
                                        <span class="text-xs px-2 py-1 rounded-full <?= $sc ?>"><?= formatStatut($projet['statut'] ?? '') ?></span>
                                        <span class="text-xs text-gray-400"><?= $projet['nb_etapes'] ?? 0 ?> étape(s)</span>
                                    </div>
                                </div>
                                <?php
                                $actionsUI = [
                                    'suspendre' => ['suspendre', 'Pause',     'fa-pause',       'text-amber-600 hover:bg-amber-50', false],
                                    'reprendre' => ['reprendre', 'Reprendre', 'fa-play',        'text-blue-600 hover:bg-blue-50',  false],
                                    'terminer'  => ['terminer',  'Terminer',  'fa-check',       'text-green-600 hover:bg-green-50', false],
                                    'rouvrir'   => ['rouvrir',   'Rouvrir',   'fa-rotate-left', 'text-blue-600 hover:bg-blue-50',  false],
                                    'supprimer' => ['delete',    'Supprimer', 'fa-trash',       'text-red-500 hover:bg-red-50',    true],
                                ];
                                $rendus = array_intersect(array_keys($actionsUI), $aa);
                                ?>
                                <?php if (!empty($rendus)): ?>
                                    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
                                        <?php foreach ($rendus as $a): ?>
                                            <?php [$suffix, $label, $icon, $cls, $confirm] = $actionsUI[$a]; ?>
                                            <form method="POST" action="/professionnel/projets/<?= $projet['id'] ?>/<?= $suffix ?>"<?= $confirm ? ' onsubmit="return ucConfirm(this, \'Supprimer ce projet ?\')"' : '' ?>>
                                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $cls ?> transition">
                                                    <i class="fas <?= $icon ?> mr-1"></i><?= $label ?>
                                                </button>
                                            </form>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Favoris -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-bold mb-4">Mes annonces favorites</h3>
                <?php if (empty($favoris)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-heart text-3xl mb-2 block"></i>
                        <p class="text-sm">Aucune annonce en favoris.</p>
                        <a href="/annonces" class="text-blue-500 hover:underline text-sm mt-1 inline-block">Parcourir les annonces</a>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($favoris as $favori): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <a href="/annonces/<?= $favori['id'] ?>" class="font-medium text-gray-800 hover:text-blue-600"><?= htmlspecialchars($favori['titre'] ?? '') ?></a>
                                    <p class="text-xs text-gray-400"><?= formatDate($favori['date'] ?? '') ?></p>
                                </div>
                                <form method="POST" action="/professionnel/favoris/<?= $favori['id'] ?>/remove">
                                    <button type="submit" class="text-pink-400 hover:text-pink-600">
                                        <i class="fas fa-heart-broken text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Notifications -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold flex items-center gap-2"><i class="fas fa-bell text-emerald-600"></i> Notifications</h3>
                    <?php if (($notifsNonLues ?? 0) > 0): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700"><?= (int)$notifsNonLues ?> non lue<?= $notifsNonLues > 1 ? 's' : '' ?></span>
                    <?php endif; ?>
                </div>
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-bell-slash text-3xl mb-2 block"></i>
                        <p class="text-sm">Aucune notification pour l'instant.</p>
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-gray-100">
                        <?php foreach ($notifications as $notif): ?>
                            <li class="py-3 flex items-start gap-3 <?= empty($notif['lu']) ? '' : 'opacity-60' ?>">
                                <span class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0 <?= empty($notif['lu']) ? 'bg-emerald-500' : 'bg-gray-300' ?>"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-800 <?= empty($notif['lu']) ? 'font-medium' : '' ?>"><?= htmlspecialchars($notif['contenu'] ?? '') ?></p>
                                    <p class="text-xs text-gray-400 mt-0.5"><?= formatDate($notif['date_envoi'] ?? '') ?></p>
                                </div>
                                <?php if (empty($notif['lu'])): ?>
                                    <form method="POST" action="/professionnel/notifications/<?= $notif['id'] ?? '' ?>/lu" class="inline">
                                        <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-700 whitespace-nowrap" title="Marquer comme lue"><i class="fas fa-check"></i></button>
                                    </form>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Contrats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">Mes contrats</h3>
                <?php if (empty($contrats)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-file-contract text-3xl mb-2 block"></i>
                        <p class="text-sm">Aucun contrat pour l'instant.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b">
                                    <th class="pb-3">Type</th>
                                    <th class="pb-3">Statut</th>
                                    <th class="pb-3">Début</th>
                                    <th class="pb-3">Fin</th>
                                    <th class="pb-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contrats as $contrat): ?>
                                    <?php $cStatut = strtolower($contrat['statut'] ?? ''); $cCol = statutCouleur($cStatut); ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3"><?= htmlspecialchars($contrat['type'] ?? '') ?></td>
                                        <td class="py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold" style="background:<?= $cCol ?>1a;color:<?= $cCol ?>"><?= formatStatut($contrat['statut'] ?? '') ?></span></td>
                                        <td class="py-3"><?= formatDate($contrat['date_debut'] ?? '') ?></td>
                                        <td class="py-3"><?= formatDate($contrat['date_fin'] ?? '') ?></td>
                                        <td class="py-3 text-right">
                                            <?php if (in_array($cStatut, ['actif', 'suspendu'])): ?>
                                                <form method="POST" action="/professionnel/contrats/<?= $contrat['id'] ?? '' ?>/resilier" class="inline" onsubmit="return ucConfirm(this, 'Résilier ce contrat ? Cette action est définitive.');">
                                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 rounded-lg px-3 py-1.5 transition-colors">
                                                        <i class="fas fa-ban mr-1"></i>Résilier
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>
    <script>
    function confirmer(m,c){var d=document.documentElement.classList.contains('dark');var s=d?'#1e293b':'#fff',t=d?'#f1f5f9':'#0f172a',b=d?'#334155':'#e2e8f0',u=d?'#94a3b8':'#64748b';var o=document.createElement('div');o.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;display:flex;align-items:center;justify-content:center';o.innerHTML='<div style="background:'+s+';border:1px solid '+b+';border-radius:12px;padding:24px;max-width:360px;width:90%;text-align:center;font-family:inherit"><p style="color:'+t+';margin:0 0 20px;font-size:15px">'+m+'</p><button type="button" id="uc-c" style="margin-right:8px;padding:8px 20px;border:1px solid '+b+';border-radius:8px;background:transparent;color:'+u+';cursor:pointer">Annuler</button><button type="button" id="uc-o" style="padding:8px 20px;border:none;border-radius:8px;background:#ef4444;color:#fff;cursor:pointer">Confirmer</button></div>';document.body.appendChild(o);o.querySelector('#uc-c').onclick=function(){o.remove()};o.querySelector('#uc-o').onclick=function(){o.remove();c()};o.addEventListener('click',function(e){if(e.target===o)o.remove()})}
    function ucConfirm(el,m){confirmer(m,function(){if(el.tagName==='A'){window.location.href=el.href}else{var f=el.closest?el.closest('form'):null;if(f)f.submit()}});return false}
    </script>
</body>
</html>
