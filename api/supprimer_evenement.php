<?php
session_start();
require_once 'db.php';

// Vérification de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../page/login.php');
    exit();
}

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // 1. Récupérer l'image pour la supprimer du dossier
    // Note : On vérifie que l'événement appartient bien à l'utilisateur (sécurité)
    // Sauf si tu as un rôle 'admin', dans ce cas ajoute : OR $_SESSION['role'] == 'admin'
    $stmt = $pdo->prepare("SELECT photo FROM evenements_agricoles WHERE id = ? AND user_id = ?");
    $stmt->execute([$event_id, $user_id]);
    $event = $stmt->fetch();

    if ($event) {
        $chemin_image = "../uploads/events/" . $event['photo'];
        
        // Supprimer le fichier image du serveur
        if (file_exists($chemin_image)) {
            unlink($chemin_image);
        }

        // 2. Supprimer l'entrée en base de données
        $delete = $pdo->prepare("DELETE FROM evenements_agricoles WHERE id = ?");
        $delete->execute([$event_id]);

        header('Location: ../page/publicite.php?msg=Événement supprimé avec succès');
    } else {
        header('Location: ../page/publicite.php?error=Action non autorisée');
    }
}