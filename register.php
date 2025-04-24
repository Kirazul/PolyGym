<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Récupérer les erreurs et les données du formulaire de la session
$errors = $_SESSION['register_errors'] ?? [];
$formData = $_SESSION['register_data'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['register_errors'], $_SESSION['register_data'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Fitness Club</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/main.js" defer></script>
    <style>
        /* Styles communs pour login et register */
        .auth-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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

        .auth-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .auth-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }

        .auth-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .btn-auth {
            width: 100%;
            padding: 1rem;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .btn-auth:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .auth-footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Header commun -->
        <header class="auth-header animate slide-in-left">
            <a href="index.php" class="auth-header-brand">
                <img src="assets/logo.png" alt="PolyGym Logo" class="header-logo">
                PolyGym
            </a>
            <div class="auth-header-links">
                <a href="index.php">Accueil</a>
                <a href="login.php">Se connecter</a>
                <a href="register.php">S'inscrire</a>
            </div>
        </header>

        <!-- Contenu principal -->
        <main class="auth-content">
            <div class="auth-card animate scale-in">
                <h1 class="auth-title animate fade-in-up delay-1">Créer un compte</h1>

                <?php if (!empty($success)): ?>
                    <div class="alert-success animate slide-in-left">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert-error animate slide-in-left">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register_process.php">
                    <div class="form-group animate slide-in-left delay-1">
                        <label for="first_name">Prénom</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required
                               value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>">
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="error-message"><?php echo $errors['first_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group animate slide-in-left delay-2">
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required
                               value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>">
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="error-message"><?php echo $errors['last_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group animate slide-in-right delay-2">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-message"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group animate slide-in-right delay-3">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" class="form-control" required
                               value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>">
                        <?php if (isset($errors['username'])): ?>
                            <div class="error-message"><?php echo $errors['username']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group animate slide-in-left delay-3">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required
                               value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="error-message"><?php echo $errors['phone']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group animate slide-in-left delay-3">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-message"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group animate slide-in-right delay-3">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="error-message"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-auth animate fade-in-up delay-3">S'inscrire</button>
                </form>

                <div class="auth-footer animate fade-in-up delay-3">
                    <p>Déjà inscrit ? <a href="login.php">Connectez-vous</a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 