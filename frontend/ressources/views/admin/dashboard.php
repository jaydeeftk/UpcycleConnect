<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
    <div>
        <p class="text-sm text-gray-500">Tableau de bord</p>
        <h1 class="text-4xl font-bold text-gray-900">Bienvenue sur l'espace admin</h1>
    </div>
    <div class="flex items-center gap-3">
        <a href="/admin/utilisateurs?export=csv" class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 border border-gray-200 px-4 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Exporter</a>
        <div class="relative" x-data="{ open: false }">
            <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="bg-gray-900 text-white px-5 py-3 rounded-xl text-sm font-medium hover:bg-gray-800 transition flex items-center gap-2">
                Nouvelle action <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div class="hidden absolute right-0 top-full mt-2 w-48 bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 border border-gray-200 rounded-xl shadow-lg z-10 overflow-hidden">
                <a href="/admin/utilisateurs/create" class="block px-4 py-3 text-sm hover:bg-gray-50">Créer un utilisateur</a>
                <a href="/admin/evenements/create" class="block px-4 py-3 text-sm hover:bg-gray-50">Créer un événement</a>
                <a href="/admin/notifications" class="block px-4 py-3 text-sm hover:bg-gray-50">Envoyer une notif</a>
            </div>
        </div>
    </div>
</div>

<?php $v = $visites ?? []; ?>
<section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6 border-l-4 border-blue-500">
        <p class="text-sm text-gray-500">Visites aujourd'hui</p>
        <h3 id="cnt-visits-today" class="text-3xl font-bold mt-3"><?= number_format($v['today'] ?? 0) ?></h3>
        <p class="text-sm text-blue-600 mt-2">Pages vues ce jour</p>
    </div>
    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6 border-l-4 border-indigo-500">
        <p class="text-sm text-gray-500">Visites 7 jours</p>
        <h3 id="cnt-visits-week" class="text-3xl font-bold mt-3"><?= number_format($v['week'] ?? 0) ?></h3>
        <p class="text-sm text-indigo-600 mt-2">Cette semaine</p>
    </div>
    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6 border-l-4 border-purple-500">
        <p class="text-sm text-gray-500">Visites 30 jours</p>
        <h3 id="cnt-visits-month" class="text-3xl font-bold mt-3"><?= number_format($v['month'] ?? 0) ?></h3>
        <p class="text-sm text-purple-600 mt-2">Ce mois</p>
    </div>
    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6 border-l-4 border-slate-400">
        <p class="text-sm text-gray-500">Total visites</p>
        <h3 id="cnt-visits-total" class="text-3xl font-bold mt-3"><?= number_format($v['total'] ?? 0) ?></h3>
        <p class="text-sm text-slate-500 mt-2">Depuis le début</p>
    </div>
</section>

<section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Utilisateurs</p>
        <h3 id="cnt-utilisateurs" class="text-3xl font-bold mt-3"><?= number_format($stats['total_utilisateurs'] ?? 0) ?></h3>
        <p class="text-sm text-emerald-600 mt-2">Total inscrits</p>
    </div>

    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Annonces</p>
        <h3 id="cnt-annonces" class="text-3xl font-bold mt-3"><?= number_format($stats['total_annonces'] ?? 0) ?></h3>
        <p class="text-sm text-emerald-600 mt-2">Total annonces</p>
    </div>

    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Événements</p>
        <h3 id="cnt-evenements" class="text-3xl font-bold mt-3"><?= number_format($stats['total_evenements'] ?? 0) ?></h3>
        <p class="text-sm text-gray-500 mt-2">Total événements</p>
    </div>

    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Messages</p>
        <h3 id="cnt-messages" class="text-3xl font-bold mt-3"><?= number_format($stats['total_messages'] ?? 0) ?></h3>
        <p class="text-sm text-amber-600 mt-2">Total messages</p>
    </div>
</section>
<script>
function animateCounter(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    let start = 0;
    const duration = 1200;
    const step = Math.ceil(target / (duration / 16));
    const timer = setInterval(() => {
        start += step;
        if (start >= target) { el.textContent = target.toLocaleString('fr-FR'); clearInterval(timer); }
        else { el.textContent = start.toLocaleString('fr-FR'); }
    }, 16);
}
document.addEventListener('DOMContentLoaded', () => {
    animateCounter('cnt-utilisateurs', <?= intval($stats['total_utilisateurs'] ?? 0) ?>);
    animateCounter('cnt-annonces', <?= intval($stats['total_annonces'] ?? 0) ?>);
    animateCounter('cnt-evenements', <?= intval($stats['total_evenements'] ?? 0) ?>);
    animateCounter('cnt-messages', <?= intval($stats['total_messages'] ?? 0) ?>);
    animateCounter('cnt-visits-today', <?= intval($v['today'] ?? 0) ?>);
    animateCounter('cnt-visits-week', <?= intval($v['week'] ?? 0) ?>);
    animateCounter('cnt-visits-month', <?= intval($v['month'] ?? 0) ?>);
    animateCounter('cnt-visits-total', <?= intval($v['total'] ?? 0) ?>);
});
</script>

<section class="grid xl:grid-cols-3 gap-6 mb-8">
    <div class="xl:col-span-2 bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold">Activité générale</h3>
                <p class="text-sm text-gray-500 mt-1">Vue d'ensemble de la plateforme</p>
            </div>
            <a href="/admin/finances" class="text-sm text-gray-600 hover:text-black font-medium">Voir plus →</a>
        </div>
        <div class="flex gap-2 mb-4">
            <button onclick="showChart('platform')" id="btn-platform" class="text-xs px-3 py-1.5 bg-slate-800 text-white rounded-lg font-medium">Plateforme</button>
            <button onclick="showChart('visites')" id="btn-visites" class="text-xs px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg font-medium hover:bg-slate-200">Visites 7j</button>
        </div>
        <div class="h-64">
            <canvas id="activityChart"></canvas>
            <canvas id="visitesChart" class="hidden"></canvas>
        </div>
        <?php
        $parJour = $v['par_jour'] ?? [];
        $visitesLabels = json_encode(array_column($parJour, 'date'));
        $visitesData   = json_encode(array_column($parJour, 'nb'));
        ?>
        <script>
        const chartPlatform = new Chart(document.getElementById('activityChart'), {
            type: 'bar',
            data: {
                labels: ['Utilisateurs','Annonces','Événements','Messages','Formations','Conteneurs'],
                datasets: [{
                    label: 'Total',
                    data: [
                        <?= intval($stats['total_utilisateurs'] ?? 0) ?>,
                        <?= intval($stats['total_annonces'] ?? 0) ?>,
                        <?= intval($stats['total_evenements'] ?? 0) ?>,
                        <?= intval($stats['total_messages'] ?? 0) ?>,
                        <?= intval($stats['total_formations'] ?? 0) ?>,
                        <?= intval($stats['total_conteneurs'] ?? 0) ?>
                    ],
                    backgroundColor: ['#10b981','#3b82f6','#8b5cf6','#f59e0b','#06b6d4','#ef4444'],
                    borderRadius: 8,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
        const chartVisites = new Chart(document.getElementById('visitesChart'), {
            type: 'line',
            data: {
                labels: <?= $visitesLabels ?>,
                datasets: [{
                    label: 'Visites',
                    data: <?= $visitesData ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#6366f1',
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
        function showChart(type) {
            const ac = document.getElementById('activityChart');
            const vc = document.getElementById('visitesChart');
            const bp = document.getElementById('btn-platform');
            const bv = document.getElementById('btn-visites');
            if (type === 'platform') {
                ac.classList.remove('hidden'); vc.classList.add('hidden');
                bp.className = 'text-xs px-3 py-1.5 bg-slate-800 text-white rounded-lg font-medium';
                bv.className = 'text-xs px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg font-medium hover:bg-slate-200';
            } else {
                vc.classList.remove('hidden'); ac.classList.add('hidden');
                bv.className = 'text-xs px-3 py-1.5 bg-slate-800 text-white rounded-lg font-medium';
                bp.className = 'text-xs px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg font-medium hover:bg-slate-200';
            }
        }
        </script>
    </div>

    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-xl font-bold">Statistiques</h3>
            <p class="text-sm text-gray-500 mt-1">Résumé de l'activité</p>
        </div>
        <div class="space-y-5">
            <div class="border-b border-gray-100 pb-4">
                <p class="font-medium">Utilisateurs inscrits</p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($stats['total_utilisateurs'] ?? 0) ?> comptes</p>
            </div>
            <div class="border-b border-gray-100 pb-4">
                <p class="font-medium">Annonces publiées</p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($stats['total_annonces'] ?? 0) ?> annonces</p>
            </div>
            <div class="border-b border-gray-100 pb-4">
                <p class="font-medium">Événements créés</p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($stats['total_evenements'] ?? 0) ?> événements</p>
            </div>
            <div>
                <p class="font-medium">Messages échangés</p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($stats['total_messages'] ?? 0) ?> messages</p>
            </div>
        </div>
    </div>
</section>

<section class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold">Prestations récentes</h3>
            <a href="/admin/services" class="text-sm text-gray-600 hover:text-black">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-sm text-gray-500 border-b border-gray-100">
                        <th class="pb-3 font-medium">Titre</th>
                        <th class="pb-3 font-medium">Catégorie</th>
                        <th class="pb-3 font-medium">Prix</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (!empty($prestations)): ?>
                        <?php foreach ($prestations as $p): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-4"><?= htmlspecialchars($p['titre'] ?? '') ?></td>
                            <td class="py-4"><?= htmlspecialchars($p['categorie'] ?? '') ?></td>
                            <td class="py-4"><?= htmlspecialchars($p['prix'] ?? '') ?>€</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="py-4 text-gray-400 text-center">Aucune prestation</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white admin-card rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold">Actions rapides</h3>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <a href="/admin/utilisateurs" class="rounded-2xl border border-gray-200 p-5 hover:bg-gray-50 transition">
                <h4 class="font-semibold">Ajouter un utilisateur</h4>
                <p class="text-sm text-gray-500 mt-2">Créer un nouveau compte dans la plateforme.</p>
            </a>
            <a href="/admin/evenements" class="rounded-2xl border border-gray-200 p-5 hover:bg-gray-50 transition">
                <h4 class="font-semibold">Créer un événement</h4>
                <p class="text-sm text-gray-500 mt-2">Planifier un atelier ou une rencontre.</p>
            </a>
            <a href="/admin/categories" class="rounded-2xl border border-gray-200 p-5 hover:bg-gray-50 transition">
                <h4 class="font-semibold">Ajouter une catégorie</h4>
                <p class="text-sm text-gray-500 mt-2">Structurer les prestations proposées.</p>
            </a>
            <a href="/admin/annonces" class="rounded-2xl border border-gray-200 p-5 hover:bg-gray-50 transition">
                <h4 class="font-semibold">Voir les demandes</h4>
                <p class="text-sm text-gray-500 mt-2">Consulter les validations en attente.</p>
            </a>
        </div>
    </div>
</section>