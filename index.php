<?php
require_once 'db_connect.php';

// Funktion zum Abrufen aller Phasen mit ihren Aufgaben
function getAllPhases($pdo)
{
    $stmt = $pdo->query("SELECT * FROM phases ORDER BY id");
    $phases = $stmt->fetchAll();

    foreach ($phases as &$phase) {
        $stmt = $pdo->prepare("SELECT * FROM subtasks WHERE phase_id = ? ORDER BY id");
        $stmt->execute([$phase['id']]);
        $phase['subtasks'] = $stmt->fetchAll();
        $phase['completed_subtasks'] = array_sum(array_column($phase['subtasks'], 'completed'));
    }

    return $phases;
}

// Initialisierung der Phasen
$phases = getAllPhases($pdo);
$currentPhase = isset($_GET['phase']) ? (int)$_GET['phase'] : 0;

// Zählen der Gesamtaufgaben und Berechnung der Gesamtzeit
$totalTasks = 0;
$completedTasks = 0;
$totalTime = 0;
$completedTime = 0;

foreach ($phases as $phase) {
    $totalTime += $phase['dauer'];
    foreach ($phase['subtasks'] as $subtask) {
        $totalTasks++;
        if ($subtask['completed']) {
            $completedTasks++;
            $completedTime += $phase['dauer'] / count($phase['subtasks']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workshop-Leitfaden</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --background-color: #ecf0f1;
            --text-color: #2c3e50;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-accent {
            background-color: var(--accent-color);
        }

        .btn-accent:hover {
            background-color: #c0392b;
        }

        .timeline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e2e8f0;
        }

        .timeline-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            cursor: pointer;
        }

        .timeline-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .timeline-label {
            font-size: 0.875rem;
            text-align: center;
            max-width: 100px;
        }

        .timeline-item.active .timeline-dot {
            transform: scale(1.2);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.3);
        }
    </style>
</head>

<body class="h-full flex flex-col bg-gray-100">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-clipboard-list text-4xl text-primary"></i>
                        <span class="ml-2 text-2xl font-bold text-gray-800">Workshop-Leitfaden</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="admin.php" class="btn-primary text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300 ease-in-out transform hover:scale-105">
                        <i class="fas fa-cog mr-2"></i>Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Gesamtfortschritt</h2>
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
                        <div class="bg-blue-600 h-4 rounded-full transition-all duration-500 ease-out" style="width: <?= ($completedTime / $totalTime) * 100 ?>%"></div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span id="progress-percentage"><?= round(($completedTime / $totalTime) * 100) ?>% abgeschlossen</span>
                        <span id="total-task-counter">
                            <i class="fas fa-tasks mr-2"></i><span id="task-count"><?= $completedTasks ?> / <?= $totalTasks ?></span> Aufgaben erledigt
                        </span>
                        <span id="estimated-time-remaining">Verbleibende Zeit: <?= round($totalTime - $completedTime) ?> Min.</span>
                    </div>
                </div>

                <div class="timeline">
                    <?php foreach ($phases as $index => $phase) : ?>
                        <div class="timeline-item <?= $index === $currentPhase ? 'active' : '' ?>" onclick="switchToPhase(<?= $index ?>)">
                            <div class="timeline-dot <?= $index === $currentPhase ? 'bg-blue-600' : 'bg-gray-400' ?>"><?= $index + 1 ?></div>
                            <div class="timeline-label"><?= htmlspecialchars($phase['name']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($phases as $index => $phase) : ?>
                    <div id="phase-content-<?= $index ?>" class="phase-content <?= $index === $currentPhase ? '' : 'hidden' ?>" data-duration="<?= $phase['dauer'] ?>">
                        <h2 class="text-2xl font-bold mb-4 text-gray-800">
                            <?= htmlspecialchars($phase['name']) ?>
                        </h2>
                        <div class="mb-6 space-y-2 text-gray-700">
                            <p><i class="fas fa-book-open mr-2 text-blue-600"></i><strong>Themenblock:</strong> <?= htmlspecialchars($phase['themenblock']) ?></p>
                            <p><i class="fas fa-users mr-2 text-blue-600"></i><strong>Teilnehmerkreis:</strong> <?= htmlspecialchars($phase['teilnehmerkreis']) ?></p>
                            <p><i class="fas fa-clipboard-check mr-2 text-blue-600"></i><strong>Vorbereitung:</strong> <?= htmlspecialchars($phase['vorbereitung']) ?></p>
                            <p><i class="fas fa-clock mr-2 text-blue-600"></i><strong>Dauer:</strong> <?= htmlspecialchars($phase['dauer']) ?> Minuten</p>
                        </div>
                        <h3 class="text-xl font-semibold mb-4 text-blue-800"><i class="fas fa-list-ul mr-2"></i>Aufgaben & Diskussionspunkte:</h3>
                        <div class="space-y-4">
                            <?php foreach ($phase['subtasks'] as $subtask) : ?>
                                <div class="subtask-item bg-gray-50 p-4 rounded-lg">
                                    <div class="subtask-header flex items-center mb-2">
                                        <input type="checkbox" <?= $subtask['completed'] ? 'checked' : '' ?> data-id="<?= $subtask['id'] ?>" class="subtask-checkbox form-checkbox h-5 w-5 text-blue-600">
                                        <span class="subtask-text ml-3 text-gray-800"><?= htmlspecialchars($subtask['text']) ?></span>
                                    </div>
                                    <div class="subtask-editor-container mt-2">
                                        <div id="toolbar-<?= $subtask['id'] ?>">
                                            <button class="ql-bold"></button>
                                            <button class="ql-italic"></button>
                                            <button class="ql-underline"></button>
                                            <button class="ql-link"></button>
                                            <button class="ql-list" value="ordered"></button>
                                            <button class="ql-list" value="bullet"></button>
                                        </div>
                                        <div id="editor-<?= $subtask['id'] ?>" class="subtask-editor"></div>
                                    </div>
                                    <input type="hidden" data-id="<?= $subtask['id'] ?>" class="subtask-notes">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-8 flex justify-between">
                    <button class="btn prev-phase bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l transition duration-300 ease-in-out transform hover:scale-105">
                        <i class="fas fa-arrow-left mr-2"></i>Vorherige Phase
                    </button>
                    <button class="btn next-phase bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r transition duration-300 ease-in-out transform hover:scale-105">
                        <i class="fas fa-arrow-right mr-2"></i>Nächste Phase
                    </button>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white shadow-md mt-8">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center">
                <a href="generate_pdf.php" class="btn-primary text-white font-bold py-2 px-4 rounded inline-flex items-center transition duration-300 ease-in-out transform hover:scale-105" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i>PDF-Bericht generieren
                </a>
            </div>
        </div>
    </footer>

    <script>
        var totalTasks = <?= $totalTasks ?>;
        var initialCompletedTasks = <?= $completedTasks ?>;
        var totalTime = <?= $totalTime ?>;
        var initialCompletedTime = <?= $completedTime ?>;

        function switchToPhase(phaseIndex) {
            document.querySelectorAll('.phase-content').forEach((content, index) => {
                if (index === phaseIndex) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });

            document.querySelectorAll('.timeline-item').forEach((item, index) => {
                if (index === phaseIndex) {
                    item.classList.add('active');
                    item.querySelector('.timeline-dot').classList.add('bg-blue-600');
                    item.querySelector('.timeline-dot').classList.remove('bg-gray-400');
                } else {
                    item.classList.remove('active');
                    item.querySelector('.timeline-dot').classList.remove('bg-blue-600');
                    item.querySelector('.timeline-dot').classList.add('bg-gray-400');
                }
            });
        }

        document.querySelector('.prev-phase').addEventListener('click', function() {
            const currentPhase = Array.from(document.querySelectorAll('.phase-content')).findIndex(content => !content.classList.contains('hidden'));
            if (currentPhase > 0) {
                switchToPhase(currentPhase - 1);
            }
        });

        document.querySelector('.next-phase').addEventListener('click', function() {
            const currentPhase = Array.from(document.querySelectorAll('.phase-content')).findIndex(content => !content.classList.contains('hidden'));
            if (currentPhase < <?= count($phases) - 1 ?>) {
                switchToPhase(currentPhase + 1);
            }
        });

        document.querySelector('a[href="generate_pdf.php"]').addEventListener('click', function(e) {
            e.preventDefault();

            // Sammle alle Daten
            let data = [];
            document.querySelectorAll('.phase-content').forEach(phase => {
                let phaseId = phase.getAttribute('data-phase');
                phase.querySelectorAll('.subtask-item').forEach(subtask => {
                    let subtaskId = subtask.querySelector('.subtask-checkbox').getAttribute('data-id');
                    let completed = subtask.querySelector('.subtask-checkbox').checked;
                    let notes = subtask.querySelector('.subtask-notes').value;
                    data.push({
                        phaseId,
                        subtaskId,
                        completed,
                        notes
                    });
                });
            });

            // Sende Daten an den Server
            fetch('update_subtasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Wenn erfolgreich, generiere PDF
                        window.location.href = 'generate_pdf.php';
                    } else {
                        alert('Fehler beim Speichern der Daten. Bitte versuchen Sie es erneut.');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
                });
        });

        // Initialisierung
        switchToPhase(<?= $currentPhase ?>);
    </script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="script.js"></script>
</body>

</html>