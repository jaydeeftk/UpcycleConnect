<?php
$success = $_SESSION['success'] ?? null;
$error_session = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$evenements = array_filter($items ?? [], fn($i) => $i['type'] === 'evenement');
$formations  = array_filter($items ?? [], fn($i) => $i['type'] === 'formation');
$ateliers    = array_filter($items ?? [], fn($i) => $i['type'] === 'atelier');
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Planning global</h2>
        <p class="text-gray-600">Gérez les événements, formations et ateliers</p>
    </div>
    <div class="flex gap-2">
        <button onclick="document.getElementById('modal-evenement').classList.remove('hidden')"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm font-semibold">
            <i class="fas fa-calendar-plus mr-2"></i>Événement
        </button>
        <button onclick="document.getElementById('modal-formation').classList.remove('hidden')"
                class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 text-sm font-semibold">
            <i class="fas fa-graduation-cap mr-2"></i>Formation
        </button>
        <button onclick="document.getElementById('modal-atelier').classList.remove('hidden')"
                class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 text-sm font-semibold">
            <i class="fas fa-tools mr-2"></i>Atelier
        </button>
    </div>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if ($error_session): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error_session) ?>
</div>
<?php endif; ?>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Événements</p>
                <p class="text-3xl font-bold text-blue-600"><?= count($evenements) ?></p>
            </div>
            <i class="fas fa-calendar-alt text-4xl text-blue-400"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Formations</p>
                <p class="text-3xl font-bold text-green-600"><?= count($formations) ?></p>
            </div>
            <i class="fas fa-graduation-cap text-4xl text-green-400"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Ateliers</p>
                <p class="text-3xl font-bold text-purple-600"><?= count($ateliers) ?></p>
            </div>
            <i class="fas fa-tools text-4xl text-purple-400"></i>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="flex gap-2 mb-4">
    <button class="filter-btn px-4 py-2 rounded-full border border-gray-300 bg-green-500 text-white text-sm font-medium transition"
            data-filter="all">Tout</button>
    <button class="filter-btn px-4 py-2 rounded-full border border-gray-300 bg-white text-gray-600 text-sm font-medium transition hover:bg-green-500 hover:text-white hover:border-green-500"
            data-filter="evenement">Événements</button>
    <button class="filter-btn px-4 py-2 rounded-full border border-gray-300 bg-white text-gray-600 text-sm font-medium transition hover:bg-green-500 hover:text-white hover:border-green-500"
            data-filter="formation">Formations</button>
    <button class="filter-btn px-4 py-2 rounded-full border border-gray-300 bg-white text-gray-600 text-sm font-medium transition hover:bg-green-500 hover:text-white hover:border-green-500"
            data-filter="atelier">Ateliers</button>
</div>

<!-- Tableau -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Animateur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($items)): ?>
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                    <i class="fas fa-calendar text-4xl mb-3 text-gray-300 block"></i>
                    Aucun élément dans le planning pour le moment.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($items as $item): ?>
            <tr class="planning-row hover:bg-gray-50" data-type="<?= htmlspecialchars($item['type']) ?>">
                <td class="px-6 py-4">
                    <?php if ($item['type'] === 'evenement'): ?>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                            <i class="fas fa-calendar-alt mr-1"></i>Événement
                        </span>
                    <?php elseif ($item['type'] === 'formation'): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            <i class="fas fa-graduation-cap mr-1"></i>Formation
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                            <i class="fas fa-tools mr-1"></i>Atelier
                        </span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">
                    <?= htmlspecialchars($item['titre'] ?? '—') ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                    <?= $item['date_debut'] ? date('d/m/Y H:i', strtotime($item['date_debut'])) : '—' ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <?= htmlspecialchars($item['lieu'] ?? '—') ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <?= htmlspecialchars($item['animateur'] ?? '—') ?>
                </td>
                <td class="px-6 py-4">
                    <?php
                    $statut = $item['statut'] ?? '';
                    $statutClass = match($statut) {
                        'valide'     => 'bg-green-100 text-green-700',
                        'en_attente' => 'bg-yellow-100 text-yellow-700',
                        'annule'     => 'bg-red-100 text-red-700',
                        default      => 'bg-gray-100 text-gray-600'
                    };
                    ?>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statutClass ?>">
                        <?= ucfirst(str_replace('_', ' ', htmlspecialchars($statut))) ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <a href="/UpcycleConnect-PA2526/frontend/public/salaries/planning/<?= htmlspecialchars($item['type']) ?>/delete/<?= (int)$item['id'] ?>"
                       onclick="return confirm('Supprimer cet élément ?')"
                       class="text-red-600 hover:text-red-800" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Événement -->
<div id="modal-evenement" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Nouvel événement</h3>
            <button onclick="document.getElementById('modal-evenement').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/salaries/planning/evenement/create">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                <input type="text" name="titre" required placeholder="Titre de l'événement"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Description..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="datetime-local" name="date" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                    <input type="text" name="lieu" placeholder="Lieu de l'événement"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacité</label>
                <input type="number" name="capacite" min="1" placeholder="Nombre de places"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-evenement').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-save mr-2"></i>Créer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Formation -->
<div id="modal-formation" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Nouvelle formation</h3>
            <button onclick="document.getElementById('modal-formation').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/salaries/planning/formation/create">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                <input type="text" name="titre" required placeholder="Titre de la formation"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Description..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
                    <input type="number" name="prix" min="0" step="0.01" placeholder="0.00"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durée (min)</label>
                    <input type="number" name="duree" min="1" placeholder="Ex: 60"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-formation').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i>Créer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Atelier -->
<div id="modal-atelier" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Nouvel atelier</h3>
            <button onclick="document.getElementById('modal-atelier').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/salaries/planning/atelier/create">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Thème *</label>
                <input type="text" name="theme" required placeholder="Thème de l'atelier"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="datetime-local" name="date" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                    <input type="text" name="lieu" placeholder="Lieu de l'atelier"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-atelier').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                <button type="submit"
                        class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                    <i class="fas fa-save mr-2"></i>Créer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Filtres
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('bg-green-500', 'text-white', 'border-green-500');
            b.classList.add('bg-white', 'text-gray-600', 'border-gray-300');
        });
        this.classList.add('bg-green-500', 'text-white', 'border-green-500');
        this.classList.remove('bg-white', 'text-gray-600', 'border-gray-300');

        const filter = this.dataset.filter;
        document.querySelectorAll('.planning-row').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.type === filter) ? '' : 'none';
        });
    });
});

// Fermer modals en cliquant en dehors
['modal-evenement', 'modal-formation', 'modal-atelier'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
});
</script>