<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Bienvenue, Admin</h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Voici l'état de votre plateforme aujourd'hui.</p>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-ghost bg-white dark:bg-slate-800 shadow-sm border-slate-200 dark:border-slate-700 hover:scale-105 active:scale-95 transition-all">
                <i class="fas fa-download mr-2"></i> Exporter
            </button>
            <button class="btn btn-primary shadow-lg shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all">
                <i class="fas fa-plus mr-2"></i> Nouvelle action
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php 
        $stats = [
            ['label' => 'Visites aujourd\'hui', 'val' => '222', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-500/10'],
            ['label' => 'Visites 7 jours', 'val' => '1,554', 'color' => 'text-blue-500', 'bg' => 'bg-blue-500/10'],
            ['label' => 'Utilisateurs', 'val' => '5', 'color' => 'text-purple-500', 'bg' => 'bg-purple-500/10'],
            ['label' => 'Messages', 'val' => '19', 'color' => 'text-orange-500', 'bg' => 'bg-orange-500/10'],
        ];
        foreach($stats as $s): ?>
        <div class="group bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-emerald-500/30 transition-all duration-300">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= $s['label'] ?></p>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-black"><?= $s['val'] ?></span>
                <div class="<?= $s['bg'] ?> <?= $s['color'] ?> w-10 h-10 rounded-xl flex items-center justify-center group-hover:rotate-12 transition-transform">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 class="font-bold text-lg">Activité Générale</h3>
                <select class="select select-sm select-bordered bg-slate-50 dark:bg-slate-800">
                    <option>7 derniers jours</option>
                    <option>30 derniers jours</option>
                </select>
            </div>
            <div class="h-[300px]">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="font-bold text-lg mb-6">Dernières Activités</h3>
            <div class="space-y-6">
                <?php for($i=0; $i<4; $i++): ?>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex-shrink-0"></div>
                    <div class="flex-1">
                        <p class="text-sm font-bold">Nouvelle annonce</p>
                        <p class="text-xs text-slate-400">Il y a 2 minutes</p>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
         <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="font-bold text-lg mb-6">Prestations récentes</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead><tr class="text-slate-400 border-slate-100 dark:border-slate-800"><th>Titre</th><th>Catégorie</th><th>Prix</th></tr></thead>
                    <tbody>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer border-slate-100 dark:border-slate-800">
                            <td class="font-bold">Réparation vélo</td>
                            <td><span class="badge badge-ghost">Reparation</span></td>
                            <td class="font-mono text-emerald-500">49.9€</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="font-bold text-lg mb-6">Actions Rapides</h3>
            <div class="grid grid-cols-2 gap-4">
                <button class="p-6 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-emerald-500 hover:bg-emerald-500/5 transition-all text-left group">
                    <i class="fas fa-user-plus mb-3 text-emerald-500 group-hover:scale-110 transition-transform"></i>
                    <p class="font-bold text-sm">Ajouter Utilisateur</p>
                </button>
                <button class="p-6 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-blue-500 hover:bg-blue-500/5 transition-all text-left group">
                    <i class="fas fa-calendar-plus mb-3 text-blue-500 group-hover:scale-110 transition-transform"></i>
                    <p class="font-bold text-sm">Créer Événement</p>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('mainChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                data: [65, 59, 80, 81, 56, 55, 70],
                borderColor: '#10b981',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                backgroundColor: gradient,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#10b981',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, border: { display: false }, ticks: { color: '#94a3b8', font: { weight: '600' } } }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
});
</script>