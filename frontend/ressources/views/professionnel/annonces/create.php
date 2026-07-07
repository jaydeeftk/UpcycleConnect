<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_ann_create_title', 'Nouvelle annonce') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4">
            <a href="/professionnel/annonces" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_ann_create_title', 'Nouvelle annonce') ?></h2>
                <p class="text-gray-600 text-sm"><?= t('pro_ann_create_subtitle', 'Remplissez le formulaire pour déposer une annonce. Elle sera vérifiée avant publication.') ?></p>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-2xl mx-auto">

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6 space-y-6">
                    <form method="POST" action="/professionnel/annonces/store">
                        <?= csrf_field() ?>

                        <!-- Informations objet -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                                <?= t('anncre_section_item', 'Informations sur l\'objet') ?>
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('anncre_label_title', 'Titre de l\'annonce') ?> <span class="text-red-500">*</span></label>
                                    <input type="text" name="titre" required
                                           value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                                           placeholder="<?= t('anncre_ph_title', 'Ex : Chaise en bois vintage, Lampe de bureau...') ?>"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('anncre_label_category', 'Catégorie') ?> <span class="text-red-500">*</span></label>
                                    <select name="categorie" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="" disabled selected><?= t('anncre_cat_placeholder', 'Sélectionnez une catégorie') ?></option>
                                        <?php foreach ([
                                            'mobilier'     => t('anncre_cat_furniture',  'Mobilier'),
                                            'electromenager' => t('anncre_cat_appliances', 'Électroménager'),
                                            'vetements'    => t('anncre_cat_clothing',   'Vêtements & Textiles'),
                                            'electronique' => t('anncre_cat_electronics','Électronique'),
                                            'livres'       => t('anncre_cat_books',      'Livres & Médias'),
                                            'jouets'       => t('anncre_cat_toys',       'Jouets'),
                                            'materiaux'    => t('anncre_cat_materials',  'Matériaux de construction'),
                                            'autre'        => t('anncre_cat_other',      'Autre'),
                                        ] as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= ($_POST['categorie'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('anncre_label_description', 'Description') ?> <span class="text-red-500">*</span></label>
                                    <textarea name="description" rows="4" required
                                              placeholder="<?= t('anncre_ph_description', 'Décrivez l\'objet : matière, dimensions, historique, défauts éventuels...') ?>"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"
                                    ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('anncre_label_condition', 'État de l\'objet') ?> <span class="text-red-500">*</span></label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                        <?php foreach ([
                                            ['neuf',  t('anncre_cond_new',     'Neuf'),    'fa-star',              'text-green-500'],
                                            ['bon',   t('anncre_cond_good',    'Bon état'),'fa-thumbs-up',         'text-blue-500'],
                                            ['usage', t('anncre_cond_used',    'Usagé'),   'fa-minus-circle',      'text-yellow-500'],
                                            ['abime', t('anncre_cond_damaged', 'Abîmé'),   'fa-exclamation-circle','text-red-500'],
                                        ] as [$val, $label, $icon, $color]): ?>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="etat" value="<?= $val ?>" class="hidden peer" required
                                                       <?= ($_POST['etat'] ?? '') === $val ? 'checked' : '' ?>>
                                                <div class="peer-checked:border-green-500 peer-checked:bg-green-50 border-2 border-gray-200 rounded-xl p-3 text-center transition hover:border-green-300">
                                                    <i class="fas <?= $icon ?> <?= $color ?> text-lg mb-1 block"></i>
                                                    <span class="text-xs font-medium text-gray-700"><?= $label ?></span>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Type de mise à disposition -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                                <?= t('anncre_section_type', 'Type de mise à disposition') ?>
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="type_annonce" value="don" class="hidden peer" checked>
                                    <div class="peer-checked:border-green-500 peer-checked:bg-green-50 border-2 border-gray-200 rounded-xl p-4 transition hover:border-green-300">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-heart text-green-500"></i>
                                            <span class="font-semibold text-sm text-gray-800"><?= t('anncre_type_gift', 'Don gratuit') ?></span>
                                        </div>
                                        <p class="text-xs text-gray-500"><?= t('anncre_type_gift_desc', 'Vous offrez cet objet gratuitement.') ?></p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="type_annonce" value="vente" class="hidden peer">
                                    <div class="peer-checked:border-blue-500 peer-checked:bg-blue-50 border-2 border-gray-200 rounded-xl p-4 transition hover:border-blue-300">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-tag text-blue-500"></i>
                                            <span class="font-semibold text-sm text-gray-800"><?= t('anncre_type_sale', 'Vente') ?></span>
                                        </div>
                                        <p class="text-xs text-gray-500"><?= t('anncre_type_sale_desc', 'Vous souhaitez vendre cet objet.') ?></p>
                                    </div>
                                </label>
                            </div>
                            <div id="prix-container" class="mt-3 hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('anncre_label_price', 'Prix de vente') ?> (€) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">€</span>
                                    <input type="number" name="prix" min="0" step="0.01" placeholder="0.00"
                                           class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <p class="text-xs text-gray-400 mt-1"><?= t('anncre_commission_note', 'Une commission de 5 à 10% sera prélevée sur la vente.') ?></p>
                            </div>
                        </div>

                        <!-- Localisation -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                                <?= t('anncre_section_location', 'Localisation') ?>
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('anncre_label_city', 'Ville') ?> <span class="text-red-500">*</span></label>
                                    <input type="text" name="ville" required
                                           value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>"
                                           placeholder="<?= t('anncre_ph_city', 'Ex : Paris') ?>"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('anncre_label_zip', 'Code postal') ?> <span class="text-red-500">*</span></label>
                                    <input type="text" name="code_postal" required maxlength="5"
                                           value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>"
                                           placeholder="<?= t('anncre_ph_zip', 'Ex : 75010') ?>"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="flex-1 bg-green-600 text-white py-2.5 rounded-lg font-semibold hover:bg-green-700 transition">
                                <i class="fas fa-paper-plane mr-2"></i><?= t('anncre_submit', 'Soumettre l\'annonce') ?>
                            </button>
                            <a href="/professionnel/annonces" class="flex-1 text-center border border-gray-300 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <?= t('anncre_cancel', 'Annuler') ?>
                            </a>
                        </div>

                        <p class="text-xs text-gray-400 text-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            <?= t('anncre_review_note', 'Votre annonce sera examinée par notre équipe avant d\'être publiée sur la plateforme.') ?>
                        </p>

                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="type_annonce"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var container = document.getElementById('prix-container');
            if (this.value === 'vente') {
                container.classList.remove('hidden');
                container.querySelector('input').required = true;
            } else {
                container.classList.add('hidden');
                container.querySelector('input').required = false;
            }
        });
    });
</script>

</body>
</html>
