<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Événements</h2>
        <p class="text-gray-600">Gérez les événements et ateliers</p>
    </div>
    <button class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
        <i class="fas fa-plus mr-2"></i>Créer un événement
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">À venir</p>
                <p class="text-3xl font-bold text-blue-600">8</p>
            </div>
            <i class="fas fa-calendar-plus text-4xl text-blue-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">En cours</p>
                <p class="text-3xl font-bold text-green-600">3</p>
            </div>
            <i class="fas fa-calendar-check text-4xl text-green-500"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Terminés</p>
                <p class="text-3xl font-bold">45</p>
            </div>
            <i class="fas fa-calendar text-4xl text-gray-400"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Participants total</p>
                <p class="text-3xl font-bold">1,247</p>
            </div>
            <i class="fas fa-users text-4xl text-purple-500"></i>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Événement</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participants</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr>
                <td class="px-6 py-4">
                    <div class="font-medium">Atelier recyclage créatif</div>
                    <div class="text-sm text-gray-500">Apprenez à transformer vos déchets</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">15/03/2026</div>
                    <div class="text-sm text-gray-500">14h00 - 17h00</div>
                </td>
                <td class="px-6 py-4 text-gray-600">Paris 10ème</td>
                <td class="px-6 py-4">
                    <div class="font-medium">24 / 30</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 80%"></div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">À venir</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </button>
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
                    <div class="font-medium">Formation réparation meubles</div>
                    <div class="text-sm text-gray-500">Techniques de base en ébénisterie</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">20/03/2026</div>
                    <div class="text-sm text-gray-500">09h00 - 12h00</div>
                </td>
                <td class="px-6 py-4 text-gray-600">Paris 11ème</td>
                <td class="px-6 py-4">
                    <div class="font-medium">15 / 20</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 75%"></div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">À venir</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </button>
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
                    <div class="font-medium">Rencontre artisans locaux</div>
                    <div class="text-sm text-gray-500">Networking et échanges</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">10/03/2026</div>
                    <div class="text-sm text-gray-500">18h00 - 21h00</div>
                </td>
                <td class="px-6 py-4 text-gray-600">Paris 13ème</td>
                <td class="px-6 py-4">
                    <div class="font-medium text-green-600">Complet</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">En cours</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-green-600 hover:text-green-800">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>