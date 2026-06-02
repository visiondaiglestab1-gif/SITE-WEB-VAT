<?php
session_start();
header('Content-Type: application/json');
require_once 'config/database.php';

// Version de test sans vérification de session
$testMessages = [
    ['id' => 1, 'sender_id' => 1, 'message' => 'Message de test 1', 'is_read' => 1, 'created_at' => date('Y-m-d H:i:s')],
    ['id' => 2, 'sender_id' => 2, 'message' => 'Message de test 2', 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s')]
];

echo json_encode(['success' => true, 'messages' => $testMessages]);
?>