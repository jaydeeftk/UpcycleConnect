<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_abo_page_title', 'Abonnement Premium') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_abo_heading', 'Abonnement Premium') ?></h2>
            <p class="text-gray-600 text-sm"><?= t('pro_abo_subtitle', 'Débloquez des outils avancés pour votre activité') ?></p>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-2xl mx-auto space-y-6">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php $statut = $abonnement['statut'] ?? null; $estActif = in_array($statut, ['actif', 'suspendu'], true); ?>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-8 text-white text-center">
                    <i class="fas fa-crown text-4xl mb-3"></i>
                    <h3 class="text-2xl font-bold"><?= t('pro_abo_plan_name', 'UpcycleConnect Premium') ?></h3>
                    <p class="text-blue-100 mt-1"><?= t('pro_abo_plan_desc', 'Tableaux de bord avancés, statistiques et alertes priorisées') ?></p>
                    <p class="text-4xl font-bold mt-4">24,99 € <span class="text-base font-normal text-blue-100">/ <?= t('pro_abo_per_month', 'mois') ?></span></p>
                </div>

                <div class="p-6">
                    <?php if ($estActif): ?>
                        <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200">
                            <div>
                                <p class="text-sm text-gray-500"><?= t('pro_abo_status_label', 'Statut') ?></p>
                                <span class="px-3 py-1 rounded-full text-sm font-medium <?= $statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= htmlspecialchars(formatStatut($statut)) ?>
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500"><?= t('pro_abo_since', 'Actif depuis le') ?></p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars(formatDate($abonnement['date_debut'] ?? '')) ?></p>
                            </div>
                        </div>
                        <div class="mb-6 text-sm text-gray-500">
                            <?= t('pro_abo_ref', 'Référence') ?> : <span class="font-mono"><?= htmlspecialchars($abonnement['id'] ?? '') ?></span>
                        </div>
                        <form method="POST" action="/professionnel/abonnement/resilier"
                              onsubmit="return confirm('<?= t('pro_abo_confirm_cancel', 'Résilier votre abonnement Premium ?') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full border border-red-300 text-red-600 py-3 rounded-lg hover:bg-red-50 transition font-medium">
                                <i class="fas fa-times mr-2"></i><?= t('pro_abo_cancel_btn', 'Résilier mon abonnement') ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <ul class="space-y-3 mb-6 text-sm text-gray-600">
                            <li class="flex items-start gap-2"><i class="fas fa-check text-green-500 mt-0.5"></i> <?= t('pro_abo_perk_1', 'Tableaux de bord avancés') ?></li>
                            <li class="flex items-start gap-2"><i class="fas fa-check text-green-500 mt-0.5"></i> <?= t('pro_abo_perk_2', "Analyse d'impact écologique détaillée") ?></li>
                            <li class="flex items-start gap-2"><i class="fas fa-check text-green-500 mt-0.5"></i> <?= t('pro_abo_perk_3', 'Statistiques sur les matériaux disponibles') ?></li>
                            <li class="flex items-start gap-2"><i class="fas fa-check text-green-500 mt-0.5"></i> <?= t('pro_abo_perk_4', 'Alertes priorisées pour la collecte') ?></li>
                        </ul>
                        <div id="abo-erreur" class="text-red-600 text-sm mb-3 hidden"></div>
                        <button type="button" id="btn-souscrire" onclick="souscrireAbonnement()"
                                class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition font-medium">
                            <i class="fas fa-lock mr-2"></i><?= t('pro_abo_subscribe_btn', 'Souscrire au Premium — 24,99 €') ?>
                        </button>
                        <p class="text-xs text-gray-400 text-center mt-3">
                            <i class="fas fa-shield-alt mr-1"></i>
                            <?= t('pro_abo_secure_notice', 'Paiement sécurisé via Stripe') ?>
                        </p>
                        <?php if ($statut === 'resilie' || $statut === 'expire'): ?>
                            <p class="text-xs text-gray-400 text-center mt-3">
                                <?= t('pro_abo_previous', 'Votre précédent abonnement est') ?> <?= htmlspecialchars(formatStatut($statut)) ?>.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        </main>
    </div>
</div>

<script>
const TOKEN = <?= json_encode($token ?? '') ?>;

async function souscrireAbonnement() {
    const btn = document.getElementById('btn-souscrire');
    const err = document.getElementById('abo-erreur');
    err.classList.add('hidden');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> <?= t('pro_abo_processing', 'Redirection vers Stripe...') ?>';

    try {
        const res = await fetch('/api/professionnels/abonnement/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
        });
        const json = await res.json();
        if (json.success && json.data && json.data.checkout_url) {
            window.location.href = json.data.checkout_url;
            return;
        }
        err.textContent = (json && json.error) || <?= json_encode(t('pro_abo_checkout_error', 'Erreur lors de la création de la session de paiement.')) ?>;
        err.classList.remove('hidden');
    } catch (e) {
        err.textContent = <?= json_encode(t('pro_abo_checkout_error', 'Erreur lors de la création de la session de paiement.')) ?>;
        err.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-lock mr-2"></i> <?= t('pro_abo_subscribe_btn', 'Souscrire au Premium — 24,99 €') ?>';
}
</script>

</body>
</html>
