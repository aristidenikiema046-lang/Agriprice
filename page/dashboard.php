<?php
// Configuration pour que la session soit reconnue partout sur le site
session_set_cookie_params(['path' => '/']);
session_start();

// Protection de la page : si pas de session, retour à l'accueil
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Connexion BDD (On utilise $db comme dans ton code original)
// Connexion BDD via le fichier centralisé
require_once __DIR__ . '/../api/db.php';

// On s'assure que la variable s'appelle $db comme dans le reste de ton fichier
$db = $pdo;

$user_role = $_SESSION['role'];
$user_id_connected = $_SESSION['user_id'];
$target_role = ($user_role == 'producteur') ? 'acheteur' : 'producteur';

// Récupérer les membres du rôle opposé
$query = $db->prepare("SELECT id, nom, email, telephone, ville, produit, quantite FROM users WHERE role = ?");
$query->execute([$target_role]);
$utilisateurs = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Agriprice</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-card .card { transition: transform 0.2s; border-radius: 15px; overflow: hidden; }
        .user-card .card:hover { transform: translateY(-5px); }
        .contact-box { background: #f0fdf4; border: 1px solid #dcfce7; padding: 12px; border-radius: 12px; }
        .label-contact { font-size: 0.75rem; color: #166534; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .info-value { font-weight: bold; color: #14532d; display: block; margin-bottom: 8px; }
        .admin-banner { background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; border-radius: 15px; padding: 15px; }
    </style>
</head>
<body style="background: #f8f9fa;">
    <div class="container mt-4">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i>Accueil
            </a>
            <h2 class="mb-0 h4 fw-bold">Agriprice Dashboard</h2>
        </div>

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <span class="badge bg-success">Mode : <?php echo ucfirst($user_role); ?></span>
            <span class="text-muted small">ID : #<?php echo $user_id_connected; ?></span>
        </div>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="admin-banner mb-4 d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <i class="fas fa-user-shield me-2"></i>
                <strong>Session Administrateur</strong> 
            </div>
            <a href="../espace_admin.php" class="btn btn-primary btn-sm fw-bold">
                <i class="fas fa-lock-open me-2"></i>Gérer le site
            </a>
        </div>
        <?php endif; ?>
        
        <h5 class="mb-4">Trouver des <?php echo $target_role; ?>s</h5>

        <div class="filter-bar bg-white p-4 shadow-sm mb-5" style="border-radius: 15px;">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Produit</label>
                    <input type="text" id="filterProduit" class="form-control" placeholder="Cacao, Maïs...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Ville</label>
                    <input type="text" id="filterVille" class="form-control" placeholder="Localisation...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Stock Min (kg)</label>
                    <input type="number" id="filterQuantite" class="form-control" placeholder="0">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" onclick="applyFilters()" class="btn btn-success w-100 fw-bold">
                        <i class="fas fa-search me-2"></i>Filtrer
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($utilisateurs as $user): ?>
                <?php 
                    $producer_id = $user['id']; 
                    // On vérifie si une transaction VALIDÉE existe pour ce contact précis ou si l'user est Premium
                    $check = $db->prepare("SELECT id FROM transactions 
                                           WHERE user_id = ? 
                                           AND status = 'valide' 
                                           AND (type = 'premium' OR (type = 'mise_en_relation' AND target_id = ?))");
                    $check->execute([$user_id_connected, $producer_id]);
                    $is_unlocked = $check->fetch();
                ?>
                <div class="col-md-4 mb-4 user-card" 
                     data-produit="<?php echo strtolower($user['produit'] ?? ''); ?>" 
                     data-ville="<?php echo strtolower($user['ville'] ?? ''); ?>" 
                     data-quantite="<?php echo (int)$user['quantite']; ?>">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <span class="badge bg-light text-success mb-2"><?php echo htmlspecialchars($user['produit'] ?: 'Divers'); ?></span>
                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($user['nom']); ?></h5>
                            <p class="small text-muted mb-3">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($user['ville']); ?> | 
                                <strong><?php echo (int)$user['quantite']; ?> kg</strong>
                            </p>
                            
                            <?php if ($is_unlocked): ?>
                                <div class="contact-box">
                                    <?php if(!empty($user['telephone'])): ?>
                                        <span class="label-contact">Téléphone / WhatsApp</span>
                                        <span class="info-value"><?php echo htmlspecialchars($user['telephone']); ?></span>
                                    <?php endif; ?>

                                    <span class="label-contact">Email</span>
                                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>

                                    <?php if(!empty($user['telephone'])): ?>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $user['telephone']); ?>" target="_blank" class="btn btn-success btn-sm w-100 rounded-pill mt-2">
                                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-light p-3 rounded text-center mb-2">
                                    <i class="fas fa-lock text-muted mb-2 d-block"></i>
                                    <span class="text-muted small">Coordonnées masquées</span>
                                </div>
                                <a href="../api/initier_paiement.php?type=mise_en_relation&id=<?php echo $producer_id; ?>" 
                                   class="btn btn-warning w-100 rounded-pill btn-sm fw-bold">
                                     Débloquer
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<script>
function applyFilters() {
    const valProduit = document.getElementById('filterProduit').value.toLowerCase().trim();
    const valVille = document.getElementById('filterVille').value.toLowerCase().trim();
    const valQuantite = parseInt(document.getElementById('filterQuantite').value) || 0;

    document.querySelectorAll('.user-card').forEach(card => {
        const p = card.getAttribute('data-produit');
        const v = card.getAttribute('data-ville');
        const q = parseInt(card.getAttribute('data-quantite'));

        const match = (valProduit === "" || p.includes(valProduit)) &&
                      (valVille === "" || v.includes(valVille)) &&
                      (q >= valQuantite);

        card.style.display = match ? "block" : "none";
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>