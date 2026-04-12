<section class="max-w-lg mx-auto px-6 py-16">

    <div class="mb-8">
        <a href="javascript:history.back()" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-6">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <h1 class="text-2xl font-bold">Finaliser le paiement</h1>
        <p class="text-base-content/60 mt-1">Paiement sécurisé via Stripe</p>
    </div>

    <?php if (isset($_GET['pending'])): ?>
        <div class="alert alert-warning mb-6">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Le système de paiement Stripe est en cours de configuration. Votre inscription a été prise en compte.</span>
        </div>
    <?php endif; ?>

    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 mb-6">
        <h2 class="font-semibold mb-4">Récapitulatif</h2>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-base-content/70"><?= htmlspecialchars($_GET['titre'] ?? 'Article') ?></span>
            <span class="font-bold"><?= htmlspecialchars($_GET['montant'] ?? '0') ?>€</span>
        </div>
        <div class="flex justify-between items-center pt-3">
            <span class="font-semibold">Total</span>
            <span class="text-xl font-bold text-green-600"><?= htmlspecialchars($_GET['montant'] ?? '0') ?>€</span>
        </div>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 mb-6">
        <h2 class="font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-lock text-green-500"></i> Informations de paiement
        </h2>

        <div id="stripe-card-element" class="bg-base-200 rounded-xl p-4 mb-4">
            <p class="text-sm text-base-content/50 text-center py-4">
                <i class="fas fa-credit-card text-2xl mb-2 block"></i>
                Stripe en cours de configuration...
            </p>
        </div>

        <div id="stripe-errors" class="text-red-500 text-sm mb-4 hidden"></div>

        <button id="btn-payer" onclick="handlePaiement()"
            class="btn btn-neutral w-full">
            <i class="fas fa-lock mr-2"></i> Payer <?= htmlspecialchars($_GET['montant'] ?? '0') ?>€
        </button>

        <p class="text-xs text-base-content/40 text-center mt-3">
            <i class="fas fa-shield-alt mr-1"></i>
            Paiement 100% sécurisé — vos données bancaires ne sont jamais stockées
        </p>
    </div>

    <div class="flex items-center justify-center gap-4 text-base-content/30">
        <i class="fab fa-cc-visa text-2xl"></i>
        <i class="fab fa-cc-mastercard text-2xl"></i>
        <i class="fab fa-cc-amex text-2xl"></i>
        <i class="fab fa-cc-paypal text-2xl"></i>
    </div>

</section>

<script>

const stripe = Stripe('pk_test_51TJkt7GjiAXa1AoyxnXTnjNzOChs8pI9PbKxFmJGuYVYYtNBa8im7erdIn7p5eQR2woteHVaBcmo3g8Al10ghAs500LxVozkxR');

async function handlePaiement() {
    const btn = document.getElementById('btn-payer');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement...';

    try {
        const res = await fetch('/api/paiements/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_utilisateur: <?= $_SESSION['user']['id'] ?? 0 ?>,
                type: '<?= htmlspecialchars($_GET['type'] ?? '') ?>',
                id_item: <?= intval($_GET['id_item'] ?? 0) ?>,
                montant: <?= floatval($_GET['montant'] ?? 0) ?>,
                titre: '<?= addslashes($_GET['titre'] ?? '') ?>'
            })
        });
        const json = await res.json();

        if (json.success && json.data && json.data.checkout_url) {
            window.location.href = json.data.checkout_url;
        } else {
            alert('Erreur lors de la création de la session de paiement');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock mr-2"></i> Payer <?= htmlspecialchars($_GET['montant'] ?? '0') ?>€';
        }
    } catch(e) {
        console.error(e);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock mr-2"></i> Payer <?= htmlspecialchars($_GET['montant'] ?? '0') ?>€';
    }
}
</script>