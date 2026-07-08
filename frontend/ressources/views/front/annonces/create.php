<section class="max-w-3xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-bullhorn text-green-600"></i>
            </div>
            <span class="text-sm font-medium text-green-600 uppercase tracking-wide"><?= t('anncre_eyebrow', 'Nouvelle annonce') ?></span>
        </div>
        <h1 class="text-3xl font-bold"><?= t('anncre_title', 'Déposer une annonce') ?></h1>
        <p class="text-base-content/60 mt-2">
            <?= t('anncre_subtitle', 'Décrivez l\'objet que vous souhaitez donner ou vendre. Votre annonce sera vérifiée par notre équipe avant d\'être publiée.') ?>
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

    <div class="bg-base-100 rounded-2xl shadow-sm p-8 space-y-8">

        <form method="POST" action="/annonces/store" enctype="multipart/form-data">
        <?= csrf_field() ?>

            <div>
                <h2 class="text-lg font-semibold mb-5 pb-3 border-b border-base-300">
                    <?= t('anncre_section_item', 'Informations sur l\'objet') ?>
                </h2>

                <div class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('anncre_label_title', 'Titre de l\'annonce') ?> <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="titre"
                            placeholder="<?= t('anncre_ph_title', 'Ex : Chaise en bois vintage, Lampe de bureau...') ?>"
                            class="input input-bordered w-full"
                            required
                            value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('anncre_label_category', 'Catégorie') ?> <span class="text-red-500">*</span></label>
                        <select name="categorie" class="select select-bordered w-full" required>
                            <option value="" disabled selected><?= t('anncre_cat_placeholder', 'Sélectionnez une catégorie') ?></option>
                            <option value="mobilier"><?= t('anncre_cat_furniture', 'Mobilier') ?></option>
                            <option value="electromenager"><?= t('anncre_cat_appliances', 'Électroménager') ?></option>
                            <option value="vetements"><?= t('anncre_cat_clothing', 'Vêtements & Textiles') ?></option>
                            <option value="electronique"><?= t('anncre_cat_electronics', 'Électronique') ?></option>
                            <option value="livres"><?= t('anncre_cat_books', 'Livres & Médias') ?></option>
                            <option value="jouets"><?= t('anncre_cat_toys', 'Jouets') ?></option>
                            <option value="materiaux"><?= t('anncre_cat_materials', 'Matériaux de construction') ?></option>
                            <option value="autre"><?= t('anncre_cat_other', 'Autre') ?></option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('anncre_label_description', 'Description') ?> <span class="text-red-500">*</span></label>
                        <textarea
                            name="description"
                            rows="4"
                            placeholder="<?= t('anncre_ph_description', 'Décrivez l\'objet : matière, dimensions, historique, défauts éventuels...') ?>"
                            class="textarea textarea-bordered w-full resize-none"
                            required
                        ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('anncre_label_condition', 'État de l\'objet') ?> <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php foreach ([
                                ['neuf', t('anncre_cond_new', 'Neuf'), 'fa-star', 'text-green-500'],
                                ['bon', t('anncre_cond_good', 'Bon état'), 'fa-thumbs-up', 'text-blue-500'],
                                ['usage', t('anncre_cond_used', 'Usagé'), 'fa-minus-circle', 'text-yellow-500'],
                                ['abime', t('anncre_cond_damaged', 'Abîmé'), 'fa-exclamation-circle', 'text-red-500'],
                            ] as [$val, $label, $icon, $color]): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="etat" value="<?= $val ?>" class="hidden peer" required>
                                    <div class="peer-checked:border-primary peer-checked:bg-primary/5 border-2 border-base-300 rounded-xl p-3 text-center transition hover:border-primary/50">
                                        <i class="fas <?= $icon ?> <?= $color ?> text-xl mb-1 block"></i>
                                        <span class="text-sm font-medium"><?= $label ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('anncre_label_photos', 'Photo de l\'objet') ?> <span class="text-error">*</span></label>
                        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" required
                               class="file-input file-input-bordered w-full">
                        <p class="text-xs text-base-content/40 mt-1"><?= t('anncre_upload_hint', 'JPG, PNG ou WebP, 5 Mo max.') ?></p>
                        <div id="photo-preview" class="mt-3 hidden">
                            <img alt="" class="h-32 rounded-lg object-cover border border-base-300">
                        </div>
                    </div>

                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-5 pb-3 border-b border-base-300">
                    <?= t('anncre_section_type', 'Type de mise à disposition') ?>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="type_annonce" value="don" class="hidden peer" checked>
                        <div class="peer-checked:border-primary peer-checked:bg-primary/5 border-2 border-base-300 rounded-xl p-5 transition hover:border-primary/50">
                            <div class="flex items-center gap-3 mb-2">
                                <i class="fas fa-heart text-green-500 text-xl"></i>
                                <span class="font-semibold"><?= t('anncre_type_gift', 'Don gratuit') ?></span>
                            </div>
                            <p class="text-sm text-base-content/60"><?= t('anncre_type_gift_desc', 'Vous offrez cet objet gratuitement à quelqu\'un qui en a besoin.') ?></p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="type_annonce" value="vente" class="hidden peer">
                        <div class="peer-checked:border-primary peer-checked:bg-primary/5 border-2 border-base-300 rounded-xl p-5 transition hover:border-primary/50">
                            <div class="flex items-center gap-3 mb-2">
                                <i class="fas fa-tag text-blue-500 text-xl"></i>
                                <span class="font-semibold"><?= t('anncre_type_sale', 'Vente') ?></span>
                            </div>
                            <p class="text-sm text-base-content/60"><?= t('anncre_type_sale_desc', 'Vous souhaitez vendre cet objet. Indiquez votre prix ci-dessous.') ?></p>
                        </div>
                    </label>
                </div>

                <div id="prix-container" class="mt-4 hidden">
                    <label class="block text-sm font-medium mb-2"><?= t('anncre_label_price', 'Prix de vente') ?> (€) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">€</span>
                        <input type="number" name="prix" min="0" step="0.01" placeholder="0.00" class="input input-bordered w-full pl-8">
                    </div>
                    <p class="text-xs text-base-content/50 mt-1"><?= t('anncre_commission_note', 'Une commission de 5 à 10% sera prélevée par UpcycleConnect sur la vente.') ?></p>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-5 pb-3 border-b border-base-300">
                    <?= t('anncre_section_location', 'Localisation') ?>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('anncre_label_city', 'Ville') ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="ville" placeholder="<?= t('anncre_ph_city', 'Ex : Paris') ?>" class="input input-bordered w-full" required value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"><?= t('anncre_label_zip', 'Code postal') ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="code_postal" placeholder="<?= t('anncre_ph_zip', 'Ex : 75010') ?>" class="input input-bordered w-full" required maxlength="5" value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-base-300">
                <button type="submit" class="btn btn-neutral flex-1">
                    <i class="fas fa-paper-plane mr-2"></i>
                    <?= t('anncre_submit', 'Soumettre l\'annonce') ?>
                </button>
                <a href="/" class="btn btn-ghost flex-1">
                    <?= t('anncre_cancel', 'Annuler') ?>
                </a>
            </div>

            <p class="text-xs text-base-content/40 text-center">
                <i class="fas fa-info-circle mr-1"></i>
                <?= t('anncre_review_note', 'Votre annonce sera examinée par notre équipe avant d\'être publiée sur la plateforme.') ?>
            </p>

        </form>
    </div>
</section>

<script>

    document.querySelectorAll('input[name="type_annonce"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const prixContainer = document.getElementById('prix-container');
            if (this.value === 'vente') {
                prixContainer.classList.remove('hidden');
                prixContainer.querySelector('input').required = true;
            } else {
                prixContainer.classList.add('hidden');
                prixContainer.querySelector('input').required = false;
            }
        });
    });

    document.getElementById('photo').addEventListener('change', function() {
        const preview = document.getElementById('photo-preview');
        const file = this.files[0];
        if (file) {
            preview.querySelector('img').src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    });
</script>