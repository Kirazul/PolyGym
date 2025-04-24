-- Création de la base de données
CREATE DATABASE IF NOT EXISTS fitness_club;
USE fitness_club;

-- Table des abonnements
CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    description TEXT
);

-- Données initiales pour les plans
INSERT IGNORE INTO plans (name, price, duration_days, description) VALUES
('normal', 90.00, 30, 'Accès standard à la salle'),
('premium', 150.00, 30, 'Accès premium + cours collectifs'),
('vip', 250.00, 30, 'Accès VIP + coach personnel');

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    subscription_status ENUM('inactive', 'active', 'expired') DEFAULT 'inactive',
    subscription_end_date DATETIME,
    plan_id INT DEFAULT NULL,
    FOREIGN KEY (plan_id) REFERENCES plans(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des souscriptions
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('active', 'expired', 'canceled') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (plan_id) REFERENCES plans(id)
);

-- Table des paiements
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    subscription_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('card', 'virement', 'cash') NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (plan_id) REFERENCES plans(id),
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
);


-- Vérification et ajout de la colonne manquante si nécessaire
ALTER TABLE users
ADD COLUMN IF NOT EXISTS subscription_end_date DATETIME AFTER subscription_status;

-- Index pour améliorer les performances
CREATE INDEX idx_user_subscription ON users(subscription_status, subscription_end_date);
CREATE INDEX idx_payments_user ON payments(user_id, status);