<?php
session_start();
require_once 'db.php'; 

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit();
}

$user_id = $_SESSION['user_id'];
// On récupère le type (transport, location, mise_en_relation, ou premium)
$type = $_GET['type'] ?? 'mise_en_relation'; 
$target_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // 1. On insère la transaction dans la base de données avec le statut 'en_attente'
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, target_id, status) VALUES (?, ?, ?, 'en_attente')");
    $stmt->execute([$user_id, $type, $target_id]);

    $message = "Demande envoyée ! Votre accès sera activé après vérification du transfert de 2000F.";

    // 2. LOGIQUE DE REDIRECTION CORRIGÉE
    if ($type === 'location') {
        // Redirection vers la page des machines (Location)
        header("Location: ../location.php?msg=" . urlencode($message));
    } 
    elseif ($type === 'transport') {
        // Redirection vers la page transport
        header("Location: ../page/transport.php?msg=" . urlencode($message));
    } 
    else {
        // Pour tout le reste (mise_en_relation, premium), vers le dashboard
        header("Location: ../page/dashboard.php?msg=" . urlencode($message));
    }
    exit();

} catch (Exception $e) {
    // En cas d'erreur SQL
    die("Erreur lors de l'enregistrement du paiement : " . $e->getMessage());
}