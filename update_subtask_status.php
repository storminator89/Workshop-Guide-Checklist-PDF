<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subtaskId = $_POST['subtask_id'] ?? 0;
    $completed = $_POST['completed'] ?? 0;

    if ($subtaskId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE subtasks SET completed = ? WHERE id = ?");
            $stmt->execute([$completed, $subtaskId]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid subtask ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
