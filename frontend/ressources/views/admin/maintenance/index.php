<div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 text-center px-4">
    <div class="mb-8 text-gray-400">
        <i class="fa-solid fa-gear text-9xl animate-spin-slow"></i>
    </div>
    
    <h1 class="text-4xl font-bold text-gray-800">Site en maintenance</h1>
    <p class="text-gray-600 mt-4 max-w-md">
        Nous effectuons actuellement des mises à jour. <br>
        L'accès sera rétabli d'ici quelques instants.
    </p>
    
    <div class="mt-12">
        <a href="/admin-portal-access" 
           class="text-[10px] text-gray-300 hover:text-gray-500 transition-colors uppercase tracking-widest">
            Accès restreint
        </a>
    </div>
</div>

<style>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    display: inline-block;
    animation: spin-slow 8s linear infinite;
}
</style>