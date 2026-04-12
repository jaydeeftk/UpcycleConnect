<section class="max-w-6xl mx-auto px-6 lg:px-10 py-16 reveal">
    <div class="mb-10 flex items-end justify-between">
        <div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center">
                    <i class="fas fa-envelope text-emerald-600"></i>
                </div>
                <span class="text-sm font-medium text-emerald-600 uppercase tracking-wide">Messagerie</span>
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight">Mes conversations</h1>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-8 h-[650px]">
        <aside class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 font-bold text-sm uppercase tracking-widest opacity-50">Contacts</div>
            <div id="conversations-list" class="flex-1 overflow-y-auto p-2 space-y-1 no-scrollbar"></div>
        </aside>

        <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden shadow-xl">
            <div id="messages-container" class="flex-1 overflow-y-auto p-8 space-y-6 bg-slate-50/30 dark:bg-slate-950/20 no-scrollbar"></div>
            
            <div class="p-6 border-t border-slate-100 dark:border-slate-800 backdrop-blur-md">
                <div id="typing-indicator" class="text-[10px] text-emerald-500 mb-2 h-4 opacity-0 transition-opacity font-bold uppercase ml-2 tracking-tighter">L'admin écrit...</div>
                <div class="flex items-center gap-3 bg-slate-100 dark:bg-slate-800/50 p-2 rounded-2xl border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all duration-300">
                    <button type="button" onclick="document.getElementById('file-input').click()" class="p-3 text-slate-400 hover:text-emerald-500 hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-all shadow-sm">
                        <i class="fas fa-paperclip text-lg"></i>
                    </button>
                    <input type="file" id="file-input" class="hidden" accept="image/*" onchange="uploadFile(this)">
                    <input type="text" id="message-input" oninput="handleTyping()" onkeypress="if(event.key==='Enter') window.sendMessage()" class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2 dark:text-slate-100" placeholder="Écrivez votre message...">
                    <button onclick="window.sendMessage()" class="bg-emerald-500 text-white p-3.5 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/40 transition-all active:scale-95">
                        <i class="fas fa-paper-plane"></i>
                    </button>
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
    return r.replace(/\[IMG\](.*?)\[\/IMG\]/g, (match, url) => {
        return `<div class="mt-2 group relative max-w-sm"><img src="${url}" class="rounded-2xl shadow-lg border dark:border-slate-700 cursor-pointer hover:scale-[1.02] transition-transform duration-300" onclick="window.open('${url}', '_blank')"><a href="${url}" download class="absolute top-2 right-2 p-2 bg-white/90 dark:bg-slate-800/90 rounded-lg text-emerald-500 opacity-0 group-hover:opacity-100 transition-all shadow-sm"><i class="fas fa-download"></i></a></div>`;
    });
}

window.sendMessage = function() {
    const el = document.getElementById('message-input');
    const val = el.value.trim();
    if(!val || typeof ws === 'undefined' || ws.readyState !== WebSocket.OPEN) return;
    ws.send(JSON.stringify({type:"message", recipient_id: parseInt(currentRecipientId), content: val}));
    el.value = '';
};

async function uploadFile(input) {
    if (!input.files.length) return;
    const fd = new FormData();
    fd.append('file', input.files[0]);
    try {
        const res = await fetch('/api/messages/upload', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            body: fd
        });
        const data = await res.json();
        if (data.url) {
            ws.send(JSON.stringify({
                type: "message",
                recipient_id: currentRecipientId,
                content: "[IMG]" + data.url + "[/IMG]"
            }));
        }
    } catch (e) { console.error("Upload error", e); }
    input.value = "";
}

function handleTyping() {
    if(typeof ws !== 'undefined' && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({type:"typing", recipient_id: parseInt(currentRecipientId)}));
    }
}
</script>