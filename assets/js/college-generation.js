(function() {
    const collegePrintForm = document.getElementById('collegePrintForm');
    if (!collegePrintForm) {
        return;
    }

    collegePrintForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const previewState = document.getElementById('previewState');
        const generateButton = collegePrintForm.querySelector('[type="submit"]');
        generateButton.disabled = true;
        setPendingBatchPrint(null);
        previewState.innerHTML = '<div class="preview-message">Generating the selected ID-card batch...</div>';

        fetch(collegePrintForm.action, {
                method: 'POST',
                headers: {'X-CSRF-Token': officerCsrf},
                body: new FormData(collegePrintForm)
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Unable to generate the selected cards.');
                }
                return response.json();
            })
            .then(function(data) {
                const viewerUrl = data.pdf_url + '#toolbar=1&navpanes=0&scrollbar=1';
                previewState.innerHTML = '<iframe class="pdf-preview-frame" title="ID card PDF preview" src="' + viewerUrl + '"></iframe>' +
                    '<a class="preview-download" href="' + data.pdf_url + '" download="' + data.download_name + '">Download PDF</a>' +
                    '<a class="preview-open" href="' + viewerUrl + '" target="_blank" rel="noopener">Open full viewer</a>';
                setPendingBatchPrint(data.batch_id, []);
            })
            .catch(function(error) {
                previewState.innerHTML = '<div class="preview-message">' + error.message + '</div>';
            })
            .finally(function() {
                generateButton.disabled = false;
            });
    });
})();
