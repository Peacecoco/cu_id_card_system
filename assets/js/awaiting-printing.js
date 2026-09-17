(function() {
    const awaitingPrintForm = document.getElementById('awaitingPrintForm');
    if (!awaitingPrintForm) {
        return;
    }

    const selectAll = document.getElementById('awaitingPrintSelectAll');
    const selectableInputs = Array.from(awaitingPrintForm.querySelectorAll('input[name="reference_numbers[]"]:not(:disabled)'));
    const syncSelectAllState = function() {
        if (!selectAll) {
            return;
        }
        const selectedCount = selectableInputs.filter(function(input) { return input.checked; }).length;
        selectAll.checked = selectableInputs.length > 0 && selectedCount === selectableInputs.length;
        selectAll.indeterminate = selectedCount > 0 && selectedCount < selectableInputs.length;
    };

    if (selectAll) {
        selectAll.disabled = selectableInputs.length === 0;
        selectAll.addEventListener('change', function() {
            selectableInputs.forEach(function(input) {
                input.checked = selectAll.checked;
            });
            syncSelectAllState();
        });
    }
    selectableInputs.forEach(function(input) {
        input.addEventListener('change', syncSelectAllState);
    });
    syncSelectAllState();

    awaitingPrintForm.addEventListener('submit', function(event) {
        const submitter = event.submitter;
        if (!submitter || submitter.value !== 'preview-cards') {
            return;
        }

        event.preventDefault();
        const selectedInputs = Array.from(awaitingPrintForm.querySelectorAll('input[name="reference_numbers[]"]:checked'));
        const references = selectedInputs.map(input => input.value);
        const previewState = document.getElementById('awaitingPrintPreviewState');

        if (!references.length) {
            previewState.innerHTML = '<div class="preview-message">Select at least one application with a matching active student record.</div>';
            return;
        }

        submitter.disabled = true;
        setPendingBatchPrint(null);
        previewState.innerHTML = '<div class="preview-message">Generating selected ID cards...</div>';

        const requestData = new FormData();
        references.forEach(reference => requestData.append('reference_numbers[]', reference));
        requestData.append('window', document.getElementById('printingWindow').value);
        requestData.append('preview', '1');
        requestData.append('generated_by', 'awaiting-print');

        fetch('generate_batch.php', {
                method: 'POST',
                headers: {'X-CSRF-Token': officerCsrf},
                body: requestData
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Unable to generate the selected cards.');
                }
                return response.json();
            })
            .then(function(data) {
                const viewerUrl = data.pdf_url + '#toolbar=1&navpanes=0&scrollbar=1';
                previewState.innerHTML = '<iframe class="selective-preview-frame" title="Awaiting-print ID card PDF preview" src="' + viewerUrl + '"></iframe>' +
                    '<a class="preview-download" href="' + data.pdf_url + '" download="' + data.download_name + '">Download PDF</a>' +
                    '<a class="preview-open" href="' + viewerUrl + '" target="_blank" rel="noopener">Open full viewer</a>';
                setPendingBatchPrint(data.batch_id);
            })
            .catch(function(error) {
                previewState.innerHTML = '<div class="preview-message">' + error.message + '</div>';
            })
            .finally(function() {
                submitter.disabled = false;
            });
    });
})();
