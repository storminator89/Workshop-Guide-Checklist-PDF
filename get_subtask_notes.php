<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $subtaskId = $_GET['subtask_id'] ?? 0;

    if ($subtaskId > 0) {
        try {
            $stmt = $pdo->prepare("SELECT notes FROM subtasks WHERE id = ?");
            $stmt->execute([$subtaskId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                echo json_encode(['success' => true, 'notes' => $result['notes']]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Subtask not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid subtask ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
