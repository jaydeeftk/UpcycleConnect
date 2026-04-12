<div class="max-w-4xl mx-auto p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col h-[700px]">
        <div class="p-4 border-b dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
            <h2 class="font-bold">Support UpcycleConnect</h2>
        </div>
        
        <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50/50 dark:bg-slate-950/20"></div>

        <div class="p-4 bg-white dark:bg-slate-900 border-t dark:border-slate-800">
            <div id="typing-indicator" class="text-[10px] text-slate-400 italic mb-1 opacity-0">L'admin écrit...</div>
            <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 p-2 rounded-xl">
                <button onclick="document.getElementById('file-input').click()" class="p-2 text-slate-500 hover:text-emerald-500"><i class="fas fa-paperclip"></i></button>
                <input type="file" id="file-input" class="hidden" accept="image/*" onchange="window.uploadFile(this)">
                <input type="text" id="message-input" oninput="window.notifyTyping()" class="flex-1 bg-transparent border-none focus:ring-0 text-sm" placeholder="Votre message...">
                <button onclick="window.sendMessage()" class="bg-emerald-500 text-white p-2 rounded-lg"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
window.token = "<?php echo $_SESSION['token'] ?? ''; ?>";
window.currentRecipientId = 1;

function escHtml(s) {
    let r = String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    return r.replace(/\[IMG\](.*?)\[\/IMG\]/g, '<div class="mt-2"><a href="$1" target="_blank"><img src="$1" class="max-w-xs rounded-lg shadow-md border dark:border-slate-700"></a></div>');
}

window.sendMessage = function() {
    const input = document.getElementById('message-input');
    const val = input.value.trim();
    if(!val || !ws) return;
    ws.send(JSON.stringify({type:"message", recipient_id:1, content:val}));
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
</script>