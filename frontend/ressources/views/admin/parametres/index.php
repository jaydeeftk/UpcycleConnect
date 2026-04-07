<div class="mb-6">
    <h2 class="text-2xl font-bold">Paramètres</h2>
    <p class="text-gray-600">Configuration générale de la plateforme</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-4">
            <nav class="space-y-2">
                <?php
                $sections = [
                    'general'      => ['Général', 'fa-cog'],
                    'notifications'=> ['Notifications', 'fa-bell'],
                    'paiements'    => ['Paiements', 'fa-credit-card'],
                    'api'          => ['API', 'fa-code'],
                    'securite'     => ['Sécurité', 'fa-shield-alt'],
                    'maintenance'  => ['Maintenance', 'fa-wrench'],
                ];
                foreach ($sections as $key => [$label, $icon]):
                ?>
                <button onclick="showSection('<?= $key ?>')" id="btn-<?= $key ?>"
                    class="section-btn w-full text-left px-4 py-3 rounded-lg transition flex items-center gap-2">
                    <i class="fas <?= $icon ?>"></i><?= $label ?>
                </button>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <div class="lg:col-span-3 space-y-6">
        <div id="section-general" class="section-content">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Paramètres généraux</h3>
                <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/parametres/update">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Nom du site</label>
                            <input type="text" name="nom_site" value="<?= htmlspecialchars($parametres['nom_site'] ?? 'UpcycleConnect') ?>" class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Email de contact</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($parametres['email'] ?? 'contact@upcycleconnect.fr') ?>" class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Description</label>
                            <textarea name="description" rows="3" class="w-full border rounded-lg px-4 py-2"><?= htmlspecialchars($parametres['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="section-notifications" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Notifications</h3>
                <p>Réglages des notifications...</p>
            </div>
        </div>

        <div id="section-paiements" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Paiements</h3>
                <p>Réglages Stripe/PayPal...</p>
            </div>
        </div>

        <div id="section-api" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Configuration API</h3>
                <input type="text" value="http://api:8080/api" readonly class="w-full border rounded-lg px-4 py-2 bg-gray-50 font-mono text-sm">
            </div>
        </div>

        <div id="section-securite" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Sécurité</h3>
                <p>Authentification JWT & Bcrypt actifs.</p>
            </div>
        </div>

        <div id="section-maintenance" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Maintenance</h3>
                <div class="space-y-4">
                    <button id="btn-toggle-maintenance" class="w-full bg-yellow-100 text-yellow-700 px-4 py-3 rounded-lg hover:bg-yellow-200 text-left">
                        <i class="fas fa-tools mr-2"></i>
                        <span id="maintenance-text">Activer le mode maintenance</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(id) {
    document.querySelectorAll('.section-content').forEach(s => s.classList.add('hidden'));
    document.querySelectorAll('.section-btn').forEach(b => {
        b.classList.remove('bg-green-50', 'text-green-700', 'font-medium');
        b.classList.add('hover:bg-gray-50');
    });
    const target = document.getElementById('section-' + id);
    if(target) target.classList.remove('hidden');
    const btn = document.getElementById('btn-' + id);
    if(btn) {
        btn.classList.add('bg-green-50', 'text-green-700', 'font-medium');
        btn.classList.remove('hover:bg-gray-50');
    }
}

let isMaintenance = <?= ($parametres['maintenance_mode'] ?? 'false') === 'true' ? 'true' : 'false' ?>;
const btnMaint = document.getElementById('btn-toggle-maintenance');
const textMaint = document.getElementById('maintenance-text');

function updateMaintUI() {
    if (isMaintenance) {
        btnMaint.classList.add('bg-red-600', 'text-white');
        btnMaint.classList.remove('bg-yellow-100', 'text-yellow-700');
        textMaint.innerText = "Désactiver le mode maintenance (ACTIF)";
    } else {
        btnMaint.classList.add('bg-yellow-100', 'text-yellow-700');
        btnMaint.classList.remove('bg-red-600', 'text-white');
        textMaint.innerText = "Activer le mode maintenance";
    }
}

btnMaint.addEventListener('click', function() {
    const newState = !isMaintenance;
    fetch('/api/admin/parametres', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "maintenance_mode": newState.toString() })
    })
    .then(res => res.json())
    .then(data => {
        isMaintenance = newState;
        updateMaintUI();
        alert(isMaintenance ? "Site verrouillé !" : "Site accessible !");
    })
    .catch(err => alert("Erreur API : " + err));
});

showSection('general');
updateMaintUI();
</script>