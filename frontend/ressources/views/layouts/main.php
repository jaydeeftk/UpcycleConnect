<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpcycleConnect</title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        (function() {
            var t = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (t === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#10b981',
                        secondary: '#3b82f6',
                        accent: '#f59e0b',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                        'slide-up': 'slideUp 0.4s ease-out forwards',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { transform: 'translateY(20px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } }
                    }
                }
            }
        };

        
        function applyTheme(theme) {
            const html = document.documentElement;
            if (theme === 'dark') {
                html.classList.add('dark');
                html.setAttribute('data-theme', 'dark');
            } else {
                html.classList.remove('dark');
                html.setAttribute('data-theme', 'light');
            }
        }

        const savedTheme = localStorage.getItem('theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(savedTheme);

        window.themeToggle = () => {
            const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        };
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        
        header {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            background-color: rgba(255, 255, 255, 0.8);
        }
        .dark header {
            background-color: rgba(2, 6, 23, 0.8);
        }

        
        .card, .bg-base-100 {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

       
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { 
            background: #10b981; 
            border-radius: 20px;
        }

       
        .reveal { animation: fade-in 0.6s ease-out; }

        .dark .bg-white { background-color: #0f172a !important; }
        .dark .bg-gray-50, .dark .bg-slate-50 { background-color: #020617 !important; }
        .dark .text-gray-900, .dark .text-slate-900, .dark .text-gray-800, .dark .text-slate-800 { color: #f1f5f9 !important; }
        .dark .border-gray-200, .dark .border-slate-200 { border-color: #1e293b !important; }
        .dark .shadow-sm { box-shadow: 0 1px 2px rgba(0,0,0,.4) !important; }
        .dark input:not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="radio"]):not([class*="bg-"]),
        .dark select:not([class*="bg-"]),
        .dark textarea:not([class*="bg-"]) {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        .dark .card { background-color: #0f172a !important; border-color: #1e293b !important; }
        .dark .dropdown-content, .dark .menu { background-color: #0f172a !important; border-color: #1e293b; }
        .dark .modal-box { background-color: #0f172a !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 selection:bg-emerald-500/30">

    <?php include __DIR__ . '/../components/front/navbar.php'; ?>

    <main class="reveal">
        <?php echo $content; ?>
    </main>

    <?php include __DIR__ . '/../components/front/footer.php'; ?>

    <script>
    (function() {
        var GEO_API = 'https://geo.api.gouv.fr/communes';
        var activeInput = null;
        var dropdown = null;

        function createDropdown() {
            var el = document.createElement('div');
            el.id = 'geo-dropdown';
            el.style.cssText = 'position:absolute;z-index:9999;background:white;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.1);max-height:240px;overflow-y:auto;min-width:240px;font-family:inherit';
            document.body.appendChild(el);
            return el;
        }

        function positionDropdown(input) {
            var rect = input.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + window.scrollY + 4) + 'px';
            dropdown.style.left = (rect.left + window.scrollX) + 'px';
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
                var cp = c.codesPostaux && c.codesPostaux[0] ? ' — ' + c.codesPostaux[0] : '';
                return '<div class="geo-item" style="padding:10px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid #f1f5f9;transition:background 120ms" data-value="' + c.nom + cp.replace('— ','') + '" data-label="' + c.nom + cp + '">' +
                    '<span style="font-weight:600">' + c.nom + '</span><span style="color:#94a3b8;font-size:11px">' + cp + '</span>' +
                    '</div>';
            }).join('');
            dropdown.querySelectorAll('.geo-item').forEach(function(item) {
                item.addEventListener('mouseenter', function() { this.style.background = '#f0fdf4'; });
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
        var obs = new MutationObserver(attachGeoComplete);
        obs.observe(document.body, { childList: true, subtree: true });
        document.addEventListener('click', function(e) {
            if (dropdown && !dropdown.contains(e.target)) hideDropdown();
        });
    })();
    </script>

</body>
</html>