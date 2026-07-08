<?php $__pro = !empty($isPro); ?>
<?php if ($__pro): ?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('msgidx_title', 'Messagerie') ?> - UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>(function(){ if ((localStorage.getItem('theme') || 'light') === 'dark') { document.documentElement.classList.add('dark'); document.documentElement.setAttribute('data-theme','dark'); } })();</script>
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100">
<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800"><?= t('msgidx_title', 'Messagerie') ?></h2>
            <p class="text-gray-600 text-sm"><?= t('msgidx_subtitle', 'Vos échanges à propos des annonces.') ?></p>
        </header>
        <main class="flex-1 overflow-y-auto bg-gray-100">
<?php endif; ?>

<section class="max-w-3xl mx-auto px-4 py-10">
    <?php if (!$__pro): ?>
    <h1 class="text-2xl font-bold mb-2"><?= t('msgidx_title', 'Messagerie') ?></h1>
    <p class="text-base-content/60 mb-8"><?= t('msgidx_subtitle', 'Vos échanges à propos des annonces.') ?></p>
    <?php endif; ?>

    <?php if (empty($conversations)): ?>
        <div class="text-center py-16 text-base-content/40">
            <i class="fas fa-comments text-4xl mb-3 block"></i>
            <p><?= t('msgidx_empty', "Aucune conversation pour l'instant.") ?></p>
            <a href="/annonces" class="link link-primary mt-2 inline-block"><?= t('msgidx_browse', 'Parcourir les annonces') ?></a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($conversations as $conv): ?>
                <div class="flex items-center gap-2">
                    <a href="/messagerie/<?= (int)($conv['id'] ?? 0) ?>"
                       class="flex-1 min-w-0 flex items-center justify-between bg-base-100 rounded-2xl shadow-sm p-5 hover:shadow-md transition">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold truncate"><?= htmlspecialchars($conv['autre_nom'] ?? '') ?></span>
                                <?php if (!empty($conv['non_lus'])): ?>
                                    <span class="badge badge-primary badge-sm"><?= (int)$conv['non_lus'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm text-base-content/50 truncate">
                                <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($conv['titre_annonce'] ?? '') ?>
                            </div>
                            <?php if (!empty($conv['dernier_message'])): ?>
                                <div class="text-sm text-base-content/70 truncate mt-1"><?= htmlspecialchars($conv['dernier_message']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($conv['date_dernier_message'])): ?>
                            <span class="text-xs text-base-content/40 flex-shrink-0 ml-4"><?= htmlspecialchars(formatDate($conv['date_dernier_message'], true)) ?></span>
                        <?php endif; ?>
                    </a>
                    <form method="POST" action="/messagerie/<?= (int)($conv['id'] ?? 0) ?>/supprimer"
                          onsubmit="return confirm('<?= t('msgidx_confirm_delete', 'Supprimer cette conversation ? Elle restera visible pour votre interlocuteur.') ?>')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-ghost btn-sm text-base-content/40 hover:text-error" title="<?= t('msgidx_delete', 'Supprimer') ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if ($__pro): ?>
        </main>
    </div>
</div>
</body>
</html>
<?php endif; ?>
