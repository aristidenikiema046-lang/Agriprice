<?php
session_start();
// 1. On remonte d'un cran pour trouver le dossier api
// Remplace la ligne 4 par celle-ci :
require_once __DIR__ . '/../api/db.php';

// Récupération des événements validés
$stmt = $pdo->query("SELECT * FROM evenements_agricoles WHERE statut = 'valide' ORDER BY date_evenement ASC");
$evenements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Agricole - Agriprice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --agri-green: #27ae60; --agri-dark: #2c3e50; }
        body { background-color: #f8f9fa; }
        .event-card { border: none; border-radius: 20px; transition: 0.3s; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); background: white; }
        .event-card:hover { transform: translateY(-10px); box-shadow: 0 12px 25px rgba(0,0,0,0.15); }
        .event-img { height: 220px; object-fit: cover; width: 100%; }
        .date-badge { position: absolute; top: 15px; left: 15px; background: white; padding: 8px 15px; border-radius: 10px; text-align: center; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 10; }
        .btn-whatsapp { background-color: #25D366; color: white; border-radius: 50px; font-weight: 600; border: none; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold mb-0">📅 Agenda <span style="color: var(--agri-green);">Agricole</span></h1>
            <p class="text-muted">Découvrez les foires et formations agricoles.</p>
        </div>
        <a href="publicite.php" class="btn btn-dark rounded-pill px-4">
            <i class="fas fa-plus-circle me-2"></i> Publier un événement
        </a>
    </div>

    <div class="row g-4">
        <?php if (count($evenements) > 0): ?>
            <?php foreach ($evenements as $evt): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card event-card h-100 position-relative">
                        <div class="date-badge text-uppercase">
                            <small class="d-block text-success"><?= date('M', strtotime($evt['date_evenement'])) ?></small>
                            <span class="fs-4"><?= date('d', strtotime($evt['date_evenement'])) ?></span>
                        </div>
                        
                        <img src="../uploads/events/<?= htmlspecialchars($evt['photo']) ?>" class="event-img" alt="Affiche">
                        
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-2 small text-muted">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i> 
                                <?= htmlspecialchars($evt['lieu']) ?>
                            </div>
                            <h5 class="fw-bold mb-3"><?= htmlspecialchars($evt['titre']) ?></h5>
                            <p class="text-muted small"><?= nl2br(htmlspecialchars(substr($evt['description'], 0, 100))) ?>...</p>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <span class="fw-bold text-success fs-5">
                                    <?= $evt['prix_ticket'] > 0 ? number_format($evt['prix_ticket'], 0, '.', ' ') . ' F' : 'GRATUIT' ?>
                                </span>
                                <a href="https://wa.me/<?= str_replace(' ', '', $evt['contact_info']) ?>?text=Infos sur l'événement : <?= urlencode($evt['titre']) ?>" 
                                   class="btn btn-whatsapp px-4" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i> Info
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h3>Aucun événement pour le moment</h3>
                <a href="publicite.php" class="btn btn-success rounded-pill mt-3">Soyez le premier à publier !</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>