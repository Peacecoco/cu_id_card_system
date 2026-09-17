                <section class="page" aria-labelledby="page-title">
                    <div class="crumbs" aria-label="Breadcrumb">
                        <span>Home</span>
                        <span class="sep">›</span>
                        <span>Biometric</span>
                        <span class="sep">›</span>
                        <span>Awaiting Printing</span>
                    </div>

                    <h1 id="page-title">Awaiting Printing</h1>
                    <p class="subtitle">Paid ID-card applications waiting to be physically printed.</p>
                    <p class="subtitle">Historical applications with unknown payment details require reconciliation and are excluded. Generating or downloading a PDF does not confirm physical printing.</p>

                    <form class="report-toolbar" method="get" action="awaiting-printing.php">
                        <label>Search queue
                            <input type="search" name="awaiting_print_search" value="<?php echo htmlspecialchars($awaitingPrintSearch); ?>" placeholder="Name, matric number, reference, or programme">
                        </label>
                        <label>Filter
                            <select name="window" id="printingWindow">
                                <option value="after" <?php echo $printingWindow==='after' ? 'selected' : ''; ?>>After 72 Hours</option>
                                <option value="before" <?php echo $printingWindow==='before' ? 'selected' : ''; ?>>Before 72 Hours</option>
                            </select>
                        </label>
                        <button class="btn primary" type="submit">Search</button>
                    </form>

                    <?php if ($awaitingPrintMessage !== ''): ?>
                        <div class="status-message"><?php echo htmlspecialchars($awaitingPrintMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($awaitingPrintError !== ''): ?>
                        <div class="status-message error"><?php echo htmlspecialchars($awaitingPrintError); ?></div>
                    <?php endif; ?>

                    <?php if (empty($awaitingPrintApplications)): ?>
                        <div class="preview-box">
                            <div class="preview-message">No paid applications are awaiting printing.</div>
                        </div>
                    <?php else: ?>
                        <form id="awaitingPrintForm" method="post" action="awaiting-printing.php<?php echo $awaitingPrintSearch !== '' ? '?awaiting_print_search=' . rawurlencode($awaitingPrintSearch) : ''; ?>">
                            <div class="report-table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th><label><input id="awaitingPrintSelectAll" type="checkbox" aria-label="Select all eligible applications"> Select all</label></th>
                                            <th>Reference</th>
                                            <th>Student</th>
                                            <th>Programme / Department</th>
                                            <th>Application type</th>
                                            <th>Paid At (Lagos)</th>
                                            <th>Refund Window</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($awaitingPrintApplications as $application): ?>
                                        <tr>
                                            <td><input type="checkbox" name="reference_numbers[]" value="<?php echo htmlspecialchars($application['referencenumber']); ?>" aria-label="Select <?php echo htmlspecialchars($application['referencenumber']); ?>"></td>
                                            <td><strong><?php echo htmlspecialchars($application['referencenumber']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($application['full_name'] ?: $application['matricnumber']); ?><br><small><?php echo htmlspecialchars($application['matricnumber']); ?></small></td>
                                            <td><?php echo htmlspecialchars($application['programme'] ?: ($application['department'] ?: 'Not available')); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst($application['applicationtype'])); ?></td>
                                            <td><?php echo htmlspecialchars((new DateTime($application['paidat']))->format('d/m/Y H:i')); ?></td>
                                            <td><?php echo $printingWindow==='before' ? 'Emergency print — refund window ends ' : 'Normal waiting period elapsed — ended '; ?><?php echo CU\IdCard\Rules::deadline($application)->format('d/m/Y H:i'); ?></td>
                                            <td hidden>
                                                    <span aria-hidden="true"> · </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="action-row">
                                <button class="btn primary" type="submit" name="preview_action" value="preview-cards">Preview Cards</button>
                                <button class="btn secondary confirm-batch-print" type="button" disabled>Confirm print</button>
                            </div>
                        </form>
                        <div class="preview-box" aria-live="polite">
                            <div class="preview-inner" id="awaitingPrintPreviewState">
                                <div class="placeholder-icon" aria-hidden="true"></div>
                                <div class="preview-message">Select eligible applications, then choose Preview Cards to generate their ID-card batch.</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
