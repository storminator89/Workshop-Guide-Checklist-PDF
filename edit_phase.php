<?php
require_once 'db_connect.php';

$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $themenblock = $_POST['themenblock'] ?? '';
    $teilnehmerkreis = $_POST['teilnehmerkreis'] ?? '';
    $vorbereitung = $_POST['vorbereitung'] ?? '';
    $dauer = $_POST['dauer'] ?? 0;

    if (!empty($name) && !empty($themenblock) && !empty($teilnehmerkreis) && !empty($vorbereitung) && $dauer > 0) {
        $stmt = $pdo->prepare("UPDATE phases SET name = ?, themenblock = ?, teilnehmerkreis = ?, vorbereitung = ?, dauer = ? WHERE id = ?");
        $stmt->execute([$name, $themenblock, $teilnehmerkreis, $vorbereitung, $dauer, $id]);

        header('Location: admin.php');
        exit;
    } else {
        $error = "Bitte füllen Sie alle Felder aus.";
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM phases WHERE id = ?");
    $stmt->execute([$id]);
    $phase = $stmt->fetch();

    if (!$phase) {
        die("Phase nicht gefunden.");
    }
}
?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase bearbeiten</title>
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
<body class="h-full flex flex-col bg-gray-100">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-clipboard-list text-4xl text-blue-500"></i>
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
                <h1 class="text-2xl font-bold mb-6 text-gray-800">Phase bearbeiten</h1>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= $error ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name:</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-tag text-gray-400"></i>
                            </div>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($phase['name']) ?>" required class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div>
                        <label for="themenblock" class="block text-sm font-medium text-gray-700">Themenblock:</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-book-open text-gray-400"></i>
                            </div>
                            <textarea id="themenblock" name="themenblock" required class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"><?= htmlspecialchars($phase['themenblock']) ?></textarea>
                        </div>
                    </div>
                    <div>
                        <label for="teilnehmerkreis" class="block text-sm font-medium text-gray-700">Teilnehmerkreis:</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-users text-gray-400"></i>
                            </div>
                            <input type="text" id="teilnehmerkreis" name="teilnehmerkreis" value="<?= htmlspecialchars($phase['teilnehmerkreis']) ?>" required class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div>
                        <label for="vorbereitung" class="block text-sm font-medium text-gray-700">Vorbereitung:</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-clipboard-check text-gray-400"></i>
                            </div>
                            <textarea id="vorbereitung" name="vorbereitung" required class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"><?= htmlspecialchars($phase['vorbereitung']) ?></textarea>
                        </div>
                    </div>
                    <div>
                        <label for="dauer" class="block text-sm font-medium text-gray-700">Dauer (in Minuten):</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                            <input type="number" id="dauer" name="dauer" value="<?= htmlspecialchars($phase['dauer']) ?>" required min="1" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary text-white font-bold py-2 px-4 rounded inline-flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Änderungen speichern
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
