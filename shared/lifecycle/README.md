# Phase 2: replacement lifecycle foundation

PHP 8.1+, PDO and MariaDB 10.4+. Load `bootstrap.php`, create `Lifecycle` with a dedicated PDO connection and explicitly configured `LocalPaymentSimulator`. This is simulated payment, not external verification. No new HTTP routes or UI are enabled in Phase 2.

## Migration

Run `php database/migrate_phase2.php` from idcard-system while legacy writers are stopped. It uses existing config, takes a JSON schema/data backup in the OS temporary directory (outside the web root), applies `database/migrations/002_replacement_lifecycle.sql`, and compares every original column value. Preserve that backup outside temporary storage if needed for deployment recovery. MariaDB DDL auto-commits; the script is restartable, not transactionally reversible. The version ledger records successful completion. Never restore a whole backup over subsequent writes without reconciliation.

The SQL adds nullable `paymentstatus`, `paidat`, `collectedat`, `collectedby` to applications. Existing values remain unchanged, with NULL payment status explicitly unknown. No old events, payment dates or batch mappings are invented. The SQL reports legacy applications, including contradictory print evidence, for manual reconciliation. Legacy requests are excluded from the new service's refund/print queues until reconciled.

`idcardpaymentattempts` snapshots reason, validated photo metadata, fee/charges, currency and payment option before an application exists. This avoids weakening the existing paymenttransactions.applicationid NOT NULL foreign key. There is at most one outstanding attempt per student through serialized student-row locking. Internal references use 128 random bits and database unique constraints.

Transactions retain their existing amounts/references; new nullable attempt/provider/currency/completion/failure fields identify v2 payments without relabeling historical simulated successes. `paidat` is absent on failure. A cancelled or abandoned attempt has no application. Abandoned pending attempts are reused; explicit cancellation permits a fresh checkout. There is no automatic expiration job in this phase.

`idcardrefunds` has one row per application, status, recorded amount, timestamps and actors for request/approval/credit. The current amount is the replacement base fee; gateway charges are not included. Confirm institutional refund-charge policy before the Account Officer UI phase.

`idcardapplicationevents` stores real transition events, actor, time and minimal metadata. Unique application/event keys prevent duplicate lifecycle events. Database triggers prohibit UPDATE/DELETE; the service exposes only transactional append and authorized reads. These controls do not restrict a database administrator from disabling triggers.

`id_card_batch_items.applicationid` is nullable for ordinary/legacy batches, foreign-keyed for replacement items, and unique within a batch. Applications may appear in multiple preview batches, but only one physical-print transition can succeed. Payment attempt/transaction/application unique keys, foreign keys and refund/history/queue indexes are included.

## Identity and authorization

`PortalSessionAdapter` accepts the existing session and a trusted server-side resolver for `loginid`. The resolver must load authenticated identity and roles from the host CU portal. Numeric role IDs cannot be safely inferred from the sibling legacy helpers, so no guessed mapping, hard-coded staff identity, login form, password database or browser-supplied roles is introduced.

Role identifiers: `student`, `student_affairs`, `id_card_officer`, `account_officer`. Student matric comes from Identity, not a submitted matric/reference. The adapter rejects missing/unresolved login. Tests construct identities directly only inside their isolated test process. Future HTTP controllers must resolve identity through the adapter, enforce CSRF for session-authenticated mutations, and never construct Identity from JSON/POST data.

## Service contract and state machine

- `beginCheckout`: identity, active/cooldown checks, validated photo metadata and fee snapshot; returns an attempt reference. No application.
- `cancelCheckout`: pending to cancelled; no application. Terminal payments cannot be cancelled.
- `completePayment`: obtains terminal result from configured provider, then atomically creates application, payment transaction and event, and links attempt. Replays return the same application.
- Successful payment: paymentstatus=paid, status=paid, paidat set.
- Failed payment: paymentstatus=failed, status=failed (no operational card state), paidat=NULL. A new attempt is allowed.
- Card flow: paid -> printed -> collected.
- Refund flow: paid -> refunded plus refund requested -> approved -> credited. Application remains refunded throughout; it never implies money was credited.
- `requestRefund`: student's own paid, unprinted, uncollected application with no refund and now < paidat + 72 hours.
- `approveRefund` / `creditRefund`: authorized role and exact current refund state; no duplicate transitions.
- `printingQueue` / `printingSelection`: after filter is >=72 hours; before filter is <72 hours. Both exclude refunds, failed/unknown payments and print/collection evidence. Search combines with the filter.
- `recordBatchItem`: renderer-only integration point linking exact application and matching active student to pending replacement batch and actual outcome. Revalidates window and state; records no physical print event.
- `confirmPrinted`: locks completed replacement batch, then all successful persisted linked applications. Rechecks eligibility and identity linkage, writes printed timestamps/events and confirms batch in one transaction. Failed/skipped items are not marked printed. Missing linkage rejects confirmation. Any conflicting item rolls back the entire confirmation.
- `collect`: only printed, uncollected, paid, non-refund applications.
- `history`: ownership-scoped for students, role-scoped for staff; empty for untouched legacy applications.

Normal and emergency selections are validated at selection and batch-item recording. Physical confirmation permits a valid selected card after it crosses the 72-hour boundary; it rechecks current paid/no-refund/no-print state rather than trusting the old selected filter.

## Transactions and time

All transitions own their transaction boundary; nested transactions are rejected. Refund and print actions lock the same application with SELECT FOR UPDATE and re-read refund state. Batch operations lock batch then applications in reference order. The first valid refund/print commit makes the other fail. Checkout creation/finalization locks the student before attempt and active applications, serializing concurrent attempts; unique payment references and attempt linkage enforce idempotency. Deadlocks/database errors roll back entirely and propagate; callers may safely retry using the same attempt reference.

PHP uses Africa/Lagos. Every lifecycle PDO session uses +01:00, Lagos's fixed offset, without depending on installed MySQL timezone tables. Eligibility uses database NOW() interpreted as Lagos. Existing timestamp values are not shifted. The legacy configurations now use the same clock for future writes.

The PaymentProvider interface intentionally has no HTTP callback implementation yet. A future real provider must verify merchant, amount, currency and reference, handle asynchronous cancellation/settlement reconciliation, and avoid long network work while holding database locks. No current result is called externally verified. The simulator outcome is selected by trusted server configuration, not by a request parameter.

## Phase boundaries and printing limitations

The existing student/Student Affairs APIs and screens still use the legacy lifecycle until Phases 3/5. New v2 services are not wired to those endpoints. Legacy print methods cannot confirm linked v2 batches or move v2 applications to printed; the old queue only shows legacy records. Phase 4 must wire the generator to printingSelection and recordBatchItem and replace its confirmation path with confirmPrinted. Do not enable new payment endpoints while leaving v2 printing unintegrated and present that as a finished workflow.

The service does not send jobs to a physical printer. A downloaded PDF can be printed outside database control. Confirmation can reject a refund conflict, but cannot undo physical output. Staff must follow the confirmation procedure; spooler reservation/dispatch is a separate integration decision. Ordinary permanent/temporary/selective batch generation remains unchanged apart from timezone configuration and v2 isolation guards.

## Verification

`php ../tests/lifecycle_phase2.php` creates a random `cu_lifecycle_test_*` schema, copies table definitions only, migrates twice, tests the services, and drops only that process's test database. It includes separate-process concurrent refund/print and duplicate-payment tests. It never writes fixture records to idcard_system. `python -B ../tests/navigation_smoke.py` checks legacy page navigation without payment or generation mutations.

Phase 3 still needs trusted session resolver wiring/CSRF, validated photo staging, student checkout/API integration, cancel confirmation, Application Requests table, payment/history modals and student refund endpoint/UI. Later phases wire replacement generation/collection, Student Affairs approval and Account Officer credit UI. No real gateway is required for the approved local development scope.
