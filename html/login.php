<?php
session_start();
include("../php/connexion.php");

// Vérifier les tentatives de connexion
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

$error = "";
$lockout_time = 300; // 5 minutes en secondes
$max_attempts = 5;

// Vérifier le verrouillage du compte
if ($_SESSION['login_attempts'] >= $max_attempts) {
    $time_since_last_attempt = time() - $_SESSION['last_attempt_time'];
    if ($time_since_last_attempt < $lockout_time) {
        $remaining_time = ceil(($lockout_time - $time_since_last_attempt) / 60);
        $error = "Trop de tentatives de connexion. Veuillez réessayer dans $remaining_time minutes.";
    } else {
        // Réinitialiser le compteur après la période de verrouillage
        $_SESSION['login_attempts'] = 0;
    }
}

// Si déjà connecté, rediriger vers le tableau de bord
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: admin/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);
    
    if (!empty($username) && !empty($password)) {
        try {
            // Vérifier l'utilisateur avec préparation des requêtes pour éviter les injections SQL
            $stmt = $pdo->prepare("SELECT id, username, password, role, nom, prenom, email, actif, salt FROM administrateurs WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();
            
            // Vérifier le mot de passe avec hachage sécurisé
            if ($admin && $admin['actif'] == 1) {
                if (password_verify($password, $admin['password'])) {
                    // Authentification réussie
                    session_regenerate_id(true); // Protection contre la fixation de session
                    
                    $_SESSION['admin'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['admin_nom'] = $admin['nom'];
                    $_SESSION['admin_prenom'] = $admin['prenom'];
                    $_SESSION['last_activity'] = time();
                    
                    // Mettre à jour la dernière connexion
                    $stmt = $pdo->prepare("UPDATE administrateurs SET derniere_connexion = NOW(), ip_connexion = :ip WHERE id = :id");
                    $stmt->execute([
                        ':ip' => $_SERVER['REMOTE_ADDR'],
                        ':id' => $admin['id']
                    ]);
                    
                    // Réinitialiser le compteur de tentatives
                    $_SESSION['login_attempts'] = 0;
                    
                    // Cookie "Se souvenir de moi"
                    if ($remember_me) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + (30 * 24 * 60 * 60); // 30 jours
                        
                        setcookie('remember_token', $token, $expires, '/', '', true, true);
                        
                        // Stocker le token en base de données
                        $stmt = $pdo->prepare("UPDATE administrateurs SET remember_token = :token, token_expiry = :expiry WHERE id = :id");
                        $stmt->execute([
                            ':token' => password_hash($token, PASSWORD_DEFAULT),
                            ':expiry' => date('Y-m-d H:i:s', $expires),
                            ':id' => $admin['id']
                        ]);
                    }
                    
                    // Journalisation de la connexion réussie
                    $stmt = $pdo->prepare("INSERT INTO logs_connexion (admin_id, ip_address, statut) VALUES (:admin_id, :ip, 'success')");
                    $stmt->execute([
                        ':admin_id' => $admin['id'],
                        ':ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                    
                    // Redirection en fonction du rôle
                    $redirect = 'admin/dashboard.php';
                    if ($admin['role'] === 'editor') {
                        $redirect = 'admin/offres.php';
                    }
                    
                    header("Location: $redirect");
                    exit();
                }
            }
            
            // Si on arrive ici, l'authentification a échoué
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            
            // Journalisation de l'échec de connexion
            if (isset($admin['id'])) {
                $stmt = $pdo->prepare("INSERT INTO logs_connexion (admin_id, ip_address, statut, details) VALUES (:admin_id, :ip, 'failed', 'Identifiants incorrects')");
                $stmt->execute([
                    ':admin_id' => $admin['id'],
                    ':ip' => $_SERVER['REMOTE_ADDR']
                ]);
            }
            
            // Message d'erreur générique pour éviter les attaques par énumération
            $remaining_attempts = $max_attempts - $_SESSION['login_attempts'];
            if ($remaining_attempts > 0) {
                $error = "Identifiants incorrects. Il vous reste $remaining_attempts tentatives.";
            } else {
                $error = "Trop de tentatives échouées. Veuillez réessayer dans 5 minutes.";
            }
            
        } catch (PDOException $e) {
            error_log("Erreur de connexion admin: " . $e->getMessage());
            $error = "Une erreur est survenue lors de la connexion. Veuillez réessayer plus tard.";
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion Admin - Dour Maroc</title>
    <link rel="stylesheet" href="../css/global.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            max-width: 180px;
            height: auto;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-login {
            background: #0d6efd;
            border: none;
            font-weight: 500;
            padding: 10px;
            height: 46px;
        }
        .btn-login:hover {
            background: #0b5ed7;
        }
        .form-floating>label {
            padding: 1rem 0.75rem;
        }
        .form-floating>.form-control:not(:placeholder-shown)~label::after,
        .form-floating>.form-control:focus~label::after {
            background-color: transparent;
        }
    </style>
</head>
<body class="h-100">
    <div class="container h-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="login-container">
                    <div class="login-logo">
                        <img src="../foto/dour.png" alt="Dour Maroc" class="img-fluid">
                        <h3 class="mt-3 mb-0">Espace Administrateur</h3>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm" autocomplete="on">
                        <input type="hidden" name="csrf_token" value="<?php echo genererTokenCSRF(); ?>">
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Nom d'utilisateur" required autofocus 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            <label for="username">
                                <i class="fas fa-user me-2"></i>Nom d'utilisateur
                            </label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Mot de passe" required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Mot de passe
                            </label>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Se souvenir de moi
                                </label>
                            </div>
                            <a href="forgot-password.php" class="text-decoration-none small">Mot de passe oublié ?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-login" id="loginButton">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </button>
                        
                        <div class="text-center mt-4">
                            <a href="../index.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Retour au site
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-muted small">
                        &copy; <?php echo date('Y'); ?> Dour Maroc. Tous droits réservés.<br>
                        <span class="text-muted">Version 2.0.0</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Désactiver le double clic sur le bouton de connexion
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = document.getElementById('loginButton');
            if (!button.disabled) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Connexion...';
            }
        });
        
        // Afficher/masquer le mot de passe
        const passwordInput = document.getElementById('password');
        const togglePassword = document.createElement('button');
        togglePassword.type = 'button';
        togglePassword.className = 'btn btn-link position-absolute end-0 top-0 p-0 me-3 mt-3';
        togglePassword.innerHTML = '<i class="far fa-eye"></i>';
        togglePassword.style.zIndex = '10';
        
        passwordInput.parentElement.style.position = 'relative';
        passwordInput.parentElement.appendChild(togglePassword);
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
        });
    </script>
</body>
</html>