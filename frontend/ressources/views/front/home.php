<style>
.fade-up {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.55s ease, transform 0.55s ease;
}
.fade-up.visible {
    opacity: 1;
    transform: translateY(0);
}
.counter-val { transition: all 0.1s; }
</style>

<section class="relative min-h-[600px] flex items-center">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1600&q=80"
             class="w-full h-full object-cover" alt="Hero UpcycleConnect">
        <div class="absolute inset-0 bg-black/50"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-6 lg:px-10 py-24 lg:py-36">
        <div class="max-w-3xl text-white">
            <span class="inline-block bg-white/20 backdrop-blur-sm text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-6 border border-white/30">
                🌱 Plateforme d'économie circulaire
            </span>
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
                Donnez une seconde vie à vos objets
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 leading-relaxed max-w-2xl">
                UpCycleConnect met en relation particuliers et prestataires pour réparer,
                transformer ou recycler les objets du quotidien de façon simple, utile et responsable.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a href="/catalogue/services"
                   class="bg-white text-black px-7 py-4 rounded-xl font-semibold hover:bg-gray-100 transition text-center">
                    Découvrir les services
                </a>
                <a href="/catalogue/evenements"
                   style="background:rgba(255,255,255,0.15);backdrop-filter:blur(4px);color:white;border:1.5px solid rgba(255,255,255,0.4);"
                   class="px-7 py-4 rounded-xl font-semibold hover:bg-white/25 transition text-center inline-block">
                    Voir les événements
                </a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16 lg:py-20">
    <h2 class="text-3xl md:text-4xl font-bold mb-10 fade-up">
        Comment fonctionne UpCycleConnect ?
    </h2>
    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group fade-up" style="transition-delay:0.05s">
            <div class="overflow-hidden h-64">
                <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=900&q=80"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                     alt="Réparer">
            </div>
            <div class="p-6">
                <h3 class="text-2xl font-semibold mb-3">Réparer</h3>
                <p class="text-base-content/70">
                    Trouvez un professionnel qualifié pour remettre en état vos objets cassés au lieu de les jeter.
                </p>
            </div>
        </div>
        <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group fade-up" style="transition-delay:0.12s">
            <div class="overflow-hidden h-64">
                <img src="https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=900&q=80"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                     alt="Transformer">
            </div>
            <div class="p-6">
                <h3 class="text-2xl font-semibold mb-3">Transformer</h3>
                <p class="text-base-content/70">
                    Donnez un nouveau style ou une nouvelle utilité à vos objets grâce à des prestations sur mesure.
                </p>
            </div>
        </div>
        <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group fade-up" style="transition-delay:0.19s">
            <div class="overflow-hidden h-64">
                <img src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=900&q=80"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                     alt="Recycler">
            </div>
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
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 fade-up">Notre impact</h2>
        <div class="grid md:grid-cols-4 gap-8 text-center">
            <div class="fade-up" style="transition-delay:0.05s">
                <div class="text-5xl font-bold text-primary counter-val" data-target="<?= $stats['objets_sauves'] ?? 0 ?>">0</div>
                <div class="text-base-content/70 mt-2">Objets sauvés</div>
            </div>
            <div class="fade-up" style="transition-delay:0.1s">
                <div class="text-5xl font-bold text-primary counter-val" data-target="<?= $stats['utilisateurs'] ?? 0 ?>">0</div>
                <div class="text-base-content/70 mt-2">Utilisateurs</div>
            </div>
            <div class="fade-up" style="transition-delay:0.15s">
                <div class="text-5xl font-bold text-primary counter-val" data-target="<?= $stats['projets_realises'] ?? 0 ?>">0</div>
                <div class="text-base-content/70 mt-2">Projets réalisés</div>
            </div>
            <div class="fade-up" style="transition-delay:0.2s">
                <div class="text-5xl font-bold text-primary"><?= $stats['co2_economise'] ?? 0 ?> t</div>
                <div class="text-base-content/70 mt-2">CO₂ économisées</div>
            </div>
        </div>
    </div>
</section>

<section class="bg-base-100 border-t border-base-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 lg:py-20">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div class="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold leading-tight mb-6">
                    Une plateforme simple pour prolonger la vie de vos objets
                </h2>
                <div class="space-y-6 text-base-content/80">
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fas fa-search-location text-green-600 text-xs"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Trouvez un prestataire près de chez vous</h3>
                            <p class="mt-1 text-base-content/70">Parcourez les prestations proposées par des professionnels pour réparer, transformer ou valoriser vos objets facilement.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fas fa-paper-plane text-blue-600 text-xs"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Déposez une demande rapidement</h3>
                            <p class="mt-1 text-base-content/70">Décrivez votre besoin, votre objet et votre objectif. Les prestataires peuvent ensuite vous répondre directement.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fas fa-calendar-check text-purple-600 text-xs"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Participez à des événements responsables</h3>
                            <p class="mt-1 text-base-content/70">Découvrez des ateliers, des rencontres et des initiatives locales autour de la réparation et du recyclage.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="/catalogue/services" class="bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                        Voir les services
                    </a>
                    <a href="/catalogue/evenements" class="bg-base-200 border border-base-300 px-6 py-3 rounded-xl font-medium hover:bg-base-300 transition">
                        Voir les événements
                    </a>
                </div>
            </div>
            <div class="bg-base-100 rounded-3xl shadow-sm overflow-hidden fade-up" style="transition-delay:0.1s">
                <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=80"
                     class="w-full h-full object-cover min-h-[420px]" alt="Équipe">
            </div>
        </div>
    </div>
</section>

<section class="bg-green-50 border-t border-green-100">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 text-center fade-up">
        <h2 class="text-3xl font-bold mb-4">Rejoignez la communauté UpcycleConnect</h2>
        <p class="text-base-content/70 max-w-2xl mx-auto mb-8">
            Participez à des événements près de chez vous, apprenez de nouvelles techniques et
            donnez une seconde vie à vos objets avec l'aide de notre communauté.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/register" class="inline-block bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                Créer un compte gratuit
            </a>
            <a href="/catalogue/evenements" class="inline-block bg-white border border-gray-200 px-8 py-3 rounded-xl font-medium hover:bg-gray-50 transition">
                Voir les événements
            </a>
        </div>
    </div>
</section>

<script>
(function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    const counters = document.querySelectorAll('.counter-val[data-target]');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const el = e.target;
            const target = parseInt(el.dataset.target, 10);
            if (!target) { el.textContent = '0'; return; }
            let current = 0;
            const step = Math.ceil(target / 60);
            const timer = setInterval(() => {
                current = Math.min(current + step, target);
                el.textContent = current.toLocaleString('fr-FR');
                if (current >= target) clearInterval(timer);
            }, 20);
            counterObserver.unobserve(el);
        });
    }, { threshold: 0.5 });
    counters.forEach(el => counterObserver.observe(el));
})();
</script>