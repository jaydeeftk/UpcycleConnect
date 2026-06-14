<?php


if (!function_exists('uc_image')) {


    function uc_unsplash(string $id, int $w = 900): string
    {
        return "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";
    }

    function uc_image_pools(): array
    {
        return [
            'evenement' => [
                'photo-1488459716781-31db52582fe9',
                'photo-1452860606245-08befc0ff44b',
                'photo-1540575467063-178a50c2df87',
                'photo-1531058020387-3be344556be6',
                'photo-1559027615-cd4628902d4a',
                'photo-1523580494863-6f3031224c94',
                'photo-1517048676732-d65bc937f952',
                'photo-1521791136064-7986c2920216',
            ],
            'formation' => [
                'photo-1558618666-fcd25c85cd64',
                'photo-1504148455328-c376907d081c',
                'photo-1518770660439-4636190af475',
                'photo-1513364776144-60967b0f800f',
                'photo-1416879595882-3373a0480b5b',
                'photo-1532996122724-e3c354a0b15b',
                'photo-1595407753234-0882f1e77954',
                'photo-1609205807107-2d29e8f53dd6',
                'photo-1452860606245-08befc0ff44b',
                'photo-1524178232363-1fb2b075b655',
            ],
 
            'service' => [
                'photo-1609205807107-2d29e8f53dd6',
                'photo-1452860606245-08befc0ff44b',
                'photo-1532996122724-e3c354a0b15b',
                'photo-1595407753234-0882f1e77954',
                'photo-1584820927498-cfe5211fd8bf',
                'photo-1581093458791-9d09c86d1f79',
                'photo-1504148455328-c376907d081c',
                'photo-1558618666-fcd25c85cd64',
            ],
            'prestation' => [
                'photo-1581093458791-9d09c86d1f79',
                'photo-1609205807107-2d29e8f53dd6',
                'photo-1452860606245-08befc0ff44b',
                'photo-1504148455328-c376907d081c',
                'photo-1518770660439-4636190af475',
                'photo-1595407753234-0882f1e77954',
                'photo-1584820927498-cfe5211fd8bf',
            ],
            // Conseils & forum : illustrations eco / upcycling / DIY
            'conseil' => [
                'photo-1532996122724-e3c354a0b15b',
                'photo-1595407753234-0882f1e77954',
                'photo-1609205807107-2d29e8f53dd6',
                'photo-1416879595882-3373a0480b5b',
                'photo-1513364776144-60967b0f800f',
                'photo-1581578731548-c64695cc6952',
                'photo-1524178232363-1fb2b075b655',
            ],
            // Objets / annonces (marketplace) : meubles, textile, electronique, deco…
            'objet' => [
                'photo-1581578731548-c64695cc6952',
                'photo-1504148455328-c376907d081c',
                'photo-1558618666-fcd25c85cd64',
                'photo-1518770660439-4636190af475',
                'photo-1532996122724-e3c354a0b15b',
                'photo-1595407753234-0882f1e77954',
                'photo-1609205807107-2d29e8f53dd6',
                'photo-1452860606245-08befc0ff44b',
            ],
        ];
    }

    /**
     * URL d'image stable et variee pour un item.
     *
     * @param string $theme evenement|formation|service|prestation|conseil|objet
     * @param mixed  $seed  identifiant stable de l'item (id numerique, sinon titre)
     * @param int    $w     largeur CDN souhaitee
     */
    function uc_image(string $theme, $seed = '', int $w = 900): string
    {
        $pools = uc_image_pools();
        $pool  = $pools[$theme] ?? $pools['formation'];
        if (is_numeric($seed)) {
            $idx = (int) $seed;
        } else {
            $idx = (int) crc32((string) $seed);
        }
        $idx = abs($idx) % count($pool);
        return uc_unsplash($pool[$idx], $w);
    }
}
