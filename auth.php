<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion / Inscription - Agriprice</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-card { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .auth-header { text-align: center; margin-bottom: 1.5rem; }
        .auth-header i { font-size: 2.5rem; margin-bottom: 0.5rem; display: block; }
    </style>
</head>
<body style="background: #f4f7f6; padding-top: 50px;">
<div class="container">
    <div class="text-center mb-5">
        <a href="index.php"><img src="./images/logo.png" alt="Logo" style="max-width: 150px;"></a>
    </div>
    <div class="row g-5 justify-content-center">
        <div class="col-md-5">
            <div class="auth-card">
                <div class="auth-header">
                    <i class="fas fa-user-plus text-success"></i>
                    <h4>Créer un compte</h4>
                </div>
                <form id="registerForm">
                    <div class="mb-3">
                        <input type="text" name="nom" placeholder="Nom complet" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <select name="role" class="form-select" required>
                            <option value="" disabled selected>Vous êtes ?</option>
                            <option value="producteur">Producteur</option>
                            <option value="acheteur">Acheteur</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <input type="tel" name="telephone" placeholder="Numéro WhatsApp (ex: 22507010203)" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" name="ville" placeholder="Ville (ex: Soubré)" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" name="produit" placeholder="Produit (ex: Cacao)" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="number" name="quantite" placeholder="Quantité disponible (kg)" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" placeholder="Email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" placeholder="Mot de passe" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">S'inscrire</button>
                </form>
                <div id="registerResult" class="mt-2"></div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="auth-card">
                <div class="auth-header">
                    <i class="fas fa-sign-in-alt text-primary"></i>
                    <h4>Se connecter</h4>
                </div>
                <form id="loginForm">
                    <div class="mb-3">
                        <input type="email" name="email" placeholder="Email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" placeholder="Mot de passe" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Connexion</button>
                </form>
                <div id="loginResult" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Inscription AJAX
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const data = {
            nom: form.nom.value,
            email: form.email.value,
            telephone: form.telephone.value, // Ajout du téléphone dans les données envoyées
            password: form.password.value,
            role: form.role.value,
            ville: form.ville.value,
            produit: form.produit.value,
            quantite: form.quantite.value
        };
        
        try {
            const res = await fetch('api/register.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                document.getElementById('registerResult').innerHTML = '<span style="color:green">Inscription réussie ! Redirection...</span>';
                setTimeout(function() {
                    window.location.href = 'page/dashboard.php';
                }, 1500);
            } else {
                document.getElementById('registerResult').innerHTML = '<span style="color:red">' + (result.error || 'Erreur') + '</span>';
            }
        } catch (error) {
            document.getElementById('registerResult').innerHTML = '<span style="color:red">Erreur de connexion au serveur</span>';
        }
    });

// Connexion AJAX
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const data = {
            email: form.email.value,
            password: form.password.value
        };
        try {
            const res = await fetch('api/login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                document.getElementById('loginResult').innerHTML = '<span style="color:green">Connexion réussie. Redirection...</span>';
                setTimeout(function() {
                    window.location.href = 'page/dashboard.php';
                }, 1000);
            } else {
                document.getElementById('loginResult').innerHTML = '<span style="color:red">' + (result.error || 'Erreur') + '</span>';
            }
        } catch (error) {
            document.getElementById('loginResult').innerHTML = '<span style="color:red">Erreur de connexion au serveur</span>';
        }
    });
</script>
</body>
</html>