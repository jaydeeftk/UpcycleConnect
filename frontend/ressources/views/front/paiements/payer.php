<section class="max-w-3xl mx-auto px-6 lg:px-10 py-16">

    <div class="bg-base-100 rounded-3xl shadow-sm p-8">

        <h1 class="text-4xl font-bold mb-6">Paiement de la prestation</h1>

        <div class="bg-base-200 rounded-xl p-4 mb-6 space-y-2">
            <div class="flex justify-between">
                <span class="text-base-content/70">Prestation</span>
                <span class="font-medium">Réparation de vélo</span>
            </div>
            <div class="flex justify-between">
                <span class="text-base-content/70">Prestataire</span>
                <span class="font-medium">Atelier RépareTout</span>
            </div>
            <div class="border-t border-base-300 pt-2 mt-2 flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>45€</span>
            </div>
        </div>

        <form class="space-y-6" method="POST" action="/UpcycleConnect-PA2526/frontend/public/payer">
            <div>
                <label class="block text-sm font-medium mb-2">Nom sur la carte</label>
                <input type="text" name="nom_carte" placeholder="Jean Dupont"
                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Numéro de carte</label>
                <input type="text" name="numero_carte" placeholder="1234 5678 9012 3456"
                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Expiration</label>
                    <input type="text" name="expiration" placeholder="MM/AA"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">CVC</label>
                    <input type="text" name="cvc" placeholder="123"
                        class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                </div>
            </div>
            <button type="submit"
                class="w-full bg-black text-white py-3 rounded-xl text-lg font-medium hover:bg-neutral-800 transition">
                Payer 45€
            </button>
        </form>

    </div>

</section>