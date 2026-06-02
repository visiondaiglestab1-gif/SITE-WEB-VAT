<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data['action'] === 'login') {
    $email = $data['email'];
    $password = $data['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && $user['status'] === 'approved') {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            
            echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
        }
    } elseif ($user && $user['status'] === 'pending') {
        echo json_encode(['success' => false, 'message' => 'Votre compte est en attente d\'approbation']);
    } elseif ($user && $user['status'] === 'rejected') {
        echo json_encode(['success' => false, 'message' => 'Votre compte a été rejeté']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email non trouvé']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Action invalide']);
}
?>