<?php
require_once 'db_connect.php';

if (isset($_GET['delete_phase'])) {
    $phase_id = $_GET['delete_phase'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM subtasks WHERE phase_id = ?")->execute([$phase_id]);
        $pdo->prepare("DELETE FROM phases WHERE id = ?")->execute([$phase_id]);
        $pdo->commit();
        $message = 'Phase erfolgreich gelöscht.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Fehler beim Löschen der Phase: ' . $e->getMessage();
    }
}

// Funktion zum Abrufen aller Phasen mit ihren Aufgaben
function getAllPhases($pdo) {
    $stmt = $pdo->query("SELECT * FROM phases ORDER BY id");
    $phases = $stmt->fetchAll();

    foreach ($phases as &$phase) {
        $stmt = $pdo->prepare("SELECT * FROM subtasks WHERE phase_id = ? ORDER BY id");
        $stmt->execute([$phase['id']]);
        $phase['subtasks'] = $stmt->fetchAll();
    }

    return $phases;
}

$phases = getAllPhases($pdo);
?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workshop-Leitfaden Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .slide-down {
            animation: slideDown 0.5s ease-in-out;
        }
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .action-button {
            transition: all 0.3s ease;
        }
        .action-button:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="h-full">
    <div class="min-h-full">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-cogs mr-2"></i>Workshop-Leitfaden Administration
                </h1>
                <a href="index.php" class="btn-primary text-white px-4 py-2 rounded-md text-sm font-medium transition-transform duration-300 hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>Zurück zum Leitfaden
                </a>
            </div>
        </header>

        <main>
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <div class="px-4 py-6 sm:px-0">
                    <div id="message" class="hidden mb-4"></div>
                    
                    <div class="mb-6">
                        <a href="add_phase.php" class="btn-primary text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center transition-transform duration-300 hover:scale-105">
                            <i class="fas fa-plus mr-2"></i>Neue Phase hinzufügen
                        </a>
                    </div>

                    <?php foreach ($phases as $phase): ?>
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6 fade-in" data-phase-id="<?= $phase['id'] ?>">
                            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    <i class="fas fa-clipboard-list mr-2"></i><?= htmlspecialchars($phase['name']) ?>
                                </h3>
                                <div>
                                    <a href="edit_phase.php?id=<?= $phase['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-4 action-button" title="Bearbeiten">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deletePhase(<?= $phase['id'] ?>)" class="text-red-600 hover:text-red-900 action-button" title="Löschen">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                <dl class="sm:divide-y sm:divide-gray-200">
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">
                                            <i class="fas fa-book-open mr-2"></i>Themenblock
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($phase['themenblock']) ?></dd>
                                    </div>
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">
                                            <i class="fas fa-users mr-2"></i>Teilnehmerkreis
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($phase['teilnehmerkreis']) ?></dd>
                                    </div>
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">
                                            <i class="fas fa-clipboard-check mr-2"></i>Vorbereitung
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($phase['vorbereitung']) ?></dd>
                                    </div>
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">
                                            <i class="fas fa-clock mr-2"></i>Dauer
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?= htmlspecialchars($phase['dauer']) ?> Minuten</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="px-4 py-5 sm:px-6">
                                <h4 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    <i class="fas fa-tasks mr-2"></i>Aufgaben
                                </h4>
                                <button onclick="toggleSubtaskForm(<?= $phase['id'] ?>)" class="btn-primary text-white px-3 py-1 rounded-md text-sm font-medium inline-flex items-center mb-4 transition-transform duration-300 hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>Neue Aufgabe hinzufügen
                                </button>
                                <div id="subtaskForm<?= $phase['id'] ?>" class="hidden mb-4 slide-down">
                                    <form onsubmit="addSubtask(event, <?= $phase['id'] ?>)" class="space-y-4">
                                        <div>
                                            <label for="subtask_text<?= $phase['id'] ?>" class="block text-sm font-medium text-gray-700">Aufgabentext:</label>
                                            <input type="text" id="subtask_text<?= $phase['id'] ?>" name="subtask_text" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        <button type="submit" class="btn-primary text-white px-3 py-1 rounded-md text-sm font-medium transition-transform duration-300 hover:scale-105">
                                            Aufgabe speichern
                                        </button>
                                    </form>
                                </div>
                                <ul id="subtaskList<?= $phase['id'] ?>" class="divide-y divide-gray-200">
                                    <?php foreach ($phase['subtasks'] as $subtask): ?>
                                        <li class="py-4 flex justify-between items-center fade-in" id="subtask<?= $subtask['id'] ?>">
                                            <div class="text-sm text-gray-900">
                                                <i class="fas fa-check-circle mr-2 <?= $subtask['completed'] ? 'text-green-500' : 'text-gray-300' ?>"></i>
                                                <?= htmlspecialchars($subtask['text']) ?>
                                            </div>
                                            <div>
                                                <a href="edit_subtask.php?id=<?= $subtask['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-4 action-button" title="Bearbeiten">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="deleteSubtask(<?= $subtask['id'] ?>)" class="text-red-600 hover:text-red-900 action-button" title="Löschen">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSubtaskForm(phaseId) {
            const form = document.getElementById(`subtaskForm${phaseId}`);
            form.classList.toggle('hidden');
            if (!form.classList.contains('hidden')) {
                form.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            }
        }

        function addSubtask(event, phaseId) {
            event.preventDefault();
            const form = event.target;
            const subtaskText = form.querySelector('input[name="subtask_text"]').value;

            fetch('add_subtask.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `phase_id=${phaseId}&subtask_text=${encodeURIComponent(subtaskText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const subtaskList = document.getElementById(`subtaskList${phaseId}`);
                    const newSubtask = document.createElement('li');
                    newSubtask.className = 'py-4 flex justify-between items-center fade-in';
                    newSubtask.id = `subtask${data.subtask_id}`;
                    newSubtask.innerHTML = `
                        <div class="text-sm text-gray-900">
                            <i class="fas fa-check-circle mr-2 text-gray-300"></i>
                            ${subtaskText}
                        </div>
                        <div>
                            <a href="edit_subtask.php?id=${data.subtask_id}" class="text-indigo-600 hover:text-indigo-900 mr-4 action-button" title="Bearbeiten">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteSubtask(${data.subtask_id})" class="text-red-600 hover:text-red-900 action-button" title="Löschen">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    `;
                    subtaskList.appendChild(newSubtask);
                    form.reset();
                    showMessage('Aufgabe erfolgreich hinzugefügt', 'success');
                } else {
                    showMessage('Fehler beim Hinzufügen der Aufgabe', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Ein Fehler ist aufgetreten', 'error');
            });
        }

        function deleteSubtask(subtaskId) {
            if (confirm('Sind Sie sicher, dass Sie diese Aufgabe löschen möchten?')) {
                fetch(`delete_subtask.php?id=${subtaskId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const subtask = document.getElementById(`subtask${subtaskId}`);
                        subtask.style.animation = 'fadeOut 0.5s ease-in-out';
                        setTimeout(() => {
            subtask.remove();
        }, 500);
        showMessage('Aufgabe erfolgreich gelöscht', 'success');
    } else {
        showMessage('Fehler beim Löschen der Aufgabe', 'error');
    }
})
.catch(error => {
    console.error('Error:', error);
    showMessage('Ein Fehler ist aufgetreten', 'error');
});
}
}

function deletePhase(phaseId) {
if (confirm('Sind Sie sicher, dass Sie diese Phase löschen möchten?')) {
fetch(`delete_phase.php?id=${phaseId}`)
.then(response => response.json())
.then(data => {
    if (data.success) {
        const phase = document.querySelector(`[data-phase-id="${phaseId}"]`);
        phase.style.animation = 'fadeOut 0.5s ease-in-out';
        setTimeout(() => {
            phase.remove();
        }, 500);
        showMessage('Phase erfolgreich gelöscht', 'success');
    } else {
        showMessage('Fehler beim Löschen der Phase', 'error');
    }
})
.catch(error => {
    console.error('Error:', error);
    showMessage('Ein Fehler ist aufgetreten', 'error');
});
}
}

function showMessage(text, type) {
const messageDiv = document.getElementById('message');
messageDiv.textContent = text;
messageDiv.className = type === 'success' 
    ? 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4'
    : 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4';
messageDiv.classList.remove('hidden');
setTimeout(() => {
    messageDiv.classList.add('hidden');
}, 3000);
}
</script>
</body>
</html>
