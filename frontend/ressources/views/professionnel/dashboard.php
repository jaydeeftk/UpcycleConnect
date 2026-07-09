<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_space', 'Espace Professionnel') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../components/pro/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_nav_dashboard', 'Tableau de bord') ?></h2>
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
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase <?= $estPremium ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' ?>">
                        <?= $estPremium ? t('nav_badge_premium', 'Premium') : t('nav_badge_freemium', 'Freemium') ?>
                    </span>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-project-diagram text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500"><?= t('pro_stat_projects', 'Projets') ?></p>
                        <p class="text-lg font-bold"><?= count($projets ?? []) ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-pink-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-heart text-2xl text-pink-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500"><?= t('pro_stat_favorites', 'Favoris') ?></p>
                        <p class="text-lg font-bold"><?= count($favoris ?? []) ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-contract text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500"><?= t('pro_stat_contracts', 'Contrats') ?></p>
                        <p class="text-lg font-bold"><?= count($contrats ?? []) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center mb-4 gap-3 flex-wrap">
                    <h3 class="text-lg font-bold"><?= t('pro_projects_title', 'Mes projets upcycling') ?></h3>
                    <div class="flex items-center gap-2">
                        <select id="filtre-origine-projet" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="toutes"><?= t('pro_projects_filter_all', 'Toutes origines') ?></option>
                            <option value="manuel"><?= t('pro_projects_filter_manual', 'Créés manuellement') ?></option>
                            <option value="devis"><?= t('pro_projects_filter_devis', 'Issus d\'un devis') ?></option>
                            <option value="catalogue"><?= t('pro_projects_filter_catalog', 'Prestations catalogue') ?></option>
                        </select>
                        <a href="/professionnel/projets/create" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition text-sm">
                            <i class="fas fa-plus mr-2"></i><?= t('pro_nav_new_project', 'Nouveau projet') ?>
                        </a>
                    </div>
                </div>
                <?php if (empty($projets)): ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-project-diagram text-4xl mb-3 block"></i>
                        <p><?= t('pro_projects_empty', 'Aucun projet pour l\'instant.') ?></p>
                        <a href="/professionnel/projets/create" class="text-blue-500 hover:underline text-sm mt-2 inline-block"><?= t('pro_projects_empty_cta', 'Créer votre premier projet') ?></a>
                    </div>
                <?php else: ?>
                    <div id="grille-projets" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($projets as $projet): ?>
                            <?php
                            $aa = $projet['allowed_actions'] ?? [];
                            $origine = $projet['origine'] ?? 'manuel';
                            $origineUI = [
                                'manuel'    => ['bg-gray-100 text-gray-600', 'fa-pen', t('pro_projects_badge_manual', 'Manuel')],
                                'devis'     => ['bg-purple-100 text-purple-700', 'fa-file-signature', t('pro_projects_badge_devis', 'Devis')],
                                'catalogue' => ['bg-orange-100 text-orange-700', 'fa-store', t('pro_projects_badge_catalog', 'Prestation catalogue')],
                            ][$origine] ?? ['bg-gray-100 text-gray-600', 'fa-pen', $origine];
                            ?>
                            <div class="border border-gray-200 rounded-lg p-4" data-origine="<?= htmlspecialchars($origine) ?>">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-gray-800">
                                            <a href="/professionnel/projets/<?= (int)($projet['id'] ?? 0) ?>" class="hover:underline"><?= htmlspecialchars($projet['titre'] ?? '') ?></a>
                                        </h4>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full <?= $origineUI[0] ?> font-medium whitespace-nowrap">
                                            <i class="fas <?= $origineUI[1] ?> mr-1"></i><?= $origineUI[2] ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars(substr($projet['description'] ?? '', 0, 80)) ?><?= strlen($projet['description'] ?? '') > 80 ? '...' : '' ?></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <?php
                                        $statutColors = ['en_cours' => 'bg-blue-100 text-blue-700', 'termine' => 'bg-green-100 text-green-700', 'pause' => 'bg-yellow-100 text-yellow-700'];
                                        $sc = $statutColors[$projet['statut'] ?? ''] ?? 'bg-gray-100 text-gray-600';
                                        ?>
                                        <span class="text-xs px-2 py-1 rounded-full <?= $sc ?>"><?= formatStatut($projet['statut'] ?? '') ?></span>
                                        <span class="text-xs text-gray-400"><?= $projet['nb_etapes'] ?? 0 ?> <?= t('pro_projects_steps', 'étape(s)') ?></span>
                                    </div>
                                </div>
                                <?php
                                $actionsUI = [
                                    'suspendre' => ['suspendre', t('pro_action_pause', 'Pause'),     'fa-pause',       'text-amber-600 hover:bg-amber-50', false],
                                    'reprendre' => ['reprendre', t('pro_action_resume', 'Reprendre'), 'fa-play',        'text-blue-600 hover:bg-blue-50',  false],
                                    'terminer'  => ['terminer',  t('pro_action_finish', 'Terminer'),  'fa-check',       'text-green-600 hover:bg-green-50', false],
                                    'rouvrir'   => ['rouvrir',   t('pro_action_reopen', 'Rouvrir'),   'fa-rotate-left', 'text-blue-600 hover:bg-blue-50',  false],
                                    'supprimer' => ['delete',    t('pro_action_delete', 'Supprimer'), 'fa-trash',       'text-red-500 hover:bg-red-50',    true],
                                ];
                                $rendus = array_intersect(array_keys($actionsUI), $aa);
                                ?>
                                <?php if (!empty($rendus)): ?>
                                    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
                                        <?php foreach ($rendus as $a): ?>
                                            <?php [$suffix, $label, $icon, $cls, $confirm] = $actionsUI[$a]; ?>
                                            <form method="POST" action="/professionnel/projets/<?= $projet['id'] ?>/<?= $suffix ?>"<?= $confirm ? ' onsubmit="return ucConfirm(this, \'' . htmlspecialchars(t('pro_project_delete_confirm', 'Supprimer ce projet ?'), ENT_QUOTES) . '\')"' : '' ?>>
                                            <?= csrf_field() ?>
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

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-bold mb-4"><?= t('pro_favorites_title', 'Mes annonces favorites') ?></h3>
                <?php if (empty($favoris)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-heart text-3xl mb-2 block"></i>
                        <p class="text-sm"><?= t('pro_favorites_empty', 'Aucune annonce en favoris.') ?></p>
                        <a href="/professionnel/annonces" class="text-blue-500 hover:underline text-sm mt-1 inline-block"><?= t('pro_favorites_browse', 'Parcourir les annonces') ?></a>
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
                                <?= csrf_field() ?>
                                    <button type="submit" class="text-pink-400 hover:text-pink-600">
                                        <i class="fas fa-heart-broken text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold flex items-center gap-2"><i class="fas fa-bell text-emerald-600"></i> <?= t('pro_notifs_title', 'Notifications') ?></h3>
                    <?php if (($notifsNonLues ?? 0) > 0): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700"><?= (int)$notifsNonLues ?> <?= t('pro_notifs_unread', 'non lue') ?><?= $notifsNonLues > 1 ? t('pro_plural_s', 's') : '' ?></span>
                    <?php endif; ?>
                </div>
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-bell-slash text-3xl mb-2 block"></i>
                        <p class="text-sm"><?= t('pro_notifs_empty', 'Aucune notification pour l\'instant.') ?></p>
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
                                    <?= csrf_field() ?>
                                        <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-700 whitespace-nowrap" title="<?= t('pro_notifs_mark_read', 'Marquer comme lue') ?>"><i class="fas fa-check"></i></button>
                                    </form>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <?php $fact = $facturation ?? []; ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold"><?= t('pro_billing_title', 'Facturation cumulée') ?></h3>
                    <span class="text-xs text-gray-500"><?= t('pro_billing_subtitle', 'Abonnements, campagnes publicitaires et commissions prélevées') ?></span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div class="bg-blue-50 rounded-xl p-4">
                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= t('pro_billing_subs', 'Abonnements') ?></div>
                        <div class="text-xl font-bold text-blue-600"><?= htmlspecialchars(formatPrix($fact['total_abonnements'] ?? 0)) ?></div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-4">
                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= t('pro_billing_ads', 'Campagnes pub') ?></div>
                        <div class="text-xl font-bold text-purple-600"><?= htmlspecialchars(formatPrix($fact['total_campagnes'] ?? 0)) ?></div>
                    </div>
                    <a href="/professionnel/commissions" class="bg-orange-50 rounded-xl p-4 block hover:shadow-md transition">
                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= t('pro_billing_commissions', 'Commissions') ?></div>
                        <div class="text-xl font-bold text-orange-600"><?= htmlspecialchars(formatPrix($fact['total_commissions'] ?? 0)) ?></div>
                        <div class="text-[10px] text-orange-500 mt-1"><?= t('pro_billing_commissions_link', 'Voir le détail') ?> <i class="fas fa-arrow-right ml-1"></i></div>
                    </a>
                    <div class="bg-emerald-50 rounded-xl p-4 border-2 border-emerald-200">
                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= t('pro_billing_total', 'Total cumulé') ?></div>
                        <div class="text-xl font-bold text-emerald-700"><?= htmlspecialchars(formatPrix($fact['total_general'] ?? 0)) ?></div>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    <?= t('pro_billing_note', 'Contrats actifs : ') ?><?= (int)($fact['nb_contrats_actifs'] ?? 0) ?>
                    <?= t('pro_billing_resilies', ' · Résiliés : ') ?><?= (int)($fact['nb_contrats_resilie'] ?? 0) ?>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4"><?= t('pro_contracts_title', 'Mes contrats') ?></h3>
                <?php if (empty($contrats)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-file-contract text-3xl mb-2 block"></i>
                        <p class="text-sm"><?= t('pro_contracts_empty', 'Aucun contrat pour l\'instant.') ?></p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b">
                                    <th class="pb-3"><?= t('pro_contracts_col_type', 'Type') ?></th>
                                    <th class="pb-3 text-right"><?= t('pro_contracts_col_amount', 'Montant') ?></th>
                                    <th class="pb-3"><?= t('pro_contracts_col_freq', 'Fréquence') ?></th>
                                    <th class="pb-3"><?= t('pro_contracts_col_status', 'Statut') ?></th>
                                    <th class="pb-3"><?= t('pro_contracts_col_start', 'Début') ?></th>
                                    <th class="pb-3"><?= t('pro_contracts_col_end', 'Fin') ?></th>
                                    <th class="pb-3 text-right"><?= t('pro_contracts_col_actions', 'Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contrats as $contrat): ?>
                                    <?php $cStatut = strtolower($contrat['statut'] ?? ''); $cCol = statutCouleur($cStatut); ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3"><?= htmlspecialchars($contrat['type'] ?? '') ?></td>
                                        <td class="py-3 text-right font-semibold"><?= htmlspecialchars(formatPrix($contrat['montant'] ?? 0)) ?></td>
                                        <td class="py-3 text-xs text-gray-600"><?= htmlspecialchars(formatStatut($contrat['frequence'] ?? '')) ?></td>
                                        <td class="py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold" style="background:<?= $cCol ?>1a;color:<?= $cCol ?>"><?= formatStatut($contrat['statut'] ?? '') ?></span></td>
                                        <td class="py-3"><?= formatDate($contrat['date_debut'] ?? '') ?></td>
                                        <td class="py-3"><?= formatDate($contrat['date_fin'] ?? '') ?></td>
                                        <td class="py-3 text-right">
                                            <a href="/professionnel/contrats/<?= $contrat['id'] ?? '' ?>/pdf" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-white hover:bg-blue-600 border border-blue-200 rounded-lg px-3 py-1.5 transition-colors mr-2 inline-block">
                                                <i class="fas fa-file-pdf mr-1"></i><?= t('pro_contract_pdf', 'PDF') ?>
                                            </a>
                                            <?php if (in_array($cStatut, ['actif', 'suspendu'])): ?>
                                                <form method="POST" action="/professionnel/contrats/<?= $contrat['id'] ?? '' ?>/resilier" class="inline" onsubmit="return ucConfirm(this, '<?= htmlspecialchars(t('pro_contract_resilier_confirm', 'Résilier ce contrat ? Cette action est définitive.'), ENT_QUOTES) ?>');">
                                                <?= csrf_field() ?>
                                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 rounded-lg px-3 py-1.5 transition-colors">
                                                        <i class="fas fa-ban mr-1"></i><?= t('pro_contract_resilier', 'Résilier') ?>
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
    function confirmer(m,c){var d=document.documentElement.classList.contains('dark');var s=d?'#1e293b':'#fff',t=d?'#f1f5f9':'#0f172a',b=d?'#334155':'#e2e8f0',u=d?'#94a3b8':'#64748b';var o=document.createElement('div');o.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;display:flex;align-items:center;justify-content:center';o.innerHTML='<div style="background:'+s+';border:1px solid '+b+';border-radius:12px;padding:24px;max-width:360px;width:90%;text-align:center;font-family:inherit"><p style="color:'+t+';margin:0 0 20px;font-size:15px">'+m+'</p><button type="button" id="uc-c" style="margin-right:8px;padding:8px 20px;border:1px solid '+b+';border-radius:8px;background:transparent;color:'+u+';cursor:pointer"><?= htmlspecialchars(t('pro_modal_cancel', 'Annuler'), ENT_QUOTES) ?></button><button type="button" id="uc-o" style="padding:8px 20px;border:none;border-radius:8px;background:#ef4444;color:#fff;cursor:pointer"><?= htmlspecialchars(t('pro_modal_confirm', 'Confirmer'), ENT_QUOTES) ?></button></div>';document.body.appendChild(o);o.querySelector('#uc-c').onclick=function(){o.remove()};o.querySelector('#uc-o').onclick=function(){o.remove();c()};o.addEventListener('click',function(e){if(e.target===o)o.remove()})}
    function ucConfirm(el,m){confirmer(m,function(){if(el.tagName==='A'){window.location.href=el.href}else{var f=el.closest?el.closest('form'):null;if(f)f.submit()}});return false}

    var selectOrigine = document.getElementById('filtre-origine-projet');
    if (selectOrigine) {
        selectOrigine.addEventListener('change', function () {
            var val = selectOrigine.value;
            document.querySelectorAll('#grille-projets > [data-origine]').forEach(function (card) {
                card.style.display = (val === 'toutes' || card.getAttribute('data-origine') === val) ? '' : 'none';
            });
        });
    }
    </script>
</body>
</html>
