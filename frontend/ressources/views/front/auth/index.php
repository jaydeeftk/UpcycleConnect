<?php $activeTab = $activeTab ?? 'login'; ?>
<section class="py-16 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-10 items-center">

            <div class="hidden lg:block">
                <div class="max-w-xl">
                    <h1 class="text-5xl font-extrabold leading-tight mb-6">
                        <?= t('auth_hero_title', 'Rejoignez une plateforme engagée pour donner une seconde vie aux objets') ?>
                    </h1>
                    <p class="text-lg text-base-content/70 mb-8 leading-relaxed">
                        <?= t('auth_hero_subtitle', 'Connectez-vous pour accéder à vos prestations, publier vos demandes, participer aux événements et rejoindre la communauté UpcycleConnect.') ?>
                    </p>
                    <div class="grid gap-4">
                        <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                            <h3 class="font-semibold text-lg mb-2"><?= t('auth_card_individual_title', 'Pour les particuliers') ?></h3>
                            <p class="text-base-content/70">
                                <?= t('auth_card_individual_text', 'Trouvez des prestataires, déposez vos demandes et participez à des événements responsables.') ?>
                            </p>
                        </div>
                        <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                            <h3 class="font-semibold text-lg mb-2"><?= t('auth_card_provider_title', 'Pour les prestataires') ?></h3>
                            <p class="text-base-content/70">
                                <?= t('auth_card_provider_text', 'Proposez vos prestations, développez votre visibilité et accompagnez les utilisateurs dans leurs projets.') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <div class="bg-base-100 rounded-3xl shadow-xl p-8 md:p-10">

                    <?php if (!empty($error)): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 text-sm">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex bg-base-200 rounded-xl p-1 mb-8 w-fit mx-auto">
                        <button id="tab-login"
                            class="px-6 py-2 rounded-lg text-sm font-medium transition <?= $activeTab === 'register' ? 'text-base-content/60' : 'bg-base-100 shadow' ?>">
                            <?= t('auth_tab_login', 'Connexion') ?>
                        </button>
                        <button id="tab-register"
                            class="px-6 py-2 rounded-lg text-sm font-medium transition <?= $activeTab === 'register' ? 'bg-base-100 shadow' : 'text-base-content/60' ?>">
                            <?= t('auth_tab_register', 'Inscription') ?>
                        </button>
                    </div>

                    <div id="login-form" class="<?= $activeTab === 'register' ? 'hidden' : '' ?>">
                        <h2 class="text-3xl font-bold text-center mb-2"><?= t('auth_tab_login', 'Connexion') ?></h2>
                        <p class="text-center text-base-content/70 mb-8"><?= t('auth_login_subtitle', 'Accédez à votre espace personnel.') ?></p>

                        <form class="space-y-5" method="POST" action="/login">
                            <div>
                                <label class="block text-sm font-medium mb-2"><?= t('auth_label_email', 'Adresse email') ?></label>
                                <input type="email" name="email" placeholder="<?= t('auth_placeholder_email', 'votre@email.com') ?>"
                                    value="<?= htmlspecialchars($email ?? '') ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"><?= t('auth_label_password', 'Mot de passe') ?></label>
                                <input type="password" name="password" placeholder="••••••••"
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>
                            <div class="flex items-center text-sm">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="remember" class="rounded" />
                                    <span><?= t('auth_remember_me', 'Se souvenir de moi') ?></span>
                                </label>
                            </div>
                            <button type="submit"
                                class="w-full bg-black text-white py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                                <?= t('auth_login_submit', 'Se connecter') ?>
                            </button>
                        </form>
                    </div>

                    <div id="register-form" class="<?= $activeTab === 'register' ? '' : 'hidden' ?>">
                        <h2 class="text-3xl font-bold text-center mb-2"><?= t('auth_tab_register', 'Inscription') ?></h2>
                        <p class="text-center text-base-content/70 mb-8"><?= t('auth_register_subtitle', 'Créez votre compte UpcycleConnect.') ?></p>

                        <form class="space-y-5" method="POST" action="/register">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('auth_label_firstname', 'Prénom') ?></label>
                                    <input type="text" name="prenom" required minlength="2" value="<?= htmlspecialchars($prenom ?? '') ?>" placeholder="<?= t('auth_placeholder_firstname', 'Votre prénom') ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('auth_label_lastname', 'Nom') ?></label>
                                    <input type="text" name="nom" required minlength="2" value="<?= htmlspecialchars($nom ?? '') ?>" placeholder="<?= t('auth_placeholder_lastname', 'Votre nom') ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"><?= t('auth_label_email', 'Adresse email') ?></label>
                                <input type="email" name="email" placeholder="<?= t('auth_placeholder_email', 'votre@email.com') ?>"
                                    value="<?= htmlspecialchars($email ?? '') ?>" required
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2"><?= t('auth_label_password', 'Mot de passe') ?></label>
                                <input type="password" name="password" required minlength="8" title="8 caractères minimum, avec au moins une lettre et un chiffre" placeholder="••••••••"
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2"><?= t('auth_role_label', "Je m'inscris en tant que") ?></label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="role" value="particulier" id="role-particulier" class="peer hidden" checked />
                                        <div class="border border-base-300 rounded-2xl p-4 text-center peer-checked:border-black peer-checked:bg-black/5 transition">
                                            <i class="fas fa-user text-2xl mb-2 block text-green-500"></i>
                                            <div class="font-semibold"><?= t('auth_role_individual', 'Particulier') ?></div>
                                            <div class="text-sm text-base-content/70 mt-1"><?= t('auth_role_individual_desc', 'Déposer une demande') ?></div>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="role" value="professionnel" id="role-professionnel" class="peer hidden" />
                                        <div class="border border-base-300 rounded-2xl p-4 text-center peer-checked:border-black peer-checked:bg-black/5 transition">
                                            <i class="fas fa-briefcase text-2xl mb-2 block text-blue-500"></i>
                                            <div class="font-semibold"><?= t('auth_role_pro', 'Professionnel/Artisan') ?></div>
                                            <div class="text-sm text-base-content/70 mt-1"><?= t('auth_role_pro_desc', 'Proposer des services') ?></div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="champs-professionnel" class="hidden space-y-5">
                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('auth_label_siret', "SIRET de l'entreprise") ?></label>
                                    <div class="flex gap-2">
                                        <input type="text" name="siret" id="siret-input" inputmode="numeric" maxlength="17" placeholder="<?= t('auth_siret_ph', '14 chiffres') ?>"
                                            class="flex-1 px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                                        <button type="button" id="siret-verify-btn"
                                            class="px-4 py-3 rounded-xl bg-black text-white font-medium hover:bg-neutral-800 transition whitespace-nowrap"><?= t('auth_siret_verify', 'Vérifier') ?></button>
                                    </div>
                                    <p id="siret-result" class="text-sm mt-2"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('auth_label_company', "Nom de l'entreprise") ?></label>
                                    <input type="text" name="nom_entreprise" id="nom-entreprise-input" readonly placeholder="<?= t('auth_company_autofill', 'Rempli après vérification du SIRET') ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-200 focus:outline-none focus:ring-2 focus:ring-black" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2"><?= t('auth_label_type', 'Type') ?></label>
                                    <select name="type" class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black">
                                        <option value="artisan"><?= t('auth_type_artisan', 'Artisan') ?></option>
                                        <option value="professionnel"><?= t('auth_type_pro', 'Professionnel') ?></option>
                                        <option value="entreprise"><?= t('auth_type_company', 'Entreprise') ?></option>
                                    </select>
                                </div>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer text-sm">
                                <input type="checkbox" name="cgu" class="rounded" />
                                <span><?= t('auth_accept_terms', "J'accepte les conditions d'utilisation") ?></span>
                            </label>
                            <button type="submit"
                                class="w-full bg-black text-white py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                                <?= t('auth_register_submit', 'Créer mon compte') ?>
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<script>
    const loginTab = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    loginTab.addEventListener('click', () => {
        loginTab.classList.add('bg-base-100', 'shadow');
        loginTab.classList.remove('text-base-content/60');
        registerTab.classList.remove('bg-base-100', 'shadow');
        registerTab.classList.add('text-base-content/60');
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    });

    registerTab.addEventListener('click', () => {
        registerTab.classList.add('bg-base-100', 'shadow');
        registerTab.classList.remove('text-base-content/60');
        loginTab.classList.remove('bg-base-100', 'shadow');
        loginTab.classList.add('text-base-content/60');
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    });

    const champsPro = document.getElementById('champs-professionnel');
    const siretInput = document.getElementById('siret-input');
    const nomEntrepriseInput = document.getElementById('nom-entreprise-input');

    function setProRequired(on) {
        if (siretInput) siretInput.required = on;
        if (nomEntrepriseInput) nomEntrepriseInput.required = on;
    }

    document.getElementById('role-particulier').addEventListener('change', function() {
        champsPro.classList.add('hidden');
        setProRequired(false);
    });

    document.getElementById('role-professionnel').addEventListener('change', function() {
        champsPro.classList.remove('hidden');
        setProRequired(true);
    });

    // Vérification du SIRET via l'API publique (proxy frontend -> API Go)
    const siretBtn = document.getElementById('siret-verify-btn');
    const siretResult = document.getElementById('siret-result');
    if (siretBtn) {
        siretBtn.addEventListener('click', function() {
            const raw = (siretInput.value || '').replace(/\D/g, '');
            if (raw.length !== 14) {
                siretResult.textContent = 'Le SIRET doit comporter 14 chiffres.';
                siretResult.className = 'text-sm mt-2 text-red-600';
                return;
            }
            siretResult.textContent = 'Vérification…';
            siretResult.className = 'text-sm mt-2 text-base-content/60';
            fetch('/siret/verify/' + raw)
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.valid) {
                        nomEntrepriseInput.value = d.nom_entreprise || '';
                        siretResult.textContent = '✓ ' + (d.nom_entreprise || 'Entreprise vérifiée');
                        siretResult.className = 'text-sm mt-2 text-emerald-600 font-medium';
                    } else {
                        nomEntrepriseInput.value = '';
                        siretResult.textContent = '✗ ' + (d.message || 'SIRET invalide');
                        siretResult.className = 'text-sm mt-2 text-red-600';
                    }
                })
                .catch(function() {
                    siretResult.textContent = 'Erreur de vérification, réessayez.';
                    siretResult.className = 'text-sm mt-2 text-red-600';
                });
        });
    }
</script>