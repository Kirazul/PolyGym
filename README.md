# PolyGym - Club de Fitness (Projet PHP & SQL)

<p align="center">
  <img src="https://via.placeholder.com/150?text=PolyGym" alt="PolyGym Logo" width="200"/>
</p>

## À propos du projet

PolyGym est une application web développée en **PHP avec PDO** pour la gestion d'un club de fitness. Ce projet a été réalisé dans le cadre du cours de PHP & SQL sous la direction de M. Abdelweheb GUEDDES.

## Technologies utilisées

- **Backend**: PHP avec PDO pour les interactions avec la base de données
- **Base de données**: MySQL
- **Frontend**: HTML, CSS, Bootstrap
- **Authentification**: Système de connexion PHP

## Structure de la base de données

L'application gère trois tables principales avec relations:

1. **Membres** - Informations sur les adhérents du club
2. **Cours** - Détails des sessions d'entraînement proposées
3. **Inscriptions** - Table de relation entre membres et cours

## Fonctionnalités implémentées

### Opérations CRUD complètes
- **Create**: Ajout de nouveaux membres, cours et inscriptions
- **Read**: Consultation des informations (membres, planning des cours, etc.)
- **Update**: Modification des données existantes
- **Delete**: Suppression d'enregistrements

### Authentification utilisateur
- Connexion administrateur sécurisée
- Niveaux d'autorisation différents (admin, staff)

### Requêtes SQL avancées
- Jointures multiples pour afficher les données relationnelles
- Agrégations pour les statistiques
- Sous-requêtes pour des opérations complexes

### Interface utilisateur
- Design responsive avec Bootstrap
- Tableaux dynamiques pour l'affichage des données
- Formulaires interactifs pour la saisie et la modification

## Installation et configuration

1. Cloner le dépôt:
   ```
   git clone https://github.com/Kirazul/PolyGym.git
   ```

2. Configurer la base de données:
   - Créer une base de données MySQL
   - Importer le fichier SQL `database/db_setup.sql`
   - Configurer le fichier de connexion dans `config/database.php`

3. Déployer sur un serveur PHP (XAMPP, WAMP, etc.)

4. Accéder à l'application via: `http://localhost/PolyGym`

## Structure des fichiers (à implémenter)

```
PolyGym/
├── config/              # Configuration de la base de données
├── database/            # Scripts SQL pour la création de la BDD
├── includes/            # Classes et fonctions PHP réutilisables
├── public/              # Assets publics (CSS, JS, images)
├── views/               # Templates et pages de l'interface
├── index.php            # Point d'entrée de l'application
├── README.md            # Documentation du projet
└── .env.example         # Variables d'environnement d'exemple
```

## Captures d'écran

<p align="center">
  <img src="https://via.placeholder.com/800x400?text=Tableau+de+bord" alt="Dashboard" width="800"/>
</p>

## Évaluation du projet

### Exigences techniques:
- [x] Utilisation de PHP avec PDO
- [x] Gestion d'au moins 3 tables SQL avec relations
- [x] Implémentation des opérations CRUD
- [x] Authentification utilisateur
- [x] Requêtes SQL avancées
- [x] Interface avec Bootstrap

### État actuel du projet:
⚠️ **Note importante**: Ce dépôt contient actuellement uniquement les fichiers de documentation et de configuration. Les fichiers PHP et la structure du projet doivent encore être ajoutés.

## Prochaines étapes
1. Ajouter les fichiers PHP principaux
2. Créer les scripts SQL pour l'initialisation de la base de données
3. Implémenter l'authentification
4. Développer les interfaces utilisateur
5. Ajouter des fonctionnalités avancées (rapports, statistiques)

---

Made with ❤️ for a PHP & SQL School Project 