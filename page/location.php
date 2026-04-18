<?php
session_start();
require_once '../api/db.php';
$user_id = $_SESSION['user_id'] ?? null;

// 1. Récupération des machines
try {
    $machines = $pdo->query("SELECT * FROM engins_agricoles ORDER BY id DESC")->fetchAll();
    
    $acces_payes = [];
    $acces_en_attente = [];
    
    if ($user_id) {
        // Accès déjà validés (statut 'complete')
        $q_v = $pdo->prepare("SELECT target_id FROM transactions WHERE user_id = ? AND type = 'location' AND status = 'complete'");
        $q_v->execute([$user_id]);
        $acces_payes = $q_v->fetchAll(PDO::FETCH_COLUMN);

        // Accès envoyés mais en attente de validation admin
        $q_a = $pdo->prepare("SELECT target_id FROM transactions WHERE user_id = ? AND type = 'location' AND status = 'en_attente'");
        $q_a->execute([$user_id]);
        $acces_en_attente = $q_a->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (Exception $e) {
    $machines = [];
    $acces_payes = [];
    $acces_en_attente = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marché Agricole - Agriprice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .navbar-custom { background-color: #27ae60; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card-machine { border: none; border-radius: 15px; transition: transform 0.3s ease; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card-machine:hover { transform: translateY(-5px); }
        .img-container { position: relative; height: 200px; }
        .img-machine { width: 100%; height: 100%; object-fit: cover; }
        .badge-price { position: absolute; bottom: 10px; right: 10px; background: rgba(255, 255, 255, 0.9); color: #27ae60; padding: 5px 12px; border-radius: 50px; font-weight: 800; }
        .btn-buy { background: #27ae60; color: white; border: none; border-radius: 10px; padding: 10px; font-weight: 600; transition: 0.2s; }
        .btn-buy:hover { background: #219150; color: white; }
        .btn-unlocked { background: #25D366; color: white; border: none; border-radius: 10px; padding: 10px; font-weight: 600; text-decoration: none; display: block; text-align: center; }
        .btn-waiting { background: #6c757d; color: white; border: none; border-radius: 10px; padding: 10px; cursor: not-allowed; text-align: center; width: 100%; opacity: 0.8; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php"><i class="fas fa-tractor me-2"></i>Agriprice Machinerie</a>
        <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#postModal">
            <i class="fas fa-plus-circle me-1"></i> Publier une annonce
        </button>
    </div>
</nav>

<div class="container mb-5">
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">Location & Vente d'engins</h2>
        <span class="badge bg-dark rounded-pill"><?= count($machines) ?> annonces</span>
    </div>

    <div class="row g-4">
        <?php foreach($machines as $m): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card card-machine h-100">
                <div class="img-container">
                    <img src="../uploads/engins/<?= $m['photo'] ?>" class="img-machine" alt="<?= htmlspecialchars($m['nom_engin']) ?>">
                    <div class="badge-price"><?= number_format($m['prix'], 0, ',', ' ') ?> F</div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge <?= $m['categorie'] == 'Vente' ? 'bg-primary' : 'bg-warning text-dark' ?> me-2">
                            <?= strtoupper($m['categorie']) ?>
                        </span>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($m['ville']) ?></small>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($m['nom_engin']) ?></h5>
                    <p class="text-muted small mb-3 text-truncate"><?= htmlspecialchars($m['description']) ?></p>
                    
                    <?php if (in_array($m['id'], $acces_payes)): ?>
                        <a href="https://wa.me/<?= str_replace(' ', '', $m['contact_vendeur']) ?>?text=Bonjour, je vous contacte via Agriprice pour votre annonce : <?= urlencode($m['nom_engin']) ?>" target="_blank" class="btn btn-unlocked w-100">
                            <i class="fab fa-whatsapp me-2"></i> Contacter le vendeur
                        </a>
                    <?php elseif (in_array($m['id'], $acces_en_attente)): ?>
                        <button class="btn btn-waiting" disabled>
                            <i class="fas fa-clock me-2"></i> Vérification en cours...
                        </button>
                    <?php else: ?>
                        <button onclick="payerCom(<?= $m['id'] ?>, '<?= addslashes($m['nom_engin']) ?>')" class="btn btn-buy w-100">
                            <i class="fas fa-lock me-2"></i> Voir le contact (2000F)
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="postModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../api/publier_engin.php" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Publier mon annonce</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-modal="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nom de la machine</label>
                    <input type="text" name="nom_engin" class="form-control" placeholder="Ex: Tracteur Kubota M7" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold small">Type d'annonce</label>
                        <select name="categorie" class="form-select">
                            <option value="Vente">Vendre</option>
                            <option value="Location">Louer</option>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold small">Prix (FCFA)</label>
                        <input type="number" name="prix" class="form-control" placeholder="Montant" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Contact WhatsApp</label>
                    <input type="text" name="contact_vendeur" class="form-control" placeholder="Ex: 22507XXXXXXXX" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Localisation (Ville)</label>
                    <input type="text" name="ville" class="form-control" placeholder="Où se trouve la machine ?" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Photo de l'engin</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold rounded-3">Mettre en ligne gratuitement</button>
            </div>
        </form>
    </div>
</div>

<script>
function payerCom(id, nom) {
    if(!<?= $user_id ? 'true' : 'false' ?>) {
        alert("Veuillez vous connecter pour débloquer le contact.");
        window.location.href = "../auth.php";
        return;
    }
    if(confirm("Pour obtenir le numéro WhatsApp du propriétaire de '" + nom + "', transférez 2000F au 07 57 61 02 31. Confirmer l'envoi ?")) {
        window.location.href = "../api/initier_paiement.php?type=location&id=" + id;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>