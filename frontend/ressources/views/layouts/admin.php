<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { primary: '#10b981', darkBlue: '#0f172a' } } }
        };

        function applyTheme(t) {
            document.documentElement.setAttribute('data-theme', t);
            t === 'dark' ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', t);
        }

        function toggleSidebar() { /* sidebar fixe */ }

        function toggleNotifs() {
            const panel = document.getElementById('notif-panel');
            panel.classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyTheme(localStorage.getItem('theme') || 'dark');
            /* sidebar toujours visible */
            loadPendingCounts();
        });

        document.addEventListener('click', (e) => {
            const panel = document.getElementById('notif-panel');
            const btn = document.getElementById('notif-btn');
            if (panel && !panel.contains(e.target) && btn && !btn.contains(e.target)) {
                panel.classList.add('hidden');
            }
        });

        async function loadPendingCounts() {
            try {
                const token = '<?= $_SESSION['token'] ?? '' ?>';
                const r = await fetch('/api/admin/dashboard', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await r.json();
                const d = data.data || {};
                const annonces = d.annonces_en_attente || 0;
                const demandes = d.demandes_en_attente || 0;
                const total = annonces + demandes;

                const badge = document.getElementById('notif-badge');
                const list = document.getElementById('notif-list');

                if (total > 0) {
                    badge.textContent = total > 9 ? '9+' : total;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }

                let html = '';
                if (annonces > 0) {
                    html += `<a href="/admin/annonces" onclick="toggleNotifs()" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors rounded-xl">
                        <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-tag text-amber-500 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">${annonces} annonce${annonces > 1 ? 's' : ''} en attente</p>
                            <p class="text-xs text-slate-400">À valider ou refuser</p>
                        </div>
                    </a>`;
                }
                if (demandes > 0) {
                    html += `<a href="/admin/demandes" onclick="toggleNotifs()" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors rounded-xl">
                        <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-box text-blue-500 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">${demandes} demande${demandes > 1 ? 's' : ''} conteneur</p>
                            <p class="text-xs text-slate-400">À traiter</p>
                        </div>
                    </a>`;
                }
                if (html === '') {
                    html = '<div class="px-4 py-8 text-center text-slate-400 text-sm"><i class="fas fa-check-circle text-emerald-500 text-2xl mb-2 block"></i>Aucune action requise</div>';
                }
                list.innerHTML = html;
            } catch(e) {}
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }
        #sidebar { background-color: #0f172a !important; }
        .nav-link { transition: background 150ms ease-out; }
        .nav-link.active { background: #10b981; color: white !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .dark body { background-color: #020617; }
        body:not(.dark) { background-color: #f8fafc; }
        #sidebar [id^="sb-section-"] { max-height: 0; overflow: hidden; }

        .dark .bg-white { background-color: #0f172a !important; }
        .dark .text-slate-800, .dark .text-slate-900, .dark .text-gray-900, .dark .text-gray-800 { color: #f1f5f9 !important; }
        .dark .border-slate-200, .dark .border-gray-200 { border-color: #1e293b !important; }
        .dark input:not([class*="bg-"]), .dark select:not([class*="bg-"]), .dark textarea:not([class*="bg-"]) {
            background-color: #1e293b; color: #f1f5f9; border-color: #334155;
        }
        .dark table thead tr { color: #94a3b8; border-color: #1e293b; }
        .dark table tbody tr { border-color: #1e293b; }
        .dark table tbody tr:hover { background-color: rgba(30,41,59,0.5) !important; }
        .dark .badge { border-color: #334155; }
        .dark .modal-box, .dark .dropdown-content { background-color: #0f172a !important; border: 1px solid #1e293b; }
        .dark select option { background-color: #1e293b; }
    </style>
    <script>
        (function() {
            var t = localStorage.getItem('theme') || 'dark';
            if (t === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            }
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                document.documentElement.setAttribute('data-sb-collapsed', 'true');
            }
        })();
    </script>
</head>
<body class="h-screen flex text-slate-900 dark:text-slate-100 transition-colors duration-300">

    <aside id="sidebar" class="w-72 flex flex-col z-30 border-r border-slate-800">
        <div class="p-6 h-20 flex items-center gap-3 border-b border-slate-800/50 overflow-hidden">
            <div class="min-w-[40px] w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white">
                <i class="fas fa-recycle text-xl"></i>
            </div>
            <span class="sb-text font-bold text-lg text-white whitespace-nowrap">UpcycleConnect</span>
        </div>

        <nav class="flex-1 overflow-y-auto no-scrollbar py-6">
            <?php include __DIR__ . '/../components/admin/sidebar.php'; ?>
        </nav>

        <div class="p-4 border-t border-slate-800/50">
            <a href="/logout" class="flex items-center gap-4 px-4 py-3 text-red-400 hover:bg-red-500/10 rounded-xl transition-all">
                <i class="fas fa-sign-out-alt text-lg"></i>
                <span class="sb-text font-bold text-xs uppercase tracking-wider">Déconnexion</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-[#020617]">
        <header class="h-20 flex items-center justify-between px-8 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-[#0f172a]/50 backdrop-blur-md z-20">
            <div class="flex items-center gap-6">
                <button onclick="toggleSidebar()" class="p-2.5 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 transition-all active:scale-95">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>
                <h2 class="font-bold text-sm text-slate-400 uppercase tracking-widest sb-text">Administration</h2>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark')" class="p-2.5 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 transition-all">
                    <i class="fas fa-sun dark:hidden text-orange-400 text-lg"></i>
                    <i class="fas fa-moon hidden dark:inline text-blue-400 text-lg"></i>
                </button>

                <div class="relative">
                    <button id="notif-btn" onclick="toggleNotifs()" class="relative p-2.5 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 transition-all">
                        <i class="fas fa-bell text-lg"></i>
                        <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-md shadow-red-500/40"></span>
                    </button>
                    <div id="notif-panel" class="hidden absolute right-0 top-12 w-72 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl shadow-black/20 border border-slate-200 dark:border-slate-700 z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <p class="font-bold text-sm">Actions requises</p>
                            <button onclick="loadPendingCounts()" class="text-slate-400 hover:text-emerald-500 transition-colors text-xs"><i class="fas fa-sync-alt"></i></button>
                        </div>
                        <div id="notif-list" class="py-2 max-h-72 overflow-y-auto">
                            <div class="px-4 py-6 text-center text-slate-400 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i>Chargement...</div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pl-3 border-l border-slate-200 dark:border-slate-800">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold"><?= $_SESSION['admin_user'] ?? 'Admin' ?></p>
                        <p class="text-[9px] text-emerald-500 font-black uppercase">Administrateur</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center text-white font-black shadow-lg shadow-emerald-500/20">A</div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-10 no-scrollbar">
            <?php echo $content; ?>
        </main>
    </div>

    <script>
    (function() {
        var GEO_API = 'https://geo.api.gouv.fr/communes';
        var activeInput = null;
        var dropdown = null;

        function createDropdown() {
            var el = document.createElement('div');
            el.id = 'geo-dropdown';
            el.style.cssText = 'position:fixed;z-index:9999;background:#1e293b;border:1px solid #334155;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.4);max-height:240px;overflow-y:auto;min-width:240px;font-family:inherit';
            document.body.appendChild(el);
            return el;
        }

        function positionDropdown(input) {
            var rect = input.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + 4) + 'px';
            dropdown.style.left = rect.left + 'px';
            dropdown.style.width = rect.width + 'px';
        }

        function hideDropdown() {
            if (dropdown) dropdown.style.display = 'none';
        }

        function showSuggestions(input, results) {
            if (!dropdown) dropdown = createDropdown();
            positionDropdown(input);
            if (!results.length) { hideDropdown(); return; }
            dropdown.style.display = 'block';
            dropdown.innerHTML = results.map(function(c) {
                var cp = c.codesPostaux && c.codesPostaux[0] ? ' ' + c.codesPostaux[0] : '';
                return '<div class="geo-item" style="padding:10px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid #334155;transition:background 120ms;color:#e2e8f0" data-label="' + c.nom + (cp ? ' — ' + cp.trim() : '') + '">' +
                    '<span style="font-weight:600">' + c.nom + '</span><span style="color:#64748b;font-size:11px">' + (cp ? ' — ' + cp.trim() : '') + '</span>' +
                    '</div>';
            }).join('');
            dropdown.querySelectorAll('.geo-item').forEach(function(item) {
                item.addEventListener('mouseenter', function() { this.style.background = '#10b98115'; });
                item.addEventListener('mouseleave', function() { this.style.background = ''; });
                item.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    if (activeInput) activeInput.value = this.dataset.label.trim();
                    hideDropdown();
                });
            });
        }

        var debounceTimer;
        function onInput(e) {
            activeInput = e.target;
            clearTimeout(debounceTimer);
            var q = e.target.value.trim();
            if (q.length < 2) { hideDropdown(); return; }
            debounceTimer = setTimeout(function() {
                fetch(GEO_API + '?nom=' + encodeURIComponent(q) + '&fields=nom,codesPostaux&limit=8&boost=population')
                    .then(function(r) { return r.json(); })
                    .then(function(data) { showSuggestions(activeInput, data); })
                    .catch(function() {});
            }, 200);
        }

        function attachGeoComplete() {
            document.querySelectorAll('input[name="lieu"],input[name="localisation"],input[name="ville"],input[name="adresse"]').forEach(function(input) {
                if (input.dataset.geocomplete) return;
                input.dataset.geocomplete = '1';
                input.setAttribute('autocomplete', 'off');
                input.addEventListener('input', onInput);
                input.addEventListener('blur', function() { setTimeout(hideDropdown, 150); });
            });
        }

        document.addEventListener('DOMContentLoaded', attachGeoComplete);
        new MutationObserver(attachGeoComplete).observe(document.body, { childList: true, subtree: true });
        document.addEventListener('click', function(e) {
            if (dropdown && !dropdown.contains(e.target)) hideDropdown();
        });
    })();
    </script>

</body>
</html>
