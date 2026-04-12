<div class="flex gap-6 h-[calc(100vh-120px)]">

    <aside class="w-72 flex-shrink-0 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800">
            <h2 class="font-bold text-sm mb-3">Conversations</h2>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="search-input" oninput="filterConversations(this.value)" placeholder="Rechercher..." class="w-full pl-8 pr-3 py-2 text-xs bg-slate-100 dark:bg-slate-800 rounded-lg border-none focus:ring-1 focus:ring-emerald-500 outline-none">
            </div>
        </div>
        <div id="conv-list" class="flex-1 overflow-y-auto no-scrollbar"></div>
    </aside>

    <div class="flex-1 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden shadow-sm">
        <div id="chat-header" class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3 hidden">
            <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-md shadow-emerald-500/20" id="chat-avatar">?</div>
            <div>
                <p id="chat-name" class="font-bold text-sm"></p>
                <p id="chat-email" class="text-xs text-slate-400"></p>
            </div>
        </div>

        <div id="empty-conv" class="flex-1 flex flex-col items-center justify-center text-slate-400 gap-3">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <i class="fas fa-comments text-2xl opacity-30"></i>
            </div>
            <p class="text-sm font-medium">Sélectionnez une conversation</p>
        </div>

        <div id="messages-area" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/20 dark:bg-slate-950/10 no-scrollbar hidden"></div>

        <div id="reply-area" class="p-4 border-t border-slate-100 dark:border-slate-800 hidden">
            <div id="send-error" class="text-xs text-red-500 mb-2 hidden px-1 font-medium"></div>
            <div class="flex items-center gap-3 bg-slate-100 dark:bg-slate-800/60 p-2 rounded-2xl border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/30 transition-all">
                <input type="text" id="reply-input" onkeypress="if(event.key==='Enter') sendReply()" class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2 px-2" placeholder="Répondre...">
                <button onclick="sendReply()" id="send-btn" class="bg-emerald-500 text-white px-4 py-2.5 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all active:scale-95 text-sm font-semibold whitespace-nowrap">
                    <i class="fas fa-paper-plane mr-1.5"></i>Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var token = <?= json_encode($_SESSION['token'] ?? '') ?>;
    var allConvs = [];
    var selectedUserId = null;

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function formatDate(d) {
        if (!d) return '';
        var dt = new Date(d.replace(' ','T'));
        return dt.toLocaleDateString('fr-FR',{day:'2-digit',month:'short'}) + ' ' + dt.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
    }

    function initials(nom, prenom) {
        return ((prenom||'').charAt(0) + (nom||'').charAt(0)).toUpperCase() || '?';
    }

    function renderConvList(convs) {
        var el = document.getElementById('conv-list');
        if (!convs.length) {
            el.innerHTML = '<div class="p-6 text-center text-slate-400 text-xs">Aucune conversation</div>';
            return;
        }
        el.innerHTML = convs.map(function(c) {
            var init = initials(c.nom, c.prenom);
            var active = c.id_utilisateur == selectedUserId ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent';
            return '<div class="conv-item flex items-center gap-3 px-4 py-3 cursor-pointer border rounded-xl mx-2 my-1 transition-all ' + active + '" data-uid="' + c.id_utilisateur + '" data-nom="' + esc(c.nom) + '" data-prenom="' + esc(c.prenom) + '" data-email="' + esc(c.email) + '" onclick="selectConv(this)">' +
                '<div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0">' + init + '</div>' +
                '<div class="min-w-0 flex-1">' +
                    '<p class="text-sm font-semibold truncate">' + esc(c.prenom) + ' ' + esc(c.nom) + '</p>' +
                    '<p class="text-xs text-slate-400 truncate">' + esc(c.dernier_message || '') + '</p>' +
                '</div>' +
                '</div>';
        }).join('');
    }

    function renderMessages(msgs) {
        var area = document.getElementById('messages-area');
        if (!msgs.length) {
            area.innerHTML = '<div class="text-center text-slate-400 text-sm py-8">Aucun message</div>';
            return;
        }
        area.innerHTML = msgs.map(function(m) {
            if (m.is_admin) {
                return '<div class="flex items-end gap-2 flex-row-reverse">' +
                    '<div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0 mb-1"><i class="fas fa-headset"></i></div>' +
                    '<div class="max-w-[70%]">' +
                    '<p class="text-[10px] text-slate-400 mb-1 font-semibold text-right">Support</p>' +
                    '<div class="bg-emerald-500 text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm shadow-md shadow-emerald-500/20">' + esc(m.contenu) + '</div>' +
                    '<p class="text-[10px] text-slate-400 mt-1 text-right">' + formatDate(m.date_envoi) + '</p>' +
                    '</div></div>';
            } else {
                return '<div class="flex items-end gap-2">' +
                    '<div class="w-7 h-7 bg-slate-300 dark:bg-slate-600 rounded-full flex items-center justify-center text-xs flex-shrink-0 mb-1"><i class="fas fa-user"></i></div>' +
                    '<div class="max-w-[70%]">' +
                    '<p class="text-[10px] text-slate-400 mb-1 font-semibold">Utilisateur</p>' +
                    '<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl rounded-bl-sm px-4 py-2.5 text-sm shadow-sm">' + esc(m.contenu) + '</div>' +
                    '<p class="text-[10px] text-slate-400 mt-1">' + formatDate(m.date_envoi) + '</p>' +
                    '</div></div>';
            }
        }).join('');
        area.scrollTop = area.scrollHeight;
    }

    window.selectConv = function(el) {
        selectedUserId = el.dataset.uid;
        document.querySelectorAll('.conv-item').forEach(function(i) {
            i.classList.remove('bg-emerald-50','dark:bg-emerald-500/10','border-emerald-200','dark:border-emerald-500/20');
            i.classList.add('border-transparent');
        });
        el.classList.remove('border-transparent');
        el.classList.add('bg-emerald-50','border-emerald-200');

        document.getElementById('chat-header').classList.remove('hidden');
        document.getElementById('empty-conv').classList.add('hidden');
        document.getElementById('messages-area').classList.remove('hidden');
        document.getElementById('reply-area').classList.remove('hidden');
        document.getElementById('send-error').classList.add('hidden');

        var nom = el.dataset.prenom + ' ' + el.dataset.nom;
        document.getElementById('chat-name').textContent = nom.trim();
        document.getElementById('chat-email').textContent = el.dataset.email;
        document.getElementById('chat-avatar').textContent = ((el.dataset.prenom||'').charAt(0) + (el.dataset.nom||'').charAt(0)).toUpperCase() || '?';

        loadConversation(selectedUserId);
    };

    function loadConversation(uid) {
        var area = document.getElementById('messages-area');
        area.innerHTML = '<div class="text-center text-slate-400 text-sm py-8"><i class="fas fa-spinner fa-spin mr-2"></i>Chargement...</div>';
        fetch('/api/messages/user/' + uid, {
            headers: { 'Authorization': 'Bearer ' + token }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) { renderMessages(d.data || []); })
        .catch(function() { area.innerHTML = '<div class="text-center text-red-400 text-sm py-8">Erreur de chargement</div>'; });
    }

    window.sendReply = function() {
        var input = document.getElementById('reply-input');
        var val = input.value.trim();
        var errEl = document.getElementById('send-error');
        errEl.classList.add('hidden');
        if (!val || !selectedUserId) return;

        var btn = document.getElementById('send-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i>Envoi...';

        fetch('/api/admin/messages/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ id_utilisateur: parseInt(selectedUserId), contenu: val })
        })
        .then(function(r) {
            if (!r.ok) return r.json().then(function(d) { throw new Error(d.error || 'Erreur'); });
            return r.json();
        })
        .then(function() {
            input.value = '';
            loadConversation(selectedUserId);
        })
        .catch(function(e) {
            errEl.textContent = e.message || 'Erreur lors de l\'envoi.';
            errEl.classList.remove('hidden');
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane mr-1.5"></i>Envoyer';
        });
    };

    window.filterConversations = function(q) {
        var filtered = q.trim() === '' ? allConvs : allConvs.filter(function(c) {
            var s = (c.nom + ' ' + c.prenom + ' ' + c.email).toLowerCase();
            return s.includes(q.toLowerCase());
        });
        renderConvList(filtered);
        document.querySelectorAll('.conv-item').forEach(function(el) {
            if (el.dataset.uid == selectedUserId) {
                el.classList.remove('border-transparent');
                el.classList.add('bg-emerald-50','border-emerald-200');
            }
        });
    };

    function loadConversations() {
        fetch('/api/admin/messages', {
            headers: { 'Authorization': 'Bearer ' + token }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            allConvs = d.data || [];
            renderConvList(allConvs);
        })
        .catch(function() {
            document.getElementById('conv-list').innerHTML = '<div class="p-6 text-center text-red-400 text-xs">Erreur de chargement</div>';
        });
    }

    document.addEventListener('DOMContentLoaded', loadConversations);
})();
</script>
