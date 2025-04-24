<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Définir les prix avant validation
$prices = [
    'normal' => 90,
    'premium' => 150,
    'vip' => 250
];

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        


        // Valider l'abonnement
        $planName = strtolower($_GET['plan']);
        if (!isset($prices[$planName])) {
            throw new Exception("Clé de prix non valide pour le plan : " . $planName);
        }
        $stmt = $pdo->prepare("SELECT id FROM plans WHERE name = ?");
        $stmt->execute([$planName]);
        $planData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$planData) {
            throw new Exception("Plan invalide");
        }

        // Mettre à jour l'utilisateur
        $stmt = $pdo->prepare("UPDATE users SET subscription_status = 'active', plan_id = ? WHERE id = ?");
        $stmt->execute([$planData['id'], $_SESSION['user']['id']]);

        // Créer l'abonnement
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime("+30 days"));
        $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$_SESSION['user']['id'], $planData['id'], $startDate, $endDate]);
        $subscriptionId = $pdo->lastInsertId();

        // Enregistrer le paiement
        $paymentMethod = $_POST['payment_method'] ?? 'card';
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, plan_id, subscription_id, amount, payment_method, status) VALUES (?, ?, ?, ?, ?, 'completed')");
        $stmt->execute([$_SESSION['user']['id'], $planData['id'], $subscriptionId, $prices[$planName], $paymentMethod]);

        // Redirection après succès
        $_SESSION['payment_success'] = true;
        $_SESSION['plan_price'] = $prices[$planName];
        header('Location: payment_success.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['payment_errors'] = ["Erreur de paiement : " . $e->getMessage()];
        header("Location: payment.php?plan=" . urlencode($_GET['plan']));
        exit();
    }
}

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
        header('Location: login.php');
    exit();
}

// Vérifier le paramètre plan
if (!isset($_GET['plan'])) {
    die("Paramètre de plan manquant");
}

$plan = strtolower($_GET['plan']);
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->prepare("SELECT id FROM plans WHERE name = ?");
    $stmt->execute([$plan]);
    
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        die("Plan invalide");
    }
} catch (PDOException $e) {
    die("Erreur de validation du plan");
}

// Utiliser le tableau des prix déjà défini plus haut


$planPrice = $prices[$plan];
$planName = ucfirst($plan);
// Définir la variable de session du prix du plan de façon sécurisée
if (is_numeric($planPrice)) {
    $_SESSION['plan_price'] = $planPrice;
} else {
    $_SESSION['plan_price'] = 0;
}
// Récupérer les erreurs s'il y en a
$errors = $_SESSION['payment_errors'] ?? [];
unset($_SESSION['payment_errors']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Fitness Club</title>
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

        .payment-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .payment-header h1 {
            color: var(--dark-gray);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .payment-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .order-summary {
            background: var(--light-gray);
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .order-summary h2 {
            color: var(--dark-gray);
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .order-details {
            display: grid;
            gap: 0.5rem;
        }

        .order-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .order-row:last-child {
            border-bottom: none;
            font-weight: bold;
        }

        .payment-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.1);
        }

        .card-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
        }

        .btn-payment {
            background: var(--success-color);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-payment:hover {
            background: #219a52;
            transform: translateY(-2px);
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .card-grid {
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

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            background: white;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background: rgba(52, 152, 219, 0.1);
        }

        .payment-method i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .payment-method h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--dark-gray);
        }

        .payment-method p {
            margin: 0.5rem 0 0;
            font-size: 0.9rem;
            color: #666;
        }

        #cardPaymentForm, #virementPaymentForm, #cashPaymentForm {
            display: none;
        }

        #cardPaymentForm.active, #virementPaymentForm.active, #cashPaymentForm.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bank-info {
            background: var(--light-gray);
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .bank-info h3 {
            margin-top: 0;
            color: var(--dark-gray);
        }

        .bank-info p {
            margin: 0.5rem 0;
        }

        .cash-info {
            background: var(--light-gray);
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .cash-info h3 {
            margin-top: 0;
            color: var(--dark-gray);
        }

        .cash-info p {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <header class="auth-header">
        <a href="index.php" class="auth-header-brand">
            <img src="assets/logo.png" alt="PolyGym Logo" class="header-logo">
            PolyGym
        </a>
        <div class="auth-header-links">
            <a href="index.php">Accueil</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="plans.php">Plans</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </header>

    <div class="payment-container">
        <div class="payment-header">
            <h1>Finaliser votre abonnement</h1>
            <p>Plan sélectionné : <?php echo htmlspecialchars($planName); ?></p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="payment-card">
            <div class="order-summary">
                <h2>Récapitulatif de la commande</h2>
                <div class="order-details">
                    <div class="order-row">
                        <span>Plan <?php echo htmlspecialchars($planName); ?></span>
                        <span><?php echo number_format($planPrice, 2); ?> TND/mois</span>
                    </div>
                    <div class="order-row">
                        <span>Total à payer aujourd'hui</span>
                        <span><?php echo number_format($planPrice, 2); ?> TND</span>
                    </div>
                </div>
            </div>

            <h2>Choisissez votre méthode de paiement</h2>
            <div class="payment-methods">
                <div class="payment-method" onclick="selectPaymentMethod('card')" id="cardMethod">
                    <i class="fas fa-credit-card"></i>
                    <h3>Carte bancaire</h3>
                    <p>Paiement sécurisé par carte</p>
                </div>
                <div class="payment-method" onclick="selectPaymentMethod('virement')" id="virementMethod">
                    <i class="fas fa-university"></i>
                    <h3>Virement bancaire</h3>
                    <p>Paiement par virement bancaire</p>
                </div>
                <div class="payment-method" onclick="selectPaymentMethod('cash')" id="cashMethod">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Paiement en espèces</h3>
                    <p>Paiement sur place</p>
                </div>
            </div>

            <form id="cardPaymentForm" method="POST" class="payment-form">
                  <?php
                  // Récupérer l'ID du plan depuis la base
                  try {
                      $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                      $stmt = $pdo->prepare("SELECT id FROM plans WHERE name = ?");
                      $planName = strtolower($_GET['plan']);
        if (!isset($prices[$planName])) {
            throw new Exception("Clé de prix non valide pour le plan : " . $planName);
        }
        $stmt->execute([$planName]);
                      $planData = $stmt->fetch(PDO::FETCH_ASSOC);
                      $plan_id = $planData['id'] ?? null;
                  
                      if (!$plan_id) {
                          die("Plan non trouvé");
                      }
                  ?>
                  <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan_id); ?>">
                  <?php
                  } catch (PDOException $e) {
                      die("Erreur de connexion : " . $e->getMessage());
                  }
                  ?>
                  <input type="hidden" name="payment_method" value="card">
                
                <div class="form-group">
                    <label for="card_number">Numéro de carte</label>
                    <input type="text" id="card_number" name="card_number" class="form-control" required placeholder="1234 5678 9012 3456" maxlength="19">
                </div>

                <div class="card-grid">
                    <div class="form-group">
                        <label for="expiry">Date d'expiration (MM/AA)</label>
                        <input type="text" id="expiry" name="expiry" class="form-control" required placeholder="MM/AA" maxlength="5">
                    </div>

                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" class="form-control" required placeholder="123" maxlength="3">
                    </div>
                </div>

                <div class="form-group">
                    <label for="card_name">Nom sur la carte</label>
                    <input type="text" id="card_name" name="card_name" class="form-control" required placeholder="JEAN DUPONT">
                </div>

                <button type="submit" class="btn-payment">
                    Payer <?php echo number_format($planPrice, 2); ?> TND
                </button>
            </form>

            <form id="virementPaymentForm" method="POST" class="payment-form" enctype="multipart/form-data">
                <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan_id); ?>">
                <input type="hidden" name="payment_method" value="virement">

                <div class="bank-info">
                    <h3>Informations bancaires pour le virement</h3>
                    <p><strong>Banque:</strong> Banque Nationale de Tunisie</p>
                    <p><strong>IBAN:</strong> TN59 1234 5678 9012 3456 7890</p>
                    <p><strong>BIC:</strong> BNTETNTT</p>
                    <p><strong>Bénéficiaire:</strong> PolyGym</p>
                    <p><strong>Montant:</strong> <?php echo number_format($planPrice, 2); ?> TND</p>
                    <p><strong>Référence:</strong> POLYGYM-<?php echo strtoupper($plan); ?>-<?php echo time(); ?></p>
                </div>

                <div class="form-group">
                    <label for="transfer_proof">Preuve de virement (optionnel)</label>
                    <input type="file" id="transfer_proof" name="transfer_proof" class="form-control" accept="image/*,.pdf">
                    <small>Vous pouvez télécharger une capture d'écran ou un PDF de votre virement</small>
                </div>

                <button type="submit" class="btn-payment">
                    Confirmer le virement
                </button>
            </form>

            <form id="cashPaymentForm" method="POST" class="payment-form">
                <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan_id); ?>">
                <input type="hidden" name="payment_method" value="cash">

                <div class="cash-info">
                    <h3>Paiement en espèces</h3>
                    <p><strong>Montant à payer:</strong> <?php echo number_format($planPrice, 2); ?> TND</p>
                    <p><strong>Adresse:</strong> 123 Rue du Sport, Tunis</p>
                    <p><strong>Horaires:</strong> Lundi au Samedi, 9h00 - 20h00</p>
                    <p><strong>Important:</strong> Veuillez vous munir du montant exact en espèces et d'une pièce d'identité.</p>
                </div>

                <div class="form-group">
                    <label for="preferred_date">Date de passage souhaitée</label>
                    <input type="date" id="preferred_date" name="preferred_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <button type="submit" class="btn-payment">
                    Réserver le paiement en espèces
                </button>
            </form>
        </div>
    </div>

    <script>
        // Formater le numéro de carte avec des espaces
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = formattedValue;
        });

        // Formater la date d'expiration
        document.getElementById('expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2);
            }
            e.target.value = value;
        });

        // Limiter le CVV à 3 chiffres
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
        });

        // Gestion des méthodes de paiement
        function selectPaymentMethod(method) {
            // Réinitialiser toutes les méthodes
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Cacher tous les formulaires
            document.getElementById('cardPaymentForm').classList.remove('active');
            document.getElementById('virementPaymentForm').classList.remove('active');
            document.getElementById('cashPaymentForm').classList.remove('active');

            // Activer la méthode sélectionnée
            switch(method) {
                case 'card':
                    document.getElementById('cardMethod').classList.add('selected');
                    document.getElementById('cardPaymentForm').classList.add('active');
                    break;
                case 'virement':
                    document.getElementById('virementMethod').classList.add('selected');
                    document.getElementById('virementPaymentForm').classList.add('active');
                    break;
                case 'cash':
                    document.getElementById('cashMethod').classList.add('selected');
                    document.getElementById('cashPaymentForm').classList.add('active');
                    break;
            }
        }

        // Sélectionner la carte bancaire par défaut au chargement de la page
        window.addEventListener('DOMContentLoaded', function() {
            selectPaymentMethod('card');
        });
    </script>
</body>
</html>