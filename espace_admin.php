<?php
session_start();
require_once 'api/db.php'; 

// Sécurité : Seul l'admin peut accéder à cette page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// --- LOGIQUE DE RÉCUPÉRATION DES DONNÉES PAR BLOC ---

// 1. BLOC 1 : Mise en relation (Dashboard / Premium)
try {
    $q1 = $pdo->prepare("SELECT t.*, u.nom, u_target.nom as cible_nom 
                         FROM transactions t 
                         JOIN users u ON t.user_id = u.id 
                         LEFT JOIN users u_target ON t.target_id = u_target.id
                         WHERE (t.type = 'mise_en_relation' OR t.type = 'premium') 
                         AND t.status = 'en_attente'");
    $q1->execute();
    $attentes_dashboard = $q1->fetchAll();
} catch (Exception $e) { $attentes_dashboard = []; }

// 2. BLOC 2 : Inscriptions Transporteurs
try {
    $q2 = $pdo->prepare("SELECT * FROM transporteurs_externes WHERE statut = 'en_attente'");
    $q2->execute();
    $dossiers_trans = $q2->fetchAll();
} catch (Exception $e) { $dossiers_trans = []; }

// 3. BLOC 3 : Déblocage WhatsApp (Transport routier)
try {
    $q3 = $pdo->prepare("SELECT t.*, u.nom as client_nom, tr.nom as trans_nom 
                         FROM transactions t 
                         JOIN users u ON t.user_id = u.id 
                         LEFT JOIN transporteurs_externes tr ON t.target_id = tr.id
                         WHERE t.type = 'transport' AND t.status = 'en_attente'");
    $q3->execute();
    $attentes_whatsapp = $q3->fetchAll();
} catch (Exception $e) { $attentes_whatsapp = []; }

// 4. BLOC 4 : Gestion des Machines & Commissions (2000F)
try {
    $q4a = $pdo->query("SELECT e.*, u.nom as vendeur_nom FROM engins_agricoles e JOIN users u ON e.user_id = u.id ORDER BY e.id DESC");
    $engins_liste = $q4a->fetchAll();

    $q4b = $pdo->query("SELECT t.*, u.nom as client_nom, e.nom_engin 
                        FROM transactions t 
                        JOIN users u ON t.user_id = u.id 
                        LEFT JOIN engins_agricoles e ON t.target_id = e.id 
                        WHERE t.type = 'location' AND t.status = 'en_attente'");
    $commissions_attente = $q4b->fetchAll();
} catch (Exception $e) { $engins_liste = []; $commissions_attente = []; }

// 5. BLOC 5 : Publicités & Événements (5 000F)
try {
    // CORRECTION : Sélection de date_transaction
    $q5 = $pdo->prepare("SELECT t.id, t.date_transaction, u.nom as client_nom, e.titre as event_titre 
                         FROM transactions t 
                         JOIN users u ON t.user_id = u.id 
                         JOIN evenements_agricoles e ON t.target_id = e.id 
                         WHERE t.type = 'publicite_evenement' AND t.status = 'en_attente'");
    $q5->execute();
    $publicites_attente = $q5->fetchAll();
} catch (Exception $e) { $publicites_attente = []; }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Agriprice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card-header { border-radius: 15px 15px 0 0 !important; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; }
        .table thead th { border-top: none; background: #fafafa; font-size: 0.75rem; color: #666; }
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; margin: 0 2px; }
        .btn-back { background-color: #fff; color: #27ae60; border: 1px solid #27ae60; transition: 0.3s; }
        .btn-back:hover { background-color: #27ae60; color: #fff; }
        .img-preview { width: 45px; height: 35px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body class="py-5">

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div class="d-flex align-items-center gap-3">
            <a href="index.php" class="btn btn-back rounded-circle shadow-sm" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="fw-bold m-0"><i class="fas fa-shield-alt text-success me-2"></i>Administration</h2>
                <p class="text-muted small mb-0">Gestion des flux Agriprice</p>
            </div>
        </div>
        <a href="api/logout.php" class="btn btn-outline-danger btn-sm px-4 rounded-pill fw-bold">Déconnexion</a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card h-100 border-top border-dark border-4">
                <div class="card-header bg-dark text-white py-3">Accès Dashboard</div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            <?php foreach($attentes_dashboard as $d): ?>
                            <tr>
                                <td class="ps-3"><strong><?= $d['nom'] ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= $d['type'] ?></span></td>
                                <td class="text-end pe-3">
                                    <a href="api/valider_paiement.php?id=<?= $d['id'] ?>" class="btn btn-success btn-action"><i class="fas fa-check"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100 border-top border-primary border-4">
                <div class="card-header bg-primary text-white py-3">Dossiers Transporteurs</div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            <?php foreach($dossiers_trans as $dt): ?>
                            <tr>
                                <td class="ps-3"><strong><?= $dt['nom'] ?></strong></td>
                                <td class="text-end pe-3">
                                    <a href="api/valider_transport_externe.php?id=<?= $dt['id'] ?>" class="btn btn-success btn-action"><i class="fas fa-check"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100 border-top border-warning border-4">
                <div class="card-header bg-warning text-dark py-3">Paiements WhatsApp</div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            <?php foreach($attentes_whatsapp as $aw): ?>
                            <tr>
                                <td class="ps-3"><strong><?= $aw['client_nom'] ?></strong></td>
                                <td class="text-end pe-3">
                                    <a href="api/valider_paiement.php?id=<?= $aw['id'] ?>" class="btn btn-success btn-action"><i class="fas fa-check"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card border-top border-info border-4 shadow-sm">
                <div class="card-header bg-info text-white py-3">
                    <i class="fas fa-bullhorn me-2"></i> Publicités & Événements à Valider (5 000F)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Organisateur</th>
                                    <th>Événement</th>
                                    <th>Montant</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($publicites_attente as $p): ?>
                                <tr>
                                    <td class="ps-3 small text-muted">
                                        <?= !empty($p['date_transaction']) ? date('d/m/y H:i', strtotime($p['date_transaction'])) : 'Inconnue' ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($p['client_nom']) ?></strong></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['event_titre']) ?></span></td>
                                    <td><b class="text-success">5 000 F</b></td>
                                    <td class="text-end pe-3">
                                        <a href="api/valider_paiement.php?id=<?= $p['id'] ?>" 
                                           class="btn btn-success btn-action" 
                                           onclick="return confirm('Valider les 5000F et publier ?')">
                                             <i class="fas fa-check"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($publicites_attente)): ?>
                                    <tr><td colspan="5" class="text-center py-3 text-muted small">Aucune publicité en attente.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card border-top border-success border-4 shadow-sm">
                <div class="card-header bg-success text-white py-3">
                    <i class="fas fa-tractor me-2"></i> Commissions & Modération Machinerie
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 border-end">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-money-bill-wave me-2"></i>Commissions (2 000F)</h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead><tr><th>Client</th><th>Machine</th><th>Action</th></tr></thead>
                                    <tbody>
                                        <?php foreach($commissions_attente as $c): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($c['client_nom']) ?></strong></td>
                                            <td class="small"><?= htmlspecialchars($c['nom_engin']) ?></td>
                                            <td><a href="api/valider_paiement.php?id=<?= $c['id'] ?>" class="btn btn-success btn-action"><i class="fas fa-check"></i></a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <h6 class="fw-bold mb-3"><i class="fas fa-list me-2"></i>Annonces en ligne</h6>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm align-middle table-hover">
                                    <thead class="table-light"><tr><th>Image</th><th>Machine</th><th>Vendeur</th><th class="text-end">Action</th></tr></thead>
                                    <tbody>
                                        <?php foreach($engins_liste as $e): ?>
                                        <tr>
                                            <td><img src="uploads/engins/<?= $e['photo'] ?>" class="img-preview"></td>
                                            <td>
                                                <div class="fw-bold small"><?= htmlspecialchars($e['nom_engin']) ?></div>
                                                <div class="text-success small"><?= number_format($e['prix'], 0, ',', ' ') ?> F</div>
                                            </td>
                                            <td class="small"><?= htmlspecialchars($e['vendeur_nom']) ?></td>
                                            <td class="text-end">
                                                <a href="api/supprimer_engin.php?id=<?= $e['id'] ?>" class="btn btn-outline-danger btn-action" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>