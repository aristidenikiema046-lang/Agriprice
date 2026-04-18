<?php
// On n'a plus besoin de session_start() ici car on accepte tout le monde
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération sécurisée des données du formulaire
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $vehicule = $_POST['vehicule'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $capacite = $_POST['capacite'] ?? '';
    
    $doc_name = null;

    // 1. Gestion de l'upload du document
    if (isset($_FILES['document_camion']) && $_FILES['document_camion']['error'] === 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $file_name = $_FILES['document_camion']['name'];
        $file_tmp = $_FILES['document_camion']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_extensions)) {
            // On crée un nom unique : trans_1709825400_nom.jpg
            $doc_name = "trans_" . time() . "_" . strtolower($nom) . "." . $ext;
            
            // Chemin vers le dossier uploads (on remonte d'un cran car on est dans /api)
            $upload_dir = '../uploads/documents/';
            
            if (!is_dir($upload_dir)) { 
                mkdir($upload_dir, 0777, true); 
            }
            
            if (!move_uploaded_file($file_tmp, $upload_dir . $doc_name)) {
                // Si l'upload échoue, on peut rediriger avec une erreur
                header('Location: ../page/transport.php?msg=Erreur lors du transfert du fichier.');
                exit();
            }
        } else {
            header('Location: ../page/transport.php?msg=Format de fichier non supporté (JPG, PNG, PDF uniquement).');
            exit();
        }
    }

    // 2. Insertion dans la table transporteurs_externes
    // Note : le champ 'statut' est 'en_attente' par défaut dans la base
    try {
        $sql = "INSERT INTO transporteurs_externes (nom, prenom, telephone, ville, vehicule, capacite, doc_transport, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nom, 
            $prenom, 
            $telephone, 
            $ville, 
            $vehicule, 
            $capacite, 
            $doc_name
        ]);

        // 3. Redirection avec message de succès
        header('Location: ../page/transport.php?msg=Votre demande a été envoyée ! Un administrateur la validera sous 24h.');
    } catch (Exception $e) {
        // En cas d'erreur SQL (ex: table manquante)
        header('Location: ../page/transport.php?msg=Erreur technique : ' . urlencode($e->getMessage()));
    }
    exit();
}