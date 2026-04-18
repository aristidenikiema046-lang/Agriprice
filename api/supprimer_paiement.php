<?php
session_start();
require_once 'db.php';

// Sécurité Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // On supprime la transaction de la table
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        header('Location: ../espace_admin.php?msg=Transaction supprimée et rejetée.');
    } else {
        header('Location: ../espace_admin.php?msg=Erreur lors de la suppression du paiement.');
    }
} else {
    header('Location: ../espace_admin.php');
}
exit();