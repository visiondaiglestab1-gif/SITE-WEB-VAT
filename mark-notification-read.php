<?php
header('Content-Type: application/json');
require_once '../config/database.php');

$data = $pdo->getData();
foreach($data['notifications'] as &$n) {
    $n['is_read'] = 1;
}
$pdo->setData($data);

echo json_encode(['success' => true]);
?>