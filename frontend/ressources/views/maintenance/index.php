<div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-4">
    <div class="mb-8 text-primary animate-spin-slow">
        <i class="fa-solid fa-gear text-9xl"></i>
    </div>
    
    <h1 class="text-4xl font-bold mb-4">Site en maintenance</h1>
    
    <p class="text-xl text-base-content/70 max-w-lg">
        Nous effectuons actuellement des mises à jour pour améliorer votre expérience sur 
        <span class="font-bold text-primary text-upcycle-green">UpcycleConnect</span>.
    </p>
    
    <div class="mt-10 p-6 bg-base-100 rounded-2xl shadow-xl border border-base-300">
        <p class="flex items-center gap-3 text-sm italic">
            <i class="fa-solid fa-circle-info text-info"></i>
            L'accès sera rétabli d'ici quelques instants. Merci de votre patience !
        </p>
    </div>
</div>

<style>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 8s linear infinite;
}
.text-upcycle-green {
    color: #2D6A4F;
}
</style>
