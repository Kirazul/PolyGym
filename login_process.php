<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    // Valider les données
    if (empty($username)) {
        $errors['username'] = "Le nom d'utilisateur est requis";
    }
    
    if (empty($password)) {
        $errors['password'] = "Le mot de passe est requis";
    }
    
    // Si aucune erreur, tenter la connexion
    if (empty($errors)) {
        if (login($username, $password)) {
            // Récupérer les informations de l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // Vérifier s'il y a une URL de redirection stockée
            $redirectUrl = $_SESSION['redirect_url'] ?? 'index.php';
            unset($_SESSION['redirect_url']);
            
            // Ajouter un message de succès
            $_SESSION['success'] = "Connexion réussie ! Bienvenue " . htmlspecialchars($user['first_name']);
            
            header("Location: $redirectUrl");
            exit();
        } else {
            $errors['auth'] = "Nom d'utilisateur ou mot de passe incorrect";
        }
    }
    
    // S'il y a des erreurs, les stocker en session et rediriger vers le formulaire
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_data'] = [
            'username' => $username
        ];
        
        header("Location: login.php");
        exit();
    }
} else {
    // Si quelqu'un essaie d'accéder directement à ce fichier, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
} 