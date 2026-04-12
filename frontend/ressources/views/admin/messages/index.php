<div class="max-w-6xl mx-auto h-[calc(100vh-10rem)] flex gap-6">
    <div class="flex-1 bg-white dark:bg-slate-900 rounded-[2rem] border dark:border-slate-800 flex flex-col overflow-hidden shadow-2xl shadow-slate-200/50 dark:shadow-none">
        <div id="messages-container" class="flex-1 overflow-y-auto p-8 space-y-6 bg-slate-50/30 dark:bg-slate-900/10 no-scrollbar"></div>
        
        <div class="p-6 border-t dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md">
            <div id="typing-indicator" class="text-[10px] text-emerald-500 mb-2 h-4 opacity-0 transition-opacity font-medium ml-2">Quelqu'un écrit...</div>
            <div class="flex items-center gap-3 bg-slate-100 dark:bg-slate-800/80 p-2 rounded-2xl border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                <button onclick="document.getElementById('file-input').click()" class="p-2.5 text-slate-400 hover:text-emerald-500 hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-all"><i class="fas fa-paperclip text-lg"></i></button>
                <input type="file" id="file-input" class="hidden" accept="image/*" onchange="handleFileUpload(this)">
                <input type="text" id="message-input" oninput="handleTypingNotification()" onkeypress="if(event.key==='Enter') window.sendMessage()" class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2" placeholder="Votre message...">
                <button onclick="window.sendMessage()" class="bg-emerald-500 text-white p-3 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all active:scale-95"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
var token = "<?php echo $_SESSION['token'] ?? ''; ?>";
var currentRecipientId = 1;
var typingTimeout;

function escHtml(s) {
    let r = String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    return r.replace(/\\[IMG\\](.*?)\\[\/IMG\\]/g, '<div class="mt-2"><a href="$1" target="_blank"><img src="$1" class="max-w-sm rounded-2xl shadow-lg border dark:border-slate-700 hover:scale-[1.01] transition-transform"></a></div>');
}

window.sendMessage = function() {
    const el = document.getElementById('message-input');
    const val = el.value.trim();
    if(!val || typeof ws === 'undefined' || ws.readyState !== WebSocket.OPEN) return;
    ws.send(JSON.stringify({type:"message", recipient_id: parseInt(currentRecipientId), content: val}));
    el.value = '';
};

async function handleFileUpload(input) {
    if(!input.files.length) return;
    let fd = new FormData();
    fd.append("file", input.files[0]);
    try {
        let res = await fetch("/api/messages/upload", {method:"POST", headers:{"Authorization":"Bearer "+token}, body:fd});
        let data = await res.json();
        if(data.url) {
            document.getElementById('message-input').value = "[IMG]"+data.url+"[/IMG]";
            window.sendMessage();
        }
    } catch(e) { console.error("Upload error", e); }
    input.value = "";
}

function handleTypingNotification() {
    if(typeof ws !== 'undefined' && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({type:"typing", recipient_id: parseInt(currentRecipientId)}));
    }
}
</script>