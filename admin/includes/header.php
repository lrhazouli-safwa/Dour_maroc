<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Vérifier l'inactivité (30 minutes)
$inactive = 1800; // 30 minutes en secondes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    // Dernière activité il y a plus de 30 minutes
    session_unset();
    session_destroy();
    header("Location: ../login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time(); // Mise à jour du temps de la dernière activité

// Vérifier le jeton CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Erreur de sécurité. Veuillez rafraîchir la page et réessayer.');
    }
}

// Fonction pour générer un jeton CSRF
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Récupérer les informations de l'administrateur
$admin_id = $_SESSION['admin_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $admin_id]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        session_destroy();
        header("Location: ../login.php?error=session_expired");
        exit();
    }
    
    // Mettre à jour les informations de session si nécessaire
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_nom'] = $admin['nom'];
    $_SESSION['admin_prenom'] = $admin['prenom'];
    
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des informations admin: " . $e->getMessage());
    die("Une erreur est survenue. Veuillez réessayer plus tard.");
}

// Fonction pour vérifier les permissions
function has_permission($required_role) {
    $user_role = $_SESSION['admin_role'] ?? 'user';
    $roles = ['superadmin' => 3, 'admin' => 2, 'editor' => 1, 'user' => 0];
    
    return ($roles[$user_role] ?? 0) >= ($roles[$required_role] ?? 0);
}

// Vérifier si la page actuelle nécessite une permission spécifique
$current_page = basename($_SERVER['PHP_SELF']);
$restricted_pages = [
    'settings.php' => 'admin',
    'users.php' => 'admin',
    'backup.php' => 'superadmin'
];

if (isset($restricted_pages[$current_page]) && !has_permission($restricted_pages[$current_page])) {
    header("Location: dashboard.php?error=permission_denied");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin - Dour Maroc</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../foto/dour.png" type="image/x-icon">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, #0b5ed7 100%);
            color: white;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .sidebar-header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .sidebar-menu .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-menu .nav-link:hover, 
        .sidebar-menu .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
        }
        
        .sidebar-menu .nav-link .badge {
            margin-left: auto;
            background-color: var(--danger-color);
            color: white;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        
        /* Top Navigation */
        .top-navbar {
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-navbar .search-box {
            position: relative;
            max-width: 400px;
            width: 100%;
        }
        
        .top-navbar .search-box input {
            padding-left: 40px;
            border-radius: 20px;
            border: 1px solid #dee2e6;
            width: 100%;
        }
        
        .top-navbar .search-box i {
            position: absolute;
            left: 15px;
            top: 10px;
            color: #6c757d;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
        }
        
        .user-menu .dropdown-toggle {
            display: flex;
            align-items: center;
            color: #333;
            text-decoration: none;
        }
        
        .user-menu .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .user-menu .user-info {
            text-align: right;
            margin-right: 10px;
        }
        
        .user-menu .user-name {
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }
        
        .user-menu .user-role {
            font-size: 0.8rem;
            color: #6c757d;
            margin: 0;
            line-height: 1.2;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Stats Cards */
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0 5px;
        }
        
        .stat-card .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }
        
        .table tbody tr {
            transition: background-color 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        /* Buttons */
        .btn {
            border-radius: 5px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.3s;
        }
        
        .btn-sm {
            padding: 4px 10px;
            font-size: 0.8rem;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        /* Badges */
        .badge {
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 10px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .top-navbar {
                margin-left: 0;
            }
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
            margin-right: 15px;
        }
        
        @media (max-width: 992px) {
            .sidebar-toggle {
                display: block;
            }
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../foto/dour.png" alt="Dour Maroc" class="img-fluid">
            <h5 class="mt-2 mb-0">Espace Admin</h5>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="destinations.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['destinations.php', 'destination-edit.php', 'destination-add.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt"></i> Destinations
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="offres.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['offres.php', 'offre-edit.php', 'offre-add.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i> Offres Spéciales
                        <span class="badge rounded-pill bg-danger">3</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="forfaits.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['forfaits.php', 'forfait-edit.php', 'forfait-add.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-suitcase"></i> Forfaits
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="vols.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['vols.php', 'vol-edit.php', 'vol-add.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-plane"></i> Vols
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="reservations.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['reservations.php', 'reservation-view.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check"></i> Réservations
                        <span class="badge rounded-pill bg-danger">5</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="clients.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['clients.php', 'client-view.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Clients
                    </a>
                </li>
                
                <?php if (has_permission('admin')): ?>
                <li class="nav-item mt-4">
                    <div class="px-3 text-uppercase small fw-bold text-white-50">Administration</div>
                </li>
                
                <li class="nav-item">
                    <a href="utilisateurs.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['utilisateurs.php', 'utilisateur-edit.php', 'utilisateur-add.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i> Utilisateurs
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="parametres.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['parametres.php', 'parametres-general.php', 'parametres-email.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Paramètres
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="sauvegardes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sauvegardes.php' ? 'active' : ''; ?>">
                        <i class="fas fa-database"></i> Sauvegardes
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item mt-4">
                    <div class="px-3 text-uppercase small fw-bold text-white-50">Autres</div>
                </li>
                
                <li class="nav-item">
                    <a href="profil.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Mon Profil
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../index.php" target="_blank" class="nav-link">
                        <i class="fas fa-external-link-alt"></i> Voir le site
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0">
                    <?php 
                    $page_titles = [
                        'dashboard.php' => 'Tableau de bord',
                        'destinations.php' => 'Gestion des destinations',
                        'destination-edit.php' => 'Modifier une destination',
                        'destination-add.php' => 'Ajouter une destination',
                        'offres.php' => 'Gestion des offres spéciales',
                        'offre-edit.php' => 'Modifier une offre',
                        'offre-add.php' => 'Ajouter une offre',
                        'forfaits.php' => 'Gestion des forfaits',
                        'forfait-edit.php' => 'Modifier un forfait',
                        'forfait-add.php' => 'Ajouter un forfait',
                        'vols.php' => 'Gestion des vols',
                        'vol-edit.php' => 'Modifier un vol',
                        'vol-add.php' => 'Ajouter un vol',
                        'reservations.php' => 'Gestion des réservations',
                        'reservation-view.php' => 'Détails de la réservation',
                        'clients.php' => 'Gestion des clients',
                        'client-view.php' => 'Profil client',
                        'utilisateurs.php' => 'Gestion des utilisateurs',
                        'utilisateur-edit.php' => 'Modifier un utilisateur',
                        'utilisateur-add.php' => 'Ajouter un utilisateur',
                        'parametres.php' => 'Paramètres du site',
                        'parametres-general.php' => 'Paramètres généraux',
                        'parametres-email.php' => 'Paramètres email',
                        'sauvegardes.php' => 'Sauvegardes',
                        'profil.php' => 'Mon profil'
                    ];
                    echo $page_titles[basename($_SERVER['PHP_SELF'])] ?? 'Tableau de bord';
                    ?>
                </h5>
            </div>
            
            <div class="user-menu">
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar">
                                <?php 
                                $initials = '';
                                if (!empty($_SESSION['admin_prenom'])) $initials .= substr($_SESSION['admin_prenom'], 0, 1);
                                if (!empty($_SESSION['admin_nom'])) $initials .= substr($_SESSION['admin_nom'], 0, 1);
                                echo $initials ?: 'A';
                                ?>
                            </div>
                            <div class="user-info d-none d-md-block">
                                <p class="user-name mb-0">
                                    <?php echo htmlspecialchars($_SESSION['admin_prenom'] . ' ' . $_SESSION['admin_nom']); ?>
                                </p>
                                <p class="user-role mb-0">
                                    <?php 
                                    $roles = [
                                        'superadmin' => 'Super Administrateur',
                                        'admin' => 'Administrateur',
                                        'editor' => 'Éditeur',
                                        'user' => 'Utilisateur'
                                    ];
                                    echo $roles[$_SESSION['admin_role']] ?? 'Utilisateur';
                                    ?>
                                </p>
                            </div>
                            <i class="fas fa-chevron-down ms-2"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="profil.php">
                                <i class="fas fa-user me-2"></i> Mon Profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="parametres.php">
                                <i class="fas fa-cog me-2"></i> Paramètres
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Flash Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    $messages = [
                        'saved' => 'Les modifications ont été enregistrées avec succès.',
                        'added' => 'L\'élément a été ajouté avec succès.',
                        'deleted' => 'L\'élément a été supprimé avec succès.',
                        'profile_updated' => 'Votre profil a été mis à jour avec succès.'
                    ];
                    echo $messages[$_GET['success']] ?? 'Opération effectuée avec succès.';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    $errors = [
                        'not_found' => 'L\'élément demandé est introuvable.',
                        'permission_denied' => 'Vous n\'avez pas la permission d\'accéder à cette page.',
                        'invalid_request' => 'Requête invalide.',
                        'upload_failed' => 'Échec du téléchargement du fichier.'
                    ];
                    echo $errors[$_GET['error']] ?? 'Une erreur est survenue. Veuillez réessayer.';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['flash_message'];
                    unset($_SESSION['flash_message']);
                    unset($_SESSION['flash_type']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
