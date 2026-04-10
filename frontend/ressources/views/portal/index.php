<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Maintenance - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-sm">
        <div class="text-center mb-8">
            <span class="text-4xl">🔒</span>
            <h1 class="text-2xl font-bold mt-3">Portail Haute Sécurité</h1>
            <p class="text-gray-500 text-sm mt-1">Accès réservé aux administrateurs UpcycleConnect</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="bg-red-50 text-red-600 text-sm rounded-lg px-4 py-3 mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin-portal-access" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Identifiant Administrateur</label>
                <input type="email" name="email" placeholder="admin@upcycle.com"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Mot de passe</label>
                <input type="password" name="password"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
            </div>
            <button type="submit"
                class="w-full bg-gray-900 text-white py-3 rounded-xl text-sm font-medium hover:bg-gray-800 transition">
                Accéder au panneau
            </button>
        </form>
        <div class="text-center mt-6">
            <a href="/UpcycleConnect-PA2526/frontend/public/" class="text-xs text-gray-400 hover:text-gray-600">← Retour à l'engrenage</a>
        </div>
    </div>
</body>
</html>