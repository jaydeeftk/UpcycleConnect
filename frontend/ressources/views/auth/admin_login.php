<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="p-8 bg-white rounded-2xl shadow-xl w-full max-w-md border border-gray-200">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Accès Administration</h2>
        
        <form action="/UpcycleConnect-PA2526/frontend/public/admin-portal-access" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Identifiant / Email</label>
                <input type="text" name="email" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input type="password" name="password" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                Se connecter
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="/UpcycleConnect-PA2526/frontend/public/" class="text-xs text-gray-400 hover:underline">Retour à l'accueil</a>
        </div>
    </div>
</div>