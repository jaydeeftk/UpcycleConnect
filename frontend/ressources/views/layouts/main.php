<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require __DIR__ . '/../components/csrf_head.php'; ?>
    <title>UpcycleConnect</title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <?php $osAppId = getenv('ONESIGNAL_APP_ID'); if (!empty($osAppId)): ?>
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({ appId: "<?= htmlspecialchars($osAppId, ENT_QUOTES) ?>", allowLocalhostAsSecureOrigin: true });
            <?php if (isset($_SESSION['user']['id'])): ?>
            try { await OneSignal.login("<?= (int)$_SESSION['user']['id'] ?>"); } catch (e) {}
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>

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
        .dark .text-gray-700, .dark .text-slate-700 { color: #e2e8f0 !important; }
        .dark .text-gray-600, .dark .text-slate-600 { color: #cbd5e1 !important; }
        .dark .text-gray-500, .dark .text-slate-500 { color: #94a3b8 !important; }
        .dark .border-gray-200, .dark .border-gray-300, .dark .border-slate-200 { border-color: #334155 !important; }

        .dark .bg-green-50,.dark .bg-emerald-50,.dark .bg-teal-50,.dark .bg-lime-50,.dark .bg-green-100,.dark .bg-emerald-100,.dark .bg-teal-100 { background-color: rgba(16,185,129,.14) !important; }
        .dark .bg-blue-50,.dark .bg-sky-50,.dark .bg-cyan-50,.dark .bg-indigo-50,.dark .bg-blue-100,.dark .bg-sky-100,.dark .bg-indigo-100 { background-color: rgba(59,130,246,.14) !important; }
        .dark .bg-purple-50,.dark .bg-violet-50,.dark .bg-fuchsia-50,.dark .bg-purple-100,.dark .bg-violet-100 { background-color: rgba(168,85,247,.14) !important; }
        .dark .bg-amber-50,.dark .bg-yellow-50,.dark .bg-orange-50,.dark .bg-amber-100,.dark .bg-yellow-100,.dark .bg-orange-100 { background-color: rgba(245,158,11,.14) !important; }
        .dark .bg-red-50,.dark .bg-rose-50,.dark .bg-pink-50,.dark .bg-red-100,.dark .bg-rose-100,.dark .bg-pink-100 { background-color: rgba(244,63,94,.14) !important; }
        .dark .text-green-600,.dark .text-emerald-600,.dark .text-teal-600,.dark .text-green-700,.dark .text-emerald-700,.dark .text-teal-700,.dark .text-green-800,.dark .text-emerald-800 { color:#6ee7b7 !important; }
        .dark .text-blue-600,.dark .text-sky-600,.dark .text-indigo-600,.dark .text-blue-700,.dark .text-sky-700,.dark .text-indigo-700,.dark .text-blue-800,.dark .text-indigo-800 { color:#93c5fd !important; }
        .dark .text-purple-600,.dark .text-violet-600,.dark .text-purple-700,.dark .text-violet-700,.dark .text-purple-800 { color:#d8b4fe !important; }
        .dark .text-amber-600,.dark .text-yellow-600,.dark .text-orange-600,.dark .text-amber-700,.dark .text-yellow-700,.dark .text-orange-700,.dark .text-amber-800,.dark .text-orange-800 { color:#fcd34d !important; }
        .dark .text-red-600,.dark .text-rose-600,.dark .text-pink-600,.dark .text-red-700,.dark .text-rose-700,.dark .text-pink-700,.dark .text-red-800,.dark .text-rose-800 { color:#fca5a5 !important; }
        .dark .bg-gray-100,.dark .bg-slate-100 { background-color:#1e293b !important; }
        .dark .bg-gray-200,.dark .bg-slate-200 { background-color:#334155 !important; }
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
<body class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 selection:bg-emerald-500/30">

    <?php include __DIR__ . '/../components/front/navbar.php'; ?>
    <?php include __DIR__ . '/../components/front/toast.php'; ?>

    <main class="reveal flex-1">
        <?php echo $content; ?>
    </main>

    <?php include __DIR__ . '/../components/front/footer.php'; ?>
    <?php include __DIR__ . '/../components/front/tutoriel.php'; ?>

    <script>
    (function() {
        var GEO_API = 'https://geo.api.gouv.fr/communes';
        var activeInput = null;
        var dropdown = null;

        function createDropdown() {
            var el = document.createElement('div');
            el.id = 'geo-dropdown';
            var isDark = document.documentElement.classList.contains('dark');
            var bg  = isDark ? '#1e293b' : '#ffffff';
            var bdr = isDark ? '#334155' : '#e2e8f0';
            el.style.cssText = 'position:absolute;z-index:9999;background:' + bg + ';border:1px solid ' + bdr + ';border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.1);max-height:240px;overflow-y:auto;min-width:240px;font-family:inherit';
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
            var isDark = document.documentElement.classList.contains('dark');
            var txtMain  = isDark ? '#f1f5f9' : '#1e293b';
            var txtHint  = isDark ? '#94a3b8' : '#94a3b8';
            var bdrItem  = isDark ? '#334155' : '#f1f5f9';
            var bgHover  = isDark ? '#334155' : '#f0fdf4';
            dropdown.innerHTML = results.map(function(c) {
                var cps = (c.codesPostaux || []).filter(Boolean);
                var hint = cps.length ? (cps.length > 1 ? cps[0] + '…' : cps[0]) : '';
                return '<div class="geo-item" style="padding:10px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid ' + bdrItem + ';transition:background 120ms;color:' + txtMain + '" data-nom="' + c.nom + '" data-cps="' + encodeURIComponent(JSON.stringify(cps)) + '">' +
                    '<span style="font-weight:600">' + c.nom + '</span>' + (hint ? '<span style="color:' + txtHint + ';font-size:11px"> — ' + hint + '</span>' : '') +
                    '</div>';
            }).join('');
            dropdown.querySelectorAll('.geo-item').forEach(function(item) {
                item.addEventListener('mouseenter', function() { this.style.background = bgHover; });
                item.addEventListener('mouseleave', function() { this.style.background = ''; });
                item.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    selectCommune(item.dataset.nom, JSON.parse(decodeURIComponent(item.dataset.cps)));
                    hideDropdown();
                });
            });
        }

        function selectCommune(nom, cps) {
            if (!activeInput) return;
            activeInput.value = nom;
            if (activeInput.form) remplirCodePostal(activeInput.form, cps);
        }

        function remplirCodePostal(form, cps) {
            var field = form.querySelector('[name="code_postal"]');
            if (!field) return;
            if (cps.length > 1) {
                if (field.tagName !== 'SELECT') {
                    var sel = document.createElement('select');
                    sel.name = 'code_postal';
                    sel.className = field.className;
                    sel.required = field.required;
                    field.parentNode.replaceChild(sel, field);
                    field = sel;
                }
                field.innerHTML = cps.map(function(code) { return '<option value="' + code + '">' + code + '</option>'; }).join('');
                field.value = cps[0];
            } else {
                if (field.tagName !== 'INPUT') {
                    var inp = document.createElement('input');
                    inp.type = 'text';
                    inp.name = 'code_postal';
                    inp.className = field.className;
                    inp.required = field.required;
                    inp.maxLength = 5;
                    field.parentNode.replaceChild(inp, field);
                    field = inp;
                }
                field.value = cps[0] || '';
            }
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
    <script>
    function confirmer(m,c){var d=document.documentElement.classList.contains('dark');var s=d?'#1e293b':'#fff',t=d?'#f1f5f9':'#0f172a',b=d?'#334155':'#e2e8f0',u=d?'#94a3b8':'#64748b';var o=document.createElement('div');o.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;display:flex;align-items:center;justify-content:center';o.innerHTML='<div style="background:'+s+';border:1px solid '+b+';border-radius:12px;padding:24px;max-width:360px;width:90%;text-align:center;font-family:inherit"><p style="color:'+t+';margin:0 0 20px;font-size:15px">'+m+'</p><button type="button" id="uc-c" style="margin-right:8px;padding:8px 20px;border:1px solid '+b+';border-radius:8px;background:transparent;color:'+u+';cursor:pointer">Annuler</button><button type="button" id="uc-o" style="padding:8px 20px;border:none;border-radius:8px;background:#ef4444;color:#fff;cursor:pointer">Confirmer</button></div>';document.body.appendChild(o);o.querySelector('#uc-c').onclick=function(){o.remove()};o.querySelector('#uc-o').onclick=function(){o.remove();c()};o.addEventListener('click',function(e){if(e.target===o)o.remove()})}
    function ucConfirm(el,m){confirmer(m,function(){if(el.tagName==='A'){window.location.href=el.href}else{var f=el.closest?el.closest('form'):null;if(f)f.submit()}});return false}
    function toast(m,ok){var c=ok?'#22c55e':'#ef4444';var o=document.createElement('div');o.style.cssText='position:fixed;top:20px;right:20px;z-index:99999;background:'+c+';color:#fff;padding:12px 20px;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,.25);font-family:inherit;font-size:14px;max-width:340px';o.textContent=m;document.body.appendChild(o);setTimeout(function(){o.style.transition='opacity .3s';o.style.opacity='0';setTimeout(function(){o.remove()},300)},3500)}
    </script>

</body>
</html>