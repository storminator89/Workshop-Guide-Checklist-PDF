<?php
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE subtasks SET completed = ?, notes = ? WHERE id = ?");

    foreach ($data as $item) {
        $stmt->execute([$item['completed'] ? 1 : 0, $item['notes'], $item['subtaskId']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
