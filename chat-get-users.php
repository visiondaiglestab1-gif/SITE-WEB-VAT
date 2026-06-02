<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';

// Vérifier si l'utilisateur est admin
if(!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'users' => []]);
    exit;
}

$myId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;
$data = $pdo->getData();
$users = [];

foreach($data['subscribers'] as $sub) {
    if($sub['id'] == $myId) continue;
    
    // Compter les messages non lus de cet utilisateur
    $unreadCount = 0;
    foreach($data['notifications'] as $notif) {
        if($notif['type'] == 'chat' && $notif['to_user_id'] == $myId && $notif['from_user_id'] == $sub['id'] && $notif['is_read'] == 0) {
            $unreadCount++;
        }
    }
    
    $users[] = [
        'id' => $sub['id'],
        'name' => $sub['first_name'] . ' ' . $sub['last_name'],
        'role' => $sub['role'] ?? 'user',
        'unread_count' => $unreadCount
    ];
}

echo json_encode(['success' => true, 'users' => $users]);
?>