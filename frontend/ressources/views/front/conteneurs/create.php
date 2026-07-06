<section class="max-w-3xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-box-open text-blue-600"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 uppercase tracking-wide"><?= t('contcre_eyebrow', 'Dépôt en conteneur') ?></span>
        </div>
        <h1 class="text-3xl font-bold"><?= t('contcre_title', 'Déposer un objet dans un conteneur') ?></h1>
        <p class="text-base-content/60 mt-2">
            <?= t('contcre_subtitle_v2', 'Un dépôt concerne toujours une annonce déjà vendue ou un don déjà réservé — notre équipe vérifie l\'objet et vous envoie un code d\'accès.') ?>
        </p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (empty($id_annonce) || empty($annonce_choisie)): ?>

        <?php if (empty($annonces_eligibles)): ?>
            <div class="bg-base-100 rounded-2xl shadow-sm p-10 text-center text-base-content/40">
                <i class="fas fa-box-open text-4xl mb-3 block"></i>
                <p><?= t('contcre_no_eligible', "Aucune annonce prête à être déposée pour l'instant.") ?></p>
                <p class="text-sm mt-2"><?= t('contcre_no_eligible_hint', 'Une annonce devient déposable une fois vendue, ou un don une fois réservé par quelqu\'un.') ?></p>
                <a href="/mes-annonces" class="link link-primary mt-3 inline-block"><?= t('contcre_go_my_ads', 'Voir mes annonces') ?></a>
            </div>
        <?php else: ?>
            <p class="text-sm text-base-content/60 mb-4"><?= t('contcre_choose_ad', 'Choisissez quelle annonce vous voulez déposer :') ?></p>
            <div class="space-y-3">
                <?php foreach ($annonces_eligibles as $a): ?>
                    <a href="/conteneurs/create?id_annonce=<?= (int)($a['id'] ?? 0) ?>"
                       class="flex items-center justify-between bg-base-100 rounded-2xl shadow-sm p-5 hover:shadow-md transition">
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($a['titre'] ?? '') ?></div>
                            <div class="text-sm text-base-content/50">
                                <?php if (($a['type_annonce'] ?? '') === 'vente'): ?>
                                    <i class="fas fa-tag text-blue-500 mr-1"></i><?= t('contcre_badge_sold', 'Vendue') ?> — <?= htmlspecialchars(formatPrix($a['prix'] ?? 0)) ?>
                                <?php else: ?>
                                    <i class="fas fa-heart text-green-500 mr-1"></i><?= t('contcre_badge_reserved', 'Don réservé') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-base-content/30"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>

        <div class="bg-base-100 rounded-2xl shadow-sm p-8 space-y-8">

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-500 uppercase font-semibold mb-1"><?= t('contcre_selected_ad', 'Annonce concernée') ?></p>
                <p class="font-semibold text-blue-900"><?= htmlspecialchars($annonce_choisie['titre'] ?? '') ?></p>
                <p class="text-sm text-blue-700">
                    <?php if (($annonce_choisie['type_annonce'] ?? '') === 'vente'): ?>
                        <i class="fas fa-tag mr-1"></i><?= t('contcre_badge_sold', 'Vendue') ?> — <?= htmlspecialchars(formatPrix($annonce_choisie['prix'] ?? 0)) ?>
                    <?php else: ?>
                        <i class="fas fa-heart mr-1"></i><?= t('contcre_badge_reserved', 'Don réservé') ?>
                    <?php endif; ?>
                </p>
            </div>

            <form method="POST" action="/conteneurs/store">
            <?= csrf_field() ?>
            <input type="hidden" name="id_annonce" value="<?= (int)$id_annonce ?>">

                <div class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('contcre_label_photo', 'Photo de l\'objet') ?></label>
                        <input
                            type="url"
                            name="photo_url"
                            placeholder="<?= t('contcre_photo_placeholder', 'https://... (lien vers une photo de votre objet)') ?>"
                            class="input input-bordered w-full"
                            value="<?= htmlspecialchars($_POST['photo_url'] ?? '') ?>"
                        >
                        <p class="text-xs text-base-content/50 mt-1"><?= t('contcre_photo_help', 'Facultatif — ajoutez un lien vers une photo pour faciliter la validation.') ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('contcre_label_etat', 'État d\'usure') ?> <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php foreach ([
                                ['bon', t('contcre_etat_bon', 'Bon état'), 'fa-thumbs-up', 'text-green-500'],
                                ['usage', t('contcre_etat_usage', 'Usagé'), 'fa-minus-circle', 'text-yellow-500'],
                                ['abime', t('contcre_etat_abime', 'Abîmé'), 'fa-exclamation-circle', 'text-orange-500'],
                                ['hs', t('contcre_etat_hs', 'Hors service'), 'fa-times-circle', 'text-red-500'],
                            ] as [$val, $label, $icon, $color]): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="etat_usure" value="<?= $val ?>" class="hidden peer" required>
                                    <div class="peer-checked:border-primary peer-checked:bg-primary/5 border-2 border-base-300 rounded-xl p-3 text-center transition hover:border-primary/50">
                                        <i class="fas <?= $icon ?> <?= $color ?> text-xl mb-1 block"></i>
                                        <span class="text-sm font-medium"><?= $label ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('contcre_label_localisation', 'Localisation souhaitée') ?> <span class="text-red-500">*</span></label>
                        <?php
                        $list = (!empty($conteneurs) && is_array($conteneurs)) ? ($conteneurs['data'] ?? $conteneurs) : [];
                        $conteneursVides = empty($list);
                        ?>
                        <select name="conteneur_id" class="select select-bordered w-full" required <?= $conteneursVides ? 'disabled' : '' ?>>
                            <?php if ($conteneursVides): ?>
                                <option value="" disabled selected><?= t('cont_create_no_container', 'Aucun conteneur disponible pour le moment') ?></option>
                            <?php else: ?>
                                <option value="" disabled selected><?= t('contcre_container_placeholder', 'Sélectionnez un conteneur') ?></option>
                                <?php foreach ($list as $conteneur):
                                    if (!is_array($conteneur)) continue;
                                ?>
                                    <option value="<?= htmlspecialchars($conteneur['id'] ?? '') ?>">
                                        <?= htmlspecialchars($conteneur['localisation'] ?? '') ?> — <?= t('contcre_capacity', 'Capacité :') ?> <?= htmlspecialchars($conteneur['capacite'] ?? '?') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('contcre_label_date', 'Date de dépôt souhaitée') ?> <span class="text-red-500">*</span></label>
                        <input
                            type="date"
                            name="date_depot"
                            class="input input-bordered w-full"
                            required
                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                            max="<?= date('Y-m-d', strtotime('+2 years')) ?>"
                            value="<?= htmlspecialchars($_POST['date_depot'] ?? '') ?>"
                        >
                        <p class="text-xs text-base-content/50 mt-1"><?= t('contcre_date_help', 'Le dépôt doit être prévu au minimum 24h après votre demande.') ?></p>
                    </div>

                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3 my-8">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1"><?= t('contcre_how_title', 'Comment ça marche ?') ?></p>
                        <ul class="space-y-1 text-blue-700">
                            <li>• <?= t('contcre_how_1', 'Votre demande sera examinée par notre équipe sous 24 à 48h.') ?></li>
                            <li>• <?= t('contcre_how_2', 'Si validée, vous recevrez un code d\'accès par email pour ouvrir le conteneur.') ?></li>
                            <li>• <?= t('contcre_how_3', 'Un code-barres sera généré et l\'autre partie sera automatiquement prévenue.') ?></li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-base-300">
                    <button type="submit" class="btn btn-neutral flex-1" <?= $conteneursVides ? 'disabled' : '' ?>>
                        <i class="fas fa-paper-plane mr-2"></i>
                        <?= t('contcre_submit', 'Soumettre la demande') ?>
                    </button>
                    <a href="/conteneurs/create" class="btn btn-ghost flex-1">
                        <?= t('contcre_cancel', 'Annuler') ?>
                    </a>
                </div>

            </form>
        </div>

    <?php endif; ?>

</section>
