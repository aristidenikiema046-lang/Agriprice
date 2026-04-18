<?php
session_start();

// 1. Erreurs pour le débug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Inclusion de la BDD (Même dossier, donc juste le nom du fichier)
require_once 'db.php'; 

// 3. Sécurité : Vérifier le rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // On remonte d'un cran (../) pour sortir de api/ et aller dans page/
    header('Location: ../espace_admin.php?msg=Paiement validé !');
    exit();
}

// ... le reste de ton code ...
// 4. Récupération des transactions (Utilisation de $pdo)
$query = $pdo->query("SELECT t.*, u.nom as nom_utilisateur 
                      FROM transactions t 
                      JOIN users u ON t.user_id = u.id 
                      WHERE t.status = 'en_attente' 
                      ORDER BY t.date_transaction DESC");
$transactions = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Agriprice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Paiements à valider</h1>
        <a href="page/dashboard.php" class="btn btn-outline-primary">Retour Dashboard</a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0" style="border-radius: 15px;">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Utilisateur</th>
                        <th>Offre</th>
                        <th>ID Cible</th>
                        <th>Date</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="ps-3"><strong><?php echo htmlspecialchars($t['nom_utilisateur']); ?></strong></td>
                                <td>
                                    <?php if($t['type'] == 'premium'): ?>
                                        <span class="badge bg-warning text-dark">👑 Premium</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">🔑 Unique</span>
                                    <?php endif; ?>
                                </td>
                                <td>#<?php echo $t['target_id'] ?? 'N/A'; ?></td>
                                <td><?php echo $t['date_transaction']; ?></td>
                                <td class="text-end pe-3">
                                    <a href="admin_valider.php?action=valider&id=<?php echo $t['id']; ?>" 
                                       class="btn btn-success btn-sm" onclick="return confirm('Valider ce paiement ?')">Valider</a>
                                    <a href="admin_valider.php?action=rejete&id=<?php echo $t['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm">Rejeter</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">Aucun paiement en attente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>