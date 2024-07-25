<?php

function saveSubtaskNotes($subtaskId, $notes) {
    $filename = "data/subtask_notes_$subtaskId.txt";
    file_put_contents($filename, $notes);
}

function getSubtaskNotes($subtaskId) {
    $filename = "data/subtask_notes_$subtaskId.txt";
    return file_exists($filename) ? file_get_contents($filename) : '';
}

function getPhases() {
    $phases = [
        [
            "name" => "Phase I",
            "themenblock" => "Begrüßung und Einleitung",
            "teilnehmerkreis" => "Key-User: IT, Fachabteilungen, Freigeber",
            "vorbereitung" => "Agenda und Präsentation vorbereiten",
            "dauer" => 30,
            "subtasks" => [
                ["id" => 'p1s1', "text" => 'Teilnehmer begrüßen', "completed" => false],
                ["id" => 'p1s2', "text" => 'Agenda vorstellen', "completed" => false],
                ["id" => 'p1s3', "text" => 'Projektziele erläutern', "completed" => false],
            ]
        ],
        [
            "name" => "Phase II",
            "themenblock" => "Vorstellung JobRouter",
            "teilnehmerkreis" => "Key-User: IT, Fachabteilungen, Freigeber",
            "vorbereitung" => "Demo-Umgebung vorbereiten",
            "dauer" => 60,
            "subtasks" => [
                ["id" => 'p2s1', "text" => 'JobRouter-Funktionen präsentieren', "completed" => false],
                ["id" => 'p2s2', "text" => 'Anwendungsbeispiele zeigen', "completed" => false],
                ["id" => 'p2s3', "text" => 'Fragen beantworten', "completed" => false],
            ]
        ],
        [
            "name" => "Phase III",
            "themenblock" => "Aufnahme der Ausgangssituation",
            "teilnehmerkreis" => "Key-User: IT, Fachabteilungen, Freigeber",
            "vorbereitung" => "Prozessverlauf, Diagramme, Richtlinien vorbereiten",
            "dauer" => 90,
            "subtasks" => [
                ["id" => 'p3s1', "text" => 'Ist-Prozess dokumentieren', "completed" => false],
                ["id" => 'p3s2', "text" => 'Probleme identifizieren', "completed" => false],
                ["id" => 'p3s3', "text" => 'Verbesserungspotenziale sammeln', "completed" => false],
            ]
        ],
        [
            "name" => "Phase IV",
            "themenblock" => "Aufnahme der allgemeinen Zielvorstellung",
            "teilnehmerkreis" => "Key-User: IT, Fachabteilungen, Freigeber",
            "vorbereitung" => "Was soll durch die Einführung im Detail erreicht werden; auch in Bezug auf existierende Probleme",
            "dauer" => 60,
            "subtasks" => [
                ["id" => 'p4s1', "text" => 'Ziele definieren', "completed" => false],
                ["id" => 'p4s2', "text" => 'Erwartungen sammeln', "completed" => false],
                ["id" => 'p4s3', "text" => 'Priorisierung vornehmen', "completed" => false],
            ]
        ],
        [
            "name" => "Phase V",
            "themenblock" => "Definitionsphase",
            "teilnehmerkreis" => "Key-User: IT, Fachabteilungen, Freigeber",
            "vorbereitung" => "Vorlagen für Workflow-Beschreibungen bereitstellen",
            "dauer" => 120,
            "subtasks" => [
                ["id" => 'p5s1', "text" => 'Workflow-Schritte definieren', "completed" => false],
                ["id" => 'p5s2', "text" => 'Rollen und Verantwortlichkeiten festlegen', "completed" => false],
                ["id" => 'p5s3', "text" => 'Schnittstellen identifizieren', "completed" => false],
            ]
        ]
    ];

    foreach ($phases as &$phase) {
        foreach ($phase['subtasks'] as &$subtask) {
            $subtask['notes'] = getSubtaskNotes($subtask['id']);
        }
    }

    return $phases;
}
?>
