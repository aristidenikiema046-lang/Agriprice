<?php
session_start();
require_once 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    die("Erreur : Vous devez être connecté pour publier un événement.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $titre = $_POST['titre'] ?? '';
    $date_evt = $_POST['date_evenement'] ?? '';
    $lieu = $_POST['lieu'] ?? '';
    $contact = $_POST['contact_info'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix_ticket = $_POST['prix_ticket'] ?? 5000;

    $dossier_destination = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'events' . DIRECTORY_SEPARATOR;
    
    if (!file_exists($dossier_destination)) {
        mkdir($dossier_destination, 0777, true);
    }

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        die("Erreur : Image manquante.");
    }

    $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $autorise = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($extension, $autorise)) {
        die("Erreur : Format d'image non supporté.");
    }

    $nouveau_nom_image = uniqid('event_') . "." . $extension;
    $chemin_final = $dossier_destination . $nouveau_nom_image;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $chemin_final)) {
        try {
            $pdo->beginTransaction();

            // 3. Insertion de l'événement
            $sqlEvt = "INSERT INTO evenements_agricoles (user_id, titre, date_evenement, lieu, contact_info, photo, description, prix_ticket, statut) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')";
            $stmtEvt = $pdo->prepare($sqlEvt);
            $stmtEvt->execute([$user_id, $titre, $date_evt, $lieu, $contact, $nouveau_nom_image, $description, $prix_ticket]);
            
            $last_event_id = $pdo->lastInsertId();

            // 4. Création de la transaction (date_transaction se remplit toute seule en DB)
            $sqlTrans = "INSERT INTO transactions (user_id, type, target_id, status) VALUES (?, 'publicite_evenement', ?, 'en_attente')";
            $stmtTrans = $pdo->prepare($sqlTrans);
            $stmtTrans->execute([$user_id, $last_event_id]);

            $pdo->commit();

            $msg = "Événement enregistré ! Envoyez les 5000F au 07 57 61 02 31 pour la publication.";
            header("Location: ../page/publicite.php?msg=" . urlencode($msg));
            exit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if (file_exists($chemin_final)) unlink($chemin_final);
            die("Erreur BDD : " . $e->getMessage());
        }
    } else {
        die("Erreur lors de l'upload.");
    }
}