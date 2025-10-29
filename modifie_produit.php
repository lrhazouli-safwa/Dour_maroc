<?php
session_start();
include("../php/connexion.php");

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">ID du produit manquant ou invalide.</div>';
    exit();
}

$id = intval($_GET['id']);
$message = '';

// Récupérer les catégories
$categories = [];
try {
    $stmt = $pdo->query("SELECT nom FROM categories WHERE actif = 1 ORDER BY ordre, nom");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = ['Céramique', 'Tapis', 'Lanternes', 'Bijoux', 'Bois sculpté', 'Cuir'];
}

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch();
if (!$produit) {
    echo '<div class="alert alert-danger">Produit introuvable.</div>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $prix = floatval($_POST['prix']);
    $categorie = htmlspecialchars($_POST['categorie']);

    // Récupérer l'ID de la catégorie
    $stmtCat = $pdo->prepare("SELECT id FROM categories WHERE nom = :nom");
    $stmtCat->execute([':nom' => $categorie]);
    $categorieId = $stmtCat->fetchColumn();
    if (!$categorieId) {
        $stmtCat = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (:nom, :description)");
        $stmtCat->execute([':nom' => $categorie, ':description' => 'Catégorie ' . $categorie]);
        $categorieId = $pdo->lastInsertId();
    }

    $sql = "UPDATE produits SET nom = :nom, description = :description, prix = :prix, categorie_id = :categorie_id WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom' => $nom,
        ':description' => $description,
        ':prix' => $prix,
        ':categorie_id' => $categorieId,
        ':id' => $id
    ]);
    header("Location: products.php?msg=modification");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un produit</title>
    <link rel="stylesheet" href="../css/global.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">Modifier le produit</h2>
    <?php echo $message; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="nom" class="form-label">Nom du produit</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($produit['nom']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="prix" class="form-label">Prix (DH)</label>
            <input type="number" step="0.01" class="form-control" id="prix" name="prix" value="<?php echo htmlspecialchars($produit['prix']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($produit['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="categorie" class="form-label">Catégorie</label>
            <select class="form-select" id="categorie" name="categorie" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($cat == $produit['categorie_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
        <a href="products.php" class="btn btn-secondary ms-2">Annuler</a>
    </form>
</div>
</body>
</html> 