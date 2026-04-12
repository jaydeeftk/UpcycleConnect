<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Modération des Annonces</h2>
        <p class="text-slate-500">Traitez les publications en attente</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold">Titre & Détails</th>
                <th class="p-4 font-semibold">Auteur</th>
                <th class="p-4 font-semibold">Statut</th>
                <th class="p-4 font-semibold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($annonces)) { ?>
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-400 italic">Aucune annonce trouvée.</td>
                </tr>
            <?php } else { ?>
                <?php foreach ($annonces as $annonce) { 
                    $statut = strtolower($annonce['statut'] ?? 'en attente');
                    $badgeColor = 'slate';
                    if ($statut === 'validee' || $statut === 'actif') $badgeColor = 'emerald';
                    if ($statut === 'rejetee' || $statut === 'inactif') $badgeColor = 'rose';
                    if ($statut === 'en attente') $badgeColor = 'amber';
                ?>
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="p-4">
                        <p class="font-bold text-slate-800"><?= htmlspecialchars($annonce['titre'] ?? 'Sans titre') ?></p>
                        <p class="text-sm text-slate-500 truncate max-w-md"><?= htmlspecialchars($annonce['description'] ?? '') ?></p>
                        <div class="flex gap-2 mt-2">
                            <span class="text-xs text-slate-400"><i class="fas fa-tag mr-1"></i><?= htmlspecialchars($annonce['categorie'] ?? 'Général') ?></span>
                            <span class="text-xs text-slate-400"><i class="fas fa-euro-sign mr-1"></i><?= number_format($annonce['prix'] ?? 0, 2) ?></span>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="text-sm font-medium text-slate-700">Utilisateur #<?= htmlspecialchars($annonce['id_particuliers'] ?? '?') ?></span>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-<?= $badgeColor ?>-50 text-<?= $badgeColor ?>-600 rounded text-xs font-bold border border-<?= $badgeColor ?>-200 uppercase tracking-wider">
                            <?= htmlspecialchars($statut) ?>
                        </span>
                    </td>
                    <td class="p-4 text-right space-x-2">
                        <?php if ($statut === 'en attente') { ?>
                            <form method="POST" action="/admin/annonces/<?= $annonce['id'] ?>/valider" class="inline">
                                <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-500 hover:bg-emerald-500 hover:text-white transition-colors" title="Valider">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" action="/admin/annonces/<?= $annonce['id'] ?>/refuser" class="inline">
                                <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-colors" title="Refuser">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        <?php } ?>
                        <form method="POST" action="/admin/annonces/<?= $annonce['id'] ?>/supprimer" class="inline" onsubmit="return confirm('Supprimer définitivement cette annonce ?');">
                            <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>