<div class="mb-6">
    <h2 class="text-2xl font-bold">Paramètres</h2>
    <p class="text-gray-600">Configuration générale de la plateforme</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-4">
            <nav class="space-y-1" id="param-nav">
                <a href="#general" onclick="showSection('general',this)" class="param-link block px-4 py-3 bg-green-50 text-green-700 rounded-lg font-medium cursor-pointer">
                    <i class="fas fa-cog mr-2"></i>Général
                </a>
                <a href="#paiements" onclick="showSection('paiements',this)" class="param-link block px-4 py-3 hover:bg-gray-50 rounded-lg cursor-pointer">
                    <i class="fas fa-credit-card mr-2"></i>Paiements
                </a>
                <a href="#systeme" onclick="showSection('systeme',this)" class="param-link block px-4 py-3 hover:bg-gray-50 rounded-lg cursor-pointer">
                    <i class="fas fa-server mr-2"></i>Système
                </a>
                <a href="#maintenance" onclick="showSection('maintenance',this)" class="param-link block px-4 py-3 hover:bg-gray-50 rounded-lg cursor-pointer">
                    <i class="fas fa-wrench mr-2"></i>Maintenance
                </a>
            </nav>
        </div>
    </div>

    <div class="lg:col-span-3 space-y-6">
        <div id="section-general" class="param-section bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-6">Paramètres généraux</h3>
            <form method="POST" action="/admin/parametres/update" class="space-y-4">
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
                        <option>Français</option>
                        <option>English</option>
                    </select>
                </div>
                <div class="mt-6 pt-6 border-t">
                    <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>

        <div id="section-paiements" class="param-section hidden bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-6">Modes de paiement</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div class="flex items-center">
                        <i class="fab fa-stripe text-4xl text-blue-600 mr-4"></i>
                        <div>
                            <div class="font-medium">Stripe</div>
                            <div class="text-sm text-gray-500">Cartes bancaires, Apple Pay</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
            </div>
        </div>

        <div id="section-systeme" class="param-section hidden bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-6">Informations système</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500">Version PHP</div>
                    <div class="text-xl font-bold"><?= phpversion() ?></div>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500">Environnement</div>
                    <div class="text-xl font-bold">Docker</div>
                </div>
            </div>
        </div>

        <div id="section-maintenance" class="param-section hidden bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-6">Mode Maintenance</h3>
            <?php
            $maintenanceFile = '/tmp/.maintenance';
            $active = file_exists($maintenanceFile);
            ?>
            <div class="mb-6 p-4 rounded-lg <?= $active ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200' ?>">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full <?= $active ? 'bg-red-500 animate-pulse' : 'bg-green-500' ?>"></div>
                    <span class="font-medium <?= $active ? 'text-red-700' : 'text-green-700' ?>">
                        Site <?= $active ? 'en maintenance (inaccessible au public)' : 'en ligne (accessible au public)' ?>
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500">Version PHP</div>
                    <div class="text-xl font-bold"><?= phpversion() ?></div>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500">Environnement</div>
                    <div class="text-xl font-bold">Docker</div>
                </div>
            </div>
            <form method="POST" action="/admin/parametres/update-maintenance">
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-lg font-medium text-white text-lg <?= $active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' ?>">
                    <?php if ($active): ?>
                        <i class="fas fa-times-circle"></i> Désactiver le mode maintenance
                    <?php else: ?>
                        <i class="fas fa-wrench"></i> Activer le mode maintenance
                    <?php endif; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function showSection(id, el) {
    document.querySelectorAll('.param-section').forEach(s => s.classList.add('hidden'));
    document.querySelectorAll('.param-link').forEach(l => {
        l.classList.remove('bg-green-50', 'text-green-700', 'font-medium');
        l.classList.add('hover:bg-gray-50');
    });
    document.getElementById('section-' + id).classList.remove('hidden');
    if (el) {
        el.classList.add('bg-green-50', 'text-green-700', 'font-medium');
        el.classList.remove('hover:bg-gray-50');
    }
}
const section = new URLSearchParams(window.location.search).get('section');
if (section) {
    const link = document.querySelector(`[onclick*="'${section}'"]`);
    if (link) showSection(section, link);
}
</script>