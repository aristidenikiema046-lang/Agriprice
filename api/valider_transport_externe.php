<?php
session_start();
require_once 'db.php';

// Sécurité Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE transporteurs_externes SET statut = 'valide' WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: ../espace_admin.php?msg=Transporteur approuvé avec succès !');
exit();