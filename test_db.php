<?php
// Test de connexion à la base de données
include("../php/connexion.php");

echo "<h1>Test de la base de données Dour Maroc</h1>";

try {
    // Test de connexion
    echo "<h2>✅ Connexion réussie</h2>";
    
    // Test des tables
    $tables = ['administrateurs', 'artisans', 'categories', 'produits', 'messages', 'avis', 'newsletter'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p><strong>$table:</strong> $count enregistrement(s)</p>";
    }
    
    // Test des catégories
    echo "<h3>Catégories disponibles:</h3>";
    $stmt = $pdo->query("SELECT nom FROM categories WHERE actif = 1 ORDER BY ordre, nom");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($categories as $cat) {
        echo "<li>$cat</li>";
    }
    echo "</ul>";
    
    // Test des produits
    echo "<h3>Produits disponibles:</h3>";
    $stmt = $pdo->query("SELECT nom, prix, categorie_id FROM produits ORDER BY date_ajout DESC LIMIT 5");
    $produits = $stmt->fetchAll();
    echo "<ul>";
    foreach ($produits as $prod) {
        echo "<li>{$prod['nom']} - {$prod['prix']} DH</li>";
    }
    echo "</ul>";
    
    // Test des artisans
    echo "<h3>Artisans disponibles:</h3>";
    $stmt = $pdo->query("SELECT nom, prenom, specialite FROM artisans WHERE actif = 1 ORDER BY nom, prenom");
    $artisans = $stmt->fetchAll();
    echo "<ul>";
    foreach ($artisans as $art) {
        echo "<li>{$art['prenom']} {$art['nom']} - {$art['specialite']}</li>";
    }
    echo "</ul>";
    
    echo "<h2>🎉 Tous les tests sont passés avec succès !</h2>";
    echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Erreur de base de données</h2>";
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez que :</p>";
    echo "<ul>";
    echo "<li>La base de données 'dourmaroc' existe</li>";
    echo "<li>Le fichier sql/sql.sql a été importé</li>";
    echo "<li>Les paramètres de connexion sont corrects</li>";
    echo "</ul>";
}
?> 