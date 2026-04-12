<section class="max-w-3xl mx-auto px-4 py-12 reveal">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center">
                <i class="fas fa-envelope text-emerald-600 text-sm"></i>
            </div>
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Messagerie</span>
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight">Support</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Échangez directement avec l'équipe UpcycleConnect.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl flex flex-col overflow-hidden" style="height:580px">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-md shadow-emerald-500/20">
                <i class="fas fa-headset text-sm"></i>
            </div>
            <div>
                <p class="font-bold text-sm">Support UpcycleConnect</p>
                <div class="flex items-center gap-1.5">
                    <span id="ws-dot" class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600 transition-colors duration-500"></span>
                    <span id="ws-label" class="text-[10px] text-slate-400 font-medium">Connexion...</span>
                </div>
            </div>
        </div>

        <div id="messages-area" class="flex-1 overflow-y-auto px-5 py-4 space-y-3 bg-slate-50/30 dark:bg-slate-950/10 no-scrollbar">
            <div id="empty-state" class="h-full flex flex-col items-center justify-center text-slate-400 gap-3">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="fas fa-comments text-xl opacity-30"></i>
                </div>
                <p class="text-sm font-medium">Aucun message pour l'instant</p>
                <p class="text-xs opacity-60">Envoyez votre premier message ci-dessous.</p>
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800">
            <div id="typing-bar" class="px-5 py-1.5 text-[10px] text-emerald-500 font-bold uppercase tracking-tight h-6 opacity-0 transition-opacity duration-200">L'admin écrit...</div>
            <div class="p-4 pt-0 flex items-end gap-3">
                <label class="p-2.5 text-slate-400 hover:text-emerald-500 cursor-pointer transition-colors flex-shrink-0">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" id="file-input" class="hidden" onchange="uploadFile(this)">
                </label>
                <div class="flex-1 bg-slate-100 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/30 transition-all px-4 py-3">
                    <textarea id="message-input" rows="1" oninput="autoResize(this); sendTyping()" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}" class="w-full bg-transparent border-none focus:ring-0 text-sm resize-none leading-5 max-h-32 overflow-y-auto dark:text-slate-100" placeholder="Votre message..."></textarea>
                </div>
                <button onclick="sendMessage()" id="send-btn" class="bg-emerald-500 text-white w-10 h-10 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all active:scale-95 flex-shrink-0 flex items-center justify-center">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </div>
            <div id="send-error" class="hidden px-5 pb-3 text-xs text-red-500 font-medium"></div>
        </div>
    </div>
</section>

<script>
(function() {
    var TOKEN   = <?= json_encode($_SESSION['token'] ?? '') ?>;
    var USER_ID = <?= json_encode($_SESSION['user']['id'] ?? 0) ?>;
    var WS_URL  = (location.protocol==='https:'?'wss':'ws')+'://'+location.host+'/api/ws?token='+encodeURIComponent(TOKEN);

    var ws = null;
    var reconnectDelay = 1000;
    var typingTimer = null;
    var hasMessages = false;

    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function fmt(d) {
        if (!d) return '';
        return new Date(d.replace(' ','T')).toLocaleString('fr-FR',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'});
    }

    function setStatus(online) {
        var dot = document.getElementById('ws-dot');
        var lbl = document.getElementById('ws-label');
        if (online) {
            dot.style.backgroundColor = '#10b981';
            lbl.textContent = 'En ligne';
            lbl.style.color = '#10b981';
        } else {
            dot.style.backgroundColor = '#94a3b8';
            lbl.textContent = 'Hors ligne';
            lbl.style.color = '#94a3b8';
        }
    }

    function connectWS() {
        if (!TOKEN) return;
        ws = new WebSocket(WS_URL);
        ws.onopen = function() { setStatus(true); reconnectDelay = 1000; };
        ws.onclose = function() {
            setStatus(false);
            setTimeout(connectWS, reconnectDelay);
            reconnectDelay = Math.min(reconnectDelay * 2, 30000);
        };
        ws.onerror = function() {};
        ws.onmessage = function(e) {
            try { handleEvent(JSON.parse(e.data)); } catch(err) {}
        };
    }

    function handleEvent(data) {
        if (data.type === 'message') {
            if (document.getElementById('empty-state')) {
                document.getElementById('empty-state').remove();
            }
            appendMsg(data);
        } else if (data.type === 'typing' && data.is_admin) {
            var bar = document.getElementById('typing-bar');
            bar.style.opacity = '1';
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() { bar.style.opacity = '0'; }, 3000);
        }
    }

    function appendMsg(data) {
        var area = document.getElementById('messages-area');
        var isMe = !data.is_admin;
        var div = document.createElement('div');
        div.className = isMe ? 'flex items-end gap-2 flex-row-reverse' : 'flex items-end gap-2';

        var fileHtml = '';
        if (data.file_url) {
            var ext = (data.file_url.split('.').pop()||'').toLowerCase();
            var isImg = ['jpg','jpeg','png','gif','webp'].indexOf(ext) >= 0;
            if (isImg) {
                fileHtml = '<div class="mt-1.5"><a href="'+esc(data.file_url)+'" target="_blank"><img src="'+esc(data.file_url)+'" class="max-w-[180px] rounded-xl border dark:border-slate-700 shadow-sm hover:opacity-90 transition-opacity cursor-pointer"></a></div>';
            } else {
                fileHtml = '<a href="'+esc(data.file_url)+'" download="'+esc(data.file_name||'fichier')+'" class="mt-1.5 flex items-center gap-2 text-xs rounded-lg px-3 py-1.5 bg-white/20 hover:bg-white/30 transition-colors"><i class="fas fa-file-download"></i>'+esc(data.file_name||'Télécharger')+'</a>';
            }
        }

        if (isMe) {
            div.innerHTML =
                '<div class="w-7 h-7 bg-slate-300 dark:bg-slate-600 rounded-full flex items-center justify-center text-xs flex-shrink-0 mb-1"><i class="fas fa-user"></i></div>'+
                '<div class="max-w-[70%]">'+
                '<p class="text-[10px] text-slate-400 mb-1 text-right font-semibold">Vous</p>'+
                '<div class="bg-emerald-500 text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm shadow-md shadow-emerald-500/20">'+esc(data.content)+fileHtml+'</div>'+
                '<p class="text-[10px] text-slate-400 mt-1 text-right">'+fmt(data.created_at)+'</p>'+
                '</div>';
        } else {
            div.innerHTML =
                '<div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0 mb-1"><i class="fas fa-headset"></i></div>'+
                '<div class="max-w-[70%]">'+
                '<p class="text-[10px] text-slate-400 mb-1 font-semibold">Support</p>'+
                '<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl rounded-bl-sm px-4 py-2.5 text-sm shadow-sm">'+esc(data.content)+fileHtml+'</div>'+
                '<p class="text-[10px] text-slate-400 mt-1">'+fmt(data.created_at)+'</p>'+
                '</div>';
        }
        area.appendChild(div);
        area.scrollTop = area.scrollHeight;
        hasMessages = true;
    }

    function loadHistory() {
        if (!USER_ID) return;
        fetch('/api/messages/user/'+USER_ID, {headers:{'Authorization':'Bearer '+TOKEN}})
            .then(function(r){return r.json();})
            .then(function(d){
                var msgs = d.data||[];
                if (msgs.length) {
                    document.getElementById('empty-state').remove();
                    msgs.forEach(function(m) {
                        appendMsg({
                            type:'message', is_admin: m.is_admin,
                            content: m.contenu, file_url: m.file_url||'', file_name: m.file_name||'',
                            created_at: m.date_envoi
                        });
                    });
                }
            })
            .catch(function(){});
    }

    window.sendMessage = function() {
        var input = document.getElementById('message-input');
        var val = input.value.trim();
        var errEl = document.getElementById('send-error');
        errEl.classList.add('hidden');
        if (!val) return;
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            fetch('/api/messages', {
                method:'POST',
                headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
                body: JSON.stringify({contenu: val})
            }).then(function(r){
                if (!r.ok) throw new Error();
                input.value=''; input.style.height='';
                if (document.getElementById('empty-state')) document.getElementById('empty-state').remove();
                appendMsg({type:'message',is_admin:false,content:val,created_at:new Date().toISOString().replace('T',' ').slice(0,19)});
            }).catch(function(){
                errEl.textContent='Erreur lors de l\'envoi.'; errEl.classList.remove('hidden');
            });
            return;
        }
        ws.send(JSON.stringify({type:'message', content: val}));
        input.value=''; input.style.height='';
    };

    window.sendTyping = function() {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({type:'typing'}));
        }
    };

    window.autoResize = function(el) {
        el.style.height='auto';
        el.style.height=Math.min(el.scrollHeight,128)+'px';
    };

    window.uploadFile = function(input) {
        if (!input.files.length) return;
        var fd = new FormData();
        fd.append('file', input.files[0]);
        fetch('/api/messages/upload', {method:'POST', headers:{'Authorization':'Bearer '+TOKEN}, body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
                if (d.url && ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({type:'message', content:'', file_url:d.url, file_name:input.files[0].name}));
                }
            }).catch(function(){});
        input.value='';
    };

    document.addEventListener('DOMContentLoaded', function() {
        loadHistory();
        if (TOKEN) connectWS();
    });
})();
</script>
