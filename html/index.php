<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dour Maroc - Accueil</title>
<link rel="stylesheet" href="../css/global.css">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .hero {
      background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e');
      background-size: cover;
      background-position: center;
      padding: 130px 0;
      color: white;
      text-shadow: 0 2px 6px rgba(0,0,0,0.6);
    }
    .category-img {
      width: 100%;
      height: 90px;
      object-fit: cover;
      border-radius: 10px;
    }
    .artisan-img {
      width: 140px;
      border-radius: 50%;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.html">Dour Maroc</a>
      
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="index.html">Accueil</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Produits</a></li>
          <li class="nav-item"><a class="nav-link" href="artisans.html">Artisans</a></li>
          <li class="nav-item"><a class="nav-link" href="about.html">À propos</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <header class="hero text-center">
    <div class="container">
      <h1 class="display-4 fw-bold">Crafted with Soul, Delivered with Care</h1>
      <p class="lead">Explorez l’authenticité de l’artisanat marocain, fait avec passion.</p>
      <a href="products.php" class="btn btn-primary btn-lg mt-3">Voir nos produits</a>
    </div>
  </header>

  <!-- Catégories -->
  <section class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Explorez nos Catégories</h2>
      <div class="row text-center">
        <div class="col-6 col-md-2 mb-4">
          <img src="https://images.unsplash.com/photo-1624811076332-e9be97c6f47e?auto=format&fit=crop&w=300&q=80" class="category-img" alt="Céramiques">
          <p class="mt-2">Céramiques</p>
        </div>
        <div class="col-6 col-md-2 mb-4">
          <img src="https://images.unsplash.com/photo-1615632042592-0591b37fcfb1?auto=format&fit=crop&w=300&q=80" class="category-img" alt="Tapis">
          <p class="mt-2">Tapis</p>
        </div>
        <div class="col-6 col-md-2 mb-4">
          <img src="https://images.unsplash.com/photo-1597262975002-c5c3b14bbd62?auto=format&fit=crop&w=300&q=80" class="category-img" alt="Lanternes">
          <p class="mt-2">Lanternes</p>
        </div>
        <div class="col-6 col-md-2 mb-4">
          <img src="https://images.unsplash.com/photo-1589487396620-dbe7a1b1b9b9?auto=format&fit=crop&w=300&q=80" class="category-img" alt="Bijoux">
          <p class="mt-2">Bijoux</p>
        </div>
        <div class="col-6 col-md-2 mb-4">
          <img src="https://images.unsplash.com/photo-1523473827532-0f53cd8d1f6e?auto=format&fit=crop&w=300&q=80" class="category-img" alt="Bois sculpté">
          <p class="mt-2">Bois</p>
        </div>
      </div>
    </div>
  </section>

 <!-- Produits en vedette -->
<section class="bg-light py-5">
  <div class="container">
    <h2 class="text-center mb-4">Produits en Vedette</h2>
    <div class="row g-4">
      <?php
      include '../php/connexion.php';
      $sql = "SELECT * FROM produits LIMIT 4";
      $stmt = $pdo->query($sql);
      
      while ($row = $stmt->fetch()) {
        echo '<div class="col-md-4 col-lg-3">
                <div class="card h-100">
                  <img src="../foto/' . htmlspecialchars($row['image']) . '" class="card-img-top" alt="' . htmlspecialchars($row['nom']) . '">
                  <div class="card-body">
                    <h5 class="card-title">' . htmlspecialchars($row['nom']) . '</h5>
                    <p class="card-text">' . htmlspecialchars($row['description']) . '</p>
                    <a href="products.php?id=' . $row['id'] . '" class="btn btn-outline-primary btn-sm">Voir plus</a>
                  </div>
                </div>
              </div>';
      }
      ?>
    </div>
  </div>
</section>

  <!-- Artisan vedette -->
  <section class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Zoom sur nos Artisans</h2>
      <div class="card text-center p-4 shadow-sm">
        <img src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=200&q=80" alt="Fatima Zohra" class="artisan-img mx-auto mb-3">
        <h4 class="fw-bold">Fatima Zohra</h4>
        <p>Potier de Fès, transmettant l’âme de l’art marocain à travers chaque pièce.</p>
        <a href="artisans.html" class="btn btn-primary btn-sm">Voir son profil</a>
      </div>
    </div>
  </section>

  <!-- Newsletter -->
  <section class="py-5 bg-light text-center">
    <div class="container">
      <h4 class="mb-3">Restez connecté(e) avec Dour Maroc</h4>
      <form class="d-flex justify-content-center gap-2">
        <input type="email" class="form-control w-25" placeholder="Votre e-mail">
        <button class="btn btn-primary">S’abonner</button>
      </form>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-light py-4">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
      <div>
        <h5>Dour Maroc</h5>
        <p class="mb-0">Artisanat authentique, du Maroc avec amour.</p>
      </div>
      <div class="text-end">
        <p class="mb-0 small">Projet réalisé par <strong>Safwa</strong> – 2025</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>

</body>
</html>
