document.addEventListener('DOMContentLoaded', function() {
    const phaseContents = document.querySelectorAll('.phase-content');
    const timelineItems = document.querySelectorAll('.timeline-item');
    const nextPhaseButton = document.querySelector('.next-phase');
    const prevPhaseButton = document.querySelector('.prev-phase');
    const checkboxes = document.querySelectorAll('.subtask-checkbox');
    const notesInputs = document.querySelectorAll('.subtask-notes');
    const totalTaskCounter = document.getElementById('total-task-counter');
    const taskCountSpan = document.getElementById('task-count');
    const progressBar = document.querySelector('.bg-blue-600');
    const progressPercentageElement = document.getElementById('progress-percentage');

    let completedTime = initialCompletedTime;

    function switchToPhase(newPhase) {
        phaseContents.forEach((content, index) => {
            if (index == newPhase) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });

        timelineItems.forEach((item, index) => {
            if (index == newPhase) {
                item.classList.add('active');
                item.querySelector('.timeline-dot').classList.add('bg-blue-600');
                item.querySelector('.timeline-dot').classList.remove('bg-gray-400');
            } else {
                item.classList.remove('active');
                item.querySelector('.timeline-dot').classList.remove('bg-blue-600');
                item.querySelector('.timeline-dot').classList.add('bg-gray-400');
            }
        });

        updateCounters();
    }

    timelineItems.forEach((item, index) => {
        item.addEventListener('click', function() {
            switchToPhase(index);
        });
    });

    if (nextPhaseButton) {
        nextPhaseButton.addEventListener('click', function() {
            const currentPhase = Array.from(phaseContents).findIndex(content => !content.classList.contains('hidden'));
            if (currentPhase < phaseContents.length - 1) {
                switchToPhase(currentPhase + 1);
            }
        });
    }

    if (prevPhaseButton) {
        prevPhaseButton.addEventListener('click', function() {
            const currentPhase = Array.from(phaseContents).findIndex(content => !content.classList.contains('hidden'));
            if (currentPhase > 0) {
                switchToPhase(currentPhase - 1);
            }
        });
    }

    function updateCounters() {
        let totalCompletedTasks = 0;

        phaseContents.forEach(content => {
            const checkboxes = content.querySelectorAll('.subtask-checkbox');
            const completedTasks = content.querySelectorAll('.subtask-checkbox:checked').length;
            totalCompletedTasks += completedTasks;
        });
    
        if (taskCountSpan) {
            taskCountSpan.textContent = `${totalCompletedTasks} / ${totalTasks}`;
        }

        updateProgressBar(completedTime);
    }

    function updateProgressBar(completedTime) {
        const progressPercentage = (completedTime / totalTime) * 100;
        if (progressBar) {
            progressBar.style.width = `${progressPercentage}%`;
        }
        if (progressPercentageElement) {
            progressPercentageElement.textContent = `${Math.round(progressPercentage)}% abgeschlossen`;
        }
        
        updateEstimatedTime(completedTime);
    }

    function updateEstimatedTime(completedTime) {
        const remainingTime = totalTime - completedTime;
        const estimatedTimeElement = document.getElementById('estimated-time-remaining');
        
        if (estimatedTimeElement) {
            if (remainingTime > 0) {
                estimatedTimeElement.textContent = `Verbleibende Zeit: ${Math.round(remainingTime)} Min.`;
            } else {
                estimatedTimeElement.textContent = 'Alle Aufgaben abgeschlossen!';
            }
        }
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const subtaskId = this.getAttribute('data-id');
            const completed = this.checked;
            const phaseContent = this.closest('.phase-content');
            
            if (phaseContent) {
                const phaseDuration = parseInt(phaseContent.getAttribute('data-duration'));
                if (!isNaN(phaseDuration)) {
                    const subtaskCount = phaseContent.querySelectorAll('.subtask-checkbox').length;
                    const subtaskDuration = phaseDuration / subtaskCount;
    
                    // AJAX-Aufruf zum Aktualisieren des Aufgabenstatus in der Datenbank
                    fetch('update_subtask_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `subtask_id=${subtaskId}&completed=${completed ? 1 : 0}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            completedTime += completed ? subtaskDuration : -subtaskDuration;
                            updateCounters();
                        } else {
                            console.error('Fehler beim Aktualisieren des Aufgabenstatus');
                            // Setze Checkbox auf vorherigen Zustand zurück
                            this.checked = !completed;
                        }
                    })
                    .catch(error => {
                        console.error('Fehler:', error);
                        // Setze Checkbox auf vorherigen Zustand zurück
                        this.checked = !completed;
                    });
                } else {
                    console.error('Ungültige Phasendauer');
                }
            } else {
                console.error('Phase-Inhalt nicht gefunden');
            }
        });
    });

    notesInputs.forEach(input => {
        const subtaskId = input.getAttribute('data-id');
        const editor = new Quill(`#editor-${subtaskId}`, {
            theme: 'snow',
            modules: {
                toolbar: `#toolbar-${subtaskId}`
            }
        });

        // Lade gespeicherte Notizen aus der Datenbank
        fetch(`get_subtask_notes.php?subtask_id=${subtaskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    editor.root.innerHTML = data.notes;
                }
            })
            .catch(error => console.error('Fehler beim Laden der Notizen:', error));

        editor.on('text-change', function() {
            const notes = editor.root.innerHTML;
            input.value = notes;
            
            // Speichern der Notizen in der Datenbank
            fetch('save_notes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `subtask_id=${subtaskId}&notes=${encodeURIComponent(notes)}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Fehler beim Speichern der Notizen');
                }
            })
            .catch(error => console.error('Fehler:', error));
        });
    });

    // Initial setup
    updateCounters();
    const initialPhase = document.querySelector('.phase-content:not(.hidden)');
    if (initialPhase) {
        const initialPhaseIndex = Array.from(phaseContents).indexOf(initialPhase);
        switchToPhase(initialPhaseIndex);
    }
});
