<?php
// Bypass Ngrok security warning
header('ngrok-skip-browser-warning: true');

session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
session_start();
$is_connected = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriprice Direct - Marché Agricole</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* RESET & BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        html { scroll-behavior: smooth; }
        body { background: #fff; color: #333; }

        /* HEADER */
        header {
            position: fixed; top: 0; left: 0; width: 100%; padding: 15px 8%;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 10000; transition: 0.5s; background: rgba(255,255,255,0.1);
        }
        header.sticky { background: #fff; padding: 10px 8%; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }

        .header-left { display: flex; align-items: center; gap: 20px; }
        .logo-cercle { width: 60px; height: 60px; object-fit: contain; }

        .navbar { display: flex; list-style: none; margin: 0; align-items: center; }
        .navbar li a { color: #fff; text-decoration: none; font-weight: 600; margin-left: 25px; transition: 0.3s; font-size: 0.95rem; }
        header.sticky .navbar li a { color: #333; }

        .btn-nav { padding: 8px 18px; border-radius: 5px; color: #fff !important; font-weight: 700 !important; }
        .btn-dash { background: #27ae60; }
        /* Style spécial pour le bouton déconnexion */
        .btn-logout { background: #e74c3c; margin-left: 10px; }

        /* BANNIÈRE */
        .banniere {
            position: relative; width: 100%; min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('./images/imgagi.jpg');
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; color: #fff; padding: 0 20px;
        }
        .banniere h2 { font-size: 3.5rem; font-weight: 700; max-width: 900px; }
        .btn-main { background: #00a859; color: #fff; padding: 15px 35px; border-radius: 50px; text-decoration: none; font-weight: 700; display: inline-block; margin-top: 20px; }

        /* SECTIONS */
        section { padding: 80px 8%; }
        .titre-texte { font-size: 2.5rem; font-weight: 700; margin-bottom: 50px; text-align: center; }
        .titre-texte span { color: #27ae60; }

        /* GRILLE SERVICES (6 Services) */
        .services-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); 
            gap: 30px; 
        }
        .service-card { 
            background: #fff; border-radius: 15px; overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: 0.3s; text-align: center;
            padding-bottom: 25px;
        }
        .service-card:hover { transform: translateY(-10px); }
        .service-card img { width: 100%; height: 220px; object-fit: cover; }
        .service-card h3 { margin: 20px 0 15px; font-weight: 600; font-size: 1.3rem; }
        .btn-service { background: #27ae60; color: #fff; padding: 8px 25px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; font-weight: 500; }

        /* A PROPOS */
        .apropos-img { width: 100%; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

        /* FOOTER */
        footer { background: #008f4c; color: #fff; padding: 60px 8% 30px; }
        footer a { color: #fff; text-decoration: none; opacity: 0.8; }
        .footer-logo { width: 100px; background: #fff; padding: 8px; border-radius: 50%; margin-bottom: 15px; }

        @media (max-width: 991px) {
            .navbar { display: none; }
            .banniere h2 { font-size: 2.2rem; }
        }
    </style>
</head>
<body>

<header id="header">
    <div class="header-left">
        <a href="index.php"><img src="./images/logo.png" class="logo-cercle" alt="Logo"></a>
        <a href="index.php" style="color:#27ae60; font-size: 1.5rem;"><i class="fas fa-home"></i></a>
    </div>
    <ul class="navbar">
        <li><a href="#banniere">Accueil</a></li>
        <li><a href="#nosservice">Nos services</a></li>
        <li><a href="#apropos">A propos</a></li>
        <li><a href="#contact">Contact</a></li>
        <?php if ($is_connected): ?>
            <li><a href="page/dashboard.php" class="btn-nav btn-dash">DASHBOARD</a></li>
            <li><a href="api/logout.php" class="btn-nav btn-logout">DECONNEXION</a></li>
        <?php else: ?>
            <li><a href="auth.php" class="btn-nav btn-dash">CONNEXION</a></li>
        <?php endif; ?>
    </ul>
</header>

<section class="banniere" id="banniere">
    <h2>Le Marché Agricole Intelligent D'Afrique</h2>
    <p>Connectez-vous directement au futur de l'agriculture.</p>
    <a href="<?= $is_connected ? 'page/dashboard.php' : 'auth.php' ?>" class="btn-main">COMMENCER MAINTENANT</a>
</section>

<section id="nosservice">
    <h2 class="titre-texte"><span>Nos</span> Services</h2>
    <div class="services-grid">
        <div class="service-card">
            <img src="./images/c1.jpg" alt="Vente">
            <h3>Vente de produits</h3>
            <a href="page/dashboard.php" class="btn-service">Accéder</a>
        </div>
        <div class="service-card">
            <img src="./images/c2.jpg" alt="Transport">
            <h3>Transport agricole</h3>
            <a href="page/transport.php" class="btn-service">Accéder</a>
        </div>
        <div class="service-card">
            <img src="./images/c3.jpg" alt="Relation">
            <h3>Mise en relation</h3>
            <a href="page/dashboard.php" class="btn-service">Accéder</a>
        </div>
        <div class="service-card">
            <img src="./images/c4.jpg" alt="Location">
            <h3>Location de machines</h3>
            <a href="page/location.php" class="btn-service">Louer un engin</a>
        </div>
        <div class="service-card">
            <img src="./images/c5.jpg" alt="Publicité">
            <h3>Publicité agro</h3>
            <a href="page/publicite.php" class="btn-service">Annoncer ici</a>
        </div>
        <div class="service-card">
            <img src="./images/c6.jpg" alt="Conseils">
            <h3>Conseils agro</h3>
            <a href="page/conseils.php" class="btn-service">Consulter</a>
        </div>
    </div>
</section>

<section class="apropos" id="apropos">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h2 class="titre-texte" style="text-align: left;"><span>A</span> Propos</h2>
            <p>Agriprice Direct est une solution numérique innovante pour connecter les acteurs du monde agricole.</p>
        </div>
        <div class="col-lg-6">
            <img src="./images/IMG-20250709-WA0005.jpg" class="apropos-img" alt="Agriculture">
        </div>
    </div>
</section>

<footer id="contact">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-4">
                <img src="./images/logo.png" class="footer-logo">
                <p>Le Marché Agricole Intelligent.</p>
            </div>
            <div class="col-md-4">
                <h3>Contact</h3>
                <p>Abidjan Marcory, CI</p>
                <p>Tél: 0757610231</p>
            </div>
            <div class="col-md-4">
                <h3>Suivez-nous</h3>
                <div class="d-flex justify-content-center gap-3 mt-2">
                    <a href="#"><i class="fab fa-facebook fa-2x"></i></a>
                    <a href="#"><i class="fab fa-whatsapp fa-2x"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
    window.addEventListener('scroll', function(){
        const header = document.querySelector('header');
        header.classList.toggle("sticky", window.scrollY > 0 );
    });
</script>
</body>
</html>