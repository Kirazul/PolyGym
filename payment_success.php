<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? 0;

try {
    // First, get the selected plan from session for new subscriptions
    $selected_plan = $_SESSION['selected_plan'] ?? '';
    $selected_price = $_SESSION['plan_price'] ?? 0;
    
    if (!empty($selected_plan)) {
        // This is a new subscription
        $plan = $selected_plan;
        $amount = $selected_price;
        $duration = $_SESSION['plan_duration'] ?? 1;
        $activation_date = date('d/m/Y');
        $expiration_date = date('d/m/Y', strtotime("+{$duration} months"));
    } else {
        // Try to get existing subscription
        $stmt = $pdo->prepare("
            SELECT 
                s.id as subscription_id,
                s.start_date,
                s.end_date,
                p.name as plan_name,
                p.price,
                COALESCE(pay.amount, p.price) as payment_amount
            FROM subscriptions s
            JOIN plans p ON s.plan_id = p.id
            LEFT JOIN payments pay ON s.id = pay.subscription_id AND pay.status = 'completed'
            WHERE s.user_id = ? 
            AND s.status = 'active'
            ORDER BY s.start_date DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$user_id]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($subscription) {
            $plan = $subscription['plan_name'];
            $amount = floatval($subscription['payment_amount']);
            $start_date = new DateTime($subscription['start_date']);
            $end_date = new DateTime($subscription['end_date']);
            
            // Calculate duration in months
            $interval = $start_date->diff($end_date);
            $duration = ($interval->y * 12) + $interval->m;
            
            // Format dates for display
            $activation_date = $start_date->format('d/m/Y');
            $expiration_date = $end_date->format('d/m/Y');
        } else {
            // If no active subscription found, check the plans table for the selected plan
            $stmt = $pdo->prepare("SELECT * FROM plans WHERE name = ?");
            $stmt->execute([$selected_plan]);
            $plan_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($plan_info) {
                $plan = $plan_info['name'];
                $amount = floatval($plan_info['price']);
                $duration = intval($plan_info['duration_days'] / 30); // Convert days to months
            } else {
                $plan = 'normal'; // Default to normal plan
                $amount = 90.00; // Default price
                $duration = 1; // Default duration
            }
            
            $activation_date = date('d/m/Y');
            $expiration_date = date('d/m/Y', strtotime("+{$duration} months"));
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching subscription data: " . $e->getMessage());
    // Fallback values in case of error
    $plan = 'normal';
    $amount = 90.00;
    $duration = 1;
    $activation_date = date('d/m/Y');
    $expiration_date = date('d/m/Y', strtotime("+1 month"));
}

// Plan display names mapping
$planNames = [
    'normal' => 'Normal',
    'premium' => 'Premium',
    'vip' => 'VIP'
];

// Ensure plan name is lowercase for comparison
$plan = strtolower(trim($plan));
$planDisplay = isset($planNames[$plan]) ? $planNames[$plan] : 'Normal';

// Ensure amount is never 0
if ($amount <= 0) {
    // Get the price from plans table
    try {
        $stmt = $pdo->prepare("SELECT price FROM plans WHERE name = ?");
        $stmt->execute([$plan]);
        $plan_price = $stmt->fetchColumn();
        $amount = $plan_price ? floatval($plan_price) : 90.00;
    } catch (PDOException $e) {
        $amount = 90.00; // Default to normal plan price
    }
}

// Clear session variables
unset($_SESSION['selected_plan'], $_SESSION['plan_duration'], $_SESSION['plan_price']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Réussi - PolyGym</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #ffffff;
            --light-gray: #ecf0f1;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-top: 80px;
            background: linear-gradient(135deg, #2c3e50, #e74c3c);
            min-height: 100vh;
            color: var(--text-color);
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            box-shadow: var(--box-shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
        }

        .navbar .container {
            width: 90%;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .header-logo {
            height: 40px;
            width: auto;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--light-gray);
        }

        /* Success Page */
        .success-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .celebration-effect {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        }

        .big-logo {
            display: block;
            width: 180px;
            height: auto;
            margin: 0 auto 1.5rem;
            filter: drop-shadow(0 0 15px rgba(255,255,255,0.4));
        }

        .certificate-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 215, 0, 0.3);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            position: relative;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .welcome-text {
            color: #ffffff;
            font-size: 1.8rem;
            margin: 1.5rem 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            font-weight: 600;
        }

        .membership-details {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 600px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .detail-value {
            color: #FFD700;
            font-weight: 600;
        }

        .action-buttons {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .success-container {
                padding: 1rem;
            }
            
            .certificate-container {
                padding: 1.5rem;
            }

            .welcome-text {
                font-size: 1.2rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
                margin-bottom: 0.5rem;
            }

            .detail-row {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="celebration-effect" id="confetti"></div>
    
    <!-- Replace include with direct navbar code -->
    <header class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                <img src="assets/logo.png" alt="PolyGym Logo" class="header-logo">
                PolyGym
            </a>
            <div class="nav-links">
                <a href="index.php">Accueil</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="plans.php">Abonnements</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </header>

    <div class="success-container">
        <div class="certificate-container">
            <img src="assets/logo.png" alt="PolyGym Logo" class="big-logo">
            <p class="welcome-text">Bienvenue dans la famille PolyGym</p>
            
            <div class="membership-details">
                <div class="detail-row">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value">Membre Officiel PolyGym</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date d'activation</span>
                    <span class="detail-value"><?php echo $activation_date; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Durée de l'abonnement</span>
                    <span class="detail-value"><?php echo $duration; ?> mois</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date d'expiration</span>
                    <span class="detail-value"><?php echo $expiration_date; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Numéro de membre</span>
                    <span class="detail-value">PG-<?php echo str_pad($user_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-primary">Accéder au Dashboard</a>
            <a href="plans.php" class="btn btn-outline">Voir tous les plans</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <script>
        window.onload = function() {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });

            const duration = 5 * 1000;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            const interval = setInterval(function() {
                const timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                const particleCount = 50 * (timeLeft / duration);
                confetti(Object.assign({}, defaults, {
                    particleCount,
                    origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
                    colors: ['#FFD700', '#FFA500', '#3498db']
                }));
                confetti(Object.assign({}, defaults, {
                    particleCount,
                    origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
                    colors: ['#FFD700', '#FFA500', '#3498db']
                }));
            }, 250);
        };
    </script>
</body>
</html>
