<section class="max-w-6xl mx-auto px-6 lg:px-10 py-16">
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-envelope text-emerald-600"></i>
            </div>
            <span class="text-sm font-medium text-emerald-600 uppercase tracking-wide">Messagerie</span>
        </div>
        <h1 class="text-3xl font-bold">Mes conversations</h1>
    </div>

    <div class="grid lg:grid-cols-4 gap-8 h-[600px]">
        <aside class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border dark:border-slate-800 flex flex-col overflow-hidden">
            <div class="p-4 border-b dark:border-slate-800 font-semibold text-sm">Contacts</div>
            <div id="conversations-list" class="flex-1 overflow-y-auto"></div>
        </aside>

        <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border dark:border-slate-800 flex flex-col overflow-hidden">
            <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50 dark:bg-slate-950/20"></div>
            
            <div class="p-4 border-t dark:border-slate-800">
                <div id="typing-indicator" class="text-[10px] text-emerald-500 mb-2 h-4 opacity-0 transition-opacity">En train d'écrire...</div>
                <div class="flex items-center gap-3 bg-slate-100 dark:bg-slate-800 p-2 rounded-xl border dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                    <button onclick="document.getElementById('file-input').click()" class="p-2 text-slate-400 hover:text-emerald-500"><i class="fas fa-paperclip text-lg"></i></button>
                    <input type="file" id="file-input" class="hidden" accept="image/*" onchange="uploadFile(this)">
                    <input type="text" id="message-input" oninput="handleTyping()" onkeypress="if(event.key==='Enter') window.sendMessage()" class="flex-1 bg-transparent border-none focus:ring-0 text-sm" placeholder="Votre message...">
                    <button onclick="window.sendMessage()" class="bg-emerald-500 text-white p-2.5 rounded-lg hover:bg-emerald-600 transition-all"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
var token = "<?php echo $_SESSION['token'] ?? ''; ?>";
var currentRecipientId = 1;
var typingTimeout;

function escHtml(s) {
    let r = String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    return r.replace(/\\[IMG\\](.*?)\\[\/IMG\\]/g, '<div class="mt-2"><a href="$1" target="_blank"><img src="$1" class="max-w-xs rounded-lg shadow-md border dark:border-slate-700"></a></div>');
}

window.sendMessage = function() {
    const el = document.getElementById('message-input');
    const val = el.value.trim();
    if(!val || typeof ws === 'undefined' || ws.readyState !== WebSocket.OPEN) return;
    ws.send(JSON.stringify({type:"message", recipient_id: parseInt(currentRecipientId), content: val}));
    el.value = '';
};

async function uploadFile(input) {
    if(!input.files.length) return;
    let fd = new FormData();
    fd.append("file", input.files[0]);
    let res = await fetch("/api/messages/upload", {method:"POST", headers:{"Authorization":"Bearer "+token}, body:fd});
    let data = await res.json();
    if(data.url) {
        document.getElementById('message-input').value = "[IMG]"+data.url+"[/IMG]";
        window.sendMessage();
    }
    input.value = "";
}

function handleTyping() {
    if(typeof ws !== 'undefined' && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({type:"typing", recipient_id: parseInt(currentRecipientId)}));
    }
}
</script>