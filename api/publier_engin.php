<?php
session_start();
require_once 'db.php'; // Connexion PDO

// 1. Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Erreur : Vous devez être connecté pour publier.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nom_engin = $_POST['nom_engin'] ?? '';
    $categorie = $_POST['categorie'] ?? 'Vente';
    $prix = $_POST['prix'] ?? 0;
    $ville = $_POST['ville'] ?? '';
    $description = $_POST['description'] ?? '';
    $contact = $_POST['contact_vendeur'] ?? ''; // RÉCUPÉRATION DU CONTACT

    // 2. Gestion de l'image
    $dossier_destination = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'engins' . DIRECTORY_SEPARATOR;
    
    if (!file_exists($dossier_destination)) {
        mkdir($dossier_destination, 0777, true);
    }

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        die("Erreur : Aucun fichier image reçu.");
    }

    $nom_image = $_FILES['photo']['name'];
    $temp_image = $_FILES['photo']['tmp_name'];
    $extension = strtolower(pathinfo($nom_image, PATHINFO_EXTENSION));
    $extensions_valides = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $extensions_valides)) {
        die("Erreur : Format d'image non supporté.");
    }

    $nouveau_nom_image = uniqid('engin_') . "." . $extension;
    $chemin_final = $dossier_destination . $nouveau_nom_image;

    if (move_uploaded_file($temp_image, $chemin_final)) {
        try {
            // 3. Insertion en base de données (AJOUT DE contact_vendeur)
            $sql = "INSERT INTO engins_agricoles (user_id, nom_engin, categorie, prix, ville, photo, description, contact_vendeur, statut) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $user_id, 
                $nom_engin, 
                $categorie, 
                (int)$prix, 
                $ville, 
                $nouveau_nom_image, 
                $description,
                $contact // Ajouté ici
            ]);

            // Redirection (Vérifie bien si ton fichier est à la racine ou dans /page)
            header("Location: ../location.php?msg=Annonce envoyée pour validation !");
            exit();

        } catch (Exception $e) {
            if (file_exists($chemin_final)) unlink($chemin_final);
            die("Erreur BDD : " . $e->getMessage());
        }
    } else {
        die("Erreur : Droits d'écriture insuffisants sur le dossier uploads.");
    }
} else {
    header("Location: ../location.php");
    exit();
}