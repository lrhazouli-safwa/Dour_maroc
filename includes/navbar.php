<?php
// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF'], '.php');
if (empty($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF'], '.html');
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Dour Maroc</a>
    
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'index') ? 'active' : ''; ?>" href="index.php">Accueil</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'products') ? 'active' : ''; ?>" href="products.php">Produits</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'artisans') ? 'active' : ''; ?>" href="artisans.html">Artisans</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'about') ? 'active' : ''; ?>" href="about.html">À propos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page == 'contact') ? 'active' : ''; ?>" href="contact.html">Contact</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</nav>