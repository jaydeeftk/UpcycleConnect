<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white"><?= t('adm_dash_welcome', 'Bienvenue, Admin') ?></h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium"><?= t('adm_dash_subtitle', 'Voici l\'état de votre plateforme aujourd\'hui.') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $kpis = [
            ['label' => t('adm_dash_stat_users', 'Utilisateurs'), 'val' => (int)($stats['total_utilisateurs'] ?? 0), 'icon' => 'fa-users', 'color' => 'text-purple-500', 'bg' => 'bg-purple-500/10'],
            ['label' => t('adm_dash_stat_annonces', 'Annonces'), 'val' => (int)($stats['total_annonces'] ?? 0), 'icon' => 'fa-bullhorn', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-500/10'],
            ['label' => t('adm_dash_stat_events', 'Événements'), 'val' => (int)($stats['total_evenements'] ?? 0), 'icon' => 'fa-calendar-days', 'color' => 'text-blue-500', 'bg' => 'bg-blue-500/10'],
            ['label' => t('adm_dash_stat_messages', 'Messages'), 'val' => (int)($stats['total_messages'] ?? 0), 'icon' => 'fa-envelope', 'color' => 'text-orange-500', 'bg' => 'bg-orange-500/10'],
        ];
        foreach($kpis as $s): ?>
        <div class="group bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-emerald-500/30 transition-all duration-300">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= $s['label'] ?></p>
            <div class="flex items-center justify-between">
                <span class="text-3xl font-black"><?= number_format($s['val'], 0, ',', ' ') ?></span>
                <div class="<?= $s['bg'] ?> <?= $s['color'] ?> w-10 h-10 rounded-xl flex items-center justify-center group-hover:rotate-12 transition-transform">
                    <i class="fas <?= $s['icon'] ?>"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
         <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="font-bold text-lg mb-6"><?= t('adm_dash_recent_prestations', 'Prestations récentes') ?></h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead><tr class="text-slate-400 border-slate-100 dark:border-slate-800"><th><?= t('adm_dash_col_titre', 'Titre') ?></th><th><?= t('adm_dash_col_categorie', 'Catégorie') ?></th><th><?= t('adm_dash_col_prix', 'Prix') ?></th></tr></thead>
                    <tbody>
                        <?php if (!empty($prestations)): ?>
                            <?php foreach (array_slice($prestations, 0, 6) as $presta): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors border-slate-100 dark:border-slate-800">
                                <td class="font-bold"><?= htmlspecialchars($presta['titre'] ?? '—') ?></td>
                                <td><span class="badge badge-ghost"><?= htmlspecialchars($presta['categorie'] ?? '—') ?></span></td>
                                <td class="font-mono text-emerald-500"><?= formatPrix($presta['prix'] ?? 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="border-slate-100 dark:border-slate-800">
                                <td colspan="3" class="text-center text-slate-400 py-8"><?= t('adm_dash_no_prestations', 'Aucune prestation pour le moment') ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="font-bold text-lg mb-6"><?= t('adm_dash_quick_actions', 'Actions Rapides') ?></h3>
            <div class="grid grid-cols-2 gap-4">
                <button onclick="window.location.href='/admin/utilisateurs/create'" class="p-6 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-emerald-500 hover:bg-emerald-500/5 transition-all text-left group">
                    <i class="fas fa-user-plus mb-3 text-emerald-500 group-hover:scale-110 transition-transform"></i>
                    <p class="font-bold text-sm"><?= t('adm_dash_add_user', 'Ajouter Utilisateur') ?></p>
                </button>
                <button onclick="window.location.href='/admin/evenements/create'" class="p-6 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-blue-500 hover:bg-blue-500/5 transition-all text-left group">
                    <i class="fas fa-calendar-plus mb-3 text-blue-500 group-hover:scale-110 transition-transform"></i>
                    <p class="font-bold text-sm"><?= t('adm_dash_create_event', 'Créer Événement') ?></p>
                </button>
            </div>
        </div>
    </div>
</div>