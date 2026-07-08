<section class="max-w-5xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-12 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= t('demcre_title', 'Faire une demande de prestation') ?></h1>
        <p class="text-lg text-base-content/70 max-w-2xl mx-auto">
            <?= t('demcre_subtitle', 'Décrivez votre besoin pour trouver un professionnel capable de réparer, transformer ou recycler votre objet.') ?>
        </p>
    </div>

    <?php if (isset($_GET['photo'])): ?>
        <div class="alert alert-error mb-6 max-w-3xl mx-auto">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= t('demcre_photo_required_error', 'Une photo de l\'objet est obligatoire (JPEG, PNG ou WebP, 5 Mo max).') ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-base-100 rounded-3xl shadow-sm p-8 md:p-10">
        <form class="space-y-8" method="POST" action="/demande-prestation" enctype="multipart/form-data">
        <?= csrf_field() ?>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('demcre_label_name', 'Nom de l\'objet') ?></label>
                    <input type="text" name="nom_objet" placeholder="<?= t('demcre_ph_name', 'Ex : Grille-pain, chaise, vélo...') ?>"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('demcre_label_category', 'Catégorie de demande') ?></label>
                    <select name="categorie"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="" disabled selected><?= t('demcre_cat_placeholder', 'Choisir une catégorie') ?></option>
                        <option><?= t('demcre_cat_repair', 'Réparation') ?></option>
                        <option><?= t('demcre_cat_transform', 'Transformation') ?></option>
                        <option><?= t('demcre_cat_recycle', 'Recyclage') ?></option>
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('demcre_label_type', 'Type d\'objet') ?></label>
                    <select name="type_objet"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="" disabled selected><?= t('demcre_type_placeholder', 'Choisir un type') ?></option>
                        <option><?= t('demcre_type_appliances', 'Électroménager') ?></option>
                        <option><?= t('demcre_type_furniture', 'Mobilier') ?></option>
                        <option><?= t('demcre_type_electronics', 'Électronique') ?></option>
                        <option><?= t('demcre_type_textile', 'Textile') ?></option>
                        <option><?= t('demcre_type_bike', 'Vélo') ?></option>
                        <option><?= t('demcre_type_other', 'Autre') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('demcre_label_state', 'État de l\'objet') ?></label>
                    <select name="etat"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="" disabled selected><?= t('demcre_state_placeholder', 'Choisir l\'état') ?></option>
                        <option><?= t('demcre_state_slightly', 'Légèrement abîmé') ?></option>
                        <option><?= t('demcre_state_damaged', 'Endommagé') ?></option>
                        <option><?= t('demcre_state_broken', 'Ne fonctionne plus') ?></option>
                        <option><?= t('demcre_state_totransform', 'À transformer') ?></option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2"><?= t('demcre_label_description', 'Description de votre besoin') ?></label>
                <textarea name="description" rows="5"
                    placeholder="<?= t('demcre_ph_description', 'Décrivez votre objet, le problème rencontré ou la transformation souhaitée...') ?>"
                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black resize-none"></textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('demcre_label_location', 'Localisation') ?></label>
                    <input type="text" name="localisation" placeholder="<?= t('demcre_ph_location', 'Ex : Paris, Lyon, Marseille...') ?>"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2"><?= t('demcre_label_budget', 'Budget estimé') ?></label>
                    <input type="text" name="budget" placeholder="<?= t('demcre_ph_budget', 'Ex : 20€, 50€, à discuter...') ?>"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2"><?= t('demcre_label_photo', 'Photo de l\'objet') ?> <span class="text-error">*</span></label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required
                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 file:mr-4 file:py-1 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-base-200 file:cursor-pointer" />
                <p class="text-sm text-base-content/60 mt-2">
                    <?= t('demcre_photo_hint', 'Une photo est obligatoire pour aider les prestataires à comprendre votre demande (JPEG, PNG ou WebP, 5 Mo max).') ?>
                </p>
            </div>

            <div class="bg-base-200 rounded-2xl p-5">
                <h2 class="text-lg font-semibold mb-2"><?= t('demcre_tip_title', 'Bon à savoir') ?></h2>
                <p class="text-base-content/70">
                    <?= t('demcre_tip_text', 'Plus votre demande est précise, plus il sera facile pour un prestataire de vous proposer une solution adaptée.') ?>
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-2">
                <button type="submit"
                    class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                    <?= t('demcre_submit', 'Envoyer ma demande') ?>
                </button>
                <a href="/prestations"
                    class="border border-base-300 px-8 py-3 rounded-xl font-medium hover:bg-base-200 transition text-center">
                    <?= t('demcre_view_services', 'Voir les prestations') ?>
                </a>
            </div>

        </form>
    </div>

</section>