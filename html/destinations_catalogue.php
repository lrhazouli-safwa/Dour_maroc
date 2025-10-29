<?php
session_start();
include("../php/connexion.php");

// Récupération des destinations
$destinations = [];
try {
    $stmt = $pdo->query("SELECT * FROM destinations WHERE actif = 1 ORDER BY nom");
    $destinations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des destinations : " . $e->getMessage();
}

// Récupération des offres
$offres = [];
try {
    $sql = "SELECT o.*, d.nom as destination_nom FROM offres o 
            LEFT JOIN destinations d ON o.destination_id = d.id 
            WHERE o.actif = 1 
            ORDER BY o.en_vedette DESC, o.date_ajout DESC";
    $stmt = $pdo->query($sql);
    $offres = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des offres : " . $e->getMessage();
}

// Récupération des catégories pour le filtre
$categories = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT categorie FROM offres WHERE categorie IS NOT NULL AND actif = 1 ORDER BY categorie");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des catégories : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Destinations & Catalogue - Dour Maroc Voyages</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        .destination-card, .offre-card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            height: 400px;
            transition: transform 0.3s;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .destination-card:hover, .offre-card:hover {
            transform: translateY(-10px);
        }
        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        .destination-card:hover .card-img, .offre-card:hover .card-img {
            transform: scale(1.1);
        }
        .card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
            color: white;
            padding: 30px 20px 20px;
        }
        .card-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.9);
            color: #333;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .search-bar {
            position: relative;
            margin-bottom: 40px;
        }
        .search-bar input {
            padding: 15px 25px;
            border-radius: 30px;
            border: 1px solid #ddd;
            width: 100%;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .search-bar i {
            position: absolute;
            right: 20px;
            top: 17px;
            color: #666;
        }
        .filter-btn {
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 8px 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        .filter-btn:hover, .filter-btn.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
        .price-tag {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .section-title {
            position: relative;
            margin-bottom: 50px;
            text-align: center;
        }
        .section-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: #0d6efd;
            margin: 15px auto 0;
        }
    </style>
</head>
<body>
    <?php include('includes/navbar.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section bg-dark text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Découvrez nos Destinations & Offres</h1>
                    <p class="lead mb-4">Trouvez la destination parfaite pour votre prochain voyage au Maroc.</p>
                </div>
                <div class="col-lg-6">
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Rechercher une destination ou une offre...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtres -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="filter-container">
                        <button class="filter-btn active" data-filter="all">Tout voir</button>
                        <button class="filter-btn" data-filter="destination">Destinations</button>
                        <button class="filter-btn" data-filter="offre">Offres spéciales</button>
                        <?php foreach ($categories as $categorie): ?>
                            <button class="filter-btn" data-category="<?php echo htmlspecialchars($categorie); ?>">
                                <?php echo htmlspecialchars($categorie); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Destinations -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title">Nos Destinations</h2>
            <div class="row" id="destinationsContainer">
                <?php foreach ($destinations as $destination): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-type="destination">
                        <div class="destination-card">
                            <img src="<?php echo htmlspecialchars($destination['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($destination['nom']); ?>" 
                                 class="card-img">
                            <?php if ($destination['tag']): ?>
                                <div class="card-tag"><?php echo htmlspecialchars($destination['tag']); ?></div>
                            <?php endif; ?>
                            <div class="card-overlay">
                                <h3 class="h4 mb-2"><?php echo htmlspecialchars($destination['nom']); ?></h3>
                                <p class="mb-3"><?php echo htmlspecialchars($destination['description_courte']); ?></p>
                                <a href="destination.php?id=<?php echo $destination['id']; ?>" class="btn btn-outline-light">
                                    Découvrir
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Offres Spéciales -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Nos Offres Spéciales</h2>
            <div class="row" id="offresContainer">
                <?php foreach ($offres as $offre): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-type="offre" data-category="<?php echo htmlspecialchars($offre['categorie']); ?>">
                        <div class="offre-card">
                            <img src="<?php echo htmlspecialchars($offre['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($offre['titre']); ?>" 
                                 class="card-img">
                            <?php if ($offre['en_vedette']): ?>
                                <div class="card-tag">En vedette</div>
                            <?php endif; ?>
                            <div class="card-overlay">
                                <h3 class="h4 mb-2"><?php echo htmlspecialchars($offre['titre']); ?></h3>
                                <?php if ($offre['destination_nom']): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <?php echo htmlspecialchars($offre['destination_nom']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="mb-2">
                                    <span class="price-tag">
                                        À partir de <?php echo number_format($offre['prix'], 0, ',', ' '); ?> DH
                                    </span>
                                </p>
                                <a href="offre.php?id=<?php echo $offre['id']; ?>" class="btn btn-primary">
                                    Voir l'offre
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white text-center">
        <div class="container">
            <h2 class="mb-4">Vous ne trouvez pas votre bonheur ?</h2>
            <p class="lead mb-4">Contactez-nous pour une offre personnalisée selon vos envies.</p>
            <a href="contact.php" class="btn btn-light btn-lg">Nous contacter</a>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtrage des cartes
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const destinationCards = document.querySelectorAll('[data-type="destination"]');
            const offreCards = document.querySelectorAll('[data-type="offre"]');
            const searchInput = document.getElementById('searchInput');

            // Gestion des filtres
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    const category = this.getAttribute('data-category');

                    // Mise à jour des boutons actifs
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Filtrage des cartes
                    const allCards = [...destinationCards, ...offreCards];
                    allCards.forEach(card => {
                        const cardType = card.getAttribute('data-type');
                        const cardCategory = card.getAttribute('data-category');
                        
                        let show = false;
                        
                        if (filter === 'all') {
                            show = true;
                        } else if (filter && cardType === filter) {
                            show = true;
                        } else if (category && cardCategory === category) {
                            show = true;
                        }
                        
                        card.style.display = show ? 'block' : 'none';
                    });
                });
            });

            // Recherche en temps réel
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const allCards = [...destinationCards, ...offreCards];

                allCards.forEach(card => {
                    const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
                    const description = card.querySelector('p')?.textContent.toLowerCase() || '';
                    
                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
