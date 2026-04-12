<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Finances & Statistiques</h2>
        <p class="text-slate-500">Vue d'ensemble de vos revenus générés</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Chiffre d'Affaires TTC</h3>
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center"><i class="fas fa-euro-sign text-lg"></i></div>
            </div>
            <p class="text-3xl font-bold text-slate-800"><?= number_format($finances['total_ttc'] ?? 0, 2, ',', ' ') ?> €</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total HT</h3>
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center"><i class="fas fa-file-invoice-dollar text-lg"></i></div>
            </div>
            <p class="text-3xl font-bold text-slate-800"><?= number_format($finances['total_ht'] ?? 0, 2, ',', ' ') ?> €</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Commissions générées</h3>
                <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center"><i class="fas fa-hand-holding-usd text-lg"></i></div>
            </div>
            <p class="text-3xl font-bold text-slate-800"><?= number_format($finances['total_commissions'] ?? 0, 2, ',', ' ') ?> €</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Factures</h3>
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center"><i class="fas fa-receipt text-lg"></i></div>
            </div>
            <p class="text-3xl font-bold text-slate-800"><?= intval($finances['nb_factures'] ?? 0) ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Évolution du CA (12 derniers mois)</h3>
        <canvas id="caChart" height="120"></canvas>
    </div>
    <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Répartition par Statut</h3>
        <canvas id="statutChart" height="250"></canvas>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const finances = <?= json_encode($finances ?? []) ?>;

        const caData = finances.ca_par_mois || [];
        const labelsCA = caData.map(item => item.mois);
        const dataCA = caData.map(item => item.ca);

        new Chart(document.getElementById('caChart'), {
            type: 'line',
            data: {
                labels: labelsCA.length ? labelsCA : ['Aucune donnée'],
                datasets: [{
                    label: 'Chiffre d\'Affaires TTC (€)',
                    data: dataCA.length ? dataCA : [0],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#10b981',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });

        const statuts = finances.statuts || {};
        const labelsStatut = Object.keys(statuts);
        const dataStatut = Object.values(statuts);

        new Chart(document.getElementById('statutChart'), {
            type: 'doughnut',
            data: {
                labels: labelsStatut.length ? labelsStatut : ['Aucune facture'],
                datasets: [{
                    data: dataStatut.length ? dataStatut : [1],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                }
            }
        });
    });
</script>