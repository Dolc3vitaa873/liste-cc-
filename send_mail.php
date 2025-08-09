<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bin = $_POST['bin'] ?? '';
    $banque = $_POST['banque'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $zip = $_POST['zip'] ?? '';
    $base = $_POST['base'] ?? '';
    $niveau = $_POST['niveau'] ?? '';
    $prix = $_POST['prix'] ?? '';
    $pseudo = $_POST['pseudo'] ?? '';
    $raw = $_POST['raw'] ?? '';

    $to = "baranostop28@gmail.com";
    $subject = "Demande de carte bancaire de $pseudo";
    $message = "Une carte a été demandée.\n\n";
    $message .= "Pseudo Telegram : $pseudo\n\n";
    $message .= "Détails de la carte :\n";
    $message .= "BIN : $bin\n";
    $message .= "Banque : $banque\n";
    $message .= "Date de naissance : $dob\n";
    $message .= "ZIP : $zip\n";
    $message .= "Base : $base\n";
    $message .= "Niveau : $niveau\n";
    $message .= "Prix : $prix\n\n";
    $message .= "Données brutes:\n$raw\n";

    $headers = "From: noreply@tondomaine.com\r\n";
    $headers .= "Reply-To: noreply@tondomaine.com\r\n";

    if (mail($to, $subject, $message, $headers)) {
        echo "Demande envoyée avec succès !";
    } else {
        echo "Erreur lors de l'envoi du mail.";
    }
} else {
    echo "Méthode non autorisée.";
}
?>
