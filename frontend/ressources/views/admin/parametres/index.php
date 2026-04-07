<div class="mb-6">
    <h2 class="text-2xl font-bold">Paramètres</h2>
    <p class="text-gray-600">Configuration générale de la plateforme</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-4">
            <nav class="space-y-2">
                <a href="#general" class="block px-4 py-3 bg-green-50 text-green-700 rounded-lg font-medium">
                    <i class="fas fa-cog mr-2"></i>Général
                </a>
                <a href="#notifications" class="block px-4 py-3 hover:bg-gray-50 rounded-lg">
                    <i class="fas fa-bell mr-2"></i>Notifications
                </a>
                <a href="#paiements" class="block px-4 py-3 hover:bg-gray-50 rounded-lg">
                    <i class="fas fa-credit-card mr-2"></i>Paiements
                </a>
                <a href="#api" class="block px-4 py-3 hover:bg-gray-50 rounded-lg">
                    <i class="fas fa-code mr-2"></i>API
                </a>
                <a href="#securite" class="block px-4 py-3 hover:bg-gray-50 rounded-lg">
                    <i class="fas fa-shield-alt mr-2"></i>Sécurité
                </a>
                <a href="#maintenance" class="block px-4 py-3 hover:bg-gray-50 rounded-lg">
                    <i class="fas fa-wrench mr-2"></i>Maintenance
                </a>
            </nav>
        </div>
    </div>


    <div class="lg:col-span-3 space-y-6">
   
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-6">Paramètres généraux</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Nom du site</label>
                    <input type="text" value="UpcycleConnect" class="w-full border rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Email de contact</label>
                    <input type="email" value="contact@upcycleconnect.fr" class="w-full border rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea rows="3" class="w-full border rounded-lg px-4 py-2">Plateforme de mise en relation pour l'upcycling</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Langue par défaut</label>
                    <select class="w-full border rounded-lg px-4 py-2">
                        <option selected>Français</option>
                        <option>English</option>
                        <option>Deutsch</option>
                        <option>Español</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Fuseau horaire</label>
                    <select class="w-full border rounded-lg px-4 py-2">
                        <option selected>Europe/Paris</option>
                        <option>UTC</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t">
                <button class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-6">Modes de paiement</h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div class="flex items-center">
                        <i class="fab fa-stripe text-4xl text-blue-600 mr-4"></i>
                        <div>
                            <div class="font-medium">Stripe</div>
                            <div class="text-sm text-gray-500">Cartes bancaires, Apple Pay, Google Pay</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div class="flex items-center">
                        <i class="fab fa-paypal text-4xl text-blue-700 mr-4"></i>
                        <div>
                            <div class="font-medium">PayPal</div>
                            <div class="text-sm text-gray-500">Paiements PayPal</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-6">Informations système</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500">Version PHP</div>
                    <div class="text-xl font-bold">8.2.0</div>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500">Version MySQL</div>
                    <div class="text-xl font-bold">8.0.35</div>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500">Espace disque utilisé</div>
                    <div class="text-xl font-bold">2.4 GB</div>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-gray-500">Taille BDD</div>
                    <div class="text-xl font-bold">145 MB</div>
                </div>
            </div>
        </div>
    </div>
</div>