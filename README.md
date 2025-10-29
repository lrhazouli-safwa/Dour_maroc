# 🌍 Dour Maroc Voyages - Agence de Voyage en Ligne

Une plateforme moderne et professionnelle pour réserver vos vols, hôtels et activités de voyage au Maroc et dans le monde.

## 📋 Table des matières

- [Installation](#installation)
- [Configuration](#configuration)
- [Structure du projet](#structure-du-projet)
- [Fonctionnalités](#fonctionnalités)
  - [Réservation de vols](#réservation-de-vols)
  - [Réservation d'hôtels](#réservation-dhôtels)
  - [Forfaits vacances](#forfaits-vacances)
  - [Activités touristiques](#activités-touristiques)
- [Base de données](#base-de-données)
- [Sécurité](#sécurité)
- [Dépannage](#dépannage)

## 🚀 Installation

### Prérequis
- WAMP/XAMPP/MAMP installé
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Compte d'API pour les services de réservation (Amadeus, Booking.com, etc.)
- Navigateur web moderne

### Étapes d'installation

1. **Cloner ou télécharger le projet**
   ```bash
   # Placez le projet dans le dossier www de WAMP
   C:\wamp64\www\Dour_maroc\
   ```

2. **Démarrer WAMP**
   - Lancez WAMP
   - Attendez que l'icône devienne verte
   - Vérifiez que Apache et MySQL sont démarrés

3. **Créer la base de données**
   - Ouvrez phpMyAdmin : `http://localhost/phpmyadmin`
   - Créez une nouvelle base de données : `dourmaroc`
   - Importez le fichier `sql/sql.sql`

4. **Configurer la connexion**
   - Modifiez `php/connexion.php` si nécessaire
   - Vérifiez les paramètres de connexion à la base de données

5. **Tester le site**
   - Accédez à : `http://localhost/Dour_maroc/html/index.php`
   - Ou version HTML : `http://localhost/Dour_maroc/html/index.html`

## ⚙️ Configuration

### Paramètres de base de données
```php
// Dans php/connexion.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dourmaroc');
define('DB_USER', 'root');  // ou votre utilisateur
define('DB_PASS', '');      // votre mot de passe
```

### Configuration des uploads
```php
// Limites des fichiers
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
```

## 📁 Structure du projet

```
Dour_maroc/
├── css/
│   └── global.css          # Styles globaux
├── foto/                   # Images uploadées
├── html/                   # Pages du site
│   ├── index.php          # Page d'accueil (PHP)
│   ├── index.html         # Page d'accueil (HTML)
│   ├── products.php       # Gestion des produits
│   ├── about.html         # À propos
│   ├── artisans.html      # Artisans
│   └── contact.html       # Contact
├── includes/              # Fichiers inclus
│   ├── navbar.php         # Navigation
│   └── footer.php         # Pied de page
├── js/
│   └── script.js          # JavaScript
├── php/
│   └── connexion.php      # Connexion BDD
├── sql/
│   └── sql.sql           # Structure BDD
├── logs/                  # Fichiers de logs
├── .htaccess             # Configuration Apache
└── README.md             # Ce fichier
```

## ✨ Fonctionnalités

### Réservation de vols
- Recherche de vols multi-villes
- Comparaison des prix des compagnies aériennes
- Choix des sièges et options de bagages
- Paiement sécurisé en ligne

### Réservation d'hôtels
- Recherche d'hébergements par critères (étoiles, équipements, etc.)
- Avis et notations des hôtels
- Meilleur prix garanti
- Annulation gratuite

### Forfaits vacances
- Séjours tout compris
- Circuits organisés
- Voyages sur mesure
- Offres spéciales et promotions

### Activités touristiques
- Excursions guidées
- Billets pour attractions
- Expériences locales
- Location de voitures
- Système de newsletter
- Messages automatiques

### 🔒 Sécurité
- Protection contre les injections SQL
- Validation des fichiers uploadés
- Headers de sécurité
- Protection des fichiers sensibles

## 🗄️ Base de données

### Tables principales
- **administrateurs** : Gestion des comptes admin
- **artisans** : Informations sur les artisans
- **categories** : Catégories de produits
- **produits** : Catalogue des produits
- **messages** : Messages de contact
- **avis** : Avis clients
- **newsletter** : Abonnés newsletter

### Données de test incluses
- 1 administrateur (admin/admin123)
- 5 artisans avec profils complets
- 10 produits dans différentes catégories
- 4 avis clients
- 3 abonnés newsletter

## 🔒 Sécurité

### Mesures implémentées
- Protection contre les injections SQL
- Validation des fichiers uploadés
- Headers de sécurité HTTP
- Protection des dossiers sensibles
- Tokens CSRF
- Échappement des données

### Fichiers protégés
- `*.sql` : Fichiers de base de données
- `*.log` : Fichiers de logs
- `includes/` : Fichiers inclus
- `php/` : Fichiers PHP
- `logs/` : Dossier de logs

## 🛠️ Dépannage

### Problème : "Cannot GET /html/index.html"
**Solution :**
- Vérifiez que WAMP est démarré (icône verte)
- Utilisez l'URL correcte : `http://localhost/Dour_maroc/html/index.php`
- Vérifiez que Apache fonctionne sur le port 80

### Problème : Pages PHP téléchargées au lieu d'être affichées
**Solution :**
- Vérifiez que PHP est activé dans WAMP
- Redémarrez WAMP complètement
- Vérifiez la configuration Apache

### Problème : Erreur de connexion à la base de données
**Solution :**
- Vérifiez que MySQL est démarré
- Vérifiez les paramètres dans `php/connexion.php`
- Importez le fichier `sql/sql.sql`

### Problème : Images non affichées
**Solution :**
- Vérifiez les permissions du dossier `foto/`
- Vérifiez que les chemins d'images sont corrects
- Utilisez des images au format JPEG, PNG ou WebP

### Problème : Formulaire d'ajout de produit ne fonctionne pas
**Solution :**
- Vérifiez que la base de données est créée
- Vérifiez les permissions d'écriture dans `foto/`
- Vérifiez la taille des fichiers uploadés

## 📞 Support

Pour toute question ou problème :
1. Vérifiez d'abord la section dépannage
2. Consultez les logs dans le dossier `logs/`
3. Vérifiez la configuration de votre serveur

## 🎯 Fonctionnalités à venir

- [ ] Système de panier d'achat
- [ ] Paiement en ligne
- [ ] Gestion des commandes
- [ ] Blog/actualités
- [ ] Multilingue (arabe/français)
- [ ] API REST
- [ ] Application mobile

## 📄 Licence

Ce projet est développé par **Safwa** pour promouvoir l'artisanat marocain.

---

**Dour Maroc** - Artisanat authentique, du Maroc avec amour. 🇲🇦 