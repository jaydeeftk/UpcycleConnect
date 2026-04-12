<?php
$userId = $user_id ?? $_SESSION['user']['id'] ?? 0;
$token  = $token ?? $_SESSION['token'] ?? $_SESSION['user']['token'] ?? '';
?>
<style>
.chat-container { height: calc(100vh - 280px); min-height: 400px; }
.bubble-user { background: #dcf8c6; color: #1a1a1a; border-radius: 18px 18px 4px 18px; }
.dark .bubble-user { background: #005c4b; color: #e9edef; }
.bubble-support { background: #fff; color: #1a1a1a; border-radius: 18px 18px 18px 4px; border: 1px solid #e9edef; }
.dark .bubble-support { background: #202c33; color: #e9edef; border-color: #2a3942; }
.chat-bg { background-color: #efeae2; }
.dark .chat-bg { background-color: #0b141a; }
.msg-in { animation: msgIn 0.15s ease-out; }
@keyframes msgIn { from { opacity:0; transform:scale(0.96) translateY(4px); } to { opacity:1; transform:scale(1) translateY(0); } }
.tail-user::after { content:''; position:absolute; bottom:0; right:-8px; width:0; height:0; border-left:8px solid #dcf8c6; border-bottom:8px solid transparent; }
.dark .tail-user::after { border-left-color: #005c4b; }
.tail-support::after { content:''; position:absolute; bottom:0; left:-8px; width:0; height:0; border-right:8px solid #fff; border-bottom:8px solid transparent; }
.dark .tail-support::after { border-right-color: #202c33; }
</style>

<div class="max-w-2xl mx-auto">
    <div class="rounded-2xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700" style="height:calc(100vh - 220px); min-height:500px; display:flex; flex-direction:column;">

        <!-- Header WhatsApp style -->
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-600 dark:bg-emerald-800">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white font-bold text-sm">UC</div>
            <div class="flex-1">
                <p class="font-semibold text-white text-sm">Support UpcycleConnect</p>
                <p class="text-xs text-emerald-100">En ligne</p>
            </div>
        </div>

        <!-- Messages -->
        <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-2 chat-bg">
            <div class="flex items-center justify-center">
                <span class="bg-white/70 dark:bg-slate-800/70 text-slate-500 dark:text-slate-400 text-xs px-3 py-1 rounded-full backdrop-blur-sm" id="loading-label">
                    <i class="fas fa-spinner fa-spin mr-1"></i>Chargement...
                </span>
            </div>
        </div>

        <!-- Saisie WhatsApp style -->
        <div class="px-3 py-2 bg-slate-100 dark:bg-slate-800 flex items-end gap-2">
            <div class="flex-1 bg-white dark:bg-slate-700 rounded-2xl px-4 py-2.5 border border-slate-200 dark:border-slate-600 focus-within:border-emerald-400 transition-colors">
                <textarea id="msg-input" rows="1"
                    oninput="autoResize(this)"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"
                    class="w-full bg-transparent border-none focus:ring-0 text-sm resize-none leading-5 max-h-24 outline-none text-slate-800 dark:text-slate-100 placeholder-slate-400"
                    placeholder="Votre message..."></textarea>
            </div>
            <button onclick="sendMessage()" id="send-btn"
                class="w-10 h-10 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-md transition-all active:scale-95 flex-shrink-0">
                <i class="fas fa-paper-plane text-sm"></i>
            </button>
        </div>
        <div id="msg-error" class="hidden px-4 py-1 text-xs text-red-500 bg-slate-100 dark:bg-slate-800">Erreur lors de l'envoi</div>
    </div>
</div>

<script>
(function(){
    var TOKEN = <?= json_encode($token) ?>;
    var USER_ID = <?= (int)$userId ?>;

    function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'); }
    function fmt(d){
        if(!d) return '';
        try{ var dt=new Date(d.replace(' ','T')); return dt.getHours().toString().padStart(2,'0')+':'+dt.getMinutes().toString().padStart(2,'0'); }
        catch(e){ return ''; }
    }

    function renderMessages(msgs){
        var box = document.getElementById('chat-box');
        if(!msgs||!msgs.length){
            box.innerHTML='<div class="flex justify-center"><span class="bg-white/70 dark:bg-slate-800/70 text-slate-500 text-xs px-3 py-1 rounded-full">Aucun message — envoyez le premier !</span></div>';
            return;
        }
        box.innerHTML = msgs.map(function(m){
            var isSupport = m.is_admin===true;
            var time = fmt(m.date_envoi||m.date||'');
            if(isSupport){
                return '<div class="flex justify-start msg-in">'
                    +'<div class="max-w-xs lg:max-w-sm relative">'
                    +'<div class="bubble-support tail-support relative px-3 py-2 shadow-sm">'
                    +'<p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mb-0.5">Support</p>'
                    +'<p class="text-sm leading-relaxed">'+esc(m.contenu||'')+'</p>'
                    +'<p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 text-right">'+time+'</p>'
                    +'</div></div></div>';
            } else {
                return '<div class="flex justify-end msg-in">'
                    +'<div class="max-w-xs lg:max-w-sm relative">'
                    +'<div class="bubble-user tail-user relative px-3 py-2 shadow-sm">'
                    +'<p class="text-sm leading-relaxed">'+esc(m.contenu||'')+'</p>'
                    +'<div class="flex items-center justify-end gap-1 mt-1">'
                    +'<span class="text-[10px] text-slate-500 dark:text-emerald-200/70">'+time+'</span>'
                    +'<i class="fas fa-check-double text-[9px] text-emerald-500 dark:text-emerald-300"></i>'
                    +'</div></div></div></div>';
            }
        }).join('');
        box.scrollTop = box.scrollHeight;
    }

    function loadMessages(){
        if(!TOKEN||!USER_ID){ document.getElementById('loading-label').textContent='Connectez-vous pour voir vos messages.'; return; }
        fetch('/api/messages/user/'+USER_ID,{headers:{'Authorization':'Bearer '+TOKEN}})
            .then(function(r){return r.json();})
            .then(function(d){ renderMessages(d.data||d||[]); })
            .catch(function(){ document.getElementById('loading-label').textContent='Impossible de charger.'; });
    }

    window.sendMessage = function(){
        var input = document.getElementById('msg-input');
        var errEl = document.getElementById('msg-error');
        var contenu = input.value.trim();
        errEl.classList.add('hidden');
        if(!contenu||!TOKEN) return;
        var btn = document.getElementById('send-btn');
        btn.disabled=true;
        fetch('/api/messages',{
            method:'POST',
            headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
            body:JSON.stringify({contenu:contenu})
        })
        .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
        .then(function(){
            input.value=''; input.style.height='';
            loadMessages();
        })
        .catch(function(err){ errEl.textContent='Erreur: '+err.message; errEl.classList.remove('hidden'); })
        .finally(function(){ btn.disabled=false; });
    };

    window.autoResize = function(el){ el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,96)+'px'; };

    document.getElementById('msg-input').addEventListener('keydown',function(e){
        if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMessage();}
    });

    loadMessages();
    setInterval(loadMessages, 5000);
})();
</script>