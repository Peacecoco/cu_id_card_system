        </main>
    </div>
    <dialog id="officerConfirmation" aria-labelledby="confirmationTitle">
        <form method="dialog">
            <h2 id="confirmationTitle">Confirm action</h2>
            <p id="confirmationMessage"></p>
            <div class="action-row"><button class="btn secondary" value="cancel">Cancel</button><button class="btn primary" value="confirm">Confirm</button></div>
        </form>
    </dialog>
    <script>const batchConfirmationAction = <?php echo json_encode($pageUrl.'?'.http_build_query($_GET),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
    const officerCsrf = <?php echo json_encode($officerCsrf); ?>;</script>
    <script src="../assets/js/biometric.js"></script>
    <?php if (!empty($pageScript)): ?>
    <script src="../assets/js/<?php echo htmlspecialchars($pageScript); ?>.js"></script>
    <?php endif; ?>
</body>
</html>
