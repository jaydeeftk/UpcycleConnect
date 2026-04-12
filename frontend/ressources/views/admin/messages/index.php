<div class="h-[calc(100vh-12rem)] bg-white dark:bg-slate-900 rounded-3xl shadow-xl border dark:border-slate-800 flex overflow-hidden">
    <div class="w-80 border-r dark:border-slate-800 flex flex-col">
        <div class="p-6 border-b dark:border-slate-800"><h3 class="font-bold">Conversations</h3></div>
        <div id="conversations-list" class="flex-1 overflow-y-auto"></div>
    </div>
    <div class="flex-1 flex flex-col bg-slate-50/30 dark:bg-slate-950/20">
        <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4"></div>
        <div class="p-6 bg-white dark:bg-slate-900 border-t dark:border-slate-800">
            <div id="typing-indicator" class="text-[10px] text-emerald-500 mb-2 opacity-0 transition-opacity">L'utilisateur écrit...</div>
            <div class="flex items-center gap-4 bg-slate-100 dark:bg-slate-800 p-2 rounded-2xl border dark:border-slate-700 focus-within:ring-2 focus-within:ring-emerald-500/20">
                <button onclick="document.getElementById('file-input').click()" class="p-2 text-slate-400 hover:text-emerald-500"><i class="fas fa-paperclip"></i></button>
                <input type="file" id="file-input" class="hidden" accept="image/*" onchange="window.uploadFile(this)">
                <input type="text" id="message-input" oninput="window.notifyTyping()" onkeypress="if(event.key==='Enter') window.sendMessage()" class="flex-1 bg-transparent border-none focus:ring-0 text-sm" placeholder="Répondre...">
                <button onclick="window.sendMessage()" class="bg-emerald-500 text-white p-3 rounded-xl hover:bg-emerald-600 transition-all"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>
<script>
window.token = "<?php echo $_SESSION['token']; ?>";
window.currentRecipientId = null;
window.ws = null;

window.sendMessage = function() {
    const input = document.getElementById('message-input');
    const content = input.value.trim();
    if(!content || !window.currentRecipientId) return;
    window.ws.send(JSON.stringify({type:"message", recipient_id: parseInt(window.currentRecipientId), content: content}));
    input.value = '';
};

window.uploadFile = async function(input) {
    if(!input.files.length) return;
    let fd = new FormData();
    fd.append("file", input.files[0]);
    let res = await fetch("/api/messages/upload", {method:"POST", headers:{"Authorization":"Bearer "+window.token}, body:fd});
    let data = await res.json();
    if(data.url) {
        document.getElementById('message-input').value = "[IMG]"+data.url+"[/IMG]";
        window.sendMessage();
    }
};

window.notifyTyping = function() {
    if(!window.ws || !window.currentRecipientId) return;
    window.ws.send(JSON.stringify({type:"typing", recipient_id: parseInt(window.currentRecipientId)}));
};
</script>