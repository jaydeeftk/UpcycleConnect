<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Dépôts Conteneurs</h2>
        <p class="text-slate-500">Demandes de dépôt d'objets en attente de traitement</p>
    </div>
    <input type="text" id="filter-demandes" onkeyup="filterTable('filter-demandes','table-demandes')"
        placeholder="Filtrer par nom, type, statut..."
        class="border border-slate-200 rounded-lg px-4 py-2 text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-emerald-300">
</div>

<?php if (empty($demandes)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center text-slate-400 italic">
        <i class="fas fa-box-open text-5xl mb-4 text-slate-200"></i>
        <p>Aucune demande de dépôt.</p>
    </div>
<?php else: ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table id="table-demandes" class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold">Utilisateur</th>
                <th class="p-4 font-semibold">Objet</th>
                <th class="p-4 font-semibold">Localisation</th>
                <th class="p-4 font-semibold">Code dépôt</th>
                <th class="p-4 font-semibold">Statut</th>
                <th class="p-4 font-semibold">Date</th>
                <th class="p-4 font-semibold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($demandes as $d):
                $statut = strtolower($d['statut'] ?? 'en_attente');
                $badgeStyle = match($statut) {
                    'validee' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'refusee' => 'bg-rose-100 text-rose-700 border-rose-200',
                    default   => 'bg-amber-100 text-amber-700 border-amber-200',
                };
                $badgeLabel = match($statut) {
                    'validee' => 'Validée',
                    'refusee' => 'Refusée',
                    default   => 'En attente',
                };
            ?>
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4">
                    <div class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '')) ?></div>
                    <div class="text-xs text-slate-400"><?= htmlspecialchars($d['email'] ?? '') ?></div>
                </td>
                <td class="p-4">
                    <div class="font-medium text-slate-800 text-sm"><?= htmlspecialchars($d['type_objet'] ?? '-') ?></div>
                    <?php if (!empty($d['description'])): ?>
                    <div class="text-xs text-slate-400 line-clamp-1 max-w-[160px]"><?= htmlspecialchars($d['description']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($d['etat_usure'])): ?>
                    <div class="text-xs text-slate-400">État: <?= htmlspecialchars($d['etat_usure']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="p-4 text-sm text-slate-600"><?= htmlspecialchars($d['localisation'] ?? '-') ?></td>
                <td class="p-4">
                    <?php if (!empty($d['code_acces'])): ?>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm font-bold text-blue-600 blur-sm select-none" id="code-<?= $d['id'] ?>"><?= htmlspecialchars($d['code_acces']) ?></span>
                        <button onclick="revealCode(<?= $d['id'] ?>)" class="text-xs bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded transition-colors" title="Révéler le code">
                            <i class="fas fa-eye text-slate-500"></i>
                        </button>
                    </div>
                    <?php else: ?>
                    <span class="text-slate-400 text-sm">—</span>
                    <?php endif; ?>
                </td>
                <td class="p-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeStyle ?> uppercase"><?= $badgeLabel ?></span>
                </td>
                <td class="p-4 text-xs text-slate-400"><?= htmlspecialchars(substr($d['date'] ?? '', 0, 10)) ?></td>
                <td class="p-4 text-right">
                    <div class="flex justify-end gap-2">
                        <?php if ($statut === 'en_attente'): ?>
                        <form method="POST" action="/admin/demandes/valider/<?= $d['id'] ?>">
                            <button type="submit" class="w-8 h-8 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-600 hover:text-white transition-colors" title="Valider">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                        <form method="POST" action="/admin/demandes/refuser/<?= $d['id'] ?>">
                            <button type="submit" class="w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition-colors" title="Refuser">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-xs text-slate-400 italic"><?= $badgeLabel ?></span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Modal révélation code -->
<div id="modal-reveal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4"><i class="fas fa-lock mr-2 text-amber-500"></i>Révéler le code</h3>
        <p class="text-sm text-slate-500 mb-4">Entrez votre mot de passe administrateur pour afficher le code de dépôt.</p>
        <input type="password" id="reveal-password" placeholder="Mot de passe admin"
            class="w-full border border-slate-200 rounded-lg px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-emerald-300 text-sm">
        <p id="reveal-error" class="text-xs text-rose-600 mb-3 hidden">Mot de passe incorrect.</p>
        <div class="flex gap-3 justify-end">
            <button onclick="closeReveal()" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg text-sm transition-colors">Annuler</button>
            <button onclick="confirmReveal()" class="px-4 py-2 bg-emerald-500 text-white rounded-lg text-sm hover:bg-emerald-600 transition-colors font-medium">Révéler</button>
        </div>
    </div>
</div>

<script>
let _revealId = null;
function revealCode(id) {
    _revealId = id;
    document.getElementById('reveal-password').value = '';
    document.getElementById('reveal-error').classList.add('hidden');
    document.getElementById('modal-reveal').classList.remove('hidden');
    setTimeout(() => document.getElementById('reveal-password').focus(), 100);
}
function closeReveal() {
    document.getElementById('modal-reveal').classList.add('hidden');
    _revealId = null;
}
function confirmReveal() {
    const pwd = document.getElementById('reveal-password').value;
    if (!pwd) return;
    document.getElementById('reveal-error').classList.add('hidden');
    fetch('/api/auth/verify', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer <?= $_SESSION['user']['token'] ?? '' ?>'},
        body: JSON.stringify({password: pwd})
    }).then(r => r.json()).then(data => {
        if (data.data && data.data.verified) {
            const el = document.getElementById('code-' + _revealId);
            if (el) { el.classList.remove('blur-sm'); el.style.userSelect = 'text'; }
            closeReveal();
        } else {
            document.getElementById('reveal-error').classList.remove('hidden');
        }
    }).catch(() => {
        document.getElementById('reveal-error').classList.remove('hidden');
    });
}
document.getElementById('reveal-password').addEventListener('keydown', e => { if (e.key === 'Enter') confirmReveal(); });

function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
