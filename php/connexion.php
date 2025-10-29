<?php
// ===== CONFIGURATION DE LA BASE DE DONNÉES =====
define('DB_HOST', 'localhost');
define('DB_NAME', 'dourmaroc');
define('DB_USER', 'root');  // Utilisez 'dourmaroc_user' si vous avez créé un utilisateur dédié
define('DB_PASS', '');      // Mot de passe de votre base de données

// ===== GESTION DES ERREURS =====
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== CONNEXION À LA BASE DE DONNÉES =====
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Test de la connexion
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    // Log de l'erreur (en production, ne pas afficher les détails)
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    
    // Message d'erreur pour l'utilisateur
    die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
}

// ===== FONCTIONS UTILITAIRES =====

/**
 * Sécurise une chaîne de caractères
 */
function securiser($donnee) {
    return htmlspecialchars(trim($donnee), ENT_QUOTES, 'UTF-8');
}

/**
 * Valide une adresse email
 */
function validerEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Génère un token CSRF
 */
function genererTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verifierTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirige vers une URL
 */
function rediriger($url) {
    header("Location: $url");
    exit();
}

/**
 * Affiche un message flash
 */
function setMessageFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getMessageFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Formate un prix
 */
function formaterPrix($prix) {
    return number_format($prix, 2, ',', ' ') . ' DH';
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function estConnecte() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

/**
 * Vérifie si l'utilisateur est admin
 */
function estAdmin() {
    return estConnecte() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Log une action
 */
function loggerAction($action, $details = '') {
    $log = date('Y-m-d H:i:s') . " - " . $action;
    if ($details) {
        $log .= " - " . $details;
    }
    error_log($log . "\n", 3, '../logs/actions.log');
}

// ===== CONFIGURATION DES SESSIONS =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== CONFIGURATION DU FUSEAU HORAIRE =====
date_default_timezone_set('Africa/Casablanca');

// ===== CONSTANTES DE L'APPLICATION =====
define('SITE_NAME', 'Dour Maroc');
define('SITE_URL', 'http://localhost/Dour_maroc');
define('UPLOAD_DIR', '../foto/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// ===== FONCTIONS DE VALIDATION =====

/**
 * Valide un fichier image uploadé
 */
function validerImage($file) {
    $erreurs = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $erreurs[] = "Erreur lors de l'upload du fichier.";
        return $erreurs;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        $erreurs[] = "Le fichier est trop volumineux (max 5MB).";
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        $erreurs[] = "Type de fichier non autorisé (JPEG, PNG ou WebP seulement).";
    }
    
    return $erreurs;
}

/**
 * Upload un fichier image
 */
function uploadImage($file, $dossier = UPLOAD_DIR) {
    $nomFichier = uniqid() . '_' . basename($file['name']);
    $cheminComplet = $dossier . $nomFichier;
    
    if (move_uploaded_file($file['tmp_name'], $cheminComplet)) {
        return $nomFichier;
    }
    
    return false;
}

/**
 * Supprime un fichier image
 */
function supprimerImage($nomFichier, $dossier = UPLOAD_DIR) {
    $cheminComplet = $dossier . $nomFichier;
    if (file_exists($cheminComplet)) {
        return unlink($cheminComplet);
    }
    return false;
}

// ===== FONCTIONS DE BASE DE DONNÉES =====

/**
 * Récupère tous les produits
 */
function getProduits($limite = null, $categorie = null, $enVedette = false) {
    global $pdo;
    
    $sql = "SELECT p.*, c.nom as categorie_nom, a.nom as artisan_nom, a.prenom as artisan_prenom 
            FROM produits p 
            LEFT JOIN categories c ON p.categorie_id = c.id 
            LEFT JOIN artisans a ON p.artisan_id = a.id 
            WHERE 1=1";
    
    $params = [];
    
    if ($categorie) {
        $sql .= " AND c.nom = :categorie";
        $params[':categorie'] = $categorie;
    }
    
    if ($enVedette) {
        $sql .= " AND p.en_vedette = 1";
    }
    
    $sql .= " ORDER BY p.date_ajout DESC";
    
    if ($limite) {
        $sql .= " LIMIT :limite";
        $params[':limite'] = $limite;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Récupère un produit par ID
 */
function getProduit($id) {
    global $pdo;
    
    $sql = "SELECT p.*, c.nom as categorie_nom, a.nom as artisan_nom, a.prenom as artisan_prenom 
            FROM produits p 
            LEFT JOIN categories c ON p.categorie_id = c.id 
            LEFT JOIN artisans a ON p.artisan_id = a.id 
            WHERE p.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Récupère toutes les catégories
 */
function getCategories() {
    global $pdo;
    
    $sql = "SELECT * FROM categories WHERE actif = 1 ORDER BY ordre, nom";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Récupère tous les artisans
 */
function getArtisans() {
    global $pdo;
    
    $sql = "SELECT * FROM artisans WHERE actif = 1 ORDER BY nom, prenom";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

// ===== GESTION DES ERREURS PERSONNALISÉE =====
function afficherErreur($message, $type = 'danger') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

function afficherSucces($message) {
    return afficherErreur($message, 'success');
}

// ===== SÉCURITÉ =====

// Protection contre les attaques XSS
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

ini_set('max_execution_time', 30);
?>