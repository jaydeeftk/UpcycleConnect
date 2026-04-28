<style>
#toast-container {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    pointer-events: none;
}
.toast-item {
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.25rem;
    border-radius: 0.875rem;
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    animation: toastIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    max-width: 340px;
}
.toast-item.toast-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.toast-item.toast-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.toast-item.toast-info    { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.toast-item.toast-out     { animation: toastOut 0.25s ease forwards; }
@keyframes toastIn  { from { opacity:0; transform:translateX(60px); } to { opacity:1; transform:translateX(0); } }
@keyframes toastOut { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(60px); } }

.btn-loading { position: relative; pointer-events: none; }
.btn-loading .btn-text { visibility: hidden; }
.btn-loading::after {
    content: '';
    position: absolute;
    width: 1rem; height: 1rem;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
}
@keyframes spin { to { transform: translate(-50%, -50%) rotate(360deg); } }
</style>

<div id="toast-container"></div>

<script>
window.showToast = function(message, type = 'info', duration = 3500) {
    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast-item toast-' + type;
    toast.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('toast-out');
        toast.addEventListener('animationend', () => toast.remove());
    }, duration);
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[data-toast]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('button[type=submit]');
            if (btn) {
                btn.classList.add('btn-loading');
                const span = btn.querySelector('.btn-text') || btn;
                if (!btn.querySelector('.btn-text')) {
                    const text = btn.innerHTML;
                    btn.innerHTML = '<span class="btn-text">' + text + '</span>';
                }
            }
        });
    });

    <?php if (isset($success)): ?>
    showToast("<?= addslashes(htmlspecialchars($success)) ?>", 'success');
    <?php endif; ?>
    <?php if (isset($error)): ?>
    showToast("<?= addslashes(htmlspecialchars($error)) ?>", 'error');
    <?php endif; ?>
});
</script>