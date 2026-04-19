<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://api:8080/api/auth/register');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['nom'=>'Test','prenom'=>'User','email'=>'test99@test.com','mot_de_passe'=>'password123','role'=>'particulier']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
echo $r;
echo curl_error($ch);


?>