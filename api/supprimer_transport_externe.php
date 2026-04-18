<?php
session_start();
require_once 'db.php';

// Sécurité Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Optionnel : On peut aussi supprimer le fichier document sur le serveur ici
    // Mais pour l'instant, supprimons juste la ligne en base
    $stmt = $pdo->prepare("DELETE FROM transporteurs_externes WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        header('Location: ../espace_admin.php?msg=Transporteur supprimé définitivement.');
    } else {
        header('Location: ../espace_admin.php?msg=Erreur lors de la suppression.');
    }
} else {
    header('Location: ../espace_admin.php');
}
exit();