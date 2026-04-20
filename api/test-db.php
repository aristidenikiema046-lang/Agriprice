<?php
include 'api/db.php';
if ($pdo) {
    echo "✅ La connexion à Clever Cloud fonctionne parfaitement !";
} else {
    echo "❌ Échec de la connexion.";
}
?>