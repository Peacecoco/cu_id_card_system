# Phase 4 Implementation Report: ID Card System Integration

## Overview
Phase 4 integrates the `idcard-system` ID Card Officer module with the shared replacement lifecycle foundation introduced in Phase 2, and student requests submitted in Phase 3. It adds replacement ID-card batch generation, PDF rendering with the replacement photograph snapshot, physical print confirmation with emergency warnings, and student card collection.

---

## 1. Files Created and Modified

### Files Created:
- `idcard-system/biometric/batch-pdf.php`: Authenticated, role-protected endpoint to securely serve batch PDFs.
- `idcard-system/biometric/collection.php`: Controller/page for replacement cards awaiting student collection.
- `idcard-system/include/biometric/collection-data.php`: Backend data loader for collection queue and search.
- `idcard-system/include/biometric/collection-view.php`: Responsive UI view for cards awaiting collection with confirmation modals.
- `idcard-system/biometric/print-confirmation.php`: Endpoint inspecting persisted batches to return physical/emergency print confirmation context.
- `idcard-system/include/session.php`: Identity adapter and CSRF protection for ID Card Officers.
- `tests/printing_phase4.py`: Comprehensive test suite verifying 95 assertions across queues, generation, emergency print, collections, and regressions.
- `tests/printing_browser.cjs`: CDP/Edge headless browser test script for UI modals, emergency warnings, PDF preview, and collection.

### Files Modified:
- `idcard-system/biometric/awaiting-printing.php`: Dedicated entry point for the replacement awaiting-print queue.
- `idcard-system/include/biometric/awaiting-data.php`: Uses `Lifecycle::printingQueue` with search and window filtering.
- `idcard-system/include/biometric/awaiting-view.php`: UI form adding 72-hour window filter (`After 72 Hours` / `Before 72 Hours`), search, table, and preview state.
- `idcard-system/include/biometric/bootstrap.php`: Enforces officer authentication, CSRF validation, handles print confirmation and collection actions, and registers navigation.
- `idcard-system/include/biometric/header.php`: Navigation bar integration for Awaiting Printing and Awaiting Collection.
- `idcard-system/include/biometric/footer.php`: Reusable `<dialog id="officerConfirmation">` modal and JavaScript bootstrap data.
- `idcard-system/assets/js/biometric.js`: Handles modal confirmations for physical printing (with emergency warning) and collection.
- `idcard-system/assets/js/awaiting-printing.js`: Select all checkboxes, batch preview fetch, and iframe previewer.
- `idcard-system/assets/css/biometric.css`: Mobile responsiveness and modal styling.
- `idcard-system/biometric/generate_batch.php`: POST-only, role-checked, CSRF-protected batch generation route for replacements and ordinary cards.
- `idcard-system/class/Renderer.php`: Interleaved duplex front/back card PDF generation; uses replacement photo snapshot into isolated directory without touching master photos.
- `idcard-system/class/PhotoProcessor.php`: Safe thumbnail and photo processing supporting custom target directories.
- `idcard-system/class/Database.php`: Guarded legacy methods to prevent accidental modification of v2 lifecycle records.
- `idcard-system/shared/lifecycle/Lifecycle.php`: Printing queue, selection validation, batch-item recording, confirm-printed transaction, and collection logic.
- `idcard-system/shared/lifecycle/Rules.php`: 72-hour business deadline rules.

---

## 2. Database Changes
No new migrations were added in Phase 4. Phase 4 fully utilizes the versioned schema from Phase 2:
- `idcardapplications`: `paymentstatus`, `paidat`, `printedat`, `collectedat`, `collectedby`.
- `id_card_batch_items`: `applicationid` foreign-key linkage.
- `idcardapplicationevents`: `card_printed` and `card_collected` audit trail events.
- All 7 pre-existing historical applications remain intact with zero schema alterations or fabricated records.

---

## 3. Awaiting Printing & 72-Hour Filtering
- Default queue: **After 72 Hours** (`window=after`), selecting paid applications where `NOW() >= paidat + 72 hours`.
- Filter option: **Before 72 Hours** (`window=before`), selecting paid applications where `NOW() < paidat + 72 hours` for emergency cases.
- Ineligible records (failed payments, unprinted legacy records with NULL payment status, refund-requested records, already printed, or collected cards) are strictly excluded.
- Search operates in conjunction with the selected window filter across reference, matric number, student name, department, and programme.

---

## 4. Emergency Printing & Refund Concurrency
- Selecting applications under the `Before 72 Hours` window enables emergency batch generation.
- The ID Card Officer is presented with a distinct confirmation prompt:
  > *"This replacement request is still within the student's 72-hour refund window. Confirming the card as printed will permanently make this application ineligible for a refund. Confirm only after physically printing the cards. Do you want to continue?"*
- Locking guarantees mutual exclusion:
  - If a student initiates a refund before physical printing is confirmed: the print confirmation transaction detects the refund and aborts the entire batch.
  - If the officer confirms printing first: the application status changes to `printed`, permanently blocking any subsequent refund request.

---

## 5. Physical Print Confirmation & Batch Traceability
- Generating, previewing, or downloading a PDF does NOT mark an application as printed.
- Application status changes to `printed` only when the officer clicks **Confirm Print** and submits `confirm-batch-printed`.
- `Lifecycle::confirmPrinted` locks the completed batch, iterates over all successful `id_card_batch_items`, verifies student and application linkage, updates `idcardapplications.status = 'printed'`, records `printedat`, and appends a `card_printed` event to `idcardapplicationevents`.

---

## 6. Replacement Photo Handling
- Card rendering specifically resolves the replacement photograph uploaded by the student during Phase 3 checkout (`idcardapplications.photopath`).
- The image is processed and saved to `uploads/photos_processed/replacements/application-{id}.jpg`.
- The student's master photograph in `students.photo_path` is never overwritten or altered.
- If the replacement photo file is missing or invalid, card generation fails safely without fallback to the master photograph.

---

## 7. Collection Flow
- Dedicated page at `biometric/collection.php` (`Awaiting Collection`).
- Displays all cards with `status = 'printed'`, `paymentstatus = 'paid'`, `printedat IS NOT NULL`, and `collectedat IS NULL`.
- Search supports application reference, matric number, and student name.
- Clicking **Confirm Collection** prompts the officer and triggers an atomic transition:
  - `status = 'collected'`
  - `collectedat = NOW()`
  - `collectedby = <officer_id>`
  - Appends `card_collected` event to `idcardapplicationevents`.
- Student's Application Requests history modal reflects both `Card printed` and `Card collected` chronologically.

---

## 8. Authentication & CSRF
- Implemented via `idcard-system/include/session.php` and `PortalSessionAdapter`.
- Requires `id_card_officer` role.
- All state-changing POST endpoints (`generate_batch.php`, `confirm-batch-printed`, `confirm-collection`) validate session-bound CSRF tokens.

---

## 9. Verification & Test Results
1. `tests/lifecycle_phase2.php`: 53 / 53 assertions passed.
2. `tests/student_phase3.py` (with browser checks): 50 / 50 assertions passed.
3. `tests/printing_phase4.py` (with browser checks): 95 / 95 assertions passed.
4. `tests/navigation_smoke.py`: 4 / 4 test cases passed.
5. Headless Edge tests captured screenshots under `tests/artifacts/` for emergency warnings, mobile collection view, student history modal, and mobile layout.
6. Zero regression on ordinary printing modes (Permanent, Temporary, Selective).

