<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= t('mesprest_title', 'Mes prestations') ?></h1>
            <p class="text-lg text-base-content/70 max-w-2xl">
                <?= t('mesprest_subtitle2', 'Retrouvez ici vos demandes de prestation et leur suivi.') ?>
            </p>
        </div>
        <a href="/demande-prestation" class="bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i><?= t('mesprest_new', 'Nouvelle demande') ?>
        </a>
    </div>

    <?php if (isset($_GET['envoye'])): ?>
        <div class="alert alert-success mb-6"><i class="fas fa-check-circle"></i><span><?= t('mesprest_sent', 'Votre demande a bien été envoyée. Un prestataire pourra vous contacter.') ?></span></div>
    <?php elseif (isset($_GET['erreur'])): ?>
        <div class="alert alert-error mb-6"><i class="fas fa-exclamation-circle"></i><span><?= t('mesprest_error', 'Une erreur est survenue lors de l\'envoi de votre demande.') ?></span></div>
    <?php endif; ?>

    <?php if (empty($prestations)): ?>
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-12 text-center">
            <i class="fas fa-clipboard-list text-5xl text-base-content/30 mb-4"></i>
            <h2 class="text-xl font-semibold mb-2"><?= t('mesprest_empty_title', 'Aucune demande pour le moment') ?></h2>
            <p class="text-base-content/60 mb-6 max-w-md mx-auto"><?= t('mesprest_empty_text', 'Décrivez votre besoin pour qu\'un prestataire puisse vous proposer une solution.') ?></p>
            <a href="/demande-prestation" class="btn btn-neutral"><?= t('mesprest_empty_cta', 'Faire une demande') ?></a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($prestations as $p):
                $statut = $p['statut'] ?? 'ouverte';
                $badge = [
                    'ouverte'  => 'bg-blue-100 text-blue-800',
                    'en_cours' => 'bg-yellow-100 text-yellow-800',
                    'traitee'  => 'bg-green-100 text-green-800',
                    'annulee'  => 'bg-gray-100 text-gray-700',
                ][$statut] ?? 'bg-blue-100 text-blue-800';
                $statutLabel = [
                    'ouverte'  => t('mesprest_status_open', 'Ouverte'),
                    'en_cours' => t('mesprest_status_inprogress', 'En cours'),
                    'traitee'  => t('mesprest_status_done', 'Traitée'),
                    'annulee'  => t('mesprest_status_cancelled', 'Annulée'),
                ][$statut] ?? $statut;
            ?>
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                        <div>
                            <div class="text-sm text-base-content/60 mb-1">
                                <?= htmlspecialchars($p['categorie'] ?? '') ?><?= !empty($p['type_objet']) ? ' • ' . htmlspecialchars($p['type_objet']) : '' ?>
                            </div>
                            <h2 class="text-2xl font-semibold"><?= htmlspecialchars($p['nom_objet'] ?? '') ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $badge ?>"><?= htmlspecialchars($statutLabel) ?></span>
                    </div>
                    <?php if (!empty($p['description'])): ?>
                        <p class="text-base-content/70 mb-4"><?= htmlspecialchars($p['description']) ?></p>
                    <?php endif; ?>
                    <div class="grid md:grid-cols-3 gap-4 text-sm text-base-content/70">
                        <div><span class="font-medium text-base-content"><?= t('mesprest_label_place', 'Lieu :') ?></span> <?= htmlspecialchars(($p['localisation'] ?? '') ?: '—') ?></div>
                        <div><span class="font-medium text-base-content"><?= t('mesprest_label_budget', 'Budget :') ?></span> <?= htmlspecialchars(($p['budget'] ?? '') ?: '—') ?></div>
                        <div><span class="font-medium text-base-content"><?= t('mesprest_label_date', 'Date :') ?></span> <?= htmlspecialchars(($p['date'] ?? '') ?: '—') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>
