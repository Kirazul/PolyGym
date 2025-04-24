<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Récupérer les messages de succès de la session
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PolyGym - Votre destination fitness</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    
    <style>
        /* Styles communs pour toutes les pages */
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

        /* Styles de la barre de navigation */
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

        /* Styles pour les messages d'alerte */
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* Styles spécifiques à la page d'accueil */
        .hero {
            background: linear-gradient(135deg, #2c3e50, #e74c3c);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            padding: 2rem;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: fadeInDown 1s ease-out;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease-out 0.5s both;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            animation: fadeInUp 1s ease-out 1s both;
        }
        
        .btn-large {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: white;
            border: none;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-primary:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .features {
            padding: 5rem 0;
            background-color: #f9f9f9;
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .membership-plans {
            padding: 5rem 0;
        }

        .plan-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .plan-card:hover {
            transform: translateY(-10px);
        }
        
        .plan-header {
            padding: 2rem;
            text-align: center;
            color: white;
        }
        
        .plan-basic .plan-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }

        .plan-premium .plan-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .plan-vip .plan-header {
            background: linear-gradient(135deg, #f1c40f, #f39c12);
        }

        .plan-price {
            font-size: 2.5rem;
            margin: 1rem 0;
        }

        .plan-features {
            padding: 2rem;
        }

        .plan-features ul {
            list-style: none;
            padding: 0;
        }

        .plan-features li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .testimonials-section {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            padding: 4rem 0;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .testimonials-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(52, 152, 219, 0.9));
            z-index: 1;
        }

        .testimonials-section .container {
            position: relative;
            z-index: 2;
        }

        .testimonials-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            margin: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #fff;
            margin-bottom: 20px;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .testimonial-author img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 10px;
        }

        .testimonial-author h4 {
            font-size: 1.2rem;
            color: #fff;
            margin: 0;
        }

        .testimonial-author p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }

        .contact {
            padding: 5rem 0;
        }

        .contact-form {
            max-width: 600px;
            margin: 0 auto;
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

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .cta-buttons {
                flex-direction: column;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }

            .auth-header {
                flex-direction: column;
                text-align: center;
            }

            .auth-header-links {
                margin-top: 1rem;
            }

            .auth-header-links a {
                margin: 0 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header commun -->
    <header class="auth-header">
        <a href="index.php" class="auth-header-brand">
            <img src="assets/logo.png" alt="PolyGym Logo" class="header-logo">
            PolyGym
        </a>
        <div class="auth-header-links">
            <a href="index.php">Accueil</a>
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="plans.php">Plans</a>
                <a href="logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="login.php">Se connecter</a>
                <a href="register.php">S'inscrire</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!empty($success)): ?>
        <div class="alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Transformez votre corps, transformez votre vie</h1>
            <p>Rejoignez notre club de fitness et commencez votre voyage vers une meilleure santé et forme physique dès aujourd'hui.</p>
            <div class="cta-buttons">
                <a href="#plans" class="btn btn-primary btn-large">Voir les abonnements</a>
                <a href="#contact" class="btn btn-outline btn-large">Nous contacter</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Pourquoi nous choisir</h2>
            <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>Équipement moderne</h3>
                    <p>Accédez à des équipements de fitness de pointe pour atteindre vos objectifs plus rapidement.</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Instructeurs experts</h3>
                    <p>Nos instructeurs certifiés sont là pour vous guider et vous motiver à chaque étape.</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Cours variés</h3>
                    <p>Des cours de yoga aux séances de HIIT, nous avons quelque chose pour tout le monde.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Membership Plans Section -->
    <section id="plans" class="membership-plans">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Nos abonnements</h2>
            <div class="row" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                <div class="col-md-4 animate slide-in-left delay-1">
                    <div class="plan-card plan-basic">
                        <div class="plan-header">
                            <h3>Basic</h3>
                            <div class="plan-price">90 TND<span>/mois</span></div>
                            <p>Parfait pour les débutants</p>
                        </div>
                        <div class="plan-features">
                            <ul>
                                <li><i class="fas fa-check"></i> Accès à la salle de musculation</li>
                                <li><i class="fas fa-check"></i> 2 cours de groupe par semaine</li>
                                <li><i class="fas fa-check"></i> Accès aux vestiaires</li>
                                <li><i class="fas fa-check"></i> Conseils nutritionnels de base</li>
                                <li><i class="fas fa-times"></i> Accès aux cours premium</li>
                                <li><i class="fas fa-times"></i> Coaching personnel</li>
                            </ul>
                            <?php if (isLoggedIn()): ?>
                                <a href="plans.php?plan=normal" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Choisir ce plan</a>
                            <?php else: ?>
                                <a href="register.php?plan=normal" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Choisir ce plan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 animate fade-in-up delay-2">
                    <div class="plan-card plan-premium">
                        <div class="plan-header">
                            <h3>Premium</h3>
                            <div class="plan-price">150 TND<span>/mois</span></div>
                            <p>Pour les passionnés</p>
                        </div>
                        <div class="plan-features">
                            <ul>
                                <li><i class="fas fa-check"></i> Accès à la salle de musculation</li>
                                <li><i class="fas fa-check"></i> Cours de groupe illimités</li>
                                <li><i class="fas fa-check"></i> Accès aux vestiaires</li>
                                <li><i class="fas fa-check"></i> Conseils nutritionnels avancés</li>
                                <li><i class="fas fa-check"></i> Accès aux cours premium</li>
                                <li><i class="fas fa-times"></i> Coaching personnel</li>
                            </ul>
                            <?php if (isLoggedIn()): ?>
                                <a href="plans.php?plan=premium" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Choisir ce plan</a>
                            <?php else: ?>
                                <a href="register.php?plan=premium" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Choisir ce plan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 animate slide-in-right delay-3">
                    <div class="plan-card plan-vip">
                        <div class="plan-header">
                            <h3>VIP</h3>
                            <div class="plan-price">250 TND<span>/mois</span></div>
                            <p>L'expérience ultime</p>
                        </div>
                        <div class="plan-features">
                            <ul>
                                <li><i class="fas fa-check"></i> Accès à la salle de musculation</li>
                                <li><i class="fas fa-check"></i> Cours de groupe illimités</li>
                                <li><i class="fas fa-check"></i> Accès aux vestiaires</li>
                                <li><i class="fas fa-check"></i> Conseils nutritionnels personnalisés</li>
                                <li><i class="fas fa-check"></i> Accès aux cours premium</li>
                                <li><i class="fas fa-check"></i> 2 séances de coaching personnel par mois</li>
                            </ul>
                            <?php if (isLoggedIn()): ?>
                                <a href="plans.php?plan=vip" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Choisir ce plan</a>
                            <?php else: ?>
                                <a href="register.php?plan=vip" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Choisir ce plan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <h2>Ce que disent nos membres</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Depuis que j'ai rejoint PolyGym, ma vie a complètement changé. Les coachs sont exceptionnels et l'ambiance est vraiment motivante. J'ai atteint mes objectifs plus rapidement que je ne l'aurais imaginé !"</p>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Sarah B.">
                        <h4>Sarah B.</h4>
                        <p>Membre depuis 2022</p>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Les équipements sont modernes et toujours bien entretenus. Le personnel est attentif et professionnel. C'est vraiment le meilleur club de fitness que j'ai fréquenté !"</p>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Marc D.">
                        <h4>Marc D.</h4>
                        <p>Membre depuis 2021</p>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"L'ambiance est géniale et les cours collectifs sont super dynamiques. J'adore particulièrement les cours de HIIT et de yoga. Une vraie communauté s'est créée ici !"</p>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Julie M.">
                        <h4>Julie M.</h4>
                        <p>Membre depuis 2023</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Contactez-nous</h2>
            <div class="contact-form" data-aos="fade-up" data-aos-delay="100">
                <form action="contact_process.php" method="POST">
                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
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
                        <li><a href="#features">Fonctionnalités</a></li>
                        <li><a href="#plans">Abonnements</a></li>
                        <li><a href="#testimonials">Témoignages</a></li>
                        <li><a href="#contact">Contact</a></li>
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
                <p>&copy; 2023 Fitness Club. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>