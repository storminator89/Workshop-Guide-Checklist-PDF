<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phase_id = $_POST['phase_id'] ?? 0;
    $subtask_text = $_POST['subtask_text'] ?? '';
    
    if (!empty($subtask_text) && $phase_id > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO subtasks (phase_id, text) VALUES (?, ?)");
            $stmt->execute([$phase_id, $subtask_text]);
            $subtask_id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'subtask_id' => $subtask_id]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Ungültige Daten']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ungültige Anfrage']);
}
