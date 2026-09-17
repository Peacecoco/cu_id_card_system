                <section class="page" aria-labelledby="page-title">
                    <div class="crumbs" aria-label="Breadcrumb">
                        <span>Home</span>
                        <span class="sep">›</span>
                        <span>Report</span>
                        <span class="sep">›</span>
                        <span>Reports and Audit</span>
                    </div>

                    <h1 id="page-title">Reports and Audit</h1>
                    <p class="subtitle">Review generated card batches and per-student results</p>

                    <form class="report-toolbar" method="get" action="reports.php">
                        <label>College
                            <select name="report_college_id">
                                <option value="">All colleges</option>
                                <?php foreach ($collegeRows as $college): ?>
                                    <option value="<?php echo (int) $college['id']; ?>" <?php echo $reportCollegeId === (int) $college['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($college['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Status
                            <select name="report_status">
                                <option value="">All statuses</option>
                                <?php foreach (['completed', 'pending', 'failed'] as $statusOption): ?>
                                    <option value="<?php echo $statusOption; ?>" <?php echo $reportStatus === $statusOption ? 'selected' : ''; ?>><?php echo ucfirst($statusOption); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>From date
                            <input type="text" name="report_from" value="<?php echo htmlspecialchars($reportFromInput); ?>" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}">
                        </label>
                        <label>To date
                            <input type="text" name="report_to" value="<?php echo htmlspecialchars($reportToInput); ?>" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}">
                        </label>
                        <button class="btn primary" type="submit">Apply filters</button>
                    </form>

                    <?php if ($reportError !== ''): ?>
                        <div class="status-message error"><?php echo htmlspecialchars($reportError); ?></div>
                    <?php endif; ?>

                    <?php
                    $reportBatchCount = count($reportRows);
                    $reportCardCount = array_sum(array_map(static fn(array $row): int => (int) $row['student_count'], $reportRows));
                    $reportSuccessCount = array_sum(array_map(static fn(array $row): int => (int) $row['success_count'], $reportRows));
                    $reportFailureCount = array_sum(array_map(static fn(array $row): int => (int) $row['failure_count'], $reportRows));
                    ?>
                    <div class="report-summary">
                        <div class="report-stat"><strong><?php echo $reportBatchCount; ?></strong><span>Batches</span></div>
                        <div class="report-stat"><strong><?php echo $reportCardCount; ?></strong><span>Cards requested</span></div>
                        <div class="report-stat"><strong><?php echo $reportSuccessCount; ?></strong><span>Cards generated</span></div>
                        <div class="report-stat"><strong><?php echo $reportFailureCount; ?></strong><span>Student failures</span></div>
                    </div>

                    <?php if (empty($reportRows)): ?>
                        <div class="preview-box">
                            <div class="preview-message">No batch records match these filters.</div>
                        </div>
                    <?php else: ?>
                        <div class="report-table-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>College</th>
                                        <th>Cards</th>
                                        <th>Success</th>
                                        <th>Failed</th>
                                        <th>Generation</th>
                                        <th>Physical print</th>
                                        <th>PDF</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportRows as $row): ?>
                                        <?php
                                        $referencePrefix = match ($row['generated_by']) {
                                            'selective' => 'SEL',
                                            'awaiting-print' => 'AWT',
                                            'temporary' => 'TMP',
                                            default => $row['college_code'] ?: 'BATCH',
                                        };
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($referencePrefix . '/' . str_pad((string) $row['id'], 3, '0', STR_PAD_LEFT)); ?></strong></td>
                                            <td><?php echo htmlspecialchars((new DateTime($row['created_at']))->format('d/m/Y H:i')); ?></td>
                                            <td><?php echo htmlspecialchars($row['college_name'] ?: 'Mixed selection'); ?></td>
                                            <td><?php echo (int) $row['student_count']; ?></td>
                                            <td><?php echo (int) $row['success_count']; ?></td>
                                            <td><?php echo (int) $row['failure_count']; ?></td>
                                            <td class="report-status <?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></td>
                                            <td class="report-status <?php echo $row['print_status'] === 'printed' ? 'completed' : ''; ?>"><?php echo htmlspecialchars(str_replace('_', ' ', $row['print_status'])); ?>
                                                <?php if ($row['status']==='completed' && $row['print_status']==='awaiting_print'): ?>
                                                <button class="btn secondary confirm-batch-print" type="button" data-batch-id="<?php echo (int)$row['id']; ?>">Confirm print</button>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php if (is_file($row['pdf_path'])): ?><a class="report-link" href="batch-pdf.php?batch_id=<?php echo (int)$row['id']; ?>" target="_blank" rel="noopener">Open PDF</a><?php else: ?>Unavailable<?php endif; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
