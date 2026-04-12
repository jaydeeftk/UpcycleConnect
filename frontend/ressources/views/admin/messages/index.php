<?php $token = $_SESSION['token'] ?? ''; ?>
<style>
.msg-bubble-support { background: linear-gradient(135deg, #10b981, #059669); }
.conv-active { background: linear-gradient(90deg, rgba(16,185,129,0.12), transparent); border-left: 3px solid #10b981; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.msg-in { animation: msgIn 0.18s ease-out; }
@keyframes msgIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
</style>

<div class="flex rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xl" style="height:calc(100vh - 160px); min-height:520px;">

    <aside class="w-72 flex-shrink-0 flex flex-col border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Messages internes</h2>
                <p class="text-xs text-slate-400 mt-0.5" id="conv-count">Chargement...</p>
            </div>
            <button onclick="loadConvList()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-emerald-500 transition-colors">
                <i class="fas fa-sync-alt text-xs"></i>
            </button>
        </div>
        <div class="p-3 border-b border-slate-100 dark:border-slate-800">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="search-input" oninput="filterConvs(this.value)" placeholder="Rechercher..."
                    class="w-full pl-8 pr-3 py-2 text-xs bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 focus:ring-1 focus:ring-emerald-500 outline-none text-slate-700 dark:text-slate-300">
            </div>
        </div>
        <div id="conv-list" class="flex-1 overflow-y-auto no-scrollbar">
            <div class="flex items-center justify-center h-20 text-slate-400 text-xs gap-2">
                <i class="fas fa-spinner fa-spin"></i> Chargement...
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col bg-slate-50 dark:bg-slate-950 min-w-0">

        <div id="chat-header" class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" style="display:none;">
            <div class="flex items-center gap-3">
                <div id="chat-avatar" class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-emerald-500/20 flex-shrink-0">?</div>
                <div class="flex-1 min-w-0">
                    <p id="chat-name" class="font-bold text-sm text-slate-800 dark:text-slate-100 truncate"></p>
                    <p id="chat-email" class="text-xs text-slate-400 truncate"></p>
                </div>
                <button onclick="loadHistory(selectedUID)" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-emerald-500 transition-colors">
                    <i class="fas fa-sync-alt text-xs"></i>
                </button>
            </div>
        </div>

        <div id="empty-state" class="flex-1 flex flex-col items-center justify-center text-slate-400 gap-4">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <i class="fas fa-comments text-2xl opacity-30"></i>
            </div>
            <div class="text-center">
                <p class="font-semibold text-sm text-slate-500 dark:text-slate-400">Selectionnez une conversation</p>
                <p class="text-xs text-slate-400 mt-1">Choisissez un utilisateur dans la liste</p>
            </div>
        </div>

        <div id="messages-area" class="flex-1 overflow-y-auto no-scrollbar p-5 space-y-3" style="display:none;"></div>

        <div id="reply-box" class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4" style="display:none;">
            <div class="flex items-end gap-3">
                <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/30 transition-all px-4 py-3">
                    <textarea id="reply-input" rows="1"
                        oninput="autoResize(this)"
                        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendReply();}"
                        class="w-full bg-transparent border-none focus:ring-0 text-sm resize-none leading-5 max-h-32 overflow-y-auto outline-none text-slate-800 dark:text-slate-100 placeholder-slate-400"
                        placeholder="Repondre a cet utilisateur..."></textarea>
                </div>
                <button onclick="sendReply()" id="send-btn"
                    class="bg-emerald-500 text-white w-10 h-10 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all active:scale-95 flex-shrink-0 flex items-center justify-center">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </div>
            <div id="send-error" class="hidden mt-2 text-xs text-red-500 font-medium px-1"></div>
        </div>
    </div>
</div>

<script>
(function() {
    var TOKEN = <?= json_encode($token) ?>;
    var selectedUID = null;
    var allConvs = [];
    var pollInterval = null;

    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'); }
    function fmt(d) {
        if (!d) return '';
        try { return new Date(d.replace(' ','T')).toLocaleString('fr-FR',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}); }
        catch(e) { return d.substring(0,16); }
    }
    function initials(nom, prenom) { return (((prenom||'')[0]||'')+((nom||'')[0]||'')).toUpperCase()||'U'; }

    window.loadConvList = function() {
        if (!TOKEN) { document.getElementById('conv-list').innerHTML='<div class="p-4 text-center text-red-400 text-xs">Session expiree</div>'; return; }
        fetch('/api/admin/messages', {headers:{'Authorization':'Bearer '+TOKEN}})
            .then(function(r){return r.json();})
            .then(function(d){
                allConvs = d.data||d||[];
                document.getElementById('conv-count').textContent = allConvs.length+' conversation'+(allConvs.length>1?'s':'');
                renderConvList(allConvs);
                restoreActiveConv();
            })
            .catch(function(){ document.getElementById('conv-list').innerHTML='<div class="p-4 text-center text-red-400 text-xs">Erreur de chargement</div>'; });
    };

    function renderConvList(convs) {
        var el = document.getElementById('conv-list');
        if (!convs.length) { el.innerHTML='<div class="p-6 text-center text-slate-400 text-xs">Aucune conversation</div>'; return; }
        el.innerHTML = convs.map(function(c) {
            var init = initials(c.nom, c.prenom);
            var isActive = c.id_utilisateur === selectedUID;
            return '<div class="conv-item flex items-center gap-3 px-4 py-3 cursor-pointer transition-all duration-150 '+(isActive?'conv-active':'hover:bg-slate-50 dark:hover:bg-slate-800/50')+'" data-uid="'+c.id_utilisateur+'" data-nom="'+esc(c.nom||'')+'" data-prenom="'+esc(c.prenom||'')+'" data-email="'+esc(c.email||'')+'" onclick="selectConv(this)">'
                +'<div class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">'+init+'</div>'
                +'<div class="min-w-0 flex-1">'
                +'<p class="text-sm font-semibold truncate text-slate-800 dark:text-slate-100">'+esc((c.prenom||'')+' '+(c.nom||''))+'</p>'
                +'<p class="text-xs text-slate-400 truncate">'+esc(c.dernier_message||c.email||'')+'</p>'
                +'</div></div>';
        }).join('');
    }

    function restoreActiveConv() {
        document.querySelectorAll('.conv-item').forEach(function(el) {
            var isActive = parseInt(el.dataset.uid) === selectedUID;
            if (isActive) { el.classList.add('conv-active'); el.classList.remove('hover:bg-slate-50','dark:hover:bg-slate-800/50'); }
            else { el.classList.remove('conv-active'); el.classList.add('hover:bg-slate-50','dark:hover:bg-slate-800/50'); }
        });
    }

    window.selectConv = function(el) {
        selectedUID = parseInt(el.dataset.uid);
        restoreActiveConv();
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('messages-area').style.display = 'flex';
        document.getElementById('messages-area').style.flexDirection = 'column';
        document.getElementById('reply-box').style.display = 'block';
        document.getElementById('chat-header').style.display = 'block';
        document.getElementById('send-error').classList.add('hidden');
        document.getElementById('chat-name').textContent = ((el.dataset.prenom||'')+' '+(el.dataset.nom||'')).trim()||'Utilisateur';
        document.getElementById('chat-email').textContent = el.dataset.email||'';
        document.getElementById('chat-avatar').textContent = initials(el.dataset.nom, el.dataset.prenom);
        loadHistory(selectedUID);
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(function(){ if(selectedUID) silentRefresh(); }, 5000);
    };

    window.loadHistory = function(uid) {
        var area = document.getElementById('messages-area');
        area.innerHTML = '<div class="flex items-center justify-center py-8 text-slate-400 text-sm gap-2"><i class="fas fa-spinner fa-spin"></i></div>';
        fetch('/api/messages/user/'+uid, {headers:{'Authorization':'Bearer '+TOKEN}})
            .then(function(r){return r.json();})
            .then(function(d){ renderMessages(d.data||d||[]); })
            .catch(function(){ area.innerHTML='<div class="text-center text-red-400 text-sm py-8">Erreur de chargement</div>'; });
    };

    function silentRefresh() {
        fetch('/api/messages/user/'+selectedUID, {headers:{'Authorization':'Bearer '+TOKEN}})
            .then(function(r){return r.json();})
            .then(function(d){
                var msgs = d.data||d||[];
                var area = document.getElementById('messages-area');
                if (msgs.length > area.querySelectorAll('.msg-bubble').length) renderMessages(msgs);
            }).catch(function(){});
    }

    function renderMessages(msgs) {
        var area = document.getElementById('messages-area');
        var atBottom = area.scrollHeight - area.scrollTop <= area.clientHeight + 60;
        if (!msgs.length) { area.innerHTML='<div class="text-center text-slate-400 text-sm py-10 opacity-50">Aucun message</div>'; return; }
        area.innerHTML = msgs.map(function(m) {
            var isAdmin = m.is_admin === true;
            var time = fmt(m.date_envoi||m.date||'');
            if (isAdmin) {
                return '<div class="msg-bubble flex items-end gap-2 flex-row-reverse msg-in">'
                    +'<div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0 mb-1"><i class="fas fa-headset"></i></div>'
                    +'<div class="max-w-xs lg:max-w-md">'
                    +'<p class="text-[10px] text-slate-400 mb-1 text-right font-semibold uppercase tracking-wide">Support</p>'
                    +'<div class="msg-bubble-support text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm shadow-md shadow-emerald-500/20">'+esc(m.contenu||'')+'</div>'
                    +'<p class="text-[10px] text-slate-400 mt-1 text-right">'+time+'</p>'
                    +'</div></div>';
            } else {
                return '<div class="msg-bubble flex items-end gap-2 msg-in">'
                    +'<div class="w-7 h-7 bg-slate-200 dark:bg-slate-700 rounded-full flex items-center justify-center text-xs flex-shrink-0 mb-1 text-slate-600 dark:text-slate-300"><i class="fas fa-user"></i></div>'
                    +'<div class="max-w-xs lg:max-w-md">'
                    +'<p class="text-[10px] text-slate-400 mb-1 font-semibold uppercase tracking-wide">Utilisateur</p>'
                    +'<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl rounded-bl-sm px-4 py-2.5 text-sm shadow-sm text-slate-800 dark:text-slate-100">'+esc(m.contenu||'')+'</div>'
                    +'<p class="text-[10px] text-slate-400 mt-1">'+time+'</p>'
                    +'</div></div>';
            }
        }).join('');
        if (atBottom) area.scrollTop = area.scrollHeight;
    }

    window.sendReply = function() {
        var input = document.getElementById('reply-input');
        var errEl = document.getElementById('send-error');
        var val = input.value.trim();
        errEl.classList.add('hidden');
        if (!val || !selectedUID) return;
        if (!TOKEN) { errEl.textContent='Session expiree.'; errEl.classList.remove('hidden'); return; }
        var btn = document.getElementById('send-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';
        fetch('/api/admin/messages/', {
            method:'POST',
            headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
            body:JSON.stringify({id_utilisateur:selectedUID, contenu:val})
        })
        .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
        .then(function(){
            input.value=''; input.style.height='';
            loadHistory(selectedUID);
            loadConvList();
        })
        .catch(function(err){ errEl.textContent='Erreur: '+err.message; errEl.classList.remove('hidden'); })
        .finally(function(){ btn.disabled=false; btn.innerHTML='<i class="fas fa-paper-plane text-sm"></i>'; });
    };

    window.autoResize = function(el) { el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,128)+'px'; };

    window.filterConvs = function(q) {
        var filtered = q.trim() ? allConvs.filter(function(c){ return ((c.nom||'')+' '+(c.prenom||'')+' '+(c.email||'')).toLowerCase().includes(q.toLowerCase()); }) : allConvs;
        renderConvList(filtered); restoreActiveConv();
    };

    document.addEventListener('DOMContentLoaded', function(){ loadConvList(); });
})();
</script>