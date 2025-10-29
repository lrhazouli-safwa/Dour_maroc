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
try {
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php?msg=suppression");
    exit();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Erreur lors de la suppression du produit.</div>';
    exit();
} 