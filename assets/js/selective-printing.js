(function() {
    const selectionForm = document.getElementById('selectionForm');
    if (!selectionForm) {
        return;
    }

    selectionForm.addEventListener('click', function(event) {
        const removeButton = event.target.closest('.student-remove');
        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('[data-student-row]');
        if (row) {
            row.remove();
        }

        const heading = selectionForm.querySelector('.selected-heading');
        const count = selectionForm.querySelectorAll('[data-student-row]').length;
        selectionForm.querySelector('#selectedCount').textContent = count;
        const generateButton = selectionForm.querySelector('[type="submit"]');
        if (generateButton) {
            generateButton.disabled = count === 0;
        }
    });

    document.getElementById('clearSelection').addEventListener('click', function() {
        selectionForm.querySelectorAll('[data-student-row]').forEach(function(row) {
            row.remove();
        });
        selectionForm.querySelector('#selectedCount').textContent = '0';
        const generateButton = selectionForm.querySelector('[type="submit"]');
        if (generateButton) {
            generateButton.disabled = true;
        setPendingBatchPrint(null);
        }
    });

    document.getElementById('searchForm').addEventListener('submit', function() {
        this.querySelectorAll('input[name="selected_ids[]"]').forEach(function(input) {
            input.remove();
        });
        selectionForm.querySelectorAll('input[name="student_ids[]"]').forEach(function(input) {
            const selectedInput = document.createElement('input');
            selectedInput.type = 'hidden';
            selectedInput.name = 'selected_ids[]';
            selectedInput.value = input.value;
            document.getElementById('searchForm').appendChild(selectedInput);
        });
    });

    selectionForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const previewState = document.getElementById('selectivePreviewState');
        const generateButton = selectionForm.querySelector('[type="submit"]');
        generateButton.disabled = true;
        setPendingBatchPrint(null);
        previewState.innerHTML = '<div class="preview-message">Generating selected ID cards...</div>';

        fetch(selectionForm.action, {
                method: 'POST',
                headers: {'X-CSRF-Token': officerCsrf},
                body: new FormData(selectionForm)
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Unable to generate the selected cards.');
                }
                return response.json();
            })
            .then(function(data) {
                const viewerUrl = data.pdf_url + '#toolbar=1&navpanes=0&scrollbar=1';
                previewState.innerHTML = '<iframe class="selective-preview-frame" title="Selected ID card PDF preview" src="' + viewerUrl + '"></iframe>' +
                    '<a class="preview-download" href="' + data.pdf_url + '" download="' + data.download_name + '">Download PDF</a>' +
                    '<a class="preview-open" href="' + viewerUrl + '" target="_blank" rel="noopener">Open full viewer</a>';
                setPendingBatchPrint(data.batch_id, []);
            })
            .catch(function(error) {
                previewState.innerHTML = '<div class="preview-message">' + error.message + '</div>';
            })
            .finally(function() {
                generateButton.disabled = selectionForm.querySelectorAll('[data-student-row]').length === 0;
            });
    });
})();
