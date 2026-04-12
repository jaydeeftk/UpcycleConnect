<div class="flex items-center justify-center min-h-screen bg-slate-900">
    <div class="p-8 bg-white rounded-2xl shadow-2xl w-full max-w-md border-t-4 border-primary">
        <div class="text-center mb-8">
            <div class="text-4xl mb-2">🔐</div>
            <h2 class="text-2xl font-bold text-gray-800">Portail Haute Sécurité</h2>
            <p class="text-sm text-gray-500 mt-2">Accès réservé aux administrateurs UpcycleConnect</p>
        </div>

        <form action="/admin-portal-access" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Identifiant Administrateur</label>
                <input type="email" name="email" placeholder="admin@upcycle.com" 
                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" 
                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" required>
            </div>

            <button type="submit" class="w-full bg-primary hover:bg-primary-focus text-white font-bold py-3 rounded-xl shadow-lg transform active:scale-95 transition-all">
                Démarrer la session
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <a href="/" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Retour à l'engrenage
            </a>
        </div>
    </div>
</div>