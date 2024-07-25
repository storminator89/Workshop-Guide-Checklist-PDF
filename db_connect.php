<?php
$db_file = __DIR__ . '/workshop.db';
$dsn = "sqlite:$db_file";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Erstelle die phases Tabelle, falls sie noch nicht existiert
    $pdo->exec("CREATE TABLE IF NOT EXISTS phases (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        themenblock TEXT NOT NULL,
        teilnehmerkreis TEXT NOT NULL,
        vorbereitung TEXT NOT NULL,
        dauer INTEGER NOT NULL
    )");

    // Erstelle die subtasks Tabelle, falls sie noch nicht existiert
    $pdo->exec("CREATE TABLE IF NOT EXISTS subtasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phase_id INTEGER,
        text TEXT NOT NULL,
        completed BOOLEAN DEFAULT 0,
        notes TEXT,
        FOREIGN KEY (phase_id) REFERENCES phases(id) ON DELETE CASCADE
    )");

    // Überprüfe, ob die notes Spalte in der subtasks Tabelle existiert
    $result = $pdo->query("PRAGMA table_info(subtasks)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (!in_array('notes', $columns)) {
        // Füge die notes Spalte hinzu, wenn sie noch nicht existiert
        $pdo->exec("ALTER TABLE subtasks ADD COLUMN notes TEXT");
    }

} catch (PDOException $e) {
    die("Verbindungsfehler: " . $e->getMessage());
}
