<section class="page" aria-labelledby="page-title">
    <div class="crumbs"><span>Home</span><span class="sep">›</span><span>Biometric</span><span class="sep">›</span><span>Awaiting Collection</span></div>
    <h1 id="page-title">Awaiting Collection</h1>
    <p class="subtitle">Printed replacement ID cards waiting for the student to collect them.</p>
    <form class="report-toolbar" method="get" action="collection.php">
        <label>Search cards<input type="search" name="search" value="<?php echo htmlspecialchars($collectionSearch); ?>" placeholder="Reference, matric number, or student name"></label>
        <button class="btn primary" type="submit">Search</button>
    </form>
    <?php if ($collectionError): ?><div class="status-message error"><?php echo htmlspecialchars($collectionError); ?></div><?php endif; ?>
    <?php if (!$collectionRows): ?><div class="preview-box"><div class="preview-message">No printed replacement cards are awaiting collection.</div></div>
    <?php else: ?>
    <div class="report-table-wrap"><table class="report-table">
        <thead><tr><th>Reference</th><th>Student</th><th>Printed At (Lagos)</th><th>Collection</th></tr></thead>
        <tbody><?php foreach ($collectionRows as $row): ?><tr>
            <td><?php echo htmlspecialchars($row['referencenumber']); ?></td>
            <td><?php echo htmlspecialchars($row['full_name'] ?: $row['matricnumber']); ?><br><small><?php echo htmlspecialchars($row['matricnumber']); ?></small></td>
            <td><?php echo htmlspecialchars($row['printedat']); ?></td>
            <td><button class="btn primary confirm-collection" type="button" data-reference="<?php echo htmlspecialchars($row['referencenumber']); ?>">Confirm Collection</button></td>
        </tr><?php endforeach; ?></tbody>
    </table></div>
    <?php endif; ?>
</section>
