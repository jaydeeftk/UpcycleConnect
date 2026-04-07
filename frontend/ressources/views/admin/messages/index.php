<div class="mb-6">
    <h2 class="text-2xl font-bold">Messagerie</h2>
    <p class="text-gray-600">Gérez les messages et notifications</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <input type="text" placeholder="Rechercher une conversation..." class="w-full border rounded-lg px-4 py-2">
        </div>

        <div class="divide-y max-h-96 overflow-y-auto">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $i => $msg): ?>
                <div class="p-4 hover:bg-gray-50 cursor-pointer <?= $i === 0 ? 'bg-green-50 border-l-4 border-green-500' : '' ?>">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                <?= strtoupper(substr($msg['prenom'] ?? 'U', 0, 1)) ?><?= strtoupper(substr($msg['nom'] ?? '', 0, 1)) ?>
                            </div>
                            <div>
                                <div class="font-medium"><?= htmlspecialchars(($msg['prenom'] ?? '') . ' ' . ($msg['nom'] ?? '')) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($msg['email'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php if ($i === 0): ?>
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-gray-600 truncate"><?= htmlspecialchars($msg['contenu'] ?? '') ?></p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-8 text-center text-gray-400">
                    Aucun message pour le moment.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-lg shadow flex flex-col" style="height: 600px;">
        <?php if (!empty($messages)): ?>
        <?php $first = $messages[0]; ?>
        <div class="p-4 border-b flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                    <?= strtoupper(substr($first['prenom'] ?? 'U', 0, 1)) ?><?= strtoupper(substr($first['nom'] ?? '', 0, 1)) ?>
                </div>
                <div>
                    <div class="font-medium"><?= htmlspecialchars(($first['prenom'] ?? '') . ' ' . ($first['nom'] ?? '')) ?></div>
                    <div class="text-sm text-green-600">En ligne</div>
                </div>
            </div>
            <div class="flex gap-2">
                <button class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-phone"></i>
                </button>
                <button class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 p-4 overflow-y-auto">
            <?php foreach ($messages as $msg): ?>
            <div class="mb-4">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold mr-2">
                        <?= strtoupper(substr($msg['prenom'] ?? 'U', 0, 1)) ?><?= strtoupper(substr($msg['nom'] ?? '', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="bg-gray-100 rounded-lg px-4 py-2 max-w-md">
                            <p><?= htmlspecialchars($msg['contenu'] ?? '') ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="flex-1 flex items-center justify-center text-gray-400">
            Sélectionnez une conversation
        </div>
        <?php endif; ?>

        <div class="p-4 border-t">
            <div class="flex gap-2">
                <button class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-paperclip text-xl"></i>
                </button>
                <input type="text" placeholder="Tapez votre message..." class="flex-1 border rounded-lg px-4 py-2">
                <button class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>