<?php
include("../php/connexion.php");
if (!isset($_GET['id'])) { header('Location: catalogue.php'); exit; }
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch();
if (!$produit) { echo "Produit introuvable."; exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($produit['nom']) ?> - Dour Maroc</title>
  <link rel="stylesheet" href="../css/global.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include("../includes/navbar.php"); ?>
  <div class="container py-5">
    <h1><?= htmlspecialchars($produit['nom']) ?></h1>
    <img src="../<?= htmlspecialchars($produit['image_url']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" style="max-width:300px;">
    <p><?= htmlspecialchars($produit['description']) ?></p>
    <p>Prix : <?= number_format($produit['prix'], 2, ',', ' ') ?> DH</p>
    <a href="ajout_panier.php?id=<?= $produit['id'] ?>" class="btn btn-primary">Ajouter au panier</a>
    <a href="catalogue.php" class="btn btn-outline-secondary">Retour au catalogue</a>
  </div>
</body>
</html> 