<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $phase_id = $_GET['id'] ?? 0;

    if ($phase_id > 0) {
        try {
            $pdo->beginTransaction();

            // Zuerst löschen wir alle zugehörigen Subtasks
            $stmt = $pdo->prepare("DELETE FROM subtasks WHERE phase_id = ?");
            $stmt->execute([$phase_id]);

            // Dann löschen wir die Phase selbst
            $stmt = $pdo->prepare("DELETE FROM phases WHERE id = ?");
            $stmt->execute([$phase_id]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Phase und zugehörige Aufgaben erfolgreich gelöscht.'
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'error' => 'Fehler beim Löschen der Phase: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Ungültige Phase-ID'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Ungültige Anfragemethode'
    ]);
}
