<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Catégories</h2>
        <p class="text-gray-600">Gérez les catégories de prestations</p>
    </div>
    <button class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Ajouter une catégorie
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total catégories</p>
                <p class="text-3xl font-bold">12</p>
            </div>
            <i class="fas fa-folder text-4xl text-blue-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Actives</p>
                <p class="text-3xl font-bold text-green-600">10</p>
            </div>
            <i class="fas fa-check-circle text-4xl text-green-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Inactives</p>
                <p class="text-3xl font-bold text-gray-600">2</p>
            </div>
            <i class="fas fa-times-circle text-4xl text-gray-400"></i>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icône</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Annonces</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr>
                <td class="px-6 py-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tools text-2xl text-blue-600"></i>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">Réparation</div>
                </td>
                <td class="px-6 py-4 text-gray-600">Réparer vos objets cassés</td>
                <td class="px-6 py-4">
                    <span class="font-bold">142</span> annonces
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Active</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-green-600 hover:text-green-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="px-6 py-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-magic text-2xl text-yellow-600"></i>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">Transformation</div>
                </td>
                <td class="px-6 py-4 text-gray-600">Transformer et customiser</td>
                <td class="px-6 py-4">
                    <span class="font-bold">98</span> annonces
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Active</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-green-600 hover:text-green-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="px-6 py-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-recycle text-2xl text-green-600"></i>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">Recyclage</div>
                </td>
                <td class="px-6 py-4 text-gray-600">Recyclage écologique</td>
                <td class="px-6 py-4">
                    <span class="font-bold">86</span> annonces
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Active</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-green-600 hover:text-green-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="px-6 py-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-paint-brush text-2xl text-purple-600"></i>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">Upcycling créatif</div>
                </td>
                <td class="px-6 py-4 text-gray-600">Créations artistiques</td>
                <td class="px-6 py-4">
                    <span class="font-bold">54</span> annonces
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Active</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-green-600 hover:text-green-800">
                            <i class="fas fa-edit"></i>
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