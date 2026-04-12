<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Gestion des Formations</h2>
        <p class="text-slate-500">Validation des demandes salariés et catalogue global</p>
    </div>
    <div class="flex gap-3">
        <input type="text" id="filter-formations" onkeyup="filterTable('filter-formations','table-formations')"
            placeholder="Filtrer..."
            class="border border-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
        <button onclick="document.getElementById('modal-formation').classList.remove('hidden')" class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors shadow-sm font-medium whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i>Nouvelle Formation
        </button>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table id="table-formations" class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold">Formation & Détails</th>
                <th class="p-4 font-semibold">Logistique</th>
                <th class="p-4 font-semibold">Statut</th>
                <th class="p-4 font-semibold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($formations)) : ?>
                <tr><td colspan="4" class="p-8 text-center text-slate-400 italic">Aucune formation enregistrée.</td></tr>
            <?php else : foreach ($formations as $f) : 
                $st = strtolower($f['statut'] ?? 'en_attente');
                $color = ($st === 'actif' || $st === 'validee') ? 'emerald' : (($st === 'rejete') ? 'rose' : 'amber');
            ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4">
                        <p class="font-bold text-slate-800"><?= htmlspecialchars($f['titre']) ?></p>
                        <p class="text-xs text-slate-500 line-clamp-1"><?= htmlspecialchars($f['description'] ?? '') ?></p>
                        <div class="flex gap-3 mt-1 text-xs font-medium text-emerald-600">
                            <span><?= number_format($f['prix'], 2) ?>€</span>
                            <span class="text-slate-400">•</span>
                            <span><?= $f['duree'] ?>h</span>
                        </div>
                    </td>
                    <td class="p-4 text-sm text-slate-600">
                        <div class="flex flex-col gap-1">
                            <span><i class="fas fa-calendar-alt mr-2 text-slate-400"></i><?= $f['date_formation'] ?? 'Non planifiée' ?></span>
                            <span><i class="fas fa-users mr-2 text-slate-400"></i><?= $f['places_dispo'] ?? 0 ?> / <?= $f['places_total'] ?? 0 ?> places</span>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-<?= $color ?>-50 text-<?= $color ?>-600 border border-<?= $color ?>-200 rounded text-[10px] font-bold uppercase">
                            <?= str_replace('_', ' ', $st) ?>
                        </span>
                    </td>
                    <td class="p-4 text-right space-x-1">
                        <?php if ($st === 'en_attente' || $st === 'en attente') : ?>
                            <a href="/admin/formations/<?= $f['id'] ?>/valider" class="inline-flex p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-600 hover:text-white transition-colors" title="Valider"><i class="fas fa-check"></i></a>
                            <a href="/admin/formations/<?= $f['id'] ?>/rejeter" class="inline-flex p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition-colors" title="Rejeter"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                        <a href="/admin/formations/<?= $f['id'] ?>/delete" onclick="return confirm('Supprimer ?')" class="inline-flex p-2 bg-slate-100 text-slate-400 rounded-lg hover:bg-slate-800 hover:text-white transition-colors"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div id="modal-formation" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Créer une formation</h3>
            <button onclick="document.getElementById('modal-formation').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST" action="/admin/formations/store" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Titre</label>
                    <input type="text" name="titre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Prix (€)</label>
                    <input type="number" step="0.01" name="prix" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Durée (h)</label>
                    <input type="number" name="duree" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date & Heure</label>
                    <input type="datetime-local" name="date_formation" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Places totales</label>
                    <input type="number" name="places_total" value="20" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Localisation</label>
                    <input type="text" name="localisation" value="Atelier Upcycle" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Salarié Animateur</label>
                    <select name="id_salaries" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 text-sm">
                        <option value="">-- Choisir un salarié --</option>
                        <?php foreach ($salaries ?? [] as $sal): ?>
                        <option value="<?= $sal['id'] ?>"><?= htmlspecialchars($sal['label'] ?? ($sal['prenom'].' '.$sal['nom'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-formation').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors">Annuler</button>
                <button type="submit" class="bg-emerald-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-emerald-600 transition-colors shadow-md">Créer la session</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>