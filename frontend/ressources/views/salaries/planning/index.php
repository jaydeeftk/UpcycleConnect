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
        <h2 class="text-2xl font-bold"><?= t('sal_planning_title', 'Planning global') ?></h2>
        <p class="text-gray-600"><?= t('sal_planning_subtitle', 'Gérez les événements, formations et ateliers') ?></p>
    </div>
    <div class="flex gap-2">
        <button onclick="document.getElementById('modal-evenement').classList.remove('hidden')"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm font-semibold">
            <i class="fas fa-calendar-plus mr-2"></i><?= t('sal_type_evenement', 'Événement') ?>
        </button>
        <button onclick="document.getElementById('modal-formation').classList.remove('hidden')"
                class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 text-sm font-semibold">
            <i class="fas fa-graduation-cap mr-2"></i><?= t('sal_type_formation', 'Formation') ?>
        </button>
        <button onclick="document.getElementById('modal-atelier').classList.remove('hidden')"
                class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 text-sm font-semibold">
            <i class="fas fa-tools mr-2"></i><?= t('sal_type_atelier', 'Atelier') ?>
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

<!  Statistiques  >
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm"><?= t('sal_nav_evenements', 'Événements') ?></p>
                <p class="text-3xl font-bold text-blue-600"><?= count($evenements) ?></p>
            </div>
            <i class="fas fa-calendar-alt text-4xl text-blue-400"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm"><?= t('sal_nav_formations', 'Formations') ?></p>
                <p class="text-3xl font-bold text-green-600"><?= count($formations) ?></p>
            </div>
            <i class="fas fa-graduation-cap text-4xl text-green-400"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm"><?= t('sal_nav_ateliers', 'Ateliers') ?></p>
                <p class="text-3xl font-bold text-purple-600"><?= count($ateliers) ?></p>
            </div>
            <i class="fas fa-tools text-4xl text-purple-400"></i>
        </div>
    </div>
</div>

<!  Vue grille (jour / semaine / mois)  >
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800"><?= t('sal_planning_grid_title', 'Vue calendrier') ?></h3>
        <div class="tabs tabs-boxed bg-gray-100 p-1 rounded-2xl">
            <button onclick="setVue('jour')" id="tab-jour" class="tab"><?= t('planning_tab_day', 'Jour') ?></button>
            <button onclick="setVue('semaine')" id="tab-semaine" class="tab tab-active"><?= t('planning_tab_week', 'Semaine') ?></button>
            <button onclick="setVue('mois')" id="tab-mois" class="tab"><?= t('planning_tab_month', 'Mois') ?></button>
        </div>
    </div>
    <div class="flex items-center justify-between mb-3">
        <button onclick="naviguer(-1)" class="btn btn-ghost btn-sm"><i class="fas fa-chevron-left"></i></button>
        <span id="periode-label" class="font-semibold text-gray-700"></span>
        <button onclick="naviguer(1)" class="btn btn-ghost btn-sm"><i class="fas fa-chevron-right"></i></button>
    </div>
    <div id="vue-container"></div>
    <div class="flex gap-4 mt-4 text-xs text-gray-500">
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-200 inline-block"></span> <?= t('sal_type_evenement', 'Événement') ?></span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-200 inline-block"></span> <?= t('sal_type_formation', 'Formation') ?></span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-purple-200 inline-block"></span> <?= t('sal_type_atelier', 'Atelier') ?></span>
    </div>
</div>

<!  Filtres  >
<div class="flex gap-2 mb-4">
    <button class="filter-btn px-4 py-2 rounded-full border border-gray-300 bg-green-500 text-white text-sm font-medium transition"
            data-filter="all"><?= t('sal_filter_all', 'Tout') ?></button>
    <button class="filter-btn px-4 py-2 rounded-full border border-gray-300 bg-white text-gray-600 text-sm font-medium transition hover:bg-green-500 hover:text-white hover:border-green-500"
            data-filter="evenement"><?= t('sal_nav_evenements', 'Événements') ?></button>
    <button class="filter-btn px-4 py-2 rounded-full border border-gray-300 bg-white text-gray-600 text-sm font-medium transition hover:bg-green-500 hover:text-white hover:border-green-500"
            data-filter="formation"><?= t('sal_nav_formations', 'Formations') ?></button>
    <button class="filter-btn px-4 py-2 rounded-full border border-gray-300 bg-white text-gray-600 text-sm font-medium transition hover:bg-green-500 hover:text-white hover:border-green-500"
            data-filter="atelier"><?= t('sal_nav_ateliers', 'Ateliers') ?></button>
</div>

<!  Tableau  >
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_type', 'Type') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_titre', 'Titre') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_date', 'Date') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_duree', 'Durée') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_lieu', 'Lieu') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_animateur', 'Animateur') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_statut', 'Statut') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_actions', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($items)): ?>
            <tr>
                <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                    <i class="fas fa-calendar text-4xl mb-3 text-gray-300 block"></i>
                    <?= t('sal_planning_empty', 'Aucun élément dans le planning pour le moment.') ?>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($items as $item): ?>
            <tr class="planning-row hover:bg-gray-50" data-type="<?= htmlspecialchars($item['type']) ?>">
                <td class="px-6 py-4">
                    <?php if ($item['type'] === 'evenement'): ?>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                            <i class="fas fa-calendar-alt mr-1"></i><?= t('sal_type_evenement', 'Événement') ?>
                        </span>
                    <?php elseif ($item['type'] === 'formation'): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            <i class="fas fa-graduation-cap mr-1"></i><?= t('sal_type_formation', 'Formation') ?>
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                            <i class="fas fa-tools mr-1"></i><?= t('sal_type_atelier', 'Atelier') ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">
                    <?= htmlspecialchars($item['titre'] ?? '—') ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                    <?= formatDate($item['date'] ?? '', true) ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                    <?php
                    $duree = (int)($item['duree'] ?? 0);
                    if ($duree >= 60) {
                        $h = intdiv($duree, 60);
                        $m = $duree % 60;
                        echo $h . 'h' . ($m > 0 ? str_pad($m, 2, '0', STR_PAD_LEFT) : '');
                    } elseif ($duree > 0) {
                        echo $duree . ' min';
                    } else {
                        echo '—';
                    }
                    ?>
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
                        <?= htmlspecialchars(formatStatut($statut)) ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <?php if ($item['peut_deleguer'] ?? false): ?>
                            <button type="button" class="text-purple-600 hover:text-purple-800" title="<?= t('sal_action_deleguer', 'Déléguer') ?>"
                                    onclick="ouvrirDeleguer('<?= htmlspecialchars($item['type']) ?>', <?= (int)($item['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($item['titre'] ?? ''), ENT_QUOTES) ?>')">
                                <i class="fas fa-user-friends"></i>
                            </button>
                        <?php endif; ?>
                        <form method="POST" action="/salaries/planning/<?= htmlspecialchars($item['type']) ?>/delete/<?= (int)($item['id'] ?? 0) ?>" class="inline"
                           onsubmit="return ucConfirm(this, '<?= t('sal_planning_delete_confirm', 'Supprimer cet élément ?') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-red-600 hover:text-red-800" title="<?= t('sal_action_delete', 'Supprimer') ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!  Modal Événement  >
<div id="modal-evenement" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('sal_planning_new_evenement', 'Nouvel événement') ?></h3>
            <button onclick="document.getElementById('modal-evenement').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action=" /salaries/planning/evenement/create">
        <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_titre', 'Titre') ?> *</label>
                <input type="text" name="titre" required placeholder="<?= t('sal_ph_evenement_titre', 'Titre de l\'événement') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_description', 'Description') ?></label>
                <textarea name="description" rows="3" placeholder="<?= t('sal_ph_description', 'Description...') ?>"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_date', 'Date') ?> *</label>
                    <input type="datetime-local" name="date" required min="<?= dateProgrammationMin() ?>" max="<?= dateProgrammationMax() ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_lieu', 'Lieu') ?></label>
                    <input type="text" name="lieu" placeholder="<?= t('sal_ph_evenement_lieu', 'Lieu de l\'événement') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_capacite', 'Capacité') ?></label>
                <input type="number" name="capacite" min="1" placeholder="<?= t('sal_ph_capacite', 'Nombre de places') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-evenement').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= t('sal_cancel', 'Annuler') ?></button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-save mr-2"></i><?= t('sal_create', 'Créer') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!  Modal Formation  >
<div id="modal-formation" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('sal_planning_new_formation', 'Nouvelle formation') ?></h3>
            <button onclick="document.getElementById('modal-formation').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action=" /salaries/planning/formation/create">
        <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_titre', 'Titre') ?> *</label>
                <input type="text" name="titre" required placeholder="<?= t('sal_ph_formation_titre', 'Titre de la formation') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_description', 'Description') ?></label>
                <textarea name="description" rows="3" placeholder="<?= t('sal_ph_description', 'Description...') ?>"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_date_debut', 'Date de début') ?> *</label>
                    <input type="datetime-local" name="date_debut" required min="<?= dateProgrammationMin() ?>" max="<?= dateProgrammationMax() ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_date_fin', 'Date de fin') ?></label>
                    <input type="date" name="date_fin" min="<?= dateProgrammationMin(false) ?>" max="<?= dateProgrammationMax(false) ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="text-xs text-gray-400 mt-1"><?= t('sal_field_date_fin_hint', 'Si la formation dure plusieurs jours') ?></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_prix', 'Prix (€)') ?></label>
                    <input type="number" name="prix" min="0" step="0.01" placeholder="0.00"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_duree', 'Durée (min)') ?></label>
                    <input type="number" name="duree" min="1" placeholder="<?= t('sal_ph_duree', 'Ex: 60') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-formation').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= t('sal_cancel', 'Annuler') ?></button>
                <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i><?= t('sal_create', 'Créer') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!  Modal Atelier  >
<div id="modal-atelier" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('sal_planning_new_atelier', 'Nouvel atelier') ?></h3>
            <button onclick="document.getElementById('modal-atelier').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action=" /salaries/planning/atelier/create">
        <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_theme', 'Thème') ?> *</label>
                <input type="text" name="theme" required placeholder="<?= t('sal_ph_atelier_theme', 'Thème de l\'atelier') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_date', 'Date') ?> *</label>
                    <input type="datetime-local" name="date" required min="<?= dateProgrammationMin() ?>" max="<?= dateProgrammationMax() ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_field_lieu', 'Lieu') ?></label>
                    <input type="text" name="lieu" placeholder="<?= t('sal_ph_atelier_lieu', 'Lieu de l\'atelier') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-atelier').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= t('sal_cancel', 'Annuler') ?></button>
                <button type="submit"
                        class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                    <i class="fas fa-save mr-2"></i><?= t('sal_create', 'Créer') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-deleguer" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('sal_deleguer_title', 'Déléguer') ?> — <span id="deleguer-titre"></span></h3>
            <button type="button" onclick="document.getElementById('modal-deleguer').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-4"><?= t('sal_deleguer_hint', 'Choisissez le salarié qui animera cet événement à votre place.') ?></p>
        <select id="deleguer-select" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4">
            <option value="0"><?= t('sal_deleguer_moi', '— Moi-même (aucune délégation) —') ?></option>
        </select>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('modal-deleguer').classList.add('hidden')"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= t('sal_cancel', 'Annuler') ?></button>
            <button type="button" onclick="validerDelegation()" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                <i class="fas fa-check mr-2"></i><?= t('sal_deleguer_valider', 'Valider') ?>
            </button>
        </div>
        <p id="deleguer-erreur" class="text-red-600 text-xs mt-2 hidden"></p>
    </div>
</div>

<script>
const TOKEN = <?= json_encode($token ?? '') ?>;
const ITEMS = <?= json_encode(array_map(fn($i) => [
    'id'       => $i['id'] ?? 0,
    'titre'    => $i['titre'] ?? '',
    'date'     => $i['date'] ?? '',
    'date_fin' => $i['date_fin'] ?? '',
    'lieu'     => $i['lieu'] ?? '',
    'duree'    => $i['duree'] ?? 0,
    'type'     => $i['type'] ?? 'evenement',
], array_values($items ?? []))) ?>;

let vue = 'semaine';
let filtreType = 'all';

function itemsVisibles() {
    return filtreType === 'all' ? ITEMS : ITEMS.filter(i => i.type === filtreType);
}
let dateRef = new Date();
dateRef.setHours(0,0,0,0);

const MOIS_FR = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
const JOURS_COURTS = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];
const JOURS_LONGS  = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

const SEMAINE_HEURE_DEBUT = 9;
const SEMAINE_HEURE_FIN   = 19;
const SEMAINE_ROW_H       = 48;

function itemDate(item) {
    if (!item.date) return null;
    const m = String(item.date).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (!m) return null;
    return new Date(+m[1], +m[2] - 1, +m[3], +(m[4] || 0), +(m[5] || 0));
}

function sameDay(a, b) {
    return a.getFullYear() === b.getFullYear() &&
           a.getMonth() === b.getMonth() &&
           a.getDate() === b.getDate();
}

function itemCouvreJour(item, jour) {
    const debut = itemDate(item);
    if (!debut) return false;
    if (!item.date_fin) return sameDay(debut, jour);
    const m = String(item.date_fin).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!m) return sameDay(debut, jour);
    const fin       = new Date(+m[1], +m[2] - 1, +m[3]);
    const jourSeul  = new Date(jour.getFullYear(), jour.getMonth(), jour.getDate());
    const debutSeul = new Date(debut.getFullYear(), debut.getMonth(), debut.getDate());
    return jourSeul >= debutSeul && jourSeul <= fin;
}

function formatHeure(d) {
    return String(d.getHours()).padStart(2,'0') + 'h' + String(d.getMinutes()).padStart(2,'0');
}

function trierParHeure(items) {
    return [...items].sort((a, b) => {
        const da = itemDate(a), db = itemDate(b);
        if (!da || !db) return 0;
        return da - db;
    });
}

function colorClass(type) {
    if (type === 'formation') return 'bg-green-100 text-green-700 border-green-300';
    if (type === 'atelier')   return 'bg-purple-100 text-purple-700 border-purple-300';
    return 'bg-blue-100 text-blue-700 border-blue-300';
}

function formatPlageHoraire(item) {
    const d = itemDate(item);
    if (!d) return '';
    let label = formatHeure(d);
    if (item.duree && item.duree > 0) {
        const fin = new Date(d);
        fin.setHours(fin.getHours() + item.duree);
        label += ' - ' + formatHeure(fin);
    }
    return label;
}

function cardHtml(item) {
    const heure = formatPlageHoraire(item);
    return `<div class="${colorClass(item.type)} border rounded-lg p-2 text-xs mb-1">
        <div class="font-semibold">${heure}</div>
        <div class="mt-0.5 leading-tight">${item.titre}</div>
        ${item.lieu ? `<div class="mt-1 opacity-70"><i class="fas fa-map-marker-alt mr-1"></i>${item.lieu}</div>` : ''}
    </div>`;
}

function carteSemainePositionnee(item) {
    const d = itemDate(item);
    const debutH = d ? Math.max(SEMAINE_HEURE_DEBUT, Math.min(d.getHours() + d.getMinutes() / 60, SEMAINE_HEURE_FIN)) : SEMAINE_HEURE_DEBUT;
    const dureeH = item.duree && item.duree > 0 ? item.duree : 1;
    const top    = (debutH - SEMAINE_HEURE_DEBUT) * SEMAINE_ROW_H;
    const height = Math.max(dureeH * SEMAINE_ROW_H, 22);
    const heure  = formatPlageHoraire(item);
    return `<div class="${colorClass(item.type)} border rounded-lg p-1 text-xs overflow-hidden absolute left-1 right-1" style="top:${top}px;height:${height}px;">
        <div class="font-semibold leading-tight">${heure}</div>
        <div class="leading-tight">${item.titre}</div>
    </div>`;
}

function setVue(v) {
    vue = v;
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('tab-active'));
    document.getElementById('tab-' + v).classList.add('tab-active');
    render();
}

function naviguer(dir) {
    if (vue === 'jour')    dateRef.setDate(dateRef.getDate() + dir);
    if (vue === 'semaine') dateRef.setDate(dateRef.getDate() + dir * 7);
    if (vue === 'mois')    dateRef.setMonth(dateRef.getMonth() + dir);
    render();
}

function render() {
    if (vue === 'jour')    renderJour();
    if (vue === 'semaine') renderSemaine();
    if (vue === 'mois')    renderMois();
}

function renderJour() {
    const label = JOURS_LONGS[dateRef.getDay()] + ' ' + dateRef.getDate() + ' ' + MOIS_FR[dateRef.getMonth()] + ' ' + dateRef.getFullYear();
    document.getElementById('periode-label').textContent = label;

    const itemsDuJour = itemsVisibles().filter(i => itemCouvreJour(i, dateRef));
    const heures = Array.from({length: 16}, (_, i) => i + 6);
    const sansHeure = itemsDuJour.filter(i => { const d = itemDate(i); return d && d.getHours() < 6; });

    let html = '<div class="bg-gray-50 rounded-lg p-4 space-y-3">';
    if (sansHeure.length > 0) {
        html += `<div class="flex gap-4 items-start">
            <span class="text-xs text-gray-400 w-12 pt-1 flex-shrink-0"><?= t('planning_all_day', 'Journée') ?></span>
            <div class="flex-1 border-t border-gray-200 pt-1 min-h-8">${trierParHeure(sansHeure).map(cardHtml).join('')}</div>
        </div>`;
    }
    for (const h of heures) {
        const label2 = String(h).padStart(2,'0') + 'h00';
        const items = itemsDuJour.filter(i => { const d = itemDate(i); return d && d.getHours() === h; });
        html += `<div class="flex gap-4 items-start">
            <span class="text-xs text-gray-400 w-12 pt-1 flex-shrink-0">${label2}</span>
            <div class="flex-1 border-t border-gray-200 pt-1 min-h-8">${trierParHeure(items).map(cardHtml).join('')}</div>
        </div>`;
    }
    html += '</div>';
    document.getElementById('vue-container').innerHTML = html;
}

function renderSemaine() {
    const lundi = new Date(dateRef);
    const jour = dateRef.getDay();
    const diff = jour === 0 ? -6 : 1 - jour;
    lundi.setDate(dateRef.getDate() + diff);

    const jours = Array.from({length: 7}, (_, i) => {
        const d = new Date(lundi);
        d.setDate(lundi.getDate() + i);
        return d;
    });

    const dimanche = jours[6];
    document.getElementById('periode-label').textContent =
        '<?= t('planning_week_of', 'Semaine du') ?> ' + lundi.getDate() + ' <?= t('planning_week_to', 'au') ?> ' + dimanche.getDate() + ' ' + MOIS_FR[dimanche.getMonth()] + ' ' + dimanche.getFullYear();

    const today = new Date(); today.setHours(0,0,0,0);
    const heuresAxe = Array.from({length: SEMAINE_HEURE_FIN - SEMAINE_HEURE_DEBUT}, (_, i) => SEMAINE_HEURE_DEBUT + i);
    const hauteurTotale = heuresAxe.length * SEMAINE_ROW_H;

    let html = '<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">';
    html += '<div class="grid" style="grid-template-columns: 48px repeat(7, 1fr);">';
    html += '<div class="border-b border-gray-200"></div>';
    for (const d of jours) {
        const isToday = sameDay(d, today);
        html += `<div class="p-3 text-center text-sm border-b border-l border-gray-200 ${isToday ? 'bg-green-50 font-bold text-green-600' : 'text-gray-400'}">
            ${JOURS_COURTS[d.getDay()]} ${d.getDate()}
        </div>`;
    }
    html += '</div>';

    html += '<div class="grid" style="grid-template-columns: 48px repeat(7, 1fr);">';
    html += `<div style="height:${hauteurTotale}px;">`;
    for (const h of heuresAxe) {
        html += `<div class="text-xs text-gray-400 text-right pr-1" style="height:${SEMAINE_ROW_H}px;">${String(h).padStart(2,'0')}h</div>`;
    }
    html += '</div>';
    for (const d of jours) {
        const items = itemsVisibles().filter(i => itemCouvreJour(i, d));
        html += `<div class="relative border-l border-gray-200" style="height:${hauteurTotale}px;">`;
        for (let i = 0; i < heuresAxe.length; i++) {
            html += `<div class="absolute left-0 right-0 border-t border-gray-100" style="top:${i * SEMAINE_ROW_H}px;"></div>`;
        }
        html += items.map(carteSemainePositionnee).join('');
        html += '</div>';
    }
    html += '</div></div>';
    document.getElementById('vue-container').innerHTML = html;
}

function renderMois() {
    const annee = dateRef.getFullYear();
    const mois  = dateRef.getMonth();
    document.getElementById('periode-label').textContent = MOIS_FR[mois] + ' ' + annee;

    const premier = new Date(annee, mois, 1);
    const dernier = new Date(annee, mois + 1, 0);
    const today   = new Date(); today.setHours(0,0,0,0);

    let joursVides = premier.getDay() === 0 ? 6 : premier.getDay() - 1;

    let html = '<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">';
    html += '<div class="grid grid-cols-7 border-b border-gray-200">';
    for (const j of ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim']) {
        html += `<div class="p-3 text-center text-xs font-semibold text-gray-400">${j}</div>`;
    }
    html += '</div><div class="grid grid-cols-7 divide-x divide-y divide-gray-200">';
    for (let i = 0; i < joursVides; i++) {
        html += '<div class="p-2 min-h-20 bg-gray-50"></div>';
    }
    for (let d = 1; d <= dernier.getDate(); d++) {
        const cur = new Date(annee, mois, d);
        const isToday = sameDay(cur, today);
        const items = itemsVisibles().filter(i => itemCouvreJour(i, cur));
        html += `<div class="p-2 min-h-20 ${isToday ? 'bg-green-50' : ''}">
            <span class="text-sm ${isToday ? 'font-bold text-green-600' : 'text-gray-500'}">${d}</span>
            ${trierParHeure(items).map(i => `<div class="${colorClass(i.type)} rounded text-xs p-1 mt-1 leading-tight">${formatPlageHoraire(i)} ${i.titre}</div>`).join('')}
        </div>`;
    }
    html += '</div></div>';
    document.getElementById('vue-container').innerHTML = html;
}

render();
</script>

<script>

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

        filtreType = filter;
        render();
    });
});

['modal-evenement', 'modal-formation', 'modal-atelier', 'modal-deleguer'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
});
</script>

<script>
let deleguerType = null, deleguerId = null;

function ouvrirDeleguer(type, id, titre) {
    deleguerType = type;
    deleguerId = id;
    document.getElementById('deleguer-titre').textContent = titre;
    document.getElementById('deleguer-erreur').classList.add('hidden');
    const select = document.getElementById('deleguer-select');
    select.innerHTML = '<option value="0"><?= t('sal_deleguer_moi', '— Moi-même (aucune délégation) —') ?></option>';
    fetch('/api/salaries/liste', { headers: { 'Authorization': 'Bearer ' + TOKEN } })
        .then(r => r.json())
        .then(json => {
            const salaries = json.data || json;
            if (Array.isArray(salaries)) {
                salaries.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.prenom + ' ' + s.nom;
                    select.appendChild(opt);
                });
            }
        })
        .catch(() => {});
    document.getElementById('modal-deleguer').classList.remove('hidden');
}

function validerDelegation() {
    const idAnimateur = parseInt(document.getElementById('deleguer-select').value, 10) || 0;
    const err = document.getElementById('deleguer-erreur');
    err.classList.add('hidden');
    fetch('/api/salaries/deleguer/' + deleguerType + '/' + deleguerId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
        body: JSON.stringify({ id_animateur: idAnimateur })
    })
        .then(r => r.json().then(json => ({ ok: r.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                err.textContent = (json && json.error) || <?= json_encode(t('sal_deleguer_err_generic', 'Une erreur est survenue.')) ?>;
                err.classList.remove('hidden');
                return;
            }
            location.reload();
        })
        .catch(() => {});
}
</script>
