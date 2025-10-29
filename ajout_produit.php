<?php
session_start();
include("connexion.php");

// Vérifier si l'utilisateur est admin (optionnel)
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

$message = "";
$categories = ['Céramique', 'Tapis', 'Lanternes', 'Bijoux', 'Bois sculpté', 'Cuir'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $prix = floatval($_POST['prix']);
    $categorie = htmlspecialchars($_POST['categorie']);
    $artisan_id = intval($_POST['artisan_id']);

    // Gestion de l'upload de l'image
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../foto/';
        $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;
        
        // Vérifier le type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Insertion en base de données
                try {
                    $sql = "INSERT INTO produits (nom, description, prix, image, image_url, categorie, artisan_id) 
                            VALUES (:nom, :description, :prix, :image, :image_url, :categorie, :artisan_id)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':nom' => $nom,
                        ':description' => $description,
                        ':prix' => $prix,
                        ':image' => $imageName,
                        ':image_url' => 'foto/' . $imageName,
                        ':categorie' => $categorie,
                        ':artisan_id' => $artisan_id ?: NULL
                    ]);

                    $message = "<div class='alert alert-success'>Produit ajouté avec succès!</div>";
                } catch (PDOException $e) {
                    $message = "<div class='alert alert-danger'>Erreur: " . $e->getMessage() . "</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Erreur lors de l'upload de l'image.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Format d'image non supporté (JPEG, PNG ou WebP seulement).</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Veuillez sélectionner une image valide.</div>";
    }
}

// Récupérer la liste des artisans pour le select
$artisans = [];
try {
    $stmt = $pdo->query("SELECT id, nom FROM artisans ORDER BY nom");
    $artisans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "<div class='alert alert-danger'>Erreur lors de la récupération des artisans: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit - Dour Maroc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
</head>
<body>
    <?php include("../includes/navbar.php"); ?>
    
    <div class="container py-5">
        <h1 class="mb-4">Ajouter un nouveau produit</h1>
        
        <?php echo $message; ?>
        
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom du produit</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            
            <div class="col-md-6">
                <label for="prix" class="form-label">Prix (DH)</label>
                <input type="number" step="0.01" class="form-control" id="prix" name="prix" required>
            </div>
            
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            
            <div class="col-md-6">
                <label for="categorie" class="form-label">Catégorie</label>
                <select class="form-select" id="categorie" name="categorie" required>
                    <option value="">Choisir une catégorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>"><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-6">
                <label for="artisan_id" class="form-label">Artisan (optionnel)</label>
                <select class="form-select" id="artisan_id" name="artisan_id">
                    <option value="">Non attribué</option>
                    <?php foreach ($artisans as $artisan): ?>
                        <option value="<?= $artisan['id'] ?>"><?= $artisan['nom'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-12">
                <label for="image" class="form-label">Image du produit</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png, image/webp" required>
                <div class="form-text">Formats acceptés: JPEG, PNG, WebP (max 5MB)</div>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Ajouter le produit</button>
                <a href="../admin/gestion_produits.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

    <?php include("../includes/footer.php"); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>