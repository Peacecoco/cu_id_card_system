                <section class="page" aria-labelledby="page-title">
                    <div class="crumbs" aria-label="Breadcrumb">
                        <span>Home</span>
                        <span class="sep">›</span>
                        <span>Biometric</span>
                        <span class="sep">›</span>
                        <span><?php echo $isTemporaryId ? 'Temporary ID Card Generator' : 'Permanent ID Card Generator'; ?></span>
                    </div>

                    <h1 id="page-title"><?php echo $isTemporaryId ? 'Temporary ID Card Generator' : 'Permanent ID Card Generator'; ?></h1>
                    <p class="subtitle">Filter and generate a student's <?php echo $isTemporaryId ? 'temporary' : 'permanent'; ?> ID card</p>

                    <form id="collegeForm" class="generator-panel" method="get" action="<?php echo htmlspecialchars($pageUrl); ?>">
                        <div class="form-grid">
                            <div class="field full">
                                <div class="field-head">College</div>
                                <div class="input-shell">
                                    <select name="college_id" id="college_id" required onchange="this.form.programme_id.value = ''; this.form.level.value = ''; this.form.submit()">
                                        <option value="">Select college</option>
                                        <?php foreach ($collegeOptions as $collegeId => $collegeName): ?>
                                            <option value="<?php echo (int) $collegeId; ?>" <?php echo $selectedCollegeId === (int) $collegeId ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($collegeName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="field">
                                <div class="field-head">Programme</div>
                                <div class="input-shell">
                                    <select name="programme_id" id="programme_id" required onchange="this.form.level.value = ''; this.form.submit()" <?php echo $selectedCollegeId > 0 ? '' : 'disabled'; ?>>
                                        <option value=""><?php echo $selectedCollegeId > 0 ? 'Select programme' : 'Select a college first'; ?></option>
                                        <?php foreach ($programmeOptions as $programme): ?>
                                            <option value="<?php echo (int) $programme['id']; ?>" <?php echo $selectedProgrammeId === (int) $programme['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($programme['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="field">
                                <div class="field-head">Level</div>
                                <div class="input-shell">
                                    <select name="level" id="level" required onchange="this.form.submit()" <?php echo $selectedProgrammeId > 0 ? '' : 'disabled'; ?>>
                                        <option value=""><?php echo $selectedProgrammeId > 0 ? 'Select level' : 'Select a programme first'; ?></option>
                                        <?php foreach ($levelOptions as $level): ?>
                                            <option value="<?php echo (int) $level; ?>" <?php echo $selectedLevel === $level ? 'selected' : ''; ?>>
                                                <?php echo (int) $level; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="helper-row">
                            <?php if ($studentLoadError !== ''): ?>
                                <span class="status-message error"><?php echo htmlspecialchars($studentLoadError); ?></span>
                            <?php elseif ($selectedCollegeId > 0 && $selectedProgrammeId === 0): ?>
                                Select a programme to load levels for this college.
                            <?php elseif ($selectedCollegeId > 0 && $selectedLevel === 0): ?>
                                Select a level to load students for this programme.
                            <?php elseif ($selectedCollegeId > 0 && $studentCount === 0): ?>
                                No active students were found for this college and level.
                            <?php elseif ($selectedCollegeId > 0): ?>
                                Previewing <?php echo $studentCount; ?> ID card<?php echo $studentCount === 1 ? '' : 's'; ?> for the selected programme and level.
                            <?php else: ?>
                                Select a college to load students for that college.
                            <?php endif; ?>
                        </div>
                    </form>
                    <?php if ($selectedCollegeId > 0 && $selectedProgrammeId > 0 && $selectedLevel > 0 && $studentCount > 0): ?>
                        <form id="collegePrintForm" method="post" action="generate_batch.php" class="action-row">
                            <input type="hidden" name="college_id" value="<?php echo (int) $selectedCollegeId; ?>">
                            <input type="hidden" name="programme_id" value="<?php echo (int) $selectedProgrammeId; ?>">
                            <input type="hidden" name="level" value="<?php echo (int) $selectedLevel; ?>">
                            <input type="hidden" name="temporary" value="<?php echo $isTemporaryId ? '1' : '0'; ?>">
                            <input type="hidden" name="generated_by" value="<?php echo $isTemporaryId ? 'temporary' : 'permanent'; ?>">
                            <input type="hidden" name="preview" value="1">
                            <button class="btn primary" type="submit">Preview Cards</button>
                            <button class="btn secondary confirm-batch-print" type="button" disabled>Confirm print</button>
                        </form>
                    <?php endif; ?>
                    <div class="preview-box" aria-live="polite">
                        <div class="preview-inner" id="previewState">
                            <div class="placeholder-icon" aria-hidden="true"></div>
                            <div class="preview-message"><?php echo $studentCount > 0 ? 'Choose Preview Cards to create a print-ready batch.' : 'Select a college, programme, and level to load students.'; ?></div>
                        </div>
                    </div>
                </section>
