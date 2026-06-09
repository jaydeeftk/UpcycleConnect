<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_nav_recuperation', 'Récupération') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-xl font-bold text-green-400">UpcycleConnect</h1>
            <p class="text-xs text-gray-400 mt-1"><?= t('pro_space', 'Espace Professionnel') ?></p>
        </div>
        <nav class="flex-1 p-4">
            <ul class="space-y-1">
                <li>
                    <a href="/professionnel" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-tachometer-alt w-5"></i><span><?= t('pro_nav_dashboard', 'Tableau de bord') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/recuperation" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-gray-700 text-white">
                        <i class="fas fa-recycle w-5"></i><span><?= t('pro_nav_recuperation', 'Récupération') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/projets/create" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-project-diagram w-5"></i><span><?= t('pro_nav_new_project', 'Nouveau projet') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/annonces" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-bullhorn w-5"></i><span><?= t('pro_nav_annonces', 'Annonces') ?></span>
                    </a>
                </li>
                <li>
                    <a href="/catalogue/services" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-tools w-5"></i><span><?= t('pro_nav_services', 'Services') ?></span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <a href="/logout" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
                <i class="fas fa-sign-out-alt w-5"></i><span><?= t('pro_nav_logout', 'Déconnexion') ?></span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_recup_title', 'Récupération d\'objets') ?></h2>
            <p class="text-gray-600 text-sm"><?= t('pro_recup_subtitle', 'Réservez un objet déposé, puis récupérez-le (bouton ou scan du code-barres).') ?></p>
        </header>

        <main class="flex-1 overflow-y-auto p-6 space-y-6">

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold mb-1"><i class="fas fa-barcode text-gray-500 mr-2"></i><?= t('pro_recup_barcode_title', 'Récupérer par code-barres') ?></h3>
                <p class="text-sm text-gray-500 mb-4"><?= t('pro_recup_barcode_hint', 'Scannez le QR avec la caméra, ou saisissez le code (UCB-…).') ?></p>
                <button type="button" id="scan-btn" class="mb-3 bg-gray-800 text-white px-5 py-2 rounded-lg hover:bg-gray-900 transition font-medium">
                    <i class="fas fa-camera mr-2"></i><?= t('pro_recup_scan_camera', 'Scanner avec la caméra') ?>
                </button>
                <div id="reader" class="hidden mb-3 max-w-xs"></div>
                <div class="text-xs text-gray-400 mb-2"><?= t('pro_recup_or_manual', '— ou saisie manuelle —') ?></div>
                <form id="scan-form" method="POST" action="/professionnel/objets/scanner" class="flex gap-2">
                <?= csrf_field() ?>
                    <input type="text" id="code-input" name="code" placeholder="UCB-XXXXXXXXXXXX" required
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <button type="submit" class="bg-green-500 text-white px-5 py-2 rounded-lg hover:bg-green-600 transition font-medium">
                        <i class="fas fa-box-open mr-2"></i><?= t('pro_action_recuperer', 'Récupérer') ?>
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold mb-4"><?= t('pro_recup_reservations_title', 'Mes réservations') ?></h3>
                <?php if (empty($reservations)): ?>
                    <p class="text-sm text-gray-400"><?= t('pro_recup_reservations_empty', 'Aucune réservation pour le moment.') ?></p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($reservations as $objet): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($objet['type'] ?? 'Objet') ?></h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?= htmlspecialchars($objet['poids'] ?? '') ?> · <?= htmlspecialchars($objet['conteneur'] ?? '') ?>
                                        </p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600"><?= formatStatut($objet['statut'] ?? '') ?></span>
                                </div>
                                <?php if (!empty($objet['code_barre'])): ?>
                                    <p class="text-xs text-gray-400 mb-2"><i class="fas fa-barcode mr-1"></i><?= htmlspecialchars($objet['code_barre']) ?></p>
                                    <div class="qrcode mb-2" data-code="<?= htmlspecialchars($objet['code_barre']) ?>"></div>
                                <?php endif; ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php
                                    $aa = $objet['allowed_actions'] ?? [];
                                    $ui = [
                                        'recuperer' => ['recuperer', t('pro_action_recuperer', 'Récupérer'), 'fa-box-open', 'bg-green-500 hover:bg-green-600'],
                                        'annuler'   => ['annuler',   t('pro_action_annuler', 'Annuler'),   'fa-times',    'bg-gray-400 hover:bg-gray-500'],
                                    ];
                                    foreach ($ui as $a => $b):
                                        if (!in_array($a, $aa, true)) continue; ?>
                                        <form method="POST" action="/professionnel/objets/<?= htmlspecialchars($objet['id'] ?? '') ?>/<?= $b[0] ?>">
                                        <?= csrf_field() ?>
                                            <button type="submit" class="text-white text-xs px-3 py-1.5 rounded <?= $b[3] ?> transition">
                                                <i class="fas <?= $b[2] ?> mr-1"></i><?= $b[1] ?>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold mb-4"><?= t('pro_recup_available_title', 'Objets disponibles') ?></h3>
                <?php if (empty($catalogue)): ?>
                    <p class="text-sm text-gray-400"><?= t('pro_recup_available_empty', 'Aucun objet disponible à la réservation.') ?></p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php foreach ($catalogue as $objet): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($objet['type'] ?? 'Objet') ?></h4>
                                <p class="text-xs text-gray-500 mt-1 mb-3">
                                    <?= htmlspecialchars($objet['poids'] ?? '') ?> · <?= htmlspecialchars($objet['conteneur'] ?? '') ?>
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <?php
                                    $aa = $objet['allowed_actions'] ?? [];
                                    if (in_array('reserver', $aa, true)): ?>
                                        <form method="POST" action="/professionnel/objets/<?= htmlspecialchars($objet['id'] ?? '') ?>/reserver">
                                        <?= csrf_field() ?>
                                            <button type="submit" class="text-white text-xs px-3 py-1.5 rounded bg-blue-500 hover:bg-blue-600 transition">
                                                <i class="fas fa-hand-holding mr-1"></i><?= t('pro_action_reserver', 'Réserver') ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>
<script>
(function () {
    var btn = document.getElementById('scan-btn');
    var input = document.getElementById('code-input');
    var form = document.getElementById('scan-form');
    if (!btn || typeof Html5Qrcode === 'undefined') { if (btn) { btn.style.display = 'none'; } return; }
    Html5Qrcode.getCameras().then(function (cams) {
        if (!cams || cams.length === 0) { btn.style.display = 'none'; }
    }).catch(function () { btn.style.display = 'none'; });
    btn.addEventListener('click', function () {
        document.getElementById('reader').classList.remove('hidden');
        var scanner = new Html5Qrcode('reader');
        scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 220 }, function (decoded) {
            scanner.stop().then(function () { input.value = decoded; form.submit(); });
        }, function () {});
    });
})();
document.querySelectorAll('.qrcode').forEach(function (el) {
    if (el.dataset.code && typeof QRCode !== 'undefined') {
        new QRCode(el, { text: el.dataset.code, width: 90, height: 90 });
    }
});
</script>
</body>
</html>
