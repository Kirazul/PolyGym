<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$userId = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT users.*, plans.name AS plan_name, subscriptions.start_date, subscriptions.end_date, subscriptions.status AS subscription_status
FROM users 
LEFT JOIN plans ON users.plan_id = plans.id
LEFT JOIN subscriptions ON users.id = subscriptions.user_id
WHERE users.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialiser les variables par défaut
$subscriptionStatus = $user['subscription_status'] ?? 'inactive';
$subscriptionPlan = $user['plan_name'] ?? 'none';
$subscriptionBadge = 'Aucun';
$subscriptionClass = 'badge-normal';
$subscriptionDetails = [];

// Déterminer le badge et les détails de l'abonnement
if ($subscriptionStatus === 'active') {
    switch($subscriptionPlan) {
        case 'normal':
            $subscriptionBadge = 'Normal';
            $subscriptionClass = 'badge-normal';
            $subscriptionDetails = [
                'Accès à la salle de sport',
                'Équipements de base',
                'Accès aux cours collectifs (2 par semaine)',
                'Suivi de progression basique'
            ];
            break;
        case 'premium':
            $subscriptionBadge = 'Premium';
            $subscriptionClass = 'badge-premium';
            $subscriptionDetails = [
                'Accès à la salle de sport',
                'Tous les équipements',
                'Accès illimité aux cours collectifs',
                'Suivi de progression avancé',
                '1 séance avec un coach par mois'
            ];
            break;
        case 'vip':
            $subscriptionBadge = 'VIP';
            $subscriptionClass = 'badge-vip';
            $subscriptionDetails = [
                'Accès à la salle de sport',
                'Tous les équipements',
                'Accès illimité aux cours collectifs',
                'Suivi de progression personnalisé',
                '4 séances avec un coach par mois',
                'Accès à la zone VIP',
                'Accès à l\'application mobile premium'
            ];
            break;
    }
}


// Mettre à jour la session avec les informations actuelles
$_SESSION['user'] = array_merge($_SESSION['user'], [
    'subscription_status' => $subscriptionStatus,
    'subscription_plan' => $user['plan_name']
]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Fitness Club</title>
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

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-title h1 {
            font-size: 2rem;
            color: var(--dark-gray);
            margin: 0;
        }

        .subscription-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            color: white;
            font-weight: 500;
        }

        .badge-normal {
            background-color: #95a5a6;
        }

        .badge-premium {
            background-color: var(--secondary-color);
        }

        .badge-vip {
            background-color: var(--warning-color);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card h2 {
            color: var(--dark-gray);
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .user-info {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            display: grid;
            grid-template-columns: 150px 1fr;
            align-items: center;
        }

        .info-label {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .info-value {
            color: var(--text-color);
        }

        .subscription-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--light-gray);
        }

        .subscription-features {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .subscription-features li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
        }

        .subscription-features li i {
            color: var(--success-color);
            margin-right: 0.5rem;
        }

        .subscription-actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .payment-list {
            margin-top: 2rem;
        }

        .subscription-alert-urgent {
            color: var(--danger-color);
            font-weight: bold;
            padding: 0.5rem;
            border-radius: 4px;
            background-color: rgba(231, 76, 60, 0.1);
        }

        .subscription-alert-warning {
            color: var(--warning-color);
            font-weight: bold;
            padding: 0.5rem;
            border-radius: 4px;
            background-color: rgba(241, 196, 15, 0.1);
        }

        .days-remaining {
            font-size: 0.9em;
            margin-left: 0.5rem;
            font-style: italic;
        }

        .payment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            margin: 0.5rem 0;
            background: var(--light-gray);
            border-radius: 5px;
        }

        .payment-actions form {
            display: flex;
            gap: 0.5rem;
        }



        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }

        .alert.success {
            background: var(--success-color);
            color: white;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                grid-template-columns: 1fr;
            }
            
            .info-label {
                margin-bottom: 0.25rem;
            }

            .payment-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .payment-actions form {
                flex-direction: column;
                width: 100%;
            }

            .form-group input,
            .form-group select {
                width: 100%;
            }
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
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="plans.php">Plans</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </header>

    <div class="dashboard-container">
        <div class="dashboard-header animate fade-in-up">
            <div class="dashboard-title">
                <h1>Bienvenue, <?php echo htmlspecialchars($user['first_name']); ?></h1>
            </div>
            <span class="subscription-badge <?php echo $subscriptionClass; ?> animate scale-in delay-1">
                <?php echo $subscriptionBadge; ?>
            </span>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card animate slide-in-left delay-1">
                <h2>Informations personnelles</h2>
                <div class="user-info">
                    <div class="info-item">
                        <div class="info-label">Nom complet:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Non renseigné'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Membre depuis:</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card animate slide-in-right delay-2">
                <h2>Détails de l'abonnement</h2>
                <div class="subscription-info">
                    <?php if ($subscriptionStatus === 'active'): ?>
                        <div class="subscription-status">
                            <p><strong>Statut:</strong> <span class="status-active">Actif</span></p>
                            <p><strong>Plan:</strong> <?php echo ucfirst($subscriptionPlan); ?></p>
                            <?php if (isset($user['start_date'])): ?>
                                <p><strong>Date de début:</strong> <?php echo date('d/m/Y', strtotime($user['start_date'])); ?></p>
                            <?php endif; ?>
                            <?php if (isset($user['end_date'])): ?>
                                <?php
                                    $end_date = strtotime($user['end_date']);
                                    $now = time();
                                    $days_remaining = ceil(($end_date - $now) / (60 * 60 * 24));
                                    $alert_class = '';
                                    if ($days_remaining <= 7) {
                                        $alert_class = 'subscription-alert-urgent';
                                    } elseif ($days_remaining <= 30) {
                                        $alert_class = 'subscription-alert-warning';
                                    }
                                ?>
                                <p class="<?php echo $alert_class; ?>">
                                    <strong>Date de fin:</strong> <?php echo date('d/m/Y', $end_date); ?>
                                    <?php if ($days_remaining > 0): ?>
                                        <span class="days-remaining">(<?php echo $days_remaining; ?> jours restants)</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <ul class="subscription-features">
                            <?php foreach ($subscriptionDetails as $feature): ?>
                                <li class="animate fade-in-up delay-3">
                                    <i class="fas fa-check"></i>
                                    <?php echo htmlspecialchars($feature); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="subscription-details">
                            <h3>Détails de l'abonnement</h3>
                            <?php if($user['subscription_status'] === 'active'): ?>
                                <p>Début: <?= date('d/m/Y', strtotime($user['start_date'])) ?></p>
                                <p>Fin: <?= date('d/m/Y', strtotime($user['end_date'])) ?></p>
                                <p>Statut: <span class="badge-<?= strtolower($user['subscription_status']) ?>"><?= ucfirst($user['subscription_status']) ?></span></p>
                            <?php else: ?>
                                <p>Aucun abonnement actif</p>
                            <?php endif; ?>
                        </div>
                        <div class="subscription-actions">
                            <a href="plans.php" class="btn btn-primary">Choisir un abonnement</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>