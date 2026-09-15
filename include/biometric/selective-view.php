                <section class="page" id="selective-printing" aria-labelledby="page-title">
                    <div class="crumbs" aria-label="Breadcrumb">
                        <span>Home</span>
                        <span class="sep">›</span>
                        <span>Biometric</span>
                        <span class="sep">›</span>
                        <span>Selective Printing</span>
                    </div>

                    <h1 id="page-title">Selective Printing</h1>
                    <p class="subtitle">Search by student name or matric number</p>

                    <div class="selective-layout">
                        <form class="generator-panel" id="searchForm" method="get" action="selective-printing.php">
                            <div class="selective-search">
                                <input type="search" name="student_search" value="<?php echo htmlspecialchars($studentSearch); ?>" placeholder="Enter name or matric number" aria-label="Search by student name or matric number" autofocus>
                                <button type="submit" class="btn primary">Search</button>
                            </div>
                            <?php if ($searchError !== ''): ?>
                                <div class="status-message error"><?php echo htmlspecialchars($searchError); ?></div>
                            <?php endif; ?>
                        </form>

                        <form class="selected-panel" id="selectionForm" method="post" action="generate_batch.php" aria-label="Selected students">
                            <div class="selected-heading">
                                <span>Selected Students (<span id="selectedCount"><?php echo count($selectedStudents); ?></span>)</span>
                                <button type="button" class="clear-selection" id="clearSelection">Cancel all</button>
                            </div>
                            <?php if (empty($selectedStudents)): ?>
                                <div class="empty-results"><?php echo $studentSearch === '' ? 'Search for a student to load results.' : 'No active students matched your search.'; ?></div>
                            <?php else: ?>
                                <?php foreach ($selectedStudents as $student): ?>
                                    <div class="student-result" data-student-row>
                                        <input type="hidden" name="student_ids[]" value="<?php echo (int) $student['id']; ?>">
                                        <div class="student-avatar"><?php echo htmlspecialchars(strtoupper(substr($student['full_name'], 0, 1))); ?></div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                                            <small><?php echo htmlspecialchars($student['matric_no']); ?></small>
                                        </div>
                                        <button type="button" class="student-remove" aria-label="Remove <?php echo htmlspecialchars($student['full_name']); ?>">x</button>
                                    </div>
                                <?php endforeach; ?>
                                <div class="selection-actions">
                                    <input type="hidden" name="preview" value="1">
                                    <button type="submit" class="btn primary" formaction="generate_batch.php" formmethod="post">Preview Cards</button>
                                    <button type="button" class="btn secondary confirm-batch-print" disabled>Confirm print</button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="preview-box" aria-live="polite">
                        <div class="preview-inner" id="selectivePreviewState">
                            <div class="placeholder-icon" aria-hidden="true"></div>
                            <div class="preview-message">Preview cards will appear here after generation.</div>
                        </div>
                    </div>
                </section>
