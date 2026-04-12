<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Événements</h2>
        <p class="text-slate-500">Gestion des événements et ateliers</p>
    </div>
    <div class="flex gap-3">
        <input type="text" id="filter-events" onkeyup="filterTable('filter-events','table-events')"
            placeholder="Filtrer..."
            class="border border-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
        <a href="/admin/evenements/create" class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors font-medium text-sm whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i>Créer
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table id="table-events" class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold">Événement</th>
                <th class="p-4 font-semibold">Date & Lieu</th>
                <th class="p-4 font-semibold">Prix</th>
                <th class="p-4 font-semibold">Animateur</th>
                <th class="p-4 font-semibold">Statut</th>
                <th class="p-4 font-semibold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($evenements)): ?>
                <tr><td colspan="6" class="p-8 text-center text-slate-400 italic">Aucun événement.</td></tr>
            <?php else: foreach ($evenements as $e):
                $statut = strtolower($e['statut'] ?? 'à venir');
                $badgeStyle = match($statut) {
                    'passé', 'passe' => 'bg-slate-100 text-slate-500 border-slate-200',
                    'annulé', 'annule' => 'bg-rose-100 text-rose-700 border-rose-200',
                    default => 'bg-blue-100 text-blue-700 border-blue-200',
                };
            ?>
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4">
                    <div class="font-bold text-slate-800"><?= htmlspecialchars($e['titre'] ?? '') ?></div>
                    <?php if (!empty($e['description'])): ?>
                    <div class="text-xs text-slate-400 line-clamp-1 max-w-[200px]"><?= htmlspecialchars(substr($e['description'], 0, 80)) ?></div>
                    <?php endif; ?>
                </td>
                <td class="p-4 text-sm text-slate-600">
                    <div><i class="fas fa-calendar-alt mr-1 text-slate-400"></i><?= htmlspecialchars(substr($e['date'] ?? '', 0, 10)) ?></div>
                    <div class="text-xs text-slate-400 mt-1"><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($e['lieu'] ?? '') ?></div>
                </td>
                <td class="p-4">
                    <?php if (($e['prix'] ?? 0) > 0): ?>
                    <span class="font-bold text-emerald-600"><?= number_format($e['prix'], 2) ?>€</span>
                    <?php else: ?>
                    <span class="text-slate-400 text-sm">Gratuit</span>
                    <?php endif; ?>
                </td>
                <td class="p-4 text-sm text-slate-600">
                    <?php $sal = trim(($e['nom_salarie'] ?? '') . ' ' . ($e['prenom_salarie'] ?? '')); ?>
                    <?= $sal ? htmlspecialchars($sal) : '<span class="text-slate-300">—</span>' ?>
                </td>
                <td class="p-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeStyle ?> uppercase">
                        <?= htmlspecialchars($e['statut'] ?? 'À venir') ?>
                    </span>
                </td>
                <td class="p-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="openEvtDetail(<?= htmlspecialchars(json_encode($e)) ?>)"
                            class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-colors" title="Voir détail">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                        <form method="POST" action="/admin/evenements/<?= $e['id'] ?>/delete" onsubmit="return confirm('Supprimer cet événement ?')">
                            <button type="submit" class="w-8 h-8 flex items-center justify-center bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-800 hover:text-white transition-colors" title="Supprimer">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal détail événement -->
<div id="modal-evt" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800" id="evt-modal-titre">Détail événement</h3>
            <button onclick="document.getElementById('modal-evt').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4 text-sm">
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Date</p><p id="evt-date" class="font-medium text-slate-700"></p></div>
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Lieu</p><p id="evt-lieu" class="font-medium text-slate-700"></p></div>
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Prix</p><p id="evt-prix" class="font-bold text-emerald-600"></p></div>
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Capacité</p><p id="evt-capacite" class="font-medium text-slate-700"></p></div>
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Statut</p><p id="evt-statut" class="font-medium text-slate-700"></p></div>
                <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Animateur</p><p id="evt-animateur" class="font-medium text-slate-700"></p></div>
            </div>
            <div><p class="text-xs text-slate-400 uppercase font-bold mb-1">Description</p><p id="evt-desc" class="text-slate-600 leading-relaxed"></p></div>
        </div>
    </div>
</div>

<script>
function openEvtDetail(e) {
    document.getElementById('evt-modal-titre').textContent = e.titre || 'Événement';
    document.getElementById('evt-date').textContent = (e.date || '').substring(0,10);
    document.getElementById('evt-lieu').textContent = e.lieu || '—';
    document.getElementById('evt-prix').textContent = e.prix > 0 ? e.prix.toFixed(2) + ' €' : 'Gratuit';
    document.getElementById('evt-capacite').textContent = e.capacite ? e.capacite + ' places' : '—';
    document.getElementById('evt-statut').textContent = e.statut || '—';
    const sal = ((e.nom_salarie || '') + ' ' + (e.prenom_salarie || '')).trim();
    document.getElementById('evt-animateur').textContent = sal || '—';
    document.getElementById('evt-desc').textContent = e.description || '—';
    document.getElementById('modal-evt').classList.remove('hidden');
}
function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
