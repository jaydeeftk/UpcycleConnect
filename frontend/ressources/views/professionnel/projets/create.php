<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_new_project_page_title', 'Nouveau projet') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4">
            <a href="/professionnel" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_new_project_heading', 'Nouveau projet upcycling') ?></h2>
                <p class="text-gray-600 text-sm"><?= t('pro_new_project_subtitle', 'Décrivez votre projet de réemploi.') ?></p>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow p-8">
                    <form method="POST" action="/professionnel/projets/store" class="space-y-5">
                    <?= csrf_field() ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_form_project_title', 'Titre du projet *') ?></label>
                            <input type="text" name="titre" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="<?= t('pro_form_project_title_ph', 'Ex: Upcycling de palettes en mobilier') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_form_description', 'Description') ?></label>
                            <textarea name="description" rows="4"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="<?= t('pro_form_description_ph', 'Décrivez votre projet...') ?>"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_form_start_date', 'Date de début') ?></label>
                                <input type="date" name="date_debut" min="<?= dateProgrammationMin(false) ?>" max="<?= dateProgrammationMax(false) ?>"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_form_status', 'Statut') ?></label>
                                <select name="statut" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="en_cours"><?= t('pro_status_in_progress', 'En cours') ?></option>
                                    <option value="pause"><?= t('pro_status_paused', 'En pause') ?></option>
                                    <option value="termine"><?= t('pro_status_done', 'Terminé') ?></option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition font-medium">
                            <i class="fas fa-plus mr-2"></i><?= t('pro_btn_create_project', 'Créer le projet') ?>
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
