<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-clipboard-list text-blue-600"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 uppercase tracking-wide">Mon espace</span>
        </div>
        <h1 class="text-3xl font-bold">Mes demandes</h1>
        <p class="text-base-content/60 mt-2">Retrouvez toutes vos annonces et demandes de dépôt en conteneur.</p>
    </div>
    <?php if (!isset($_SESSION['user'])): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-8 text-center">
            <p class="text-base-content/70 mb-4">Vous devez être connecté pour voir vos demandes.</p>
            <a href="/login"
                class="inline-block bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                Se connecter
            </a>
        </div>
    <?php else: ?>
        <?php
        $type = $_GET['type'] ?? 'tous';
        $annonces = $annonces ?? [];
        $conteneurs = $conteneurs ?? [];

        $annoncesFiltered = $annonces;
        if (isset($_GET['don'])) {
            $annoncesFiltered = array_filter($annonces, fn($a) => ($a['type_annonce'] ?? '') === 'don');
        }
        if (isset($_GET['vente'])) {
            $annoncesFiltered = array_filter($annonces, fn($a) => ($a['type_annonce'] ?? '') === 'vente');
        }

        $conteneursFiltred = $conteneurs;
        if (isset($_GET['don'])) {
            $conteneursFiltred = array_filter($conteneurs, fn($c) => ($c['destination'] ?? '') === 'don');
        }
        if (isset($_GET['vente'])) {
            $conteneursFiltred = array_filter($conteneurs, fn($c) => ($c['destination'] ?? '') === 'vente');
        }

        $showAnnonces = $type === 'tous' || $type === 'annonces';
        $showConteneurs = $type === 'tous' || $type === 'conteneurs';
        ?>

        <div class="flex flex-wrap gap-3 mb-8">
            <a href="?type=tous" class="btn btn-sm <?= $type === 'tous' && !isset($_GET['don']) && !isset($_GET['vente']) ? 'btn-neutral' : 'btn-ghost' ?>">
                Tout voir
            </a>
            <a href="?type=annonces<?= isset($_GET['don']) ? '&don=1' : '' ?><?= isset($_GET['vente']) ? '&vente=1' : '' ?>" class="btn btn-sm <?= $type === 'annonces' ? 'btn-neutral' : 'btn-ghost' ?>">
                <i class="fas fa-bullhorn mr-2"></i> Annonces
            </a>
            <a href="?type=conteneurs<?= isset($_GET['don']) ? '&don=1' : '' ?><?= isset($_GET['vente']) ? '&vente=1' : '' ?>" class="btn btn-sm <?= $type === 'conteneurs' ? 'btn-neutral' : 'btn-ghost' ?>">
                <i class="fas fa-box-open mr-2"></i> Dépôts conteneurs
            </a>
            <div class="divider divider-horizontal"></div>
            <a href="?type=<?= $type ?>&don=1" class="btn btn-sm <?= isset($_GET['don']) ? 'btn-neutral' : 'btn-ghost' ?>">
                <i class="fas fa-heart mr-2 text-green-500"></i> Dons
            </a>
            <a href="?type=<?= $type ?>&vente=1" class="btn btn-sm <?= isset($_GET['vente']) ? 'btn-neutral' : 'btn-ghost' ?>">
                <i class="fas fa-tag mr-2 text-blue-500"></i> Ventes
            </a>
        </div>

        <?php if ($showAnnonces): ?>
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-bullhorn text-green-500"></i> Mes annonces
                    <span class="badge badge-ghost badge-sm"><?= count($annoncesFiltered) ?></span>
                </h2>
                <?php if (empty($annoncesFiltered)): ?>
                    <div class="bg-base-100 rounded-2xl border border-base-300 p-6 text-center">
                        <p class="text-base-content/60 mb-3">Vous n'avez pas encore d'annonces.</p>
                        <a href="/annonces/create" class="btn btn-neutral btn-sm">
                            Déposer une annonce
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($annoncesFiltered as $annonce): ?>
                            <?php
                            $statutColor = match($annonce['statut'] ?? 'en_attente') {
                                'validee' => 'badge-success',
                                'rejetee' => 'badge-error',
                                default   => 'badge-warning',
                            };
                            $statutLabel = match($annonce['statut'] ?? 'en_attente') {
                                'validee' => 'Validée',
                                'rejetee' => 'Rejetée',
                                default   => 'En attente',
                            };
                            ?>
                            <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <?php if (($annonce['type_annonce'] ?? '') === 'vente'): ?>
                                                <span class="badge badge-ghost badge-sm gap-1">
                                                    <i class="fas fa-tag text-blue-500"></i> Vente
                                                </span>
                                                <span class="font-semibold text-blue-500"><?= $annonce['prix'] ?? 0 ?>€</span>
                                            <?php else: ?>
                                                <span class="badge badge-ghost badge-sm gap-1">
                                                    <i class="fas fa-heart text-green-500"></i> Don
                                                </span>
                                            <?php endif; ?>
                                            <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($annonce['categorie'] ?? '') ?></span>
                                        </div>
                                        <h3 class="text-lg font-semibold mb-1"><?= htmlspecialchars($annonce['titre'] ?? '') ?></h3>
                                        <p class="text-sm text-base-content/60 mb-3"><?= htmlspecialchars($annonce['contenu'] ?? '') ?></p>
                                        <div class="flex gap-4 text-xs text-base-content/50">
                                            <span><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($annonce['ville'] ?? '') ?></span>
                                            <span><i class="fas fa-clock mr-1"></i><?= htmlspecialchars($annonce['date'] ?? '') ?></span>
                                            <span><i class="fas fa-box mr-1"></i>État : <?= htmlspecialchars($annonce['etat'] ?? '') ?></span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="badge <?= $statutColor ?> flex-shrink-0"><?= $statutLabel ?></span>
                                        <?php if (($annonce['statut'] ?? '') === 'en_attente'): ?>
                                            <form method="POST" action="/annonces/<?= $annonce['id'] ?>/annuler">
                                                <button type="submit" class="btn btn-ghost btn-xs text-red-500 hover:bg-red-50">
                                                    <i class="fas fa-times mr-1"></i> Annuler
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($showConteneurs): ?>
            <div>
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-box-open text-blue-500"></i> Mes dépôts en conteneur
                    <span class="badge badge-ghost badge-sm"><?= count($conteneursFiltred) ?></span>
                </h2>
                <?php if (empty($conteneursFiltred)): ?>
                    <div class="bg-base-100 rounded-2xl border border-base-300 p-6 text-center">
                        <p class="text-base-content/60 mb-3">Vous n'avez pas encore de dépôts en conteneur.</p>
                        <a href="/conteneurs/create" class="btn btn-neutral btn-sm">
                            Déposer un objet
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($conteneursFiltred as $conteneur): ?>
                            <?php
                            $statutColor = match($conteneur['statut'] ?? 'en_attente') {
                                'validee'    => 'badge-success',
                                'refusee'    => 'badge-error',
                                default      => 'badge-warning',
                            };
                            $statutLabel = match($conteneur['statut'] ?? 'en_attente') {
                                'validee'    => 'Validé',
                                'refusee'    => 'Refusé',
                                default      => 'En attente',
                            };
                            ?>
                            <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($conteneur['type_objet'] ?? '') ?></span>
                                            <?php if (($conteneur['destination'] ?? '') === 'vente'): ?>
                                                <span class="badge badge-ghost badge-sm gap-1">
                                                    <i class="fas fa-tag text-blue-500"></i> Vente
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-ghost badge-sm gap-1">
                                                    <i class="fas fa-heart text-green-500"></i> Don
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-base-content/60 mb-3"><?= htmlspecialchars($conteneur['description'] ?? '') ?></p>
                                        <div class="flex gap-4 text-xs text-base-content/50">
                                            <span><i class="fas fa-calendar mr-1"></i><?= htmlspecialchars($conteneur['date'] ?? '') ?></span>
                                        </div>
                                        <?php if (($conteneur['statut'] ?? '') === 'validee' && !empty($conteneur['code_acces'])): ?>
                                            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                                                <p class="text-xs font-semibold text-green-700 mb-1"><i class="fas fa-key mr-1"></i> Votre code d'accès au conteneur :</p>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <span id="code-front-<?= $conteneur['id'] ?>" class="text-2xl font-bold text-green-600 tracking-widest blur-sm select-none"><?= htmlspecialchars($conteneur['code_acces']) ?></span>
                                                    <button onclick="revealFrontCode(<?= $conteneur['id'] ?>)" class="text-xs bg-white border border-green-300 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-100 transition font-medium">
                                                        <i class="fas fa-eye mr-1"></i>Révéler
                                                    </button>
                                                </div>
                                                <p class="text-xs text-green-600 mt-1">Entrez votre mot de passe pour afficher le code de dépôt.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge <?= $statutColor ?> flex-shrink-0"><?= $statutLabel ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- Modal révélation code dépôt -->
<div id="modal-reveal-front" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-base-100 rounded-2xl shadow-xl w-full max-w-sm p-6 border border-base-300">
        <h3 class="text-lg font-bold mb-2"><i class="fas fa-lock mr-2 text-amber-500"></i>Confirmer votre identité</h3>
        <p class="text-sm text-base-content/60 mb-4">Entrez votre mot de passe pour afficher le code de dépôt.</p>
        <input type="password" id="reveal-front-pwd" placeholder="Mot de passe"
            class="input input-bordered w-full mb-3">
        <p id="reveal-front-error" class="text-xs text-error mb-3 hidden">Mot de passe incorrect.</p>
        <div class="flex gap-3 justify-end">
            <button onclick="document.getElementById('modal-reveal-front').classList.add('hidden')"
                class="btn btn-ghost btn-sm">Annuler</button>
            <button onclick="confirmFrontReveal()"
                class="btn btn-neutral btn-sm">Révéler</button>
        </div>
    </div>
</div>

<script>
let _revealFrontId = null;
function revealFrontCode(id) {
    _revealFrontId = id;
    document.getElementById('reveal-front-pwd').value = '';
    document.getElementById('reveal-front-error').classList.add('hidden');
    document.getElementById('modal-reveal-front').classList.remove('hidden');
    setTimeout(() => document.getElementById('reveal-front-pwd').focus(), 100);
}
function confirmFrontReveal() {
    const pwd = document.getElementById('reveal-front-pwd').value;
    if (!pwd) return;
    document.getElementById('reveal-front-error').classList.add('hidden');
    fetch('/api/auth/verify', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer <?= $_SESSION['user']['token'] ?? '' ?>'},
        body: JSON.stringify({password: pwd})
    }).then(r => r.json()).then(data => {
        if (data.data && data.data.verified) {
            const el = document.getElementById('code-front-' + _revealFrontId);
            if (el) { el.classList.remove('blur-sm'); el.style.userSelect = 'text'; }
            document.getElementById('modal-reveal-front').classList.add('hidden');
        } else {
            document.getElementById('reveal-front-error').classList.remove('hidden');
        }
    }).catch(() => {
        document.getElementById('reveal-front-error').classList.remove('hidden');
    });
}
document.getElementById('reveal-front-pwd').addEventListener('keydown', e => { if (e.key === 'Enter') confirmFrontReveal(); });
</script>