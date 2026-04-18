<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents('php://input'), true);

// Vérification de la présence des champs (Ajout du champ 'telephone')
if (!isset($data['nom'], $data['email'], $data['telephone'], $data['password'], $data['role'], $data['ville'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Champs obligatoires manquants (nom, email, telephone, password, role, ville)']);
    exit;
}

$nom = trim($data['nom']);
$email = trim($data['email']);
$telephone = trim($data['telephone']); // Récupération du numéro WhatsApp
$password = $data['password'];
$ville = trim($data['ville']);
$produit = trim($data['produit'] ?? '');
$quantite = (int)($data['quantite'] ?? 0);

// Définition du rôle
$role = $data['role']; 

// Forcer le rôle admin si l'email correspond
if ($email === 'admin@agriprice.com') {
    $role = 'admin';
}

// Vérifier si l'email existe déjà
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Email déjà utilisé']);
    exit;
}

// Hasher le mot de passe
$hash = password_hash($password, PASSWORD_DEFAULT);

// --- MODIFICATION DE LA REQUÊTE SQL POUR INCLURE LE TÉLÉPHONE ---
$sql = 'INSERT INTO users (nom, email, telephone, password, role, ville, produit, quantite) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$nom, $email, $telephone, $hash, $role, $ville, $produit, $quantite])) {
    $userId = $pdo->lastInsertId();
    
    // On remplit la session pour connecter l'utilisateur immédiatement
    $_SESSION['user_id'] = $userId;
    $_SESSION['nom'] = $nom;
    $_SESSION['role'] = $role;
    
    echo json_encode(['success' => true, 'message' => 'Inscription réussie']);
} else {
    http_response_code(500);
    echo json_encode(['error' => "Erreur lors de l'inscription dans la base de données"]);
}
?>