<div class="flex gap-5 h-[calc(100vh-160px)] min-h-[500px]">

    <aside class="w-72 flex-shrink-0 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="font-bold text-sm">Conversations</h2>
            <span id="online-badge" class="hidden w-2 h-2 rounded-full bg-emerald-400 shadow shadow-emerald-400/50"></span>
        </div>
        <div class="p-3 border-b border-slate-100 dark:border-slate-800">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="search-input" oninput="filterConvs(this.value)" placeholder="Rechercher..." class="w-full pl-8 pr-3 py-2 text-xs bg-slate-100 dark:bg-slate-800 rounded-lg border-none focus:ring-1 focus:ring-emerald-500 outline-none">
            </div>
        </div>
        <div id="conv-list" class="flex-1 overflow-y-auto no-scrollbar p-2 space-y-1">
            <div class="text-center text-slate-400 text-xs py-8"><i class="fas fa-spinner fa-spin mr-2"></i>Chargement...</div>
        </div>
    </aside>

    <div class="flex-1 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden shadow-sm">
        <div id="chat-header" class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 items-center gap-3 hidden flex">
            <div id="chat-avatar" class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-md shadow-emerald-500/20 flex-shrink-0">?</div>
            <div class="flex-1 min-w-0">
                <p id="chat-name" class="font-bold text-sm truncate"></p>
                <p id="chat-status" class="text-xs text-slate-400"></p>
            </div>
        </div>

        <div id="empty-state" class="flex-1 flex flex-col items-center justify-center text-slate-400 gap-3">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <i class="fas fa-comments text-xl opacity-30"></i>
            </div>
            <p class="text-sm font-medium">Sélectionnez une conversation</p>
        </div>

        <div id="messages-area" class="flex-1 overflow-y-auto p-5 space-y-3 bg-slate-50/30 dark:bg-slate-950/10 no-scrollbar hidden"></div>

        <div id="reply-box" class="hidden border-t border-slate-100 dark:border-slate-800">
            <div id="typing-bar" class="px-5 py-1.5 text-[10px] text-emerald-500 font-bold uppercase tracking-tight h-6 opacity-0 transition-opacity duration-200"></div>
            <div class="p-4 pt-0 flex items-end gap-3">
                <label class="p-2.5 text-slate-400 hover:text-emerald-500 cursor-pointer transition-colors flex-shrink-0">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" id="file-input" class="hidden" onchange="uploadFile(this)">
                </label>
                <div class="flex-1 bg-slate-100 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/30 transition-all px-4 py-3">
                    <textarea id="reply-input" rows="1" oninput="autoResize(this); sendTyping()" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendReply();}" class="w-full bg-transparent border-none focus:ring-0 text-sm resize-none leading-5 max-h-32 overflow-y-auto" placeholder="Répondre..."></textarea>
                </div>
                <button onclick="sendReply()" id="send-btn" class="bg-emerald-500 text-white w-10 h-10 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all active:scale-95 flex-shrink-0 flex items-center justify-center">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </div>
            <div id="send-error" class="hidden px-5 pb-3 text-xs text-red-500 font-medium"></div>
        </div>
    </div>
</div>

<script>
(function() {
    var TOKEN = <?= json_encode($_SESSION['token'] ?? '') ?>;
    var WS_URL = (location.protocol === 'https:' ? 'wss' : 'ws') + '://' + location.host + '/api/ws?token=' + encodeURIComponent(TOKEN);

    var ws = null;
    var selectedUID = null;
    var allConvs = [];
    var typingTimers = {};
    var reconnectDelay = 1000;

    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function fmt(d) {
        if (!d) return '';
        return new Date(d.replace(' ','T')).toLocaleString('fr-FR',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'});
    }
    function initials(nom,prenom) {
        return ((prenom||'')[0]+(nom||'')[0]).toUpperCase()||'?';
    }

    function connectWS() {
        ws = new WebSocket(WS_URL);
        ws.onopen = function() {
            document.getElementById('online-badge').classList.remove('hidden');
            reconnectDelay = 1000;
        };
        ws.onclose = function() {
            document.getElementById('online-badge').classList.add('hidden');
            setTimeout(connectWS, reconnectDelay);
            reconnectDelay = Math.min(reconnectDelay * 2, 30000);
        };
        ws.onerror = function(e) { console.warn('WS error', e); };
        ws.onmessage = function(e) {
            try { handleWsEvent(JSON.parse(e.data)); } catch(err) {}
        };
    }

    function handleWsEvent(data) {
        if (data.type === 'message') {
            var convUID = data.is_admin ? data.to_user_id : data.from;
            updateConvInList(convUID, data.content, data.created_at, data.from_name);
            if (convUID === selectedUID || (data.is_admin && data.to_user_id === selectedUID)) {
                appendMessage(data);
            }
        } else if (data.type === 'typing') {
            if (!data.is_admin) {
                showTyping(data.from);
            }
        }
    }

    function updateConvInList(uid, lastMsg, date, name) {
        var found = false;
        for (var i = 0; i < allConvs.length; i++) {
            if (allConvs[i].id_utilisateur === uid) {
                allConvs[i].dernier_message = lastMsg;
                allConvs[i].derniere_date = date;
                found = true;
                allConvs.unshift(allConvs.splice(i,1)[0]);
                break;
            }
        }
        if (!found) {
            loadConvList();
            return;
        }
        renderConvList(allConvs);
        restoreActiveConv();
    }

    function showTyping(fromUID) {
        if (fromUID !== selectedUID) return;
        var bar = document.getElementById('typing-bar');
        bar.textContent = 'En train d\'écrire...';
        bar.style.opacity = '1';
        clearTimeout(typingTimers[fromUID]);
        typingTimers[fromUID] = setTimeout(function() {
            bar.style.opacity = '0';
        }, 3000);
    }

    function appendMessage(data) {
        var area = document.getElementById('messages-area');
        var empty = area.querySelector('.no-msgs');
        if (empty) empty.remove();

        var div = document.createElement('div');
        div.className = data.is_admin
            ? 'flex items-end gap-2 flex-row-reverse'
            : 'flex items-end gap-2';

        var fileHtml = '';
        if (data.file_url) {
            var ext = (data.file_url.split('.').pop()||'').toLowerCase();
            var isImg = ['jpg','jpeg','png','gif','webp'].indexOf(ext) >= 0;
            if (isImg) {
                fileHtml = '<div class="mt-1.5"><a href="'+esc(data.file_url)+'" target="_blank"><img src="'+esc(data.file_url)+'" class="max-w-[200px] rounded-xl border dark:border-slate-700 shadow-sm hover:opacity-90 transition-opacity cursor-pointer"></a></div>';
            } else {
                fileHtml = '<a href="'+esc(data.file_url)+'" download="'+esc(data.file_name||'fichier')+'" class="mt-1.5 flex items-center gap-2 text-xs bg-white/20 rounded-lg px-3 py-1.5 hover:bg-white/30 transition-colors"><i class="fas fa-file-download"></i>'+esc(data.file_name||'Télécharger')+'</a>';
            }
        }

        if (data.is_admin) {
            div.innerHTML =
                '<div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0 mb-1"><i class="fas fa-headset"></i></div>'+
                '<div class="max-w-[70%]">'+
                '<p class="text-[10px] text-slate-400 mb-1 text-right font-semibold">Vous</p>'+
                '<div class="bg-emerald-500 text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm shadow-md shadow-emerald-500/20">'+esc(data.content)+fileHtml+'</div>'+
                '<p class="text-[10px] text-slate-400 mt-1 text-right">'+fmt(data.created_at)+'</p>'+
                '</div>';
        } else {
            div.innerHTML =
                '<div class="w-7 h-7 bg-slate-300 dark:bg-slate-600 rounded-full flex items-center justify-center text-xs flex-shrink-0 mb-1"><i class="fas fa-user"></i></div>'+
                '<div class="max-w-[70%]">'+
                '<p class="text-[10px] text-slate-400 mb-1 font-semibold">'+esc(data.from_name)+'</p>'+
                '<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl rounded-bl-sm px-4 py-2.5 text-sm shadow-sm">'+esc(data.content)+fileHtml+'</div>'+
                '<p class="text-[10px] text-slate-400 mt-1">'+fmt(data.created_at)+'</p>'+
                '</div>';
        }
        area.appendChild(div);
        area.scrollTop = area.scrollHeight;
    }

    function renderMessages(msgs) {
        var area = document.getElementById('messages-area');
        area.innerHTML = '';
        if (!msgs.length) {
            area.innerHTML = '<div class="no-msgs text-center text-slate-400 text-sm py-10">Aucun message</div>';
            return;
        }
        msgs.forEach(function(m) {
            appendMessage({
                type: 'message',
                from: m.is_admin ? 0 : selectedUID,
                from_name: m.is_admin ? 'Support' : (selectedUID+''),
                is_admin: m.is_admin,
                content: m.contenu,
                file_url: m.file_url||'',
                file_name: m.file_name||'',
                created_at: m.date_envoi,
                to_user_id: selectedUID
            });
        });
    }

    function renderConvList(convs) {
        var el = document.getElementById('conv-list');
        if (!convs.length) {
            el.innerHTML = '<div class="text-center text-slate-400 text-xs py-8">Aucune conversation</div>';
            return;
        }
        el.innerHTML = convs.map(function(c) {
            var init = initials(c.nom, c.prenom);
            var active = c.id_utilisateur === selectedUID;
            return '<div class="conv-item flex items-center gap-3 px-3 py-2.5 cursor-pointer rounded-xl transition-all duration-150 '+(active?'bg-emerald-50 dark:bg-emerald-500/10':'hover:bg-slate-50 dark:hover:bg-slate-800/50')+'" data-uid="'+c.id_utilisateur+'" data-nom="'+esc(c.nom)+'" data-prenom="'+esc(c.prenom)+'" data-email="'+esc(c.email)+'" onclick="selectConv(this)">'+
                '<div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm shadow-emerald-500/20">'+init+'</div>'+
                '<div class="min-w-0 flex-1">'+
                    '<p class="text-sm font-semibold truncate">'+esc(c.prenom)+' '+esc(c.nom)+'</p>'+
                    '<p class="text-xs text-slate-400 truncate max-w-[140px]">'+esc(c.dernier_message||'')+'</p>'+
                '</div>'+
                '</div>';
        }).join('');
    }

    function restoreActiveConv() {
        if (!selectedUID) return;
        document.querySelectorAll('.conv-item').forEach(function(el) {
            if (parseInt(el.dataset.uid) === selectedUID) {
                el.classList.add('bg-emerald-50','dark:bg-emerald-500/10');
                el.classList.remove('hover:bg-slate-50','dark:hover:bg-slate-800/50');
            }
        });
    }

    window.selectConv = function(el) {
        selectedUID = parseInt(el.dataset.uid);

        document.querySelectorAll('.conv-item').forEach(function(i) {
            i.classList.remove('bg-emerald-50','dark:bg-emerald-500/10');
            i.classList.add('hover:bg-slate-50','dark:hover:bg-slate-800/50');
        });
        el.classList.add('bg-emerald-50','dark:bg-emerald-500/10');
        el.classList.remove('hover:bg-slate-50','dark:hover:bg-slate-800/50');

        document.getElementById('empty-state').classList.add('hidden');
        document.getElementById('messages-area').classList.remove('hidden');
        document.getElementById('reply-box').classList.remove('hidden');
        document.getElementById('chat-header').classList.remove('hidden');
        document.getElementById('chat-header').style.display = 'flex';

        document.getElementById('chat-name').textContent = (el.dataset.prenom+' '+el.dataset.nom).trim();
        document.getElementById('chat-status').textContent = el.dataset.email;
        document.getElementById('chat-avatar').textContent = initials(el.dataset.nom, el.dataset.prenom);
        document.getElementById('send-error').classList.add('hidden');

        loadHistory(selectedUID);
    };

    function loadHistory(uid) {
        var area = document.getElementById('messages-area');
        area.innerHTML = '<div class="text-center text-slate-400 text-sm py-8"><i class="fas fa-spinner fa-spin mr-2"></i></div>';
        fetch('/api/messages/user/'+uid, { headers: {'Authorization':'Bearer '+TOKEN} })
            .then(function(r){return r.json();})
            .then(function(d){ renderMessages(d.data||[]); })
            .catch(function(){ area.innerHTML='<div class="text-center text-red-400 text-sm py-8">Erreur de chargement</div>'; });
    }

    window.sendReply = function() {
        var input = document.getElementById('reply-input');
        var val = input.value.trim();
        var errEl = document.getElementById('send-error');
        errEl.classList.add('hidden');
        if (!val || !selectedUID) return;
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            errEl.textContent = 'Connexion temps réel indisponible — rafraîchissez la page.';
            errEl.classList.remove('hidden');
            return;
        }
        ws.send(JSON.stringify({type:'message', to: selectedUID, content: val}));
        input.value = '';
        input.style.height = '';
    };

    window.sendTyping = function() {
        if (!ws || ws.readyState !== WebSocket.OPEN || !selectedUID) return;
        ws.send(JSON.stringify({type:'typing', to: selectedUID}));
    };

    window.autoResize = function(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 128) + 'px';
    };

    window.uploadFile = function(input) {
        if (!input.files.length || !selectedUID) return;
        var fd = new FormData();
        fd.append('file', input.files[0]);
        fetch('/api/messages/upload', {method:'POST', headers:{'Authorization':'Bearer '+TOKEN}, body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
                if (d.url && ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({type:'message', to: selectedUID, content:'', file_url: d.url, file_name: input.files[0].name}));
                }
            })
            .catch(function(){});
        input.value = '';
    };

    window.filterConvs = function(q) {
        var filtered = q.trim() ? allConvs.filter(function(c){
            return (c.nom+' '+c.prenom+' '+c.email).toLowerCase().includes(q.toLowerCase());
        }) : allConvs;
        renderConvList(filtered);
        restoreActiveConv();
    };

    function loadConvList() {
        fetch('/api/admin/messages', {headers:{'Authorization':'Bearer '+TOKEN}})
            .then(function(r){return r.json();})
            .then(function(d){
                allConvs = d.data||[];
                renderConvList(allConvs);
                restoreActiveConv();
            })
            .catch(function(){
                document.getElementById('conv-list').innerHTML = '<div class="p-4 text-center text-red-400 text-xs">Erreur</div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadConvList();
        connectWS();
    });
})();
</script>
