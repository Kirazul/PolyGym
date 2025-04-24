<?php
/**
 * Configuration principale de l'application PolyGym
 * 
 * Ce fichier contient les paramètres essentiels de l'application :
 * - Configuration de la base de données
 * - Initialisation de PDO
 * - Configuration des sessions
 * - Fonctions utilitaires de gestion des erreurs
 */

// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');     // Hôte de la base de données (localhost pour développement)
define('DB_NAME', 'fitness_club');  // Nom de la base de données
define('DB_USER', 'root');         // Nom d'utilisateur MySQL (à modifier en production)
define('DB_PASS', '');             // Mot de passe MySQL (à modifier en production)

// Configuration de la connexion PDO avec gestion des erreurs optimisée
try {
    // Configuration de la connexion avec support UTF-8
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Options de configuration PDO pour une meilleure sécurité et performance
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Active la gestion d'erreurs par exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Retourne les résultats sous forme de tableau associatif
        PDO::ATTR_EMULATE_PREPARES => false,                // Désactive l'émulation des requêtes préparées
        PDO::ATTR_PERSISTENT => true                        // Utilise des connexions persistantes pour de meilleures performances
    ];

    // Création de l'instance PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Log l'erreur pour le débogage (en production)
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    
    // Affiche un message d'erreur convivial pour l'utilisateur
    die("Impossible de se connecter à la base de données. Veuillez réessayer plus tard ou contacter l'administrateur.");
}

// Configuration de la session avec des paramètres de sécurité renforcés
ini_set('session.cookie_httponly', 1);  // Protège contre les attaques XSS
ini_set('session.use_only_cookies', 1); // Force l'utilisation exclusive des cookies
ini_set('session.cookie_secure', 1);    // Force les cookies en HTTPS (à commenter en développement local)

// Démarre ou reprend une session
session_start();

/**
 * Gère les messages d'erreur de l'application
 * 
 * @param string $message Message d'erreur à afficher
 * @param string $log_message Message optionnel pour le log (par défaut = message d'erreur)
 * @return void
 */
function handleError($message, $log_message = null) {
    // Stocke le message d'erreur dans la session
    $_SESSION['error'] = $message;
    
    // Log l'erreur si un message de log spécifique est fourni
    if ($log_message) {
        error_log($log_message);
    }
    
    // Redirige vers la page d'erreur
    header('Location: error.php');
    exit();
}

/**
 * Gère les messages de succès de l'application
 * 
 * @param string $message Message de succès à afficher
 * @return bool Toujours true pour permettre le chaînage
 */
function handleSuccess($message) {
    $_SESSION['success'] = $message;
    return true;
}

/**
 * Nettoie et valide les entrées utilisateur
 * 
 * @param string $data Données à nettoyer
 * @return string Données nettoyées
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 