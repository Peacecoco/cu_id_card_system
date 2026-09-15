let pendingBatchPrint = null;

function setPendingBatchPrint(batchId, referenceNumbers) {
    if (!batchId) {
        return;
    }

    pendingBatchPrint = {
        batchId: batchId,
        referenceNumbers: referenceNumbers || []
    };
    document.querySelectorAll('.confirm-batch-print').forEach(function(button) {
        button.disabled = false;
    });
}

document.addEventListener('click', function(event) {
    const button = event.target.closest('.confirm-batch-print');
    if (!button || !pendingBatchPrint) {
        return;
    }

    if (!window.confirm('Are you sure you have physically printed these cards?')) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'post';
    form.action = batchConfirmationAction;

    [['action', 'confirm-batch-printed'], ['batch_id', String(pendingBatchPrint.batchId)]].forEach(function(field) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = field[0];
        input.value = field[1];
        form.appendChild(input);
    });

    pendingBatchPrint.referenceNumbers.forEach(function(referenceNumber) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'reference_numbers[]';
        input.value = referenceNumber;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
});

document.querySelectorAll('[data-nav-toggle]').forEach(function(toggle) {
    function toggleNavigation(event) {
        if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        event.preventDefault();
        toggle.closest('.nav-item').classList.toggle('open');
    }

    toggle.addEventListener('click', toggleNavigation);
    toggle.addEventListener('keydown', toggleNavigation);
});
