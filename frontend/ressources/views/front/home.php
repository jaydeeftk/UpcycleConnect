<section class="relative">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1600&q=80" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-6 lg:px-10 py-24 lg:py-36">
        <div class="max-w-3xl text-white">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
                Donnez une seconde vie à vos objets
            </h1>

            <p class="mt-6 text-lg md:text-xl text-white/80 leading-relaxed max-w-2xl">
                UpCycleConnect met en relation particuliers et prestataires pour réparer,
                transformer ou recycler les objets du quotidien de façon simple, utile et responsable.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a href="/prestations" class="bg-black text-white px-7 py-4 rounded-xl font-semibold hover:bg-neutral-800 transition text-center">
                    Découvrir les prestations
                </a>

                <a href="/devenir-prestataire" class="bg-white text-black px-7 py-4 rounded-xl font-semibold hover:bg-neutral-200 transition text-center">
                    Devenir prestataire
                </a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16 lg:py-20">
    <h2 class="text-3xl md:text-4xl font-bold mb-10">
        Comment fonctionne UpCycleConnect ?
    </h2>

    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
            <img src="https://images.unsplash.com/photo-1604186838309-c6715f0d3e6d?auto=format&fit=crop&w=900&q=80" class="w-full h-64 object-cover">

            <div class="p-6">
                <h3 class="text-2xl font-semibold mb-3">Réparer</h3>
                <p class="text-base-content/70">
                    Trouvez un professionnel qualifié pour remettre en état vos objets cassés au lieu de les jeter.
                </p>
            </div>
        </div>

        <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
            <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80" class="w-full h-64 object-cover">

            <div class="p-6">
                <h3 class="text-2xl font-semibold mb-3">Transformer</h3>
                <p class="text-base-content/70">
                    Donnez un nouveau style ou une nouvelle utilité à vos objets grâce à des prestations sur mesure.
                </p>
            </div>
        </div>

        <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
            <img src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=900&q=80" class="w-full h-64 object-cover">

            <div class="p-6">
                <h3 class="text-2xl font-semibold mb-3">Recycler</h3>
                <p class="text-base-content/70">
                    Orientez vos objets vers les bonnes solutions de réemploi et de recyclage pour limiter les déchets.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="bg-base-100 py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Notre impact</h2>

        <div class="grid md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-5xl font-bold text-primary"><?= number_format($stats['objets_sauves'] ?? 0) ?></div>
                <div class="text-base-content/70 mt-2">Objets sauvés</div>
            </div>

            <div>
                <div class="text-5xl font-bold text-primary"><?= number_format($stats['utilisateurs'] ?? 0) ?></div>
                <div class="text-base-content/70 mt-2">Utilisateurs</div>
            </div>

            <div>
                <div class="text-5xl font-bold text-primary"><?= number_format($stats['projets_realises'] ?? 0) ?></div>
                <div class="text-base-content/70 mt-2">Projets réalisés</div>
            </div>

            <div>
                <div class="text-5xl font-bold text-primary"><?= $stats['co2_economise'] ?? 0 ?> t</div>
                <div class="text-base-content/70 mt-2">CO₂ économisées</div>
            </div>
        </div>
    </div>
</section>

<section class="bg-base-100 border-t border-base-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 lg:py-20">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold leading-tight mb-6">
                    Une plateforme simple pour prolonger la vie de vos objets
                </h2>

                <div class="space-y-6 text-base-content/80">
                    <div>
                        <h3 class="font-semibold text-lg">Trouvez un prestataire près de chez vous</h3>
                        <p class="mt-2">
                            Parcourez les prestations proposées par des professionnels pour réparer,
                            transformer ou valoriser vos objets facilement.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg">Déposez une demande rapidement</h3>
                        <p class="mt-2">
                            Décrivez votre besoin, votre objet et votre objectif. Les prestataires
                            peuvent ensuite vous répondre directement.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg">Participez à des événements responsables</h3>
                        <p class="mt-2">
                            Découvrez des ateliers, des rencontres et des initiatives locales autour
                            de la réparation et du recyclage.
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="/prestations" class="bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                        Voir les prestations
                    </a>

                    <a href="/evenements" class="bg-base-200 border border-base-300 px-6 py-3 rounded-xl font-medium hover:bg-base-300 transition">
                        Voir les événements
                    </a>
                </div>
            </div>

            <div class="bg-base-100 rounded-3xl shadow-sm overflow-hidden">
                <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=80" class="w-full h-full object-cover min-h-[420px]">
            </div>
        </div>
    </div>
</section>