<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: tokens.php');
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM token_api WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: tokens.php?mensaje=Token eliminado correctamente');
    exit();
} catch (PDOException $e) {
    header('Location: tokens.php?mensaje=Error al eliminar el token');
    exit();
}
?>