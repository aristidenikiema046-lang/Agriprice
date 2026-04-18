<?php
session_start();
require_once 'db.php';

$type = $_GET['type'] ?? 'transport'; 
$id = $_GET['id'] ?? 0; // ID du producteur ou transporteur à débloquer

// Configuration pour les autres types (Transport / Location)
$is_mise_en_relation = ($type === 'mise_en_relation');

// MODIFICATION DU PRIX : 5000 pour transport, 2000 pour location
$montant_defaut = ($type === 'location') ? 2000 : 5000;

// LOGIQUE DE REDIRECTION DYNAMIQUE
$retour_url = ($type === 'location') ? "../location.php" : "../page/transport.php";
$retour_texte = ($type === 'location') ? "Retour aux machines" : "Retour aux transporteurs";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriprice - Choisir votre offre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .pricing-card { border: none; border-radius: 20px; transition: transform 0.3s; }
        .pricing-card:hover { transform: scale(1.02); }
        .admin-box { background-color: #f1f8f5; border-radius: 15px; border: 1px dashed #27ae60; padding: 15px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Finalisation du paiement</h1>
        <p class="lead text-muted">Choisissez l'option qui vous convient pour continuer.</p>
    </div>

    <div class="row justify-content-center g-4">
        <?php if ($is_mise_en_relation): ?>
            <div class="col-md-5">
                <div class="card pricing-card shadow-lg h-100 border-top border-5 border-warning">
                    <div class="card-body text-center p-4">
                        <div class="display-6 mb-3 text-warning"><i class="fas fa-address-card"></i></div>
                        <h3 class="fw-bold">Carte Unique</h3>
                        <p class="text-muted">Débloquez les coordonnées de ce producteur uniquement.</p>
                        <h2 class="my-4">5 000 <small class="fs-6">FCFA</small></h2>
                        <button onclick="openPayment('mise_en_relation', 5000)" class="btn btn-warning w-100 rounded-pill py-2 fw-bold shadow-sm">Choisir cette option</button>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card pricing-card shadow-lg h-100 border-top border-5 border-success">
                    <div class="card-body text-center p-4">
                        <div class="display-6 mb-3 text-success"><i class="fas fa-crown"></i></div>
                        <h3 class="fw-bold">Accès Illimité</h3>
                        <p class="text-muted">Débloquez tous les contacts de la plateforme sans limite.</p>
                        <h2 class="my-4">15 000 <small class="fs-6">FCFA</small></h2>
                        <button onclick="openPayment('premium', 15000)" class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow-sm">Devenir Premium</button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-5">
                <div class="card pricing-card shadow-lg p-4 border-top border-5 border-primary">
                    <div class="card-body text-center">
                        <div class="display-6 mb-3 text-primary">
                            <i class="<?= ($type === 'transport') ? 'fas fa-truck' : 'fas fa-tools' ?>"></i>
                        </div>
                        <h3 class="fw-bold"><?= ($type === 'transport') ? 'Service Transport' : 'Service Machinerie' ?></h3>
                        <p class="text-muted">Frais de déblocage du contact vérifié.</p>
                        <h2 class="my-4"><?= number_format($montant_defaut, 0, ',', ' ') ?> <small class="fs-6">FCFA</small></h2>
                        <button onclick="openPayment('<?= $type ?>', <?= $montant_defaut ?>)" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">Payer maintenant</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="<?= $retour_url ?>" class="text-secondary text-decoration-none small">
            <i class="fas fa-arrow-left me-1"></i> <?= $retour_texte ?>
        </a>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Instructions de transfert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted">Montant à transférer : <strong id="modalAmount" class="text-dark"></strong> FCFA</p>
                <div class="admin-box mb-4">
                    <span class="text-uppercase small fw-bold text-success">Numéro de l'Administrateur</span>
                    <h3 class="fw-bold mt-2 mb-0">07 57 61 02 31</h3>
                </div>
                <div class="alert alert-warning small mb-4 text-start">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Effectuez le transfert via Orange, MTN, Moov ou Wave sur le numéro ci-dessus, puis cliquez sur le bouton de confirmation.
                </div>
                <form action="process_payment.php" method="GET">
                    <input type="hidden" name="type" id="formType">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow">J'ai effectué le paiement</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openPayment(type, amount) {
    document.getElementById('modalAmount').innerText = amount.toLocaleString();
    document.getElementById('formType').value = type;
    var myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    myModal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>