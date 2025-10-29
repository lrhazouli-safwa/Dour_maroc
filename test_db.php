<?php
// Test de connexion à la base de données
$host = 'localhost';
$dbname = 'dourmaroc';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connexion à la base de données réussie !<br><br>";
    
    // Vérifier si la table administrateurs existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'administrateurs'");
    if ($stmt->rowCount() > 0) {
        echo "La table 'administrateurs' existe.<br>";
        
        // Afficher la structure de la table
        $stmt = $pdo->query("DESCRIBE administrateurs");
        echo "<br><strong>Structure de la table administrateurs :</strong><br>";
        echo "<table border='1'>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Afficher les utilisateurs existants
        $stmt = $pdo->query("SELECT id, username, nom, prenom, email, role, actif FROM administrateurs");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($admins)) {
            echo "<br><strong>Utilisateurs administrateurs :</strong><br>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nom d'utilisateur</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th><th>Actif</th></tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['nom'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($admin['prenom'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($admin['email'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($admin['role'] ?? '') . "</td>";
                echo "<td>" . ($admin['actif'] ? 'Oui' : 'Non') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<br>Aucun administrateur trouvé dans la base de données.";
        }
    } else {
        echo "La table 'administrateurs' n'existe pas.<br>";
        
        // Créer la table si elle n'existe pas
        echo "<br>Création de la table 'administrateurs'...<br>";
        $sql = "CREATE TABLE IF NOT EXISTS administrateurs (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "Table 'administrateurs' créée avec succès.<br>";
        
        // Ajouter un administrateur par défaut
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
            echo "<br>Compte administrateur créé avec succès !<br>";
            echo "Nom d'utilisateur: admin<br>";
            echo "Mot de passe: admin123<br>";
            echo "<strong>Veuillez changer ce mot de passe après votre première connexion.</strong>";
        } else {
            echo "<br>Erreur lors de la création du compte administrateur.";
        }
    }
    
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    
    // Essayer de se connecter sans spécifier la base de données
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<br><br>Connexion au serveur MySQL réussie, mais impossible de se connecter à la base de données '$dbname'.<br>";
        
        // Essayer de créer la base de données
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "Base de données '$dbname' créée avec succès.<br>";
            echo "Veuvez rafraîchir cette page pour terminer la configuration.";
        } catch (PDOException $e2) {
            echo "Erreur lors de la création de la base de données : " . $e2->getMessage();
        }
    } catch (PDOException $e2) {
        echo "<br>Impossible de se connecter au serveur MySQL. Vérifiez vos paramètres de connexion.";
    }
}
?>
