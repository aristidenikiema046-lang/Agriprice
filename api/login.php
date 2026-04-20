<?php
session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
session_start();
header('Content-Type: application/json');

// On appelle la connexion qui est dans le même dossier
require_once __DIR__ . '/db.php'; 

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(['success' => false, 'error' => 'Champs manquants']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

try {
    // Vérifie bien que le nom de la table est 'users' (en minuscules)
    $stmt = $pdo->prepare('SELECT id, nom, email, password, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Email ou mot de passe incorrect']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        'success' => true,
        'role' => $user['role'],
        'nom' => $user['nom']
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur BDD : ' . $e->getMessage()]);
}