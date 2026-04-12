<section class="py-16 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-10 items-center">

            <div class="hidden lg:block">
                <div class="max-w-xl">
                    <h1 class="text-5xl font-extrabold leading-tight mb-6">
                        Rejoignez une plateforme engagée pour donner une seconde vie aux objets
                    </h1>
                    <p class="text-lg text-base-content/70 mb-8 leading-relaxed">
                        Connectez-vous pour accéder à vos prestations, publier vos demandes,
                        participer aux événements et rejoindre la communauté UpcycleConnect.
                    </p>
                    <div class="grid gap-4">
                        <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                            <h3 class="font-semibold text-lg mb-2">Pour les particuliers</h3>
                            <p class="text-base-content/70">
                                Trouvez des prestataires, déposez vos demandes et participez à des événements responsables.
                            </p>
                        </div>
                        <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                            <h3 class="font-semibold text-lg mb-2">Pour les prestataires</h3>
                            <p class="text-base-content/70">
                                Proposez vos prestations, développez votre visibilité et accompagnez les utilisateurs dans leurs projets.
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

                    <div class="flex bg-base-200 rounded-xl p-1 mb-8 w-fit mx-auto">
                        <button id="tab-login"
                            class="px-6 py-2 rounded-lg text-sm font-medium bg-base-100 shadow transition">
                            Connexion
                        </button>
                        <button id="tab-register"
                            class="px-6 py-2 rounded-lg text-sm font-medium text-base-content/60 transition">
                            Inscription
                        </button>
                    </div>

                    <div id="login-form">
                        <h2 class="text-3xl font-bold text-center mb-2">Connexion</h2>
                        <p class="text-center text-base-content/70 mb-8">Accédez à votre espace personnel.</p>

                        <form class="space-y-5" method="POST" action="/login">
                            <div>
                                <label class="block text-sm font-medium mb-2">Adresse email</label>
                                <input type="email" name="email" placeholder="votre@email.com"
                                    value="<?= htmlspecialchars($email ?? '') ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mot de passe</label>
                                <input type="password" name="password" placeholder="••••••••"
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="remember" class="rounded" />
                                    <span>Se souvenir de moi</span>
                                </label>
                                <a href="#" class="hover:underline">Mot de passe oublié ?</a>
                            </div>
                            <button type="submit"
                                class="w-full bg-black text-white py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                                Se connecter
                            </button>
                        </form>
                    </div>

                    <div id="register-form" class="hidden">
                        <h2 class="text-3xl font-bold text-center mb-2">Inscription</h2>
                        <p class="text-center text-base-content/70 mb-8">Créez votre compte UpcycleConnect.</p>

                        <form class="space-y-5" method="POST" action="/register">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nom complet</label>
                                <input type="text" name="nom" placeholder="Votre nom complet"
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Adresse email</label>
                                <input type="email" name="email" placeholder="votre@email.com"
                                    value="<?= htmlspecialchars($email ?? '') ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mot de passe</label>
                                <input type="password" name="password" placeholder="••••••••"
                                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Je m'inscris en tant que</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="role" value="particulier" id="role-particulier" class="peer hidden" checked />
                                        <div class="border border-base-300 rounded-2xl p-4 text-center peer-checked:border-black peer-checked:bg-black/5 transition">
                                            <i class="fas fa-user text-2xl mb-2 block text-green-500"></i>
                                            <div class="font-semibold">Particulier</div>
                                            <div class="text-sm text-base-content/70 mt-1">Déposer une demande</div>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="role" value="professionnel" id="role-professionnel" class="peer hidden" />
                                        <div class="border border-base-300 rounded-2xl p-4 text-center peer-checked:border-black peer-checked:bg-black/5 transition">
                                            <i class="fas fa-briefcase text-2xl mb-2 block text-blue-500"></i>
                                            <div class="font-semibold">Professionnel/Artisan</div>
                                            <div class="text-sm text-base-content/70 mt-1">Proposer des services</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="champs-professionnel" class="hidden space-y-5">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Nom de l'entreprise</label>
                                    <input type="text" name="nom_entreprise" placeholder="Votre entreprise"
                                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Type</label>
                                    <select name="type" class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black">
                                        <option value="artisan">Artisan</option>
                                        <option value="professionnel">Professionnel</option>
                                        <option value="entreprise">Entreprise</option>
                                    </select>
                                </div>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer text-sm">
                                <input type="checkbox" name="cgu" class="rounded" />
                                <span>J'accepte les conditions d'utilisation</span>
                            </label>
                            <button type="submit"
                                class="w-full bg-black text-white py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                                Créer mon compte
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

    document.getElementById('role-particulier').addEventListener('change', function() {
        document.getElementById('champs-professionnel').classList.add('hidden');
    });

    document.getElementById('role-professionnel').addEventListener('change', function() {
        document.getElementById('champs-professionnel').classList.remove('hidden');
    });
</script>