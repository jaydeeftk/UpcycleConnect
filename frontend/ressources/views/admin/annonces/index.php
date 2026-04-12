<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Gestion des Annonces</h2>
        <p class="text-slate-500">Modérez les publications</p>
    </div>
    <input type="text" id="filter-annonces" onkeyup="filterTable('filter-annonces','table-annonces')"
        placeholder="Filtrer par titre, ville, statut..."
        class="border border-slate-200 rounded-lg px-4 py-2 text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-emerald-300">
</div>

<?php if (empty($annonces)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center text-slate-400 italic">
        <i class="fas fa-bullhorn text-5xl mb-4 text-slate-200"></i>
        <p>Aucune annonce trouvée dans la base de données.</p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table id="table-annonces" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-semibold">ID</th>
                    <th class="p-4 font-semibold">Annonce</th>
                    <th class="p-4 font-semibold">Prix / Ville</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($annonces as $a):
                    $status = trim($a['statut'] ?? 'en attente');
                    $badgeStyle = match($status) {
                        'validee' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'rejetee', 'refusee' => 'bg-rose-100 text-rose-700 border-rose-200',
                        default => 'bg-amber-100 text-amber-700 border-amber-200'
                    };
                ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-sm font-mono text-slate-400">#<?= $a['id'] ?></td>
                    <td class="p-4">
                        <div class="font-bold text-slate-800"><?= htmlspecialchars($a['titre'] ?? 'Sans titre') ?></div>
                        <div class="text-xs text-slate-500 truncate max-w-xs"><?= htmlspecialchars(substr($a['description'] ?? '', 0, 80)) ?></div>
                        <div class="mt-1">
                            <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded text-slate-500 font-medium uppercase">
                                <?= htmlspecialchars($a['categorie'] ?? 'Divers') ?>
                            </span>
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="text-sm font-bold text-emerald-600"><?= number_format($a['prix'] ?? 0, 2) ?> €</div>
                        <div class="text-xs text-slate-400">
                            <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($a['ville'] ?? 'N/C') ?>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeStyle ?> uppercase">
                            <?= str_replace('_', ' ', $status) ?>
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick='openAnnonceDetail(<?= htmlspecialchars(json_encode($a)) ?>)'
                                class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-colors" title="Voir détail">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            <?php if ($status !== 'validee'): ?>
                                <form method="POST" action="/admin/annonces/<?= $a['id'] ?>/valider">
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-600 hover:text-white transition-colors" title="Valider">
                                        <i class="fas fa-check text-xs"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if ($status !== 'rejetee' && $status !== 'refusee'): ?>
                                <form method="POST" action="/admin/annonces/<?= $a['id'] ?>/refuser">
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition-colors" title="Refuser">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="/admin/annonces/<?= $a['id'] ?>/supprimer" onsubmit="return confirm('Supprimer définitivement cette annonce ?')">
                                <button type="submit" class="w-8 h-8 flex items-center justify-center bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-800 hover:text-white transition-colors" title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal détail annonce -->
<div id="modal-annonce" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800" id="annonce-modal-titre">Détail annonce</h3>
            <button onclick="document.getElementById('modal-annonce').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4 text-sm">
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Prix</p><p id="annonce-prix" class="font-bold text-emerald-600"></p></div>
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Ville</p><p id="annonce-ville" class="font-medium text-slate-700"></p></div>
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Catégorie</p><p id="annonce-categorie" class="font-medium text-slate-700"></p></div>
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Statut</p><p id="annonce-statut" class="font-medium text-slate-700"></p></div>
            </div>
            <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Description complète</p><p id="annonce-desc" class="text-slate-600 leading-relaxed whitespace-pre-wrap max-h-60 overflow-y-auto"></p></div>
        </div>
    </div>
</div>

<script>
function openAnnonceDetail(a) {
    document.getElementById('annonce-modal-titre').textContent = a.titre || 'Annonce';
    document.getElementById('annonce-prix').textContent = parseFloat(a.prix || 0).toFixed(2) + ' €';
    document.getElementById('annonce-ville').textContent = a.ville || '—';
    document.getElementById('annonce-categorie').textContent = a.categorie || '—';
    document.getElementById('annonce-statut').textContent = a.statut || '—';
    document.getElementById('annonce-desc').textContent = a.description || '—';
    document.getElementById('modal-annonce').classList.remove('hidden');
}
function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
