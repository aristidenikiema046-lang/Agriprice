<?php
session_start();
require_once '../api/db.php';

// On récupère l'ID de l'utilisateur connecté pour vérifier s'il peut supprimer ses propres événements
$user_id = $_SESSION['user_id'] ?? null;

// Récupération des événements validés
try {
    $stmt = $pdo->prepare("SELECT * FROM evenements_agricoles WHERE statut = 'valide' ORDER BY date_evenement ASC");
    $stmt->execute();
    $evenements = $stmt->fetchAll();
} catch (Exception $e) { 
    $evenements = []; 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Agricole | Agriprice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --agri-green: #27ae60; 
            --agri-green-dark: #1e8449;
            --agri-orange: #f39c12;
            --light-bg: #f4f9f4;
        }
        
        body { background-color: var(--light-bg); font-family: 'Inter', sans-serif; color: #2d3436; }
        
        /* Header Vert Agriprice */
        .page-header {
            background: linear-gradient(135deg, var(--agri-green-dark) 0%, var(--agri-green) 100%);
            padding: 80px 0;
            color: white;
            border-bottom: 6px solid var(--agri-orange);
            border-radius: 0 0 50px 50px;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        /* Bouton Retour Accueil */
        .btn-home {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 10px 18px;
            border-radius: 12px;
            text-decoration: none;
            backdrop-filter: blur(5px);
            transition: 0.3s;
            font-weight: 600;
        }
        .btn-home:hover { background: white; color: var(--agri-green-dark); }

        /* Bannière d'action */
        .action-banner {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(39, 174, 96, 0.1);
            margin-top: -70px;
            border: 1px solid rgba(39, 174, 96, 0.2);
        }

        /* Cartes Agenda */
        .event-card {
            border: none;
            border-radius: 25px;
            background: white;
            transition: 0.4s;
            height: 100%;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .event-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        
        .event-img-container { position: relative; height: 240px; }
        .event-img { height: 100%; width: 100%; object-fit: cover; }
        
        /* Badge Date Orange */
        .event-date-badge {
            position: absolute; top: 15px; left: 15px;
            background: var(--agri-orange);
            color: white;
            padding: 10px; border-radius: 18px;
            text-align: center; min-width: 65px;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.4);
        }
        .event-date-day { font-weight: 900; font-size: 1.4rem; display: block; line-height: 1; }
        .event-date-month { font-size: 0.8rem; text-transform: uppercase; font-weight: 700; }

        /* Boutons */
        .btn-publish { 
            background: var(--agri-green); border: none; color: white; 
            padding: 15px 30px; border-radius: 20px; font-weight: 800;
            box-shadow: 0 10px 20px rgba(39, 174, 96, 0.3);
        }
        .btn-publish:hover { background: var(--agri-green-dark); color: white; transform: scale(1.05); }

        .btn-contact {
            background: var(--agri-orange); color: white;
            border-radius: 15px; font-weight: 700; padding: 8px 20px;
            border: none; transition: 0.3s; text-decoration: none;
        }
        .btn-contact:hover { background: #d35400; color: white; }

        .btn-delete-event {
            background: #ff4757; color: white;
            border-radius: 15px; padding: 8px 15px;
            border: none; transition: 0.3s;
        }
        .btn-delete-event:hover { background: #ff6b81; transform: scale(1.1); }

        .form-control { border-radius: 15px; border: 2px solid #edf2f7; background: #f8fafc; }
        .form-control:focus { border-color: var(--agri-green); background: white; box-shadow: none; }
    </style>
</head>
<body>

<header class="page-header">
    <a href="../index.php" class="btn-home shadow-sm">
        <i class="fas fa-home me-2"></i> Accueil
    </a>
    <div class="container">
        <h1 class="fw-bold display-4 text-white">Agenda Agricole</h1>
        <p class="lead text-white opacity-90">Ne manquez plus aucun événement majeur du secteur.</p>
    </div>
</header>

<div class="container">
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 text-center">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="action-banner d-flex flex-wrap align-items-center justify-content-between mb-5">
        <div class="mb-3 mb-md-0">
            <h3 class="fw-bold mb-1 text-success">Promouvez votre événement</h3>
            <p class="text-muted mb-0">Visibilité garantie auprès de milliers d'acteurs pour <span class="fw-bold text-dark">5 000 F</span>.</p>
        </div>
        <button class="btn btn-publish" data-bs-toggle="modal" data-bs-target="#eventModal">
            <i class="fas fa-plus-circle me-2"></i>Publier mon événement
        </button>
    </div>

    <div class="mb-4 d-flex align-items-center">
        <div style="width: 50px; height: 5px; background: var(--agri-orange); border-radius: 10px; margin-right: 15px;"></div>
        <h2 class="fw-bold mb-0">Calendrier des activités</h2>
    </div>

    <div class="row g-4 mb-5">
        <?php if(empty($evenements)): ?>
            <div class="col-12 text-center py-5 bg-white rounded-5 shadow-sm border border-dashed">
                <i class="fas fa-calendar-alt fa-4x text-light mb-3"></i>
                <p class="text-muted fs-5">Aucun événement n'est programmé pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach($evenements as $ev): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="event-card shadow-sm">
                        <div class="event-img-container">
                            <div class="event-date-badge">
                                <span class="event-date-day"><?= date('d', strtotime($ev['date_evenement'])) ?></span>
                                <span class="event-date-month"><?= date('M', strtotime($ev['date_evenement'])) ?></span>
                            </div>
                            <img src="../uploads/events/<?= htmlspecialchars($ev['photo']) ?>" class="event-img" alt="Affiche">
                        </div>
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($ev['titre']) ?></h5>
                            <div class="text-success small fw-bold mb-3">
                                <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($ev['lieu']) ?>
                            </div>
                            <p class="text-secondary small mb-4" style="height: 40px; overflow: hidden;">
                                <?= htmlspecialchars($ev['description']) ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <?php if($user_id && $user_id == $ev['user_id']): ?>
                                    <a href="../api/supprimer_evenement.php?id=<?= $ev['id'] ?>" 
                                       class="btn-delete-event" 
                                       onclick="return confirm('Voulez-vous vraiment supprimer cet événement ?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="fw-bold text-dark"><?= $ev['prix_ticket'] > 0 ? number_format($ev['prix_ticket'], 0, ',', ' ')." F" : "Entrée Libre" ?></span>
                                <?php endif; ?>

                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $ev['contact_info']) ?>" class="btn btn-contact">
                                    <i class="fab fa-whatsapp me-1"></i> Infos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../api/publier_evenement.php" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 30px;">
            <div class="modal-header bg-success text-white border-0 p-4">
                <h4 class="fw-bold mb-0">Détails de l'événement</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-4">
                <div class="mb-3">
                    <label class="small fw-bold mb-1">Nom de l'événement</label>
                    <input type="text" name="titre" class="form-control" placeholder="Ex: Salon de l'Agriculture" required>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="small fw-bold mb-1">Date</label>
                        <input type="date" name="date_evenement" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="small fw-bold mb-1">Lieu</label>
                        <input type="text" name="lieu" class="form-control" placeholder="Ville" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold mb-1">WhatsApp de contact</label>
                    <input type="text" name="contact_info" class="form-control" placeholder="07XXXXXXXX" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold mb-1">Image de l'affiche</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-0">
                    <label class="small fw-bold mb-1">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Heure, programme, etc..."></textarea>
                </div>
                <input type="hidden" name="prix_ticket" value="0">
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-publish w-100 py-3">Soumettre pour 5 000 F</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>