<?php
session_start();
include("../php/connexion.php");
$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];

// Supprimer un produit
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    unset($_SESSION['panier'][$id]);
    header('Location: panier.php');
    exit;
}

// Vider le panier
if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    header('Location: panier.php');
    exit;
}

// Récupérer les infos produits
$produits = [];
$total = 0;
if (!empty($panier)) {
    $ids = array_keys($panier);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $produits = $stmt->fetchAll(PDO::FETCH_UNIQUE);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Votre panier - Dour Maroc</title>
  <link rel="stylesheet" href="../css/global.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include("../includes/navbar.php"); ?>
  <div class="container py-5">
    <h1 class="mb-4">Votre panier</h1>
    <?php if (empty($panier)): ?>
      <div class="alert alert-info">Votre panier est vide.</div>
      <a href="catalogue.php" class="btn btn-primary">Retourner au catalogue</a>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Produit</th>
              <th>Prix unitaire</th>
              <th>Quantité</th>
              <th>Sous-total</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($panier as $id => $item):
              $prod = $produits[$id];
              $sous_total = $prod['prix'] * $item['quantite'];
              $total += $sous_total;
            ?>
            <tr>
              <td><?= htmlspecialchars($prod['nom']) ?></td>
              <td><?= number_format($prod['prix'], 2, ',', ' ') ?> DH</td>
              <td><?= $item['quantite'] ?></td>
              <td><?= number_format($sous_total, 2, ',', ' ') ?> DH</td>
              <td>
                <a href="panier.php?supprimer=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce produit ?');">Supprimer</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">Total</th>
              <th><?= number_format($total, 2, ',', ' ') ?> DH</th>
            </tr>
          </tfoot>
        </table>
      </div>
      <div class="d-flex justify-content-between mt-4">
        <a href="destinations.html" class="btn btn-outline-secondary">Retour aux destinations</a>
        <a href="panier.php?vider=1" class="btn btn-warning" onclick="return confirm('Vider le panier ?');">Vider le panier</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>