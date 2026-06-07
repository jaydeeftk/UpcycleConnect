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
            <?= t('contcre_subtitle', 'Remplissez ce formulaire pour soumettre une demande de dépôt. Notre équipe vérifiera votre objet et vous enverra un code d\'accès au conteneur.') ?>
        </p>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-10">
        <?php foreach ([
            ['1', t('contcre_step1_title', 'Votre demande'), t('contcre_step1_desc', 'Décrivez votre objet'), 'text-blue-600 bg-blue-100'],
            ['2', t('contcre_step2_title', 'Vérification'), t('contcre_step2_desc', 'Notre équipe valide'), 'text-base-content/40 bg-base-200'],
            ['3', t('contcre_step3_title', 'Code d\'accès'), t('contcre_step3_desc', 'Déposez votre objet'), 'text-base-content/40 bg-base-200'],
        ] as [$num, $title, $desc, $style]): ?>
            <div class="text-center">
                <div class="w-10 h-10 rounded-full <?= $style ?> flex items-center justify-center font-bold text-lg mx-auto mb-2">
                    <?= $num ?>
                </div>
                <div class="text-sm font-medium"><?= $title ?></div>
                <div class="text-xs text-base-content/50"><?= $desc ?></div>
            </div>
        <?php endforeach; ?>
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

    <div class="bg-base-100 rounded-2xl shadow-sm p-8 space-y-8">

        <form method="POST" action="/conteneurs/store">

            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-5 pb-3 border-b border-base-300">
                    <?= t('contcre_section_object', 'Informations sur l\'objet') ?>
                </h2>

                <div class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('contcre_label_type', 'Type d\'objet') ?> <span class="text-red-500">*</span></label>
                        <select name="type_objet" class="select select-bordered w-full" required>
                            <option value="" disabled selected><?= t('contcre_type_placeholder', 'Sélectionnez un type') ?></option>
                            <option value="mobilier"><?= t('contcre_type_mobilier', 'Mobilier') ?></option>
                            <option value="electromenager"><?= t('contcre_type_electromenager', 'Électroménager') ?></option>
                            <option value="vetements"><?= t('contcre_type_vetements', 'Vêtements & Textiles') ?></option>
                            <option value="electronique"><?= t('contcre_type_electronique', 'Électronique') ?></option>
                            <option value="livres"><?= t('contcre_type_livres', 'Livres & Médias') ?></option>
                            <option value="jouets"><?= t('contcre_type_jouets', 'Jouets') ?></option>
                            <option value="materiaux"><?= t('contcre_type_materiaux', 'Matériaux de construction') ?></option>
                            <option value="autre"><?= t('contcre_type_autre', 'Autre') ?></option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('contcre_label_description', 'Description de l\'objet') ?> <span class="text-red-500">*</span></label>
                        <textarea
                            name="description"
                            rows="4"
                            placeholder="<?= t('contcre_description_placeholder', 'Décrivez l\'objet : matière, dimensions, état général, raison du dépôt...') ?>"
                            class="textarea textarea-bordered w-full resize-none"
                            required
                        ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

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

                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-5 pb-3 border-b border-base-300">
                    <?= t('contcre_section_container', 'Choix du conteneur') ?>
                </h2>

                <div class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('contcre_label_localisation', 'Localisation souhaitée') ?> <span class="text-red-500">*</span></label>
                        <select name="conteneur_id" class="select select-bordered w-full" required>
                            <option value="" disabled selected><?= t('contcre_container_placeholder', 'Sélectionnez un conteneur') ?></option>
                            <?php if (!empty($conteneurs) && is_array($conteneurs)): ?>
                                <?php
                                $list = $conteneurs['data'] ?? $conteneurs;
                                foreach ($list as $conteneur):
                                    if (!is_array($conteneur)) continue;
                                ?>
                                    <option value="<?= htmlspecialchars($conteneur['id'] ?? '') ?>">
                                        <?= htmlspecialchars($conteneur['localisation'] ?? '') ?> — <?= t('contcre_capacity', 'Capacité :') ?> <?= htmlspecialchars($conteneur['capacite'] ?? '?') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">Paris 10ème - Rue La Fayette</option>
                                <option value="2">Paris 11ème</option>
                                <option value="3">Paris 13ème</option>
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
                            value="<?= htmlspecialchars($_POST['date_depot'] ?? '') ?>"
                        >
                        <p class="text-xs text-base-content/50 mt-1"><?= t('contcre_date_help', 'Le dépôt doit être prévu au minimum 24h après votre demande.') ?></p>
                    </div>

                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-5 pb-3 border-b border-base-300">
                    <?= t('contcre_section_destination', 'Destination souhaitée') ?>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="destination" value="don" class="hidden peer" checked>
                        <div class="peer-checked:border-primary peer-checked:bg-primary/5 border-2 border-base-300 rounded-xl p-5 transition hover:border-primary/50">
                            <div class="flex items-center gap-3 mb-2">
                                <i class="fas fa-heart text-green-500 text-xl"></i>
                                <span class="font-semibold"><?= t('contcre_dest_don', 'Don') ?></span>
                            </div>
                            <p class="text-sm text-base-content/60"><?= t('contcre_dest_don_desc', 'L\'objet sera mis à disposition gratuitement pour un artisan ou professionnel.') ?></p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="destination" value="vente" class="hidden peer">
                        <div class="peer-checked:border-primary peer-checked:bg-primary/5 border-2 border-base-300 rounded-xl p-5 transition hover:border-primary/50">
                            <div class="flex items-center gap-3 mb-2">
                                <i class="fas fa-tag text-blue-500 text-xl"></i>
                                <span class="font-semibold"><?= t('contcre_dest_vente', 'Vente') ?></span>
                            </div>
                            <p class="text-sm text-base-content/60"><?= t('contcre_dest_vente_desc', 'L\'objet sera mis en vente. Indiquez votre prix souhaité ci-dessous.') ?></p>
                        </div>
                    </label>
                </div>

                <div id="prix-vente-container" class="mt-4 hidden">
                    <label class="block text-sm font-medium mb-2"><?= t('contcre_label_prix', 'Prix de vente souhaité') ?> (€)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">€</span>
                        <input type="number" name="prix_vente" min="0" step="0.01" placeholder="0.00" class="input input-bordered w-full pl-8">
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3 mb-8">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1"><?= t('contcre_how_title', 'Comment ça marche ?') ?></p>
                    <ul class="space-y-1 text-blue-700">
                        <li>• <?= t('contcre_how_1', 'Votre demande sera examinée par notre équipe sous 24 à 48h.') ?></li>
                        <li>• <?= t('contcre_how_2', 'Si validée, vous recevrez un code d\'accès par email pour ouvrir le conteneur.') ?></li>
                        <li>• <?= t('contcre_how_3', 'Un code-barres sera généré pour permettre aux professionnels de récupérer l\'objet.') ?></li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-base-300">
                <button type="submit" class="btn btn-neutral flex-1">
                    <i class="fas fa-paper-plane mr-2"></i>
                    <?= t('contcre_submit', 'Soumettre la demande') ?>
                </button>
                <a href="/" class="btn btn-ghost flex-1">
                    <?= t('contcre_cancel', 'Annuler') ?>
                </a>
            </div>

        </form>
    </div>
</section>

<script>
    document.querySelectorAll('input[name="destination"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const container = document.getElementById('prix-vente-container');
            container.classList.toggle('hidden', this.value !== 'vente');
        });
    });
</script>