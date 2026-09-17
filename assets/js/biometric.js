let pendingBatchPrint = null;
function setPendingBatchPrint(batchId) {
    pendingBatchPrint = batchId || null;
    document.querySelectorAll('.confirm-batch-print').forEach(button => { button.disabled = !pendingBatchPrint; });
}
function confirmOfficerAction(title, message) {
    const dialog = document.getElementById('officerConfirmation');
    document.getElementById('confirmationTitle').textContent = title;
    document.getElementById('confirmationMessage').textContent = message;
    dialog.returnValue = 'cancel';
    return new Promise(resolve => {
        dialog.addEventListener('close', () => resolve(dialog.returnValue === 'confirm'), {once: true});
        dialog.showModal();
    });
}
function submitOfficerAction(fields) {
    const form = document.createElement('form');
    form.method = 'post'; form.action = batchConfirmationAction;
    Object.entries({...fields, csrf_token: officerCsrf}).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = name; input.value = value; form.appendChild(input);
    });
    document.body.appendChild(form); form.submit();
}
document.addEventListener('click', async event => {
    const button = event.target.closest('.confirm-batch-print, .confirm-collection');
    if (!button) return;
    if (button.dataset.batchId) pendingBatchPrint = Number(button.dataset.batchId);
    button.disabled = true;
    try {
        if (button.classList.contains('confirm-collection')) {
            if (await confirmOfficerAction('Confirm Collection', 'Confirm that this replacement ID card has been collected by the student. Reference: ' + button.dataset.reference)) {
                submitOfficerAction({action: 'confirm-collection', reference: button.dataset.reference});
            }
        } else if (pendingBatchPrint) {
            const batchId = pendingBatchPrint;
            const response = await fetch('print-confirmation.php?batch_id=' + encodeURIComponent(batchId));
            const details = await response.json();
            if (!response.ok) throw new Error(details.message || 'Unable to confirm this batch.');
            const message = details.emergency
                ? "This replacement request is still within the student's 72-hour refund window. Confirming the card as printed will permanently make this application ineligible for a refund. Confirm only after physically printing the cards. Do you want to continue?"
                : 'Confirm that you have physically printed the cards in batch ' + batchId + '.';
            if (await confirmOfficerAction(details.emergency ? 'Confirm Emergency Print' : 'Confirm Physical Print', message)) {
                submitOfficerAction({action: 'confirm-batch-printed', batch_id: batchId});
            }
        }
    } catch (error) { window.alert(error.message); }
    finally { button.disabled = false; }
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
