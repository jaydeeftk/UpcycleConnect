<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Prestations</h2>
        <p class="text-gray-600">Gérez toutes les annonces et prestations</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total</p>
                <p class="text-3xl font-bold">326</p>
            </div>
            <i class="fas fa-bullhorn text-4xl text-blue-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Validées</p>
                <p class="text-3xl font-bold text-green-600">298</p>
            </div>
            <i class="fas fa-check-circle text-4xl text-green-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">En attente</p>
                <p class="text-3xl font-bold text-orange-600">17</p>
            </div>
            <i class="fas fa-clock text-4xl text-orange-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Rejetées</p>
                <p class="text-3xl font-bold text-red-600">11</p>
            </div>
            <i class="fas fa-times-circle text-4xl text-red-500"></i>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <input type="text" placeholder="Rechercher..." class="border rounded-lg px-4 py-2">
        <select class="border rounded-lg px-4 py-2">
            <option>Tous les statuts</option>
            <option>En attente</option>
            <option>Validée</option>
            <option>Rejetée</option>
        </select>
        <select class="border rounded-lg px-4 py-2">
            <option>Toutes les catégories</option>
            <option>Réparation</option>
            <option>Transformation</option>
            <option>Recyclage</option>
        </select>
        <input type="date" class="border rounded-lg px-4 py-2">
        <button class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
            <i class="fas fa-filter mr-2"></i>Filtrer
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Auteur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr>
                <td class="px-6 py-4">
                    <div class="font-medium">Réparation chaise ancienne</div>
                    <div class="text-sm text-gray-500">Chaise en bois à restaurer...</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">Réparation</span>
                </td>
                <td class="px-6 py-4 text-gray-600">Jean Dupont</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Validée</span>
                </td>
                <td class="px-6 py-4 text-gray-600">15/01/2026</td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="px-6 py-4">
                    <div class="font-medium">Customisation lampe bois</div>
                    <div class="text-sm text-gray-500">Donner un nouveau style à...</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Transformation</span>
                </td>
                <td class="px-6 py-4 text-gray-600">Marie Lambert</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm">En attente</span>
                </td>
                <td class="px-6 py-4 text-gray-600">14/01/2026</td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-green-600 hover:text-green-800" title="Valider">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800" title="Rejeter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="px-6 py-4">
                    <div class="font-medium">Recyclage équipement bureau</div>
                    <div class="text-sm text-gray-500">Anciens meubles de bureau...</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Recyclage</span>
                </td>
                <td class="px-6 py-4 text-gray-600">Pierre Martin</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">Archivée</span>
                </td>
                <td class="px-6 py-4 text-gray-600">10/01/2026</td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>