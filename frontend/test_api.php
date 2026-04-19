<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://api:8080/api/forum/sujets');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'titre' => 'Test sujet',
    'contenu' => 'Contenu test',
    'categorie' => 'general',
    'id_utilisateur' => 2
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
echo $r;

?>