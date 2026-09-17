# ID Card Management and Printing System

PHP/mPDF dashboard for ordinary permanent, temporary and selective cards, plus replacement printing and collection. Phase 4 integrates the shared lifecycle in `shared/lifecycle/`; Student Affairs and account-officer UI work is outside this phase.

## Setup and identity

Use PHP 8.1+ with `pdo_mysql`, `gd`, `mbstring`, Composer dependencies and the Phase 2 database migration. Existing snapshots are reference imports, not upgrades for a live database. See [shared lifecycle setup](shared/lifecycle/README.md) and `database/migrate_phase2.php` before configuring a new database. Phase 4 adds no migration and does not rewrite historical records.

The three projects share a database and remain sibling directories. Defaults are localhost / idcard_system / root / empty password; configure `CU_IDCARD_DB_HOST`, `CU_IDCARD_DB_NAME`, `CU_IDCARD_DB_USER`, `CU_IDCARD_DB_PASS` on the server. Defaults match the existing local installation; production credentials belong in server configuration.

`include/session.php` resolves `$_SESSION['loginid']` using the Phase 2 `PortalSessionAdapter`. Set `CU_IDCARD_IDENTITY_RESOLVER` to a trusted PHP file returning a Closure which looks up that login in the real CU staff/role store and returns `CU\IdCard\Identity`. It must grant `id_card_officer` only to authorized officers. Browser actor/role fields are never used. The authoritative CU staff directory/session mapping has not been supplied; the default is fail-closed. Unauthenticated pages show the navigation shell without student queues or reports; generation, confirmation, collection and PDF reads reject access.

For isolated local development only, set `CU_IDCARD_DEV_MODE=1` and `CU_IDCARD_DEV_ACTOR=<test officer identity>` in the server environment. The adapter accepts this only on loopback (or trusted CLI), only without a real login session and without a configured resolver. Never enable this mode on a production host. Session cookies use HttpOnly, SameSite=Lax and Secure under HTTPS. POST actions require a random, session/actor-bound CSRF token, sent by the dashboard in `X-CSRF-Token` or the confirmation form. Session locks are released before database/rendering work.

CLI retains `php biometric/generate_batch.php <college_id> [level] [programme_id]`. It requires the same trusted identity configuration; with an external resolver set `CU_IDCARD_CLI_LOGIN` to the login to resolve. CLI has no browser CSRF requirement. PHP no longer reads a missing CLI REQUEST_METHOD.

The local XAMPP PHP configuration has GD installed but disabled. Enable GD in the deployment PHP configuration before real rendering. Tests enable it per process with `-d extension=gd`; they do not edit global php.ini or restart Apache.

## Pages and ordinary compatibility

| Page under biometric/ | Purpose |
| --- | --- |
| permanent-id.php | College/programme/level selection and ordinary permanent cards. |
| temporary-id.php | The same filters and existing temporary card template behavior. |
| selective-printing.php | Name/matric search and selected active students. |
| awaiting-printing.php | Eligible replacement applications, window filter and PDF preview. |
| collection.php | Search printed replacement cards and confirm collection. |
| reports.php | Existing college/status/date report, counts, PDF reads and recoverable physical confirmation. |

`dashboard.php` remains a compatibility redirect, including the new collection route. Filters survive redirects. Every navigation item still has its own page. Ordinary templates, 54 x 86 mm dimensions, front/back order, college branding, temporary labeling, filters and photo preparation are preserved. Selective rendering re-reads students after photo processing so it uses the freshly prepared photo. Random filename suffixes prevent batches generated in the same second from overwriting each other.

Ordinary batches retain NULL application linkage and the guarded ordinary physical-confirmation method. They do not change replacement applications. The master photo processor still reuses existing processed photos and updates the processed path when preparing an ordinary student photo. Existing individual ordinary rendering failures retain their previous per-student behavior.

## Replacement queue and timing

`Lifecycle::printingQueue` is authoritative. The query joins an active student by matric and requires all of:

- `paymentstatus='paid'`, `status='paid'`, known `paidat <= NOW()`;
- no printed or collected timestamp;
- no row in `idcardrefunds`, regardless of refund stage;
- default **After 72 Hours**: `DATE_ADD(paidat, INTERVAL 72 HOUR) <= NOW()`;
- **Before 72 Hours**: `DATE_ADD(paidat, INTERVAL 72 HOUR) > NOW()`.

The five search predicates (reference, matric, name, department, programme) are ORed inside the eligibility predicate, with bound parameters. They never broaden the eligible dataset. Results order by paidat/applicationid. PHP and DB connections use Africa/Lagos / +01:00. Exactly 72 hours belongs only to After. The table displays Paid At and the deadline from shared `Rules::deadline`; no client countdown decides eligibility. A filter is submitted with Search. Generation revalidates the chosen window via `printingSelection` and `recordBatchItem`; crossing the boundary during generation can require refreshing and regenerating.

Unknown legacy payment information is never guessed. Legacy paid applications (`paymentstatus IS NULL`) are excluded from this queue and need explicit reconciliation outside Phase 4. The Phase 2 legacy Database methods remain restricted to legacy records for compatibility, but the dashboard no longer calls the browser-reference legacy update method. Unlinked historical awaiting-print batches cannot be confirmed through the replacement service. No paidat, payment status, event or batch linkage is fabricated for the seven historical applications.

## Generation, traceability and photo source

Web generation is POST-only, role-checked and CSRF-protected. Ordinary college/student-ID requests keep their existing modes. Replacement requests send `generated_by=awaiting-print`, `reference_numbers[]`, and `window=before|after`. Mixed college/student-ID/temporary parameters are rejected. Submitted references select applications; they never supply authoritative student IDs or actors.

1. Shared `printingSelection` locks/revalidates each application and resolves its active student.
2. Renderer creates a pending `awaiting-print` batch and uses the existing card templates.
3. Replacement photo source is **idcardapplications.photopath**, copied from the paid checkout snapshot in Phase 3. It must be `uploads/idcard/<safe filename>.jpg|jpeg|png`, inside the trusted student upload root after realpath resolution. There is no master-photo fallback.
4. GD prepares the image in `uploads/photos_processed/replacements/application-<applicationid>.jpg`. This separate directory cannot collide with the ordinary matric filename. Neither master photo column is changed.
5. Each rendered result calls shared `recordBatchItem`, persisting batch_id, exact applicationid, resolved student_id and outcome. Eligibility is rechecked at recording time. A render/linkage failure marks the entire replacement batch failed and publishes no usable PDF. A failed result is recorded when the application still permits linkage; if a concurrent refund prevents even that write, the batch remains failed and the rejection is logged. Successful items already recorded in an aborted batch cannot be confirmed.
6. Only after PDF output succeeds is generation marked completed. PDF generation, preview and download never mark an application printed.

Path overrides, useful for isolated tests: `CU_IDCARD_OUTPUT_PATH`, `CU_IDCARD_PROCESSED_PATH`, `CU_IDCARD_MPDF_PATH`, `CU_IDCARD_REPLACEMENT_PHOTOS_PATH`. The last defaults to sibling `cu_student/uploads/idcard`; configure it to match the student's upload override. Give the PHP account appropriate write/read permissions. Templates remain under assets/idcardtemplates.

`batch-pdf.php?batch_id=<id>` serves a completed batch after an officer-role check and validates its stored path is directly in the configured output directory. `&download=1` requests an attachment. All new previews/report links use this endpoint. Existing static output URLs are not revoked by Phase 4; deployment should keep PDF storage outside the public document root using the output override if access-controlled storage is required. Paths/SQL errors are logged server-side and are not returned as generation error details.

## Physical printing and concurrency

The officer uses Confirm print after actually printing. Before showing the modal, `print-confirmation.php` reads the persisted batch and successful items; an unexpired refund window produces an explicit emergency warning that printing permanently removes refund eligibility. Cancel performs no transition. The officer can resume confirmation from Reports after navigating away. The backend accepts a batch ID and CSRF token, never a list of browser references to mark printed.

Shared `confirmPrinted` owns the transaction: locks completed/unprinted replacement batch, reads successful linked items, locks/re-reads applications, checks paid/unprinted/uncollected/no refund and student correspondence, updates each application to printed with printedat/printmethod, appends `card_printed` with trusted actor and batch metadata, then marks the batch physically printed. All changes commit together. No successful item is silently skipped. Failed/skipped-only or unlinked batches are rejected. One ineligible item rolls back every update and event. Duplicate confirmation is rejected.

The existing shared application/refund locks resolve refund versus printing: refund commits first -> confirmation fails; print commits first -> refund fails. The rule also applies to emergency printing inside 72 hours. A stale PDF or warning cannot bypass the committing service. Transactions cannot prove a physical printer action; staff must confirm immediately after printing and recheck any conflict. No automated test submits a printer job.

## Collection and student history

`Lifecycle::collectionQueue` requires printed status, paid payment, a printed timestamp, no collected timestamp and no refund record. Search is reference/matric/name within that dataset. The shared architecture adds collection data/view files and one navigation item. Confirm Collection opens a modal identifying the reference. POST calls `Lifecycle::collect`, which locks the application/refund, rechecks eligibility and atomically writes collected status, collectedat, collectedby and `card_collected`. Paid, failed, refunded, unknown legacy and duplicate collection transitions fail. Successful rows leave the queue.

Student Phase 3 history already reads `idcardapplicationevents`; the actual printed and collected events appear there without another history implementation. No historical events are reconstructed.

## Verification

From REFACTOR, run:

```text
C:/xampp/php/php.exe tests/lifecycle_phase2.php
python -B tests/student_phase3.py
python -B tests/printing_phase4.py
python -B tests/navigation_smoke.py
```

Printing tests create a random disposable database, application snapshots, actual mPDF PDFs and processed photos; all are removed afterward. They hash live application/payment/refund/event/student/batch data before and after. Set `CU_PRINT_BROWSER_TEST=1` to additionally run the local Edge/CDP browser flow; screenshots go to tests/artifacts. The browser simulates database confirmations only. The navigation suite reads Apache pages; all mutations occur against isolated test servers/databases. See [Phase 4 report](PHASE4_REPORT.md) for tested results and the exact changed-file inventory.

Remaining deployment work: supply the authoritative CU session/role resolver, enable GD, configure credentials/storage/error display for the host, and validate actual duplex/printer/ribbon behavior separately. Phase 5 should implement Student Affairs refund approval through the existing shared service. The account-officer UI remains a later separately approved phase.
