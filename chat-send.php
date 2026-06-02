<?php
session_start();
header('Content-Type: application/json');
require_once 'config/database.php';

// Version de test
echo json_encode(['success' => true, 'message' => 'Message envoyé (test)']);
?>