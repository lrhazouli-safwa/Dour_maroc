<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=dourmaroc;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Création de la table administrateurs si elle n'existe pas
$pdo->exec("CREATE TABLE IF NOT EXISTS administrateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    actif TINYINT(1) NOT NULL DEFAULT 1,
    derniere_connexion DATETIME DEFAULT NULL,
    ip_connexion VARCHAR(45) DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    token_expiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Vérifier si des administrateurs existent
$stmt = $pdo->query("SELECT COUNT(*) as count FROM administrateurs");
$count = $stmt->fetch()['count'];

if ($count == 0) {
    // Créer un administrateur par défaut
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $nom = 'Admin';
    $prenom = 'Système';
    $email = 'admin@dourmaroc.com';
    $role = 'admin';
    
    $stmt = $pdo->prepare("INSERT INTO administrateurs 
        (username, password, nom, prenom, email, role) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$username, $password, $nom, $prenom, $email, $role])) {
        echo "Compte administrateur créé avec succès !<br>";
        echo "Nom d'utilisateur: admin<br>";
        echo "Mot de passe: admin123<br>";
        echo "<strong>Veuillez changer ce mot de passe après votre première connexion.</strong>";
    } else {
        echo "Erreur lors de la création du compte administrateur.";
    }
} else {
    echo "Des comptes administrateurs existent déjà dans la base de données.";
}

// Afficher tous les administrateurs
$stmt = $pdo->query("SELECT id, username, nom, prenom, email, role, actif FROM administrateurs");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($admins)) {
    echo "<h3>Liste des administrateurs :</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nom d'utilisateur</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th><th>Actif</th></tr>";
    foreach ($admins as $admin) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['nom']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['prenom']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['role']) . "</td>";
        echo "<td>" . ($admin['actif'] ? 'Oui' : 'Non') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
