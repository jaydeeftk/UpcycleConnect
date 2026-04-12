<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Modération Forum</h2>
        <p class="text-slate-500">Gestion des sujets et discussions</p>
    </div>
    <input type="text" id="filter-forum" onkeyup="filterTable('filter-forum','table-forum')"
        placeholder="Filtrer par titre, auteur..."
        class="border border-slate-200 rounded-lg px-4 py-2 text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-emerald-300">
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table id="table-forum" class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold">Sujet</th>
                <th class="p-4 font-semibold">Auteur</th>
                <th class="p-4 font-semibold">Réponses</th>
                <th class="p-4 font-semibold">Statut</th>
                <th class="p-4 font-semibold">Date</th>
                <th class="p-4 font-semibold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($sujets)) { ?>
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-400 italic">Le forum est vide pour le moment.</td>
                </tr>
            <?php } else { ?>
                <?php foreach ($sujets as $sujet) {
                    $idSujet = $sujet['id'] ?? '';
                    $statut = strtolower($sujet['statut'] ?? 'ouvert');
                    $statutBadge = match($statut) {
                        'ferme', 'fermé' => 'bg-slate-100 text-slate-500 border-slate-200',
                        'signale', 'signalé' => 'bg-rose-100 text-rose-700 border-rose-200',
                        default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    };
                ?>
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="p-4">
                        <p class="font-bold text-slate-800"><?= htmlspecialchars($sujet['titre'] ?? 'Sujet sans titre') ?></p>
                        <?php if (!empty($sujet['categorie'])): ?>
                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded text-slate-500 font-medium uppercase"><?= htmlspecialchars($sujet['categorie']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4">
                        <div class="flex items-center">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold mr-2 text-xs">
                                <?= strtoupper(substr($sujet['auteur'] ?? 'U', 0, 1)) ?>
                            </div>
                            <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($sujet['auteur'] ?? 'Utilisateur') ?></span>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="inline-flex items-center gap-1 text-sm text-slate-600">
                            <i class="fas fa-reply text-slate-400 text-xs"></i>
                            <?= intval($sujet['nb_reponses'] ?? 0) ?>
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $statutBadge ?> uppercase">
                            <?= htmlspecialchars(ucfirst($statut)) ?>
                        </span>
                    </td>
                    <td class="p-4 text-sm text-slate-500">
                        <?= htmlspecialchars(substr($sujet['date'] ?? '', 0, 10)) ?>
                    </td>
                    <td class="p-4 text-right space-x-2">
                        <a href="/conseils/forum/<?= $idSujet ?>" target="_blank"
                           class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-colors" title="Voir le sujet">
                            <i class="fas fa-external-link-alt text-xs"></i>
                        </a>
                        <form method="POST" action="/admin/forum/sujets/<?= $idSujet ?>/supprimer" class="inline" onsubmit="return confirm('Supprimer ce sujet et toutes ses réponses ?');">
                            <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors" title="Supprimer">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
