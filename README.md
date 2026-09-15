# ID Card Management and Printing System

PHP/mPDF application for permanent, temporary, and selectively chosen student ID cards. It prepares photos, creates front/back PDF batches, logs generation results, and records physical-print confirmation.

## Related projects

| Project | Responsibility |
| --- | --- |
| [CU Student](../cu_student/README.md) | Replacement applications, status, invoices, and local payment recording. |
| [CU Student Affairs](../cu_studentaffairs/README.md) | Review, rejection, fee approval, and payment deadlines. |
| `idcard-system` | Paid replacement queue, card PDFs, print confirmation, and batch reports. |

The projects share the `idcard_system` database. The implemented replacement path is `submitted → awaitingpayment → paid → printed`, with rejection and payment-expiry branches. Later pickup/collection/closure statuses exist in the data model, but these three folders do not implement those actions.

## Setup

1. Use PHP 8.1+, MySQL/MariaDB, Composer, and PHP extensions `pdo_mysql`, `gd`, and `mbstring`. Student uploads additionally need `fileinfo`.
2. Run `composer install` from this folder. [composer.json](composer.json) requires `mpdf/mpdf` `^8.2`.
3. Prepare the database below and edit [include/config.php](include/config.php). Configure the two sibling projects separately to use the same database.
4. Create `output/` and make it writable. Allow PHP to create/write `tmp/mpdf/` and `uploads/photos_processed/`.
5. Supply student JPEG/PNG photos and valid `students.photo_path` values. Existing processed photos are reused when present. Paths must resolve from the generator; absolute filesystem paths avoid working-directory ambiguity. Imported paths may need adjustment for this installation.
6. Check `colleges.logo_path` and the branding/signature/barcode assets in `assets/images/`. College logo paths are resolved relative to this project root.
7. Keep all three folders as siblings under the PHP server document root. For this workspace, open `http://localhost/REFACTOR/idcard-system/biometric/permanent-id.php`.

### Database setup

For a new development database, create `idcard_system` and import [idcard_system (4).sql](<idcard_system (4).sql>) through phpMyAdmin or the MySQL client. It includes academic tables, students, applications, batches, batch items, and physical-print columns. It also contains historical records and installation-specific paths.

Do not treat a full snapshot as a migration for an existing installation. `schema.sql` is a simplified reference missing tables required by the dashboard; `(3).sql` is an older snapshot.

**The `(4).sql` dump does not fully bootstrap the student/review/payment modules.** To enable the shared workflow:

1. Bring across only the `idcardsettings` table definition, seed rows, indexes, and AUTO_INCREMENT statements from [Full_ID_Card_Module/idcarddb.sql](../Full_ID_Card_Module/idcarddb.sql). Do not import that entire separate application's database over the printing schema.
2. Import [cu_student/database/payment_setup.sql](../cu_student/database/payment_setup.sql) into the shared database. It creates `paymentoptions` and `paymenttransactions`; re-running it also updates the seeded option configuration.
3. Confirm active settings exist for `damaged` and `loststolen`. Fees, expiry/cooldown days, upload limits, and MIME allowlists are read from this table.

No consolidated migration/bootstrap script is currently supplied.

## Dashboard

Each navigation item has its own PHP page under `biometric/`:

| Page | Purpose |
| --- | --- |
| `permanent-id.php` (default) | Select college, programme, and level; generate permanent cards. |
| `temporary-id.php` | Same selection, with matriculation number omitted and a temporary-ID label. |
| `selective-printing.php` | Search active students by name/matriculation number, remove unwanted selections, and generate cards. Search returns up to 50 matches. |
| `awaiting-printing.php` | Search paid replacement applications; generate for those with a matching active student. |
| `reports.php` | Filter by college, generation status, and date range; inspect counts, print status, and PDFs. Dates use `DD/MM/YYYY`. |

`dashboard.php` now redirects to permanent cards by default. Old section bookmarks redirect to their dedicated page and retain data filters; normal navigation and forms use only dedicated URLs. Search, selection IDs, college/programme/level, and report filters remain query data.

Preview creates a real PDF and audit batch. Opening/downloading it does not confirm physical printing. After printing, the dashboard submits `action=confirm-batch-printed` and `batch_id`; replacement batches also submit selected `reference_numbers[]`. This records the batch as printed and moves supplied paid applications to `printed` with method `local`.

Generation status (`pending`, `completed`, `failed`) and physical-print status (`awaiting_print`, `printed`) are separate. Report references use college prefixes or `SEL`, such as `ENG/001` and `SEL/003`.

## Generator interface

Run from this folder:

```text
php biometric/generate_batch.php <college_id>
php biometric/generate_batch.php <college_id> <level> <programme_id>
```

CLI generates permanent cards, prints progress/results, and exits nonzero on fatal errors. The script currently reads `$_SERVER['REQUEST_METHOD']` before branching on request mode, so ordinary CLI execution may emit an undefined-key warning.

Web endpoint: `biometric/generate_batch.php`.

- GET/POST: `college_id`, optional `level` and `programme_id`.
- POST: `student_ids[]` selects active students instead of college filtering.
- POST `preview=1`: returns JSON containing `pdf_url`, `download_name`, `batch_id`, and `success_count`.
- GET `inline=1`: streams a PDF inline; otherwise a non-preview response is a PDF attachment.
- `temporary=1`: enables temporary cards for college-based generation.
- POST `generated_by`: accepts `selective`, `awaiting-print`, `temporary`, or `permanent` as a batch label.

## Folder guide

| Path | Responsibility |
| --- | --- |
| `biometric/dashboard.php` | Compatibility redirect to dedicated pages; preserves old bookmarks and POST bodies. |
| `biometric/permanent-id.php`, `temporary-id.php`, `selective-printing.php`, `awaiting-printing.php`, `reports.php` | Dedicated navigation entry points, each loading its own data and view. |
| `include/biometric/` | Shared bootstrap/print confirmation, header/footer, and extracted page data/views. Permanent and temporary generation share the college form. |
| `assets/css/biometric.css`, `assets/js/` | Extracted dashboard styling, shared navigation/confirmation, and page-specific generation scripts. |
| `biometric/generate_batch.php` | Web/CLI photo preparation and PDF generation. |
| `class/Database.php` | Academic queries, paid queue, photo updates, batches, print states, and reports. |
| `class/PhotoProcessor.php` | Center-crops JPEG/PNG photos to 260 × 307 px, adjusts brightness/contrast, and saves JPEG at quality 88. |
| `class/Renderer.php` | Templates, front/back rendering, PDF output, and per-student audit logs. |
| `include/config.php` | Database, paths, card/photo dimensions, and Windows-compatible mPDF asset URIs. |
| `assets/idcardtemplates/front/shared_front.php` | Shared college-themed front. |
| `assets/idcardtemplates/back/shared_back.php` | Shared monochrome back. |
| `assets/idcardtemplates/partials/` | `header_logo.php`, `middle.php`, and `footer.php`. |
| `assets/images/` | University/college branding, building photo, signature, and static barcode. |
| `uploads/photos/` | Original student photos. |
| `uploads/photos_processed/` | Generated print-ready photos. |
| `output/` | Generated PDF batches. |
| `tmp/mpdf/` | mPDF working files and cache. |
| `vendor/` | Composer-managed dependencies. |
| `schema.sql`, `idcard_system (3).sql`, `idcard_system (4).sql` | Reference schema and snapshots; see setup caveats. |
| `ID_CARD_SYSTEM_WORKFLOW.md` | Earlier workflow overview; this README reflects current code where they differ. |

## Rendering and print operation

Card dimensions are configured as 54 × 86 mm with zero PDF margins. Template CSS also has fixed dimensions, so changing configuration alone may not resize every element.

Fronts use the college's `primary_color` and `logo_path`. A template at `assets/idcardtemplates/front/{template_key}.php` overrides the shared front. The renderer supports additional college records, but the dashboard selector currently starts from a fixed set of four IDs.

Each student renders front then back, repeated through the PDF. Per-student rendering failures are logged in `id_card_batch_items`; zero successful cards marks the batch failed. Photo preparation collects its own failures, and rendering can fall back to the original photo path.

Keep the back template monochrome. Its barcode is a static image, not generated for each student. Individual card generation is available through selective printing.

### Verifying monochrome back printing

A black-looking PDF does not prove which ribbon panels a Magicard 300 driver uses. Verify using the actual printer, driver, and ribbon:

1. Confirm installed ribbon type (YMCKO or YMCKOK), duplex page order, and card dimensions.
2. Print a small front/back test batch.
3. Check black-only/K-panel routing options and printer job information.
4. If supported, print a back-only black test page.
5. Compare ribbon usage before and after routing changes using the printer utility, counter, or physical panels.
6. Repeat with equal card quantity/coverage and record usage and print quality.

## Current integration limits

- Authentication/authorization is absent and the dashboard user display is hard-coded.
- Replacement previews use master `students.photo_path` / `photo_processed_path`. New application `photopath` values are not automatically promoted into the master record or used by generation.
- Print confirmation trusts submitted references. No persisted application-to-batch mapping checks that every reference was successfully rendered; batch/application print updates are separate operations.
- There is no master-photo upload endpoint here. `cu_student` handles replacement uploads.
- Pickup scheduling, collection, and closure are not implemented here.
- `include/config.php` enables displayed errors; deployment configuration still needs to be integrated with the host portal.

## Verification and troubleshooting

Navigation regression checks are available at [tests/navigation_smoke.py](../tests/navigation_smoke.py). Run `python tests/navigation_smoke.py` from REFACTOR with Apache/PHP running. They check page routes, assets, filters, and redirects without generating PDFs or changing application/payment records. With development data, generate one permanent and one temporary card, inspect both PDF sides and images, generate a selective batch, and check report counts. Preview a paid replacement application and confirm it stays `paid` until physical-print confirmation.

Generation writes PDFs, photo paths, and audit records. Confirmation changes shared application states. For failures, check database tables/configuration, Composer autoload, GD, photo paths, branding assets, and write permissions. Resolve missing active-student matches before generating cards from the paid queue.
