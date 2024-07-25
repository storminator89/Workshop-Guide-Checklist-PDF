<?php
require_once 'functions.php';

$phases = getPhases();
$totalTasks = 0;
$completedTasks = 0;

foreach ($phases as $phase) {
    foreach ($phase['subtasks'] as $subtask) {
        $totalTasks++;
        if ($subtask['completed']) {
            $completedTasks++;
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['total' => $totalTasks, 'completed' => $completedTasks]);
?>
