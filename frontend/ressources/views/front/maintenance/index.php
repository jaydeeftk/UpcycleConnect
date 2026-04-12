<div class="maintenance-container">
    <i class="fas fa-cog fa-spin" style="font-size: 100px; color: #2ecc71;"></i>
    <h1>Site en cours de maintenance</h1>
    <p>Nous revenons très vite !</p>
</div>

<style>
.maintenance-container {
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: #1a1a1a;
    color: white;
    font-family: sans-serif;
}
.fa-spin {
    animation: fa-spin 2s infinite linear;
}
@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(359deg); }
}
</style>
