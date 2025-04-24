<?php
/**
 * Système d'authentification et de gestion des utilisateurs de PolyGym
 * 
 * Ce fichier gère toutes les fonctionnalités liées à l'authentification :
 * - Connexion/Déconnexion des utilisateurs
 * - Gestion des sessions
 * - Vérification des droits d'accès
 * - Gestion des abonnements
 */

// Ne pas démarrer la session ici car elle est déjà démarrée dans config.php
require_once 'config.php';

/**
 * Vérifie si un utilisateur est actuellement connecté
 * 
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

/**
 * Vérifie si l'utilisateur a un abonnement actif et valide
 * 
 * @return bool True si l'abonnement est actif et non expiré
 */
function hasActiveSubscription() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = $_SESSION['user'];
    if (!isset($user['subscription_status']) || $user['subscription_status'] !== 'active') {
        return false;
    }
    
    // Vérification de la date d'expiration
    if (isset($user['subscription_end_date'])) {
        try {
            $end_date = new DateTime($user['subscription_end_date']);
            $now = new DateTime();
            if ($end_date < $now) {
                // Mise à jour automatique du statut si expiré
                updateSubscriptionStatus($user['id'], 'expired');
                return false;
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la vérification de la date d'expiration: " . $e->getMessage());
            return false;
        }
    }
    
    return true;
}

/**
 * Récupère les informations de l'utilisateur connecté
 * 
 * @return array|null Données de l'utilisateur ou null si non connecté
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Met à jour les informations de l'utilisateur en session
 * 
 * @param array $userData Nouvelles données utilisateur
 */
function updateUserSession($userData) {
    $_SESSION['user'] = array_merge($_SESSION['user'] ?? [], $userData);
}

/**
 * Met à jour le statut d'abonnement d'un utilisateur
 * 
 * @param int $userId ID de l'utilisateur
 * @param string $status Nouveau statut ('active', 'inactive', 'expired')
 * @return bool Succès de la mise à jour
 */
function updateSubscriptionStatus($userId, $status) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET subscription_status = ?, 
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $userId]);
        
        // Mise à jour de la session si c'est l'utilisateur courant
        if (isset($_SESSION['user']) && $_SESSION['user']['id'] === $userId) {
            $_SESSION['user']['subscription_status'] = $status;
        }
        return true;
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour du statut d'abonnement : " . $e->getMessage());
        return false;
    }
}

/**
 * Force la connexion pour accéder à une page
 * Redirige vers login.php si non connecté
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

/**
 * Authentifie un utilisateur
 * 
 * @param string $username Nom d'utilisateur
 * @param string $password Mot de passe
 * @return bool Succès de la connexion
 */
function login($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   p.name as subscription_plan,
                   s.start_date as subscription_start_date,
                   s.end_date as subscription_end_date
            FROM users u
            LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active'
            LEFT JOIN plans p ON s.plan_id = p.id
            WHERE u.username = ?
            ORDER BY s.start_date DESC
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Vérification de l'expiration de l'abonnement
            if ($user['subscription_status'] === 'active' && 
                !empty($user['subscription_end_date']) && 
                strtotime($user['subscription_end_date']) < time()) {
                updateSubscriptionStatus($user['id'], 'expired');
                $user['subscription_status'] = 'expired';
            }

            // Stockage des informations utiles en session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'subscription_status' => $user['subscription_status'],
                'subscription_plan' => $user['subscription_plan'],
                'subscription_start_date' => $user['subscription_start_date'],
                'subscription_end_date' => $user['subscription_end_date']
            ];
            return true;
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la connexion : " . $e->getMessage());
    }
    
    return false;
}

/**
 * Déconnecte l'utilisateur et détruit la session
 */
function logout() {
    // Destruction de toutes les données de session
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
    
    // Redirection vers la page de connexion
    header('Location: login.php');
    exit();
}

/**
 * Inscrit un nouvel utilisateur
 * 
 * @param string $username Nom d'utilisateur
 * @param string $password Mot de passe
 * @param string $email Email
 * @param string $firstName Prénom
 * @param string $lastName Nom
 * @param string $phone Téléphone
 * @return bool Succès de l'inscription
 */
function register($username, $password, $email, $firstName, $lastName, $phone) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Hashage sécurisé du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (
                username, password, email, first_name, last_name, phone,
                subscription_status, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 'inactive', NOW(), NOW()
            )
        ");
        
        $result = $stmt->execute([
            $username, $hashedPassword, $email, $firstName, $lastName, $phone
        ]);
        
        if ($result) {
            $pdo->commit();
            // Connexion automatique après inscription
            return login($username, $password);
        }
        
        $pdo->rollBack();
        return false;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Erreur lors de l'inscription : " . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si l'utilisateur peut accéder aux fonctionnalités premium
 * 
 * @return bool True si l'utilisateur a un abonnement premium ou VIP actif
 */
function canAccessPremiumFeature() {
    return hasActiveSubscription() && in_array(
        $_SESSION['user']['subscription_plan'] ?? '', 
        ['premium', 'vip']
    );
}

/**
 * Vérifie si l'utilisateur est un membre VIP
 * 
 * @return bool True si l'utilisateur a un abonnement VIP actif
 */
function isVipMember() {
    return hasActiveSubscription() && 
           ($_SESSION['user']['subscription_plan'] ?? '') === 'vip';
}
?> 