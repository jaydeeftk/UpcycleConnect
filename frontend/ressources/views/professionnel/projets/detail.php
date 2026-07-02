<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($projet['titre'] ?? 'Projet') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-xl font-bold text-green-400">UpcycleConnect</h1>
            <p class="text-xs text-gray-400 mt-1"><?= t('pro_space', 'Espace Professionnel') ?></p>
        </div>
        <nav class="flex-1 p-4">
            <ul class="space-y-1">
                <li>
                    <a href="/professionnel" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-gray-700 text-white">
                        <i class="fas fa-tachometer-alt w-5"></i><span><?= t('pro_nav_dashboard', 'Tableau de bord') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/recuperation" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-recycle w-5"></i><span><?= t('pro_nav_recuperation', 'Récupération') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/projets/create" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-project-diagram w-5"></i><span><?= t('pro_nav_new_project', 'Nouveau projet') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/annonces" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-bullhorn w-5"></i><span><?= t('pro_nav_annonces', 'Annonces') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/catalogue/services" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-tools w-5"></i><span><?= t('pro_nav_services', 'Services') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/abonnement" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-crown w-5"></i><span><?= t('pro_nav_abonnement', 'Abonnement Premium') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/publicites" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-ad w-5"></i><span><?= t('pro_nav_publicites', 'Campagnes publicitaires') ?></span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <a href="/logout" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
                <i class="fas fa-sign-out-alt w-5"></i><span><?= t('pro_nav_logout', 'Déconnexion') ?></span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <a href="/professionnel" class="text-sm text-blue-500 hover:underline mb-1 inline-block">
                        <i class="fas fa-arrow-left mr-1"></i> <?= t('proprj_back', 'Retour au tableau de bord') ?>
                    </a>
                    <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($projet['titre'] ?? '') ?></h2>
                </div>
                <?php
                $statutColors = [
                    'en_cours' => 'bg-blue-100 text-blue-800',
                    'pause'    => 'bg-yellow-100 text-yellow-800',
                    'termine'  => 'bg-green-100 text-green-800',
                ];
                $statutColor = $statutColors[$projet['statut'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="px-3 py-1 rounded-full text-sm font-medium <?= $statutColor ?>">
                    <?= htmlspecialchars(formatStatut($projet['statut'] ?? '')) ?>
                </span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-4xl mx-auto space-y-6">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Edition projet -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-1"><?= t('proprj_edit_title', 'Informations du projet') ?></h3>
                <div class="text-sm text-gray-500 mb-6">
                    <?php if (!empty($projet['date_debut'])): ?>
                        <?= t('proprj_since', 'Depuis le') ?> <?= htmlspecialchars(formatDate($projet['date_debut'])) ?>
                    <?php endif; ?>
                </div>
                <form method="POST" action="/professionnel/projets/<?= (int)$projet['id'] ?>/update" class="space-y-4">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_field_title', 'Titre') ?> *</label>
                        <input type="text" name="titre" required maxlength="150" value="<?= htmlspecialchars($projet['titre'] ?? '') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_field_desc', 'Description') ?></label>
                        <textarea name="description" rows="4" maxlength="2000"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($projet['description'] ?? '') ?></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm font-semibold">
                            <i class="fas fa-save mr-2"></i><?= t('proprj_save', 'Enregistrer les modifications') ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Etapes -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-1"><?= t('proprj_steps_title', 'Étapes du projet') ?></h3>
                <p class="text-sm text-gray-500 mb-6">
                    <?= count($etapes ?? []) ?> <?= t('proprj_steps_count', 'étape(s) enregistrée(s)') ?>
                </p>

                <?php if (empty($etapes)): ?>
                    <div class="text-center py-10 text-gray-400 mb-6 bg-gray-50 rounded-lg">
                        <i class="fas fa-images text-4xl mb-3 block"></i>
                        <p class="text-sm"><?= t('proprj_steps_empty', "Aucune étape pour l'instant. Ajoutez la première avec ses photos avant / après.") ?></p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4 mb-8">
                        <?php foreach ($etapes as $i => $etape): ?>
                            <?php
                            $photoAvant = null; $photoApres = null;
                            foreach (($etape['photos'] ?? []) as $ph) {
                                if (($ph['type_photo'] ?? '') === 'avant') $photoAvant = $ph;
                                if (($ph['type_photo'] ?? '') === 'apres') $photoApres = $ph;
                            }
                            ?>
                            <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-start gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                                            <?= $i + 1 ?>
                                        </span>
                                        <div>
                                            <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($etape['nom'] ?? '—') ?></h4>
                                            <?php if (!empty($etape['description'])): ?>
                                                <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($etape['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <form method="POST" action="/professionnel/projets/<?= (int)$projet['id'] ?>/etapes/<?= (int)$etape['id'] ?>/delete"
                                          onsubmit="return ucConfirm(this, '<?= t('proprj_step_confirm_delete', 'Supprimer cette étape ?') ?>')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                                <div class="grid grid-cols-2 gap-4 ml-11">
                                    <div>
                                        <div class="text-xs text-gray-500 mb-1 uppercase tracking-wide font-medium"><?= t('proprj_before', 'Avant') ?></div>
                                        <?php if ($photoAvant): ?>
                                            <img src="<?= htmlspecialchars($photoAvant['url']) ?>" alt="Avant" class="w-full aspect-video object-cover rounded-lg border border-gray-200">
                                        <?php else: ?>
                                            <div class="w-full aspect-video bg-gray-50 rounded-lg border border-dashed border-gray-200 flex items-center justify-center text-gray-400 text-xs"><?= t('proprj_no_photo', 'Pas de photo') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500 mb-1 uppercase tracking-wide font-medium"><?= t('proprj_after', 'Après') ?></div>
                                        <?php if ($photoApres): ?>
                                            <img src="<?= htmlspecialchars($photoApres['url']) ?>" alt="Après" class="w-full aspect-video object-cover rounded-lg border border-gray-200">
                                        <?php else: ?>
                                            <div class="w-full aspect-video bg-gray-50 rounded-lg border border-dashed border-gray-200 flex items-center justify-center text-gray-400 text-xs"><?= t('proprj_no_photo', 'Pas de photo') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Form ajout étape -->
                <form method="POST" action="/professionnel/projets/<?= (int)$projet['id'] ?>/etapes" enctype="multipart/form-data" class="space-y-4 border-t border-gray-200 pt-6">
                    <?= csrf_field() ?>
                    <h4 class="font-semibold text-sm uppercase tracking-wide text-gray-500"><?= t('proprj_step_add', 'Ajouter une étape') ?></h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_step_name', 'Nom') ?> *</label>
                        <input type="text" name="nom" required maxlength="100"
                               placeholder="<?= t('proprj_step_name_ph', 'Ex : Ponçage et préparation du bois') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_step_desc', 'Description') ?></label>
                        <textarea name="description" rows="2" maxlength="255"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_step_photo_before', 'Photo « avant »') ?></label>
                            <input type="file" name="photo_avant" accept="image/jpeg,image/png,image/webp,image/gif"
                                   class="block w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_step_photo_after', 'Photo « après »') ?></label>
                            <input type="file" name="photo_apres" accept="image/jpeg,image/png,image/webp,image/gif"
                                   class="block w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 text-sm font-semibold">
                            <i class="fas fa-plus mr-2"></i><?= t('proprj_step_add_btn', "Ajouter l'étape") ?>
                        </button>
                    </div>
                </form>
            </div>

        </div>
        </main>
    </div>
</div>

<script>
function confirmer(m,c){var d=document.documentElement.classList.contains('dark');var s=d?'#1e293b':'#fff',t=d?'#f1f5f9':'#0f172a',b=d?'#334155':'#e2e8f0',u=d?'#94a3b8':'#64748b';var o=document.createElement('div');o.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;display:flex;align-items:center;justify-content:center';o.innerHTML='<div style="background:'+s+';border:1px solid '+b+';border-radius:12px;padding:24px;max-width:360px;width:90%;text-align:center;font-family:inherit"><p style="color:'+t+';margin:0 0 20px;font-size:15px">'+m+'</p><button type="button" id="uc-c" style="margin-right:8px;padding:8px 20px;border:1px solid '+b+';border-radius:8px;background:transparent;color:'+u+';cursor:pointer"><?= htmlspecialchars(t('pro_modal_cancel', 'Annuler'), ENT_QUOTES) ?></button><button type="button" id="uc-o" style="padding:8px 20px;border:none;border-radius:8px;background:#ef4444;color:#fff;cursor:pointer"><?= htmlspecialchars(t('pro_modal_confirm', 'Confirmer'), ENT_QUOTES) ?></button></div>';document.body.appendChild(o);o.querySelector('#uc-c').onclick=function(){o.remove()};o.querySelector('#uc-o').onclick=function(){o.remove();c()};o.addEventListener('click',function(e){if(e.target===o)o.remove()})}
function ucConfirm(el,m){confirmer(m,function(){if(el.tagName==='A'){window.location.href=el.href}else{var f=el.closest?el.closest('form'):null;if(f)f.submit()}});return false}
</script>
</body>
</html>
