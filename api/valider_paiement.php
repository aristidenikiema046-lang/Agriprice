<?php
session_start();
require_once 'db.php';

// Sécurité Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 1. Récupération de la transaction
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->execute([$id]);
    $t = $stmt->fetch();

    if ($t) {
        // 2. Mise à jour du statut global de la transaction
        $update = $pdo->prepare("UPDATE transactions SET status = 'complete' WHERE id = ?");
        $update->execute([$id]);

        $msg = "";

        // 3. Logique par type
        if ($t['type'] === 'transport') {
            $sql = "INSERT INTO acces_transporteurs (user_id, transporteur_id) VALUES (?, ?)";
            $ins = $pdo->prepare($sql);
            $ins->execute([$t['user_id'], $t['target_id']]);
            $msg = "Accès WhatsApp transporteur activé !";
        } 
        elseif ($t['type'] === 'location') {
            $msg = "Paiement commission machine validé !";
        }
        elseif ($t['type'] === 'publicite_evenement') {
            // On passe l'événement de 'en_attente' à 'valide' pour agenda.php
            $sqlEvt = "UPDATE evenements_agricoles SET statut = 'valide' WHERE id = ?";
            $upEvt = $pdo->prepare($sqlEvt);
            $upEvt->execute([$t['target_id']]);
            $msg = "Événement validé et publié sur l'agenda !";
        }
        elseif ($t['type'] === 'premium_hebdo' || $t['type'] === 'premium_mensuel') {
            $msg = "Le pack de boost a été activé avec succès !";
        }
        else {
            $msg = "Paiement validé !";
        }

        // REDIRECTION VERS TON NOUVEAU NOM DE FICHIER
        header("Location: ../espace_admin.php?msg=" . urlencode($msg));
        exit();
    } else {
        die("Transaction introuvable dans la base de données.");
    }
} else {
    die("ID manquant.");
}