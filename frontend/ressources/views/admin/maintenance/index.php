<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnMaintenance = document.querySelector('.btn-maintenance');
    if (btnMaintenance) {
        btnMaintenance.addEventListener('click', function() {
            fetch('/api/admin/parametres', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ "maintenance_mode": "true" })
            }).then(res => {
                if(res.ok) {
                    alert("Mode maintenance activé !");
                    location.reload();
                }
            }).catch(err => console.error("Erreur:", err));
        });
    }
});
</script>