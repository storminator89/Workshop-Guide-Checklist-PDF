<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $subtask_id = $_GET['id'] ?? 0;
    
    if ($subtask_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM subtasks WHERE id = ?");
            $stmt->execute([$subtask_id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Ungültige Aufgaben-ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ungültige Anfrage']);
}
