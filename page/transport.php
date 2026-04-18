<?php
session_start();
require_once '../api/db.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    // On ne récupère que les transporteurs approuvés par l'admin
    $stmt = $pdo->prepare("SELECT * FROM transporteurs_externes WHERE statut = 'valide' ORDER BY id DESC");
    $stmt->execute();
    $transporteurs = $stmt->fetchAll();
} catch (Exception $e) { 
    $transporteurs = []; 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Agricole - Agriprice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #27ae60; }
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .hero-section { background: linear-gradient(rgba(40, 167, 69, 0.9), rgba(20, 90, 40, 0.9)), url('../images/c2.jpg'); background-size: cover; color: white; padding: 60px 0; border-radius: 0 0 30px 30px; }
        .card-transport { border: none; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; background: white; }
        .card-transport:hover { transform: translateY(-5px); }
        .btn-whatsapp { background-color: #25D366; color: white; border-radius: 50px; font-weight: 600; text-decoration: none; display: inline-block; text-align: center; }
        .btn-pay { background-color: #fb911f; color: white; border-radius: 50px; font-weight: 600; border: none; }
        .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .badge-price { background: #fff3e0; color: #e67e22; padding: 5px 12px; border-radius: 10px; font-size: 0.8rem; }
        .nav-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f0fdf4; color: #27ae60; text-decoration: none; transition: 0.3s; position: relative; }
        .nav-icon:hover { background: #27ae60; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white shadow-sm sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <a href="../index.php" class="nav-icon" title="Retour à l'accueil"><i class="fas fa-home"></i></a>
            <a class="navbar-brand fw-bold text-success m-0" href="../index.php">Agriprice Direct</a>
        </div>
    </div>
</nav>

<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-5 fw-bold">Transport & Logistique Agricole</h1>
        <p class="lead">Accédez aux coordonnées des transporteurs vérifiés.</p>
    </div>
</section>

<div class="container my-5">
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-5">
        <div class="col-lg-4">
            <div class="form-container">
                <h4 class="fw-bold mb-3"><i class="fas fa-id-card text-success me-2"></i>Devenir Transporteur</h4>
                <form action="../api/register_transport.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nom & Prénom</label>
                        <div class="d-flex gap-2">
                            <input type="text" name="nom" class="form-control" placeholder="Nom" required>
                            <input type="text" name="prenom" class="form-control" placeholder="Prénom" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Numéro WhatsApp</label>
                        <input type="text" name="telephone" class="form-control" placeholder="+225..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Type de Véhicule</label>
                        <select class="form-select" name="vehicule" required>
                            <option value="Camion 10 tonnes">Camion 10 tonnes</option>
                            <option value="Camion 30 tonnes">Camion 30 tonnes</option>
                            <option value="Tricycle Agricole">Tricycle Agricole</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ville de base</label>
                        <input type="text" name="ville" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Capacité</label>
                        <input type="text" name="capacite" class="form-control" placeholder="Ex: 15 Tonnes" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Justificatif (PDF/Image)</label>
                        <input type="file" name="document_camion" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 mt-2">Envoyer pour vérification</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <h4 class="fw-bold mb-4">Véhicules Vérifiés Disponibles</h4>
            <div class="row g-3">
                <?php if(empty($transporteurs)): ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4 border">
                        <p class="text-muted">Aucun transporteur disponible pour le moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($transporteurs as $t): ?>
                    <div class="col-md-6">
                        <div class="card card-transport h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($t['nom']) ?> <?= htmlspecialchars($t['prenom']) ?></h6>
                                </div>
                                <span class="badge-price">5000 FCFA</span>
                            </div>
                            
                            <p class="small mb-1 text-muted"><i class="fas fa-map-marker-alt me-1"></i> <strong>Zone :</strong> <?= htmlspecialchars($t['ville']) ?></p>
                            <p class="small mb-3 text-muted"><i class="fas fa-truck me-1"></i> <strong>Véhicule :</strong> <?= htmlspecialchars($t['vehicule']) ?> (<?= htmlspecialchars($t['capacite']) ?>)</p>

                            <?php 
                            $has_access = false;
                            if($user_id) {
                                $check = $pdo->prepare("SELECT id FROM transactions WHERE user_id = ? AND target_id = ? AND type = 'transport' AND status = 'valide'");
                                $check->execute([$user_id, $t['id']]);
                                $has_access = $check->fetch();
                            }
                            ?>

                            <?php if($has_access): ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $t['telephone']) ?>" target="_blank" class="btn btn-whatsapp w-100 btn-sm py-2">
                                    <i class="fab fa-whatsapp me-2"></i>Contacter : <?= htmlspecialchars($t['telephone']) ?>
                                </a>
                            <?php else: ?>
                                <button onclick="demarrerPaiement(<?= $t['id'] ?>, '<?= addslashes($t['nom']) ?>')" class="btn btn-pay w-100 btn-sm py-2">
                                    <i class="fas fa-lock me-2"></i>Débloquer le contact
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function demarrerPaiement(id, nom) {
    if(!<?= $user_id ? 'true' : 'false' ?>) {
        alert("Veuillez vous connecter pour effectuer un paiement.");
        window.location.href = "../auth.php";
        return;
    }

    // MODIFICATION 2 : La boîte de dialogue affiche 5000
    if(confirm("Voulez-vous payer 5000 FCFA pour débloquer le contact de " + nom + " ?")) {
        window.location.href = "../api/initier_paiement.php?type=transport&id=" + id;
    }
}
</script>

</body>
</html>