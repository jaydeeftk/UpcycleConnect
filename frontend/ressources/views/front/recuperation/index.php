<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= t('recup_title', 'Récupérer mes objets') ?></h1>
        <p class="text-lg text-base-content/70 max-w-2xl">
            <?= t('recup_subtitle', 'Retrouvez ici les objets que vous avez achetés ou dont vous avez réservé le don, et marquez-les comme récupérés une fois en main.') ?>
        </p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success mb-6"><i class="fas fa-check-circle"></i><span><?= htmlspecialchars($_SESSION['success']) ?></span></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error mb-6"><i class="fas fa-exclamation-circle"></i><span><?= htmlspecialchars($_SESSION['error']) ?></span></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 mb-8">
        <h3 class="text-lg font-bold mb-1"><i class="fas fa-barcode text-base-content/50 mr-2"></i><?= t('recup_barcode_title', 'Récupérer par code-barres') ?></h3>
        <p class="text-sm text-base-content/60 mb-4"><?= t('recup_barcode_hint', 'Scannez le QR avec la caméra, ou saisissez le code (UCB-…).') ?></p>
        <button type="button" id="scan-btn" class="mb-3 btn btn-neutral">
            <i class="fas fa-camera mr-2"></i><?= t('recup_scan_camera', 'Scanner avec la caméra') ?>
        </button>
        <div id="reader" class="hidden mb-3 max-w-xs"></div>
        <div class="text-xs text-base-content/40 mb-2"><?= t('recup_or_manual', '— ou saisie manuelle —') ?></div>
        <form id="scan-form" method="POST" action="/mes-objets/recuperer-par-code" class="flex gap-2">
            <?= csrf_field() ?>
            <input type="text" id="code-input" name="code" placeholder="UCB-XXXXXXXXXXXX" required
                class="flex-1 input input-bordered">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-box-open mr-2"></i><?= t('recup_action_recuperer', 'Récupérer') ?>
            </button>
        </form>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
        <h3 class="text-lg font-bold mb-4"><?= t('recup_mine_title', 'Mes objets') ?> (<?= count($mesObjets) ?>)</h3>
        <?php if (empty($mesObjets)): ?>
            <p class="text-sm text-base-content/40"><?= t('recup_mine_empty', "Aucun objet à récupérer pour l'instant. Un objet apparaît ici une fois que le vendeur ou le donateur l'a déposé et que le dépôt est validé.") ?></p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($mesObjets as $objet): ?>
                    <div class="border border-base-300 rounded-xl p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold"><?= htmlspecialchars($objet['titre'] ?: ($objet['type'] ?? 'Objet')) ?></h4>
                                    <?php if (($objet['type_annonce'] ?? '') === 'vente'): ?>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-semibold uppercase"><?= t('recup_badge_purchase', 'Achat') ?></span>
                                    <?php elseif (($objet['type_annonce'] ?? '') === 'don'): ?>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold uppercase"><?= t('recup_badge_donation', 'Don') ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-base-content/50 mt-1">
                                    <?= htmlspecialchars($objet['type'] ?? '') ?> · <?= htmlspecialchars($objet['conteneur'] ?? '') ?>
                                </p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-base-200 text-base-content/70"><?= formatStatut($objet['statut'] ?? '') ?></span>
                        </div>
                        <?php if (!empty($objet['code_barre'])): ?>
                            <p class="text-xs text-base-content/40 mb-2"><i class="fas fa-barcode mr-1"></i><?= htmlspecialchars($objet['code_barre']) ?></p>
                            <div class="qrcode mb-2" data-code="<?= htmlspecialchars($objet['code_barre']) ?>"></div>
                        <?php endif; ?>
                        <?php if (in_array('recuperer', $objet['allowed_actions'] ?? [], true)): ?>
                            <form method="POST" action="/mes-objets/<?= (int)($objet['id'] ?? 0) ?>/recuperer">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-white text-xs px-3 py-1.5 rounded bg-green-500 hover:bg-green-600 transition">
                                    <i class="fas fa-box-open mr-1"></i><?= t('recup_action_recuperer', 'Récupérer') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</section>
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
