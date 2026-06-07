<?php

$role = $_SESSION['user']['role'] ?? '';
if ($role !== 'particulier') return;
if (!empty($_SESSION['user']['tutoriel_vu'])) return;

$tuto = [
    'step'   => t('tuto_step', 'Étape'),
    'close'  => t('tuto_close', 'Fermer'),
    'skip'   => t('tuto_skip', 'Passer le tutoriel'),
    'prev'   => t('tuto_prev', '← Précédent'),
    'next'   => t('tuto_next', 'Suivant →'),
    'finish' => t('tuto_finish', 'Terminer 🎉'),
    'steps'  => [
        [
            'titre'    => t('tuto_s1_title', '👋 Bienvenue sur UpcycleConnect !'),
            'texte'    => t('tuto_s1_text', 'Nous allons vous faire découvrir les fonctionnalités principales de la plateforme en quelques étapes. Suivez le guide !'),
            'cible'    => null,
            'position' => 'center',
        ],
        [
            'titre'    => t('tuto_s2_title', '♻️ Déposer un objet'),
            'texte'    => t('tuto_s2_text', 'Depuis le menu "Déposer", vous pouvez publier une annonce ou demander à déposer un objet dans l\'un de nos conteneurs.'),
            'cible'    => '[data-tuto="deposer"]',
            'position' => 'bottom',
        ],
        [
            'titre'    => t('tuto_s3_title', '🛠️ Les prestations'),
            'texte'    => t('tuto_s3_text', 'Parcourez les prestations proposées par nos artisans et professionnels pour réparer, transformer ou recycler vos objets.'),
            'cible'    => '[data-tuto="prestations"]',
            'position' => 'bottom',
        ],
        [
            'titre'    => t('tuto_s4_title', '💡 Espace Conseils'),
            'texte'    => t('tuto_s4_text', 'Accédez à des conseils d\'experts et échangez avec la communauté dans notre forum dédié à l\'upcycling.'),
            'cible'    => '[data-tuto="conseils"]',
            'position' => 'bottom',
        ],
        [
            'titre'    => t('tuto_s5_title', '🌱 Votre Upcycling Score'),
            'texte'    => t('tuto_s5_text', 'Suivez votre impact environnemental grâce à votre score. Plus vous participez, plus vous montez en niveau et débloquez des avantages !'),
            'cible'    => '[data-tuto="score"]',
            'position' => 'bottom',
        ],
        [
            'titre'    => t('tuto_s6_title', '📅 Votre Planning'),
            'texte'    => t('tuto_s6_text', 'Retrouvez tous vos cours, événements et activités dans votre planning personnel.'),
            'cible'    => '[data-tuto="planning"]',
            'position' => 'bottom',
        ],
        [
            'titre'    => t('tuto_s7_title', '🎉 Vous êtes prêt !'),
            'texte'    => t('tuto_s7_text', 'Vous connaissez maintenant les fonctionnalités essentielles d\'UpcycleConnect. Bonne exploration et bonne upcycling !'),
            'cible'    => null,
            'position' => 'center',
        ],
    ],
];
?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const tutorielVu = localStorage.getItem('tutoriel_vu');
    if (tutorielVu) return;

    const TUTO = <?= json_encode($tuto, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const etapes = TUTO.steps;

    let etapeActuelle = 0;

    const overlay = document.createElement('div');
    overlay.id = 'tuto-overlay';
    overlay.style.cssText = `
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
        z-index: 9998;
        transition: all 0.3s ease;
        pointer-events: all;
    `;
    document.body.style.overflow = 'hidden';
    document.body.appendChild(overlay);

    const bulle = document.createElement('div');
    bulle.id = 'tuto-bulle';
    bulle.style.cssText = `
        position: fixed;
        z-index: 9999;
        background: white;
        border-radius: 16px;
        padding: 28px;
        max-width: 380px;
        width: 90%;
        box-shadow: 0 25px 60px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    `;
    document.body.appendChild(bulle);

    const spotlight = document.createElement('div');
    spotlight.id = 'tuto-spotlight';
    spotlight.style.cssText = `
        position: fixed;
        z-index: 9997;
        border-radius: 12px;
        box-shadow: 0 0 0 9999px rgba(0,0,0,0.7);
        transition: all 0.4s ease;
        pointer-events: none;
    `;
    document.body.appendChild(spotlight);

    function afficherEtape(index) {
        const etape = etapes[index];
        const total = etapes.length;

        bulle.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <span style="font-size:12px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">
                    ${TUTO.step} ${index + 1} / ${total}
                </span>
                <button id="tuto-fermer" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:20px; line-height:1; padding:0 4px;" title="${TUTO.close}">×</button>
            </div>

            <!-- Barre de progression -->
            <div style="background:#f3f4f6; border-radius:99px; height:4px; margin-bottom:20px;">
                <div style="background:linear-gradient(to right, #10b981, #059669); height:4px; border-radius:99px; width:${((index + 1) / total) * 100}%; transition:width 0.3s ease;"></div>
            </div>

            <h3 style="font-size:18px; font-weight:700; margin-bottom:10px; color:#111827;">${etape.titre}</h3>
            <p style="font-size:14px; color:#6b7280; line-height:1.6; margin-bottom:24px;">${etape.texte}</p>

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <button id="tuto-passer" style="background:none; border:none; cursor:pointer; font-size:13px; color:#9ca3af; text-decoration:underline; padding:0;">${TUTO.skip}</button>
                <div style="display:flex; gap:8px;">
                    ${index > 0 ? `<button id="tuto-precedent" style="background:#f3f4f6; border:none; cursor:pointer; padding:10px 18px; border-radius:10px; font-size:14px; font-weight:600; color:#374151;">${TUTO.prev}</button>` : ''}
                    <button id="tuto-suivant" style="background:#111827; border:none; cursor:pointer; padding:10px 22px; border-radius:10px; font-size:14px; font-weight:600; color:white;">
                        ${index === total - 1 ? TUTO.finish : TUTO.next}
                    </button>
                </div>
            </div>
        `;

        const cibleEl = etape.cible ? document.querySelector(etape.cible) : null;

        if (cibleEl) {
            const rect = cibleEl.getBoundingClientRect();
            const padding = 8;
            spotlight.style.display = 'block';
            spotlight.style.top = (rect.top - padding) + 'px';
            spotlight.style.left = (rect.left - padding) + 'px';
            spotlight.style.width = (rect.width + padding * 2) + 'px';
            spotlight.style.height = (rect.height + padding * 2) + 'px';

            const bulleTop = rect.bottom + 16;
            const bulleLeft = Math.max(16, Math.min(rect.left, window.innerWidth - 400));
            bulle.style.top = bulleTop + 'px';
            bulle.style.left = bulleLeft + 'px';
            bulle.style.transform = 'none';
        } else {
            spotlight.style.display = 'none';
            bulle.style.top = '50%';
            bulle.style.left = '50%';
            bulle.style.transform = 'translate(-50%, -50%)';
        }

        document.getElementById('tuto-suivant').addEventListener('click', () => {
            if (index === total - 1) {
                terminerTutoriel();
            } else {
                afficherEtape(index + 1);
            }
        });

        const precedentBtn = document.getElementById('tuto-precedent');
        if (precedentBtn) {
            precedentBtn.addEventListener('click', () => afficherEtape(index - 1));
        }

        document.getElementById('tuto-fermer').addEventListener('click', terminerTutoriel);
        document.getElementById('tuto-passer').addEventListener('click', terminerTutoriel);
    }

    function terminerTutoriel() {
        localStorage.setItem('tutoriel_vu', 'true');
        fetch('/tutoriel/done').catch(function () {});
        document.body.style.overflow = '';
        overlay.remove();
        bulle.remove();
        spotlight.remove();
    }

    afficherEtape(0);
});
</script>
