# CU ID Card System — Printing and Collection

The ID Card Officer portal handles ordinary card generation, replacement-card printing, and replacement-card collection. Domain lifecycle code in `domain/` is authoritative for replacement application state changes.

## Officer pages

Pages under `biometric/`:

- `awaiting-printing.php` — replacement queue; default After 72 Hours and optional Before 72 Hours emergency queue
- `collection.php` — printed replacement cards awaiting collection
- `permanent-id.php`, `temporary-id.php`, `selective-printing.php` — ordinary existing card workflows
- `reports.php` — batch reports and PDF access

## Replacement printing flow

Only applications with `status=paid`, `paymentstatus=paid`, no refund row, and no print/collection timestamp are eligible.

- Before 72 hours: emergency printing only.
- At/after 72 hours: normal replacement printing.
- Generate/Preview creates a batch and batch items; it does not mark a card printed.
- Confirm Print performs the physical-print transition `paid → printed` and writes `card_printed`.
- Confirm Collection performs `printed → collected` and writes `card_collected`.

Replacement batch items store the exact `applicationid`. Replacement rendering uses the application snapshot photo, never overwrites the master student photo, and fails safely if that snapshot is unavailable. Refunds and printing are mutually exclusive through shared row locking.

## Dependencies

Requires PHP 8.1+, `pdo_mysql`, `gd`, `mbstring`, and Composer dependencies:

```powershell
cd idcard-system
composer install
```

Enable `extension=gd` in the PHP configuration used by Apache/XAMPP. Browser rendering fails if Apache PHP does not have GD enabled.

## Authentication and local testing

Production requires `CU_IDCARD_IDENTITY_RESOLVER`, resolving portal session `loginid` to an `id_card_officer` identity.

For local testing:

```env
CU_AUTH_BYPASS=1
CU_AUTH_BYPASS_IDCARD_ACTOR=DEV_IDCARD_OFFICER
```

The bypass is loopback-only and must not be enabled in production. CSRF remains required for browser POST actions.

## Configuration

- `CU_IDCARD_DB_HOST`, `CU_IDCARD_DB_NAME`, `CU_IDCARD_DB_USER`, `CU_IDCARD_DB_PASS`
- `CU_IDCARD_OUTPUT_PATH` — generated PDF storage
- `CU_IDCARD_PROCESSED_PATH` — processed photo storage
- `CU_IDCARD_MPDF_PATH` — mPDF temporary storage
- `CU_IDCARD_REPLACEMENT_PHOTOS_PATH` — sibling Student replacement upload storage

Keep output, temporary files, uploads, and `vendor/` out of Git. Existing historical output PDFs should be retained locally if database batch records still reference them.

## Schema

Use `database/migrations/002_replacement_lifecycle.sql` when upgrading an older database. It creates the payment-attempt, refund, event, and batch-linkage schema used by all four portals. Do not rerun it against a database that already records `002_replacement_lifecycle` in `idcardmigrations`.
