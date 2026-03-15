<section class="max-w-5xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-12 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Faire une demande de prestation</h1>
        <p class="text-lg text-base-content/70 max-w-2xl mx-auto">
            Décrivez votre besoin pour trouver un professionnel capable de réparer,
            transformer ou recycler votre objet.
        </p>
    </div>

    <div class="bg-base-100 rounded-3xl shadow-sm p-8 md:p-10">
        <form class="space-y-8" method="POST" action="/UpcycleConnect-PA2526/frontend/public/demande-prestation">

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Nom de l'objet</label>
                    <input type="text" name="nom_objet" placeholder="Ex : Grille-pain, chaise, vélo..."
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Catégorie de demande</label>
                    <select name="categorie"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="" disabled selected>Choisir une catégorie</option>
                        <option>Réparation</option>
                        <option>Transformation</option>
                        <option>Recyclage</option>
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Type d'objet</label>
                    <select name="type_objet"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="" disabled selected>Choisir un type</option>
                        <option>Électroménager</option>
                        <option>Mobilier</option>
                        <option>Électronique</option>
                        <option>Textile</option>
                        <option>Vélo</option>
                        <option>Autre</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">État de l'objet</label>
                    <select name="etat"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="" disabled selected>Choisir l'état</option>
                        <option>Légèrement abîmé</option>
                        <option>Endommagé</option>
                        <option>Ne fonctionne plus</option>
                        <option>À transformer</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Description de votre besoin</label>
                <textarea name="description" rows="5"
                    placeholder="Décrivez votre objet, le problème rencontré ou la transformation souhaitée..."
                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black resize-none"></textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Localisation</label>
                    <input type="text" name="localisation" placeholder="Ex : Paris, Lyon, Marseille..."
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Budget estimé</label>
                    <input type="text" name="budget" placeholder="Ex : 20€, 50€, à discuter..."
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Ajouter une photo de l'objet</label>
                <input type="file" name="photo"
                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 file:mr-4 file:py-1 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-base-200 file:cursor-pointer" />
                <p class="text-sm text-base-content/60 mt-2">
                    Ajoutez une photo pour aider les prestataires à mieux comprendre votre demande.
                </p>
            </div>

            <div class="bg-base-200 rounded-2xl p-5">
                <h2 class="text-lg font-semibold mb-2">Bon à savoir</h2>
                <p class="text-base-content/70">
                    Plus votre demande est précise, plus il sera facile pour un prestataire de vous proposer
                    une solution adaptée.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-2">
                <button type="submit"
                    class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                    Envoyer ma demande
                </button>
                <a href="/UpcycleConnect-PA2526/frontend/public/prestations"
                    class="border border-base-300 px-8 py-3 rounded-xl font-medium hover:bg-base-200 transition text-center">
                    Voir les prestations
                </a>
            </div>

        </form>
    </div>

</section>