<?php
// ressources/views/salarie/dashboard.php
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold">Tableau de bord</h2>
    <p class="text-gray-600">Bienvenue, <?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'Salarié') ?> !</p>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Conseils</p>
                <p class="text-3xl font-bold text-yellow-600"><?= $nb_conseils ?></p>
            </div>
            <i class="fas fa-lightbulb text-4xl text-yellow-400"></i>
        </div>
        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/conseils"
           class="text-sm text-yellow-600 hover:underline mt-3 block">Gérer →</a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Événements</p>
                <p class="text-3xl font-bold text-blue-600"><?= $nb_evenements ?></p>
            </div>
            <i class="fas fa-calendar-alt text-4xl text-blue-400"></i>
        </div>
        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/evenements"
   class="text-sm text-blue-600 hover:underline mt-3 block">Gérer →</a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Formations</p>
                <p class="text-3xl font-bold text-green-600"><?= $nb_formations ?></p>
            </div>
            <i class="fas fa-graduation-cap text-4xl text-green-400"></i>
        </div>
       <a href="/UpcycleConnect-PA2526/frontend/public/salaries/formations"
   class="text-sm text-green-600 hover:underline mt-3 block">Gérer →</a>

    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Ateliers</p>
                <p class="text-3xl font-bold text-purple-600"><?= $nb_ateliers ?></p>
            </div>
            <i class="fas fa-tools text-4xl text-purple-400"></i>
        </div>
        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/ateliers"
   class="text-sm text-purple-600 hover:underline mt-3 block">Gérer →</a>
    </div>
</div>


<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-bold">Prochaines activités</h3>
        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/planning"
           class="text-sm text-green-600 hover:underline">Voir tout →</a>
    </div>
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($prochains_items)): ?>
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-calendar text-3xl mb-2 text-gray-300 block"></i>
                    Aucune activité planifiée.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($prochains_items as $item): ?>
            <tr class="hover:bg-gray-50">
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
                <td class="px-6 py-4 text-sm text-gray-600">
                    <?= $item['date_debut'] ? date('d/m/Y H:i', strtotime($item['date_debut'])) : '—' ?>
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
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>