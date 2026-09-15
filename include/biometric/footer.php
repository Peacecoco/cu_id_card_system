        </main>
    </div>
    <script>const batchConfirmationAction = <?php echo json_encode($pageUrl); ?>;</script>
    <script src="../assets/js/biometric.js"></script>
    <?php if (!empty($pageScript)): ?>
    <script src="../assets/js/<?php echo htmlspecialchars($pageScript); ?>.js"></script>
    <?php endif; ?>
</body>
</html>
