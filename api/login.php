<?php
// Configuration pour que la session soit accessible partout sur le domaine
session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
session_start();
header('Content-Type: application/json');

// Connexion BDD
// Remplace require_once 'db.php'; par :
// Remplace require_once 'db.php'; par cette ligne plus précise :
require_once __DIR__ . '/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(['success' => false, 'error' => 'Champs manquants']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

// On récupère les infos essentielles, dont le rôle
$stmt = $pdo->prepare('SELECT id, nom, email, password, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'error' => 'Email ou mot de passe incorrect']);
    exit;
}

// Sécurité : Régénérer l'ID de session à la connexion pour éviter les fixations de session
session_regenerate_id(true);

// Initialisation des variables de session
$_SESSION['user_id'] = $user['id'];
$_SESSION['nom'] = $user['nom'];
$_SESSION['role'] = $user['role']; // Crucial pour espace_admin.php

echo json_encode([
    'success' => true,
    'role' => $user['role'],
    'nom' => $user['nom']
]);