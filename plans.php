<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// Récupérer les messages de succès de la session
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Si un plan est sélectionné, rediriger vers la page de paiement
if (isset($_GET['plan'])) {
    $plan = $_GET['plan'];
    if (in_array($plan, ['normal', 'premium', 'vip'])) {
        header("Location: payment.php?plan=" . $plan);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Plans - Fitness Club</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #e74c3c;
            --accent-color: #2ecc71;
            --dark-gray: #2c3e50;
            --light-gray: #ecf0f1;
            --text-color: #333;
            --success-color: #27ae60;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
        }

        .auth-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .auth-header-brand {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .header-logo {
            height: 40px;
            width: auto;
            margin-right: 10px;
            vertical-align: middle;
        }

        .auth-header-links a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            transition: color 0.3s ease;
        }

        .auth-header-links a:hover {
            color: var(--secondary-color);
        }

        .plans-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .plans-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .plans-header h1 {
            font-size: 2.5rem;
            color: var(--dark-gray);
            margin-bottom: 1rem;
        }

        .plans-header p {
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .plan-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .plan-card:hover {
            transform: translateY(-10px);
        }

        .plan-header {
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .plan-normal .plan-header {
            background: #95a5a6;
        }

        .plan-premium .plan-header {
            background: var(--secondary-color);
        }

        .plan-vip .plan-header {
            background: var(--warning-color);
        }

        .plan-price {
            font-size: 2.5rem;
            margin: 1rem 0;
        }

        .plan-price span {
            font-size: 1rem;
            opacity: 0.8;
        }

        .plan-features {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }

        .plan-features ul {
            flex: 1;
            margin-bottom: 1rem;
        }

        .plan-features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features i {
            margin-right: 0.5rem;
        }

        .fa-check {
            color: var(--success-color);
        }

        .fa-times {
            color: var(--danger-color);
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 1rem;
            text-align: center;
            text-decoration: none;
            color: white;
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-normal {
            background: #95a5a6;
        }

        .btn-premium {
            background: var(--secondary-color);
        }

        .btn-vip {
            background: var(--warning-color);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .plans-grid {
                grid-template-columns: 1fr;
            }
        }

        .footer {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 3rem 0;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-links h3 {
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.2rem;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: white;
            transform: translateX(5px);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }
    </style>
</head>
<body>
    <header class="auth-header animate slide-in-left">
        <a href="index.php" class="auth-header-brand">
            <img src="assets/logo.png" alt="PolyGym Logo" class="header-logo">
            PolyGym
        </a>
        <div class="auth-header-links">
            <a href="index.php">Accueil</a>
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="plans.php" class="active">Plans</a>
                <a href="logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="login.php">Se connecter</a>
                <a href="register.php">S'inscrire</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="plans-container">
        <div class="plans-header animate fade-in-up">
            <h1>Nos Plans d'Abonnement</h1>
            <p>Choisissez le plan qui correspond le mieux à vos objectifs</p>
        </div>

        <div class="plans-grid">
            <div class="plan-card plan-normal animate slide-in-left delay-1">
                <div class="plan-header">
                    <h2>Normal</h2>
                    <div class="plan-price">90 TND<span>/mois</span></div>
                </div>
                <div class="plan-features">
                    <ul>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Accès à la salle de sport</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Équipements de base</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Cours collectifs (2/semaine)</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Suivi de progression basique</li>
                    </ul>
                    <a href="?plan=normal" class="btn btn-normal animate fade-in-up delay-3">Choisir ce plan</a>
                </div>
            </div>

            <div class="plan-card plan-premium animate scale-in delay-2">
                <div class="plan-header">
                    <h2>Premium</h2>
                    <div class="plan-price">150 TND<span>/mois</span></div>
                </div>
                <div class="plan-features">
                    <ul>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Accès à la salle de sport</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Tous les équipements</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Cours collectifs illimités</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Suivi de progression avancé</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> 1 séance coach/mois</li>
                    </ul>
                    <a href="?plan=premium" class="btn btn-premium animate fade-in-up delay-3">Choisir ce plan</a>
                </div>
            </div>

            <div class="plan-card plan-vip animate slide-in-right delay-1">
                <div class="plan-header">
                    <h2>VIP</h2>
                    <div class="plan-price">250 TND<span>/mois</span></div>
                </div>
                <div class="plan-features">
                    <ul>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Accès à la salle de sport</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Tous les équipements</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Cours collectifs illimités</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Suivi personnalisé</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> 4 séances coach/mois</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> Accès zone VIP</li>
                        <li class="animate fade-in-up delay-2"><i class="fas fa-check"></i> App mobile premium</li>
                    </ul>
                    <a href="?plan=vip" class="btn btn-vip animate fade-in-up delay-3">Choisir ce plan</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer animate fade-in-up delay-3">
        <div class="footer-content">
            <div class="footer-links">
                <h3>Fitness Club</h3>
                <p>Transformez votre corps, transformez votre vie.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h3>Liens rapides</h3>
                <ul>
                    <li><a href="index.php#features">Fonctionnalités</a></li>
                    <li><a href="plans.php">Abonnements</a></li>
                    <li><a href="index.php#testimonials">Témoignages</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Contact</h3>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> 123 Rue du Fitness, 75000 Paris</li>
                    <li><i class="fas fa-phone"></i> +33 1 23 45 67 89</li>
                    <li><i class="fas fa-envelope"></i> info@fitnessclub.com</li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Fitness Club. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html> 