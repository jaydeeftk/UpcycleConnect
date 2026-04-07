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
                        <div>
                            <label class="block text-sm font-medium mb-2">Langue par défaut</label>
                            <select name="langue" class="w-full border rounded-lg px-4 py-2">
                                <option <?= ($parametres['langue'] ?? '') === 'Français' ? 'selected' : '' ?>>Français</option>
                                <option <?= ($parametres['langue'] ?? '') === 'English' ? 'selected' : '' ?>>English</option>
                                <option <?= ($parametres['langue'] ?? '') === 'Deutsch' ? 'selected' : '' ?>>Deutsch</option>
                                <option <?= ($parametres['langue'] ?? '') === 'Español' ? 'selected' : '' ?>>Español</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Fuseau horaire</label>
                            <select name="fuseau" class="w-full border rounded-lg px-4 py-2">
                                <option>Europe/Paris</option>
                                <option>UTC</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t">
                        <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
                            <i class="fas fa-save mr-2"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="section-notifications" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Notifications</h3>
                <p class="text-gray-500">Configurez les préférences de notifications de la plateforme.</p>
                <div class="mt-4 space-y-4">
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div><p class="font-medium">Notifications email</p><p class="text-sm text-gray-500">Envoyer des emails aux utilisateurs</p></div>
                        <input type="checkbox" checked class="w-5 h-5">
                    </div>
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div><p class="font-medium">Notifications push</p><p class="text-sm text-gray-500">Via OneSignal</p></div>
                        <input type="checkbox" class="w-5 h-5">
                    </div>
                </div>
            </div>
        </div>

        <div id="section-paiements" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Modes de paiement</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex items-center">
                            <i class="fab fa-stripe text-4xl text-blue-600 mr-4"></i>
                            <div><div class="font-medium">Stripe</div><div class="text-sm text-gray-500">Cartes bancaires, Apple Pay, Google Pay</div></div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex items-center">
                            <i class="fab fa-paypal text-4xl text-blue-700 mr-4"></i>
                            <div><div class="font-medium">PayPal</div><div class="text-sm text-gray-500">Paiements PayPal</div></div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div id="section-api" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Configuration API</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">URL de base API</label>
                        <input type="text" value="http://api:8080/api" readonly class="w-full border rounded-lg px-4 py-2 bg-gray-50 font-mono text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Version</label>
                        <input type="text" value="v1" readonly class="w-full border rounded-lg px-4 py-2 bg-gray-50">
                    </div>
                </div>
            </div>
        </div>

        <div id="section-securite" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Sécurité</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div><p class="font-medium">Authentification JWT</p><p class="text-sm text-gray-500">Tokens valides 24h</p></div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Actif</span>
                    </div>
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div><p class="font-medium">Chiffrement bcrypt</p><p class="text-sm text-gray-500">Mots de passe hashés</p></div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Actif</span>
                    </div>
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div><p class="font-medium">HTTPS</p><p class="text-sm text-gray-500">Connexion sécurisée</p></div>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Non configuré</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="section-maintenance" class="section-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6">Maintenance</h3>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="border rounded-lg p-4">
                        <div class="text-sm text-gray-500">Version PHP</div>
                        <div class="text-xl font-bold"><?= PHP_VERSION ?></div>
                    </div>
                    <div class="border rounded-lg p-4">
                        <div class="text-sm text-gray-500">Environnement</div>
                        <div class="text-xl font-bold">Docker</div>
                    </div>
                </div>
                <div class="space-y-3">
                    <button class="w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 text-left">
                        <i class="fas fa-broom mr-2"></i>Vider le cache
                    </button>
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
    const url = '/api/admin/parametres/'; 

    fetch('/api/admin/parametres/', { 
        method: 'PUT',
        headers: { 
        'Content-Type': 'application/json'
        },
        body: JSON.stringify({ "maintenance_mode": newState.toString() })
    })
    .then(res => {
        console.log("Status Code:", res.status);
        if (res.status === 404) throw new Error("La route n'existe pas sur le serveur (404)");
        return res.json();
    })
    .then(data => {
        isMaintenance = newState;
        updateMaintUI();
        location.reload();
    })
    .catch(err => {
        console.error(err);
        alert("Détail de l'erreur : " + err.message);
    });
});

showSection('general');
updateMaintUI();
</script>