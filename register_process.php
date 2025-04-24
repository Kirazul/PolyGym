<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Valider les données
    if (empty($firstName)) {
        $errors['first_name'] = "Le prénom est requis";
    }
    
    if (empty($lastName)) {
        $errors['last_name'] = "Le nom est requis";
    }
    
    if (empty($email)) {
        $errors['email'] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide";
    }
    
    if (empty($username)) {
        $errors['username'] = "Le nom d'utilisateur est requis";
    } elseif (strlen($username) < 3) {
        $errors['username'] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
    }
    
    if (empty($phone)) {
        $errors['phone'] = "Le numéro de téléphone est requis";
    }
    
    if (empty($password)) {
        $errors['password'] = "Le mot de passe est requis";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Les mots de passe ne correspondent pas";
    }
    
    // Vérifier si l'email ou le nom d'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetchColumn() > 0) {
        $errors['email'] = "Cet email ou ce nom d'utilisateur est déjà utilisé";
    }
    
    // Si aucune erreur, créer le compte
    if (empty($errors)) {
        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insérer le nouvel utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        if ($stmt->execute([$username, $hashedPassword, $email, $firstName, $lastName, $phone])) {
            // Rediriger vers la page de connexion avec un message de succès
            $_SESSION['success'] = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Une erreur est survenue lors de la création du compte.";
        }
    }
    
    // S'il y a des erreurs, les stocker en session et rediriger
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_data'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'username' => $username,
            'phone' => $phone
        ];
        header("Location: register.php");
        exit();
    }
}

// Si on arrive ici, c'est que quelqu'un a essayé d'accéder directement au fichier
header("Location: register.php");
exit();
?> 