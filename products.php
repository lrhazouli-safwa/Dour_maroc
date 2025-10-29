<?php
session_start();
include("../php/connexion.php");

// Vérification admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit();
}

$message = "";

// Récupération des catégories depuis la base de données
$categories = [];
try {
    $stmt = $pdo->query("SELECT nom FROM categories WHERE actif = 1 ORDER BY ordre, nom");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Fallback vers les catégories par défaut
$categories = ['Céramique', 'Tapis', 'Lanternes', 'Bijoux', 'Bois sculpté', 'Cuir'];
}

// Récupération des produits existants
$produits = [];
try {
    $sql = "SELECT p.*, c.nom as categorie_nom, a.nom as artisan_nom, a.prenom as artisan_prenom 
            FROM produits p 
            LEFT JOIN categories c ON p.categorie_id = c.id 
            LEFT JOIN artisans a ON p.artisan_id = a.id 
            ORDER BY p.date_ajout DESC";
    $stmt = $pdo->query($sql);
    $produits = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = '<div class="alert alert-warning">Erreur lors du chargement des produits.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $prix = floatval($_POST['prix']);
    $categorie = htmlspecialchars($_POST['categorie']);

    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../foto/';
        $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;

        // Vérification du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                try {
                    // Récupérer l'ID de la catégorie
                    $stmtCat = $pdo->prepare("SELECT id FROM categories WHERE nom = :nom");
                    $stmtCat->execute([':nom' => $categorie]);
                    $categorieId = $stmtCat->fetchColumn();
                    
                    if (!$categorieId) {
                        // Si la catégorie n'existe pas, créer une catégorie par défaut
                        $stmtCat = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (:nom, :description)");
                        $stmtCat->execute([':nom' => $categorie, ':description' => 'Catégorie ' . $categorie]);
                        $categorieId = $pdo->lastInsertId();
                    }
                    
                    $sql = "INSERT INTO produits (nom, description, prix, categorie_id, image, image_url) 
                            VALUES (:nom, :description, :prix, :categorie_id, :image, :image_url)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':nom' => $nom,
                        ':description' => $description,
                        ':prix' => $prix,
                        ':categorie_id' => $categorieId,
                        ':image' => $imageName,
                        ':image_url' => 'foto/' . $imageName
                    ]);

                    $message = '<div class="alert alert-success">Produit ajouté avec succès!</div>';
                    
                    // Recharger les produits
                    $stmt = $pdo->query($sql);
                    $produits = $stmt->fetchAll();
                } catch (PDOException $e) {
                    $message = '<div class="alert alert-danger">Erreur: ' . $e->getMessage() . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'upload de l\'image.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Format d\'image non supporté (JPEG, PNG ou WebP seulement).</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Veuillez sélectionner une image valide.</div>';
    }
}

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Dour Maroc</title>
  <link rel="stylesheet" href="../css/global.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-store me-2"></i>Dour Maroc - Admin
            </a>
            
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=1">
                            <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary ms-2 px-3" href="login.php" style="border-radius:6px;">Connexion Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

  <div class="container py-5">
        <div class="row">
            <!-- Formulaire d'ajout -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Ajouter un produit
                        </h5>
                    </div>
                    <div class="card-body">
    <?php echo $message; ?>
    
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
        <label for="nom" class="form-label">Nom du produit</label>
        <input type="text" class="form-control" id="nom" name="nom" required>
      </div>
      
                            <div class="mb-3">
        <label for="prix" class="form-label">Prix (DH)</label>
        <input type="number" step="0.01" class="form-control" id="prix" name="prix" required>
      </div>
      
                            <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
      </div>
      
                            <div class="mb-3">
        <label for="categorie" class="form-label">Catégorie</label>
        <select class="form-select" id="categorie" name="categorie" required>
          <option value="">Choisir une catégorie</option>
          <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      
                            <div class="mb-3">
        <label for="image" class="form-label">Image du produit</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png, image/webp" required>
        <div class="form-text">Formats acceptés: JPEG, PNG, WebP (max 5MB)</div>
      </div>
      
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Ajouter
                            </button>
    </form>
                    </div>
                </div>
  </div>
            
            <!-- Liste des produits -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Produits existants (<?php echo count($produits); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($produits)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun produit ajouté pour le moment.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Nom</th>
                                            <th>Catégorie</th>
                                            <th>Prix</th>
                                            <th>Artisan</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produits as $produit): ?>
                                            <tr>
                                                <td>
                                                    <?php if (file_exists('../' . $produit['image_url'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($produit['image_url']); ?>" 
                                                             alt="<?php echo htmlspecialchars($produit['nom']); ?>" 
                                                             class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($produit['nom']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo substr(htmlspecialchars($produit['description']), 0, 50); ?>...</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($produit['categorie_nom']); ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo number_format($produit['prix'], 2, ',', ' '); ?> DH</strong>
                                                </td>
                                                <td>
                                                    <?php if ($produit['artisan_nom']): ?>
                                                        <?php echo htmlspecialchars($produit['artisan_prenom'] . ' ' . $produit['artisan_nom']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo date('d/m/Y', strtotime($produit['date_ajout'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="http://localhost/Dour_maroc/html/modifie_produit.php?id=<?php echo $produit['id']; ?>" class="btn btn-outline-primary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="http://localhost/Dour_maroc/html/supprime_produit.php?id=<?php echo $produit['id']; ?>" class="btn btn-outline-danger" title="Supprimer" onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-shield-alt me-2"></i>
                Zone d'administration - Dour Maroc
            </p>
            <small class="text-muted">
                Connecté en tant que <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            </small>
        </div>
    </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>