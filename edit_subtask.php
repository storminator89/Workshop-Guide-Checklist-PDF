<?php
require_once 'db_connect.php';

$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'] ?? '';

    if (!empty($text)) {
        $stmt = $pdo->prepare("UPDATE subtasks SET text = ? WHERE id = ?");
        $stmt->execute([$text, $id]);

        header('Location: admin.php');
        exit;
    } else {
        $error = "Bitte füllen Sie alle Felder aus.";
    }
} else {
    $stmt = $pdo->prepare("SELECT s.*, p.name as phase_name FROM subtasks s JOIN phases p ON s.phase_id = p.id WHERE s.id = ?");
    $stmt->execute([$id]);
    $subtask = $stmt->fetch();

    if (!$subtask) {
        die("Aufgabe nicht gefunden.");
    }
}
?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aufgabe bearbeiten</title>
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
    </style>
</head>
<body class="h-full flex flex-col">
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
                    <a href="admin.php" class="text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Zurück zur Übersicht
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-4 text-gray-800">Aufgabe bearbeiten</h1>
                <h2 class="text-xl font-semibold mb-6 text-gray-700">Für Phase: <?= htmlspecialchars($subtask['phase_name']) ?></h2>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= $error ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="text" class="block text-sm font-medium text-gray-700">Aufgabentext:</label>
                        <textarea id="text" name="text" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"><?= htmlspecialchars($subtask['text']) ?></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary text-white font-bold py-2 px-4 rounded inline-flex items-center transition duration-300 ease-in-out transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Aufgabe aktualisieren
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-white shadow-md mt-8">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; 2023 Workshop-Leitfaden. Alle Rechte vorbehalten.
            </p>
        </div>
    </footer>
</body>
</html>
