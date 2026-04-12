<section class="max-w-5xl mx-auto px-6 lg:px-10 py-16 reveal">
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center">
                <i class="fas fa-envelope text-emerald-600"></i>
            </div>
            <span class="text-sm font-medium text-emerald-600 uppercase tracking-wide">Messagerie</span>
        </div>
        <h1 class="text-4xl font-extrabold tracking-tight">Mes messages</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">Échangez directement avec le support UpcycleConnect.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl flex flex-col overflow-hidden" style="height:600px">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-md shadow-emerald-500/30">
                <i class="fas fa-headset text-sm"></i>
            </div>
            <div>
                <p class="font-bold text-sm">Support UpcycleConnect</p>
                <p class="text-[11px] text-emerald-500 font-semibold">En ligne</p>
            </div>
        </div>

        <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/30 dark:bg-slate-950/10 no-scrollbar">
            <div id="empty-state" class="h-full flex flex-col items-center justify-center text-slate-400 gap-3">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="fas fa-comments text-2xl opacity-40"></i>
                </div>
                <p class="text-sm font-medium">Aucun message pour l'instant</p>
                <p class="text-xs opacity-60">Envoyez votre premier message ci-dessous.</p>
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            <div id="send-error" class="text-xs text-red-500 mb-2 hidden font-medium px-1"></div>
            <div class="flex items-center gap-3 bg-slate-100 dark:bg-slate-800/60 p-2 rounded-2xl border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/30 transition-all duration-300">
                <input type="text" id="message-input" onkeypress="if(event.key==='Enter') sendMessage()" class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2 px-2 dark:text-slate-100" placeholder="Écrivez votre message...">
                <button onclick="sendMessage()" id="send-btn" class="bg-emerald-500 text-white px-5 py-3 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all active:scale-95 text-sm font-semibold">
                    <i class="fas fa-paper-plane mr-1.5"></i>Envoyer
                </button>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    var token = <?= json_encode($_SESSION['token'] ?? '') ?>;
    var userId = <?= json_encode($_SESSION['user']['id'] ?? 0) ?>;

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function formatDate(d) {
        if (!d) return '';
        var dt = new Date(d.replace(' ', 'T'));
        return dt.toLocaleDateString('fr-FR', {day:'2-digit',month:'short'}) + ' ' + dt.toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});
    }

    function renderMessages(msgs) {
        var container = document.getElementById('messages-container');
        var empty = document.getElementById('empty-state');
        if (!msgs || msgs.length === 0) {
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');
        var html = '';
        msgs.forEach(function(m) {
            if (m.is_admin) {
                html += '<div class="flex items-end gap-2">';
                html += '<div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0 mb-1"><i class="fas fa-headset"></i></div>';
                html += '<div class="max-w-[70%]">';
                html += '<p class="text-[10px] text-slate-400 mb-1 font-semibold">Support</p>';
                html += '<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl rounded-bl-sm px-4 py-2.5 text-sm shadow-sm">' + esc(m.contenu) + '</div>';
                html += '<p class="text-[10px] text-slate-400 mt-1">' + formatDate(m.date_envoi) + '</p>';
                html += '</div></div>';
            } else {
                html += '<div class="flex items-end gap-2 flex-row-reverse">';
                html += '<div class="w-7 h-7 bg-slate-300 dark:bg-slate-600 rounded-full flex items-center justify-center text-slate-600 dark:text-slate-200 text-xs flex-shrink-0 mb-1"><i class="fas fa-user"></i></div>';
                html += '<div class="max-w-[70%]">';
                html += '<p class="text-[10px] text-slate-400 mb-1 font-semibold text-right">Vous</p>';
                html += '<div class="bg-emerald-500 text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm shadow-md shadow-emerald-500/20">' + esc(m.contenu) + '</div>';
                html += '<p class="text-[10px] text-slate-400 mt-1 text-right">' + formatDate(m.date_envoi) + '</p>';
                html += '</div></div>';
            }
        });
        container.innerHTML = html;
        container.scrollTop = container.scrollHeight;
    }

    function loadMessages() {
        fetch('/api/messages/user/' + userId, {
            headers: { 'Authorization': 'Bearer ' + token }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            renderMessages(data.data || []);
        })
        .catch(function() {});
    }

    window.sendMessage = function() {
        var input = document.getElementById('message-input');
        var val = input.value.trim();
        var errEl = document.getElementById('send-error');
        errEl.classList.add('hidden');
        if (!val) return;

        var btn = document.getElementById('send-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i>Envoi...';

        fetch('/api/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ contenu: val })
        })
        .then(function(r) {
            if (!r.ok) return r.json().then(function(d) { throw new Error(d.error || 'Erreur'); });
            return r.json();
        })
        .then(function() {
            input.value = '';
            loadMessages();
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

    document.addEventListener('DOMContentLoaded', loadMessages);
})();
</script>
