# ID Card Management and Printing System

## Executive Summary

The ID Card Management and Printing System produces permanent, temporary, and selectively chosen student ID cards as PDF batches. It obtains student records from MySQL, prepares passport photographs for print, renders a front and back card for each student, saves the PDF output, and creates a batch audit trail.

The default page is `biometric/permanent-id.php`. Each navigation item has a dedicated PHP page; `biometric/dashboard.php` remains a compatibility redirect.

## End-to-End Workflow

```text
Dashboard selection
  -> Load/filter active student records from MySQL
  -> Send chosen student set to biometric/generate_batch.php
  -> Create print-ready versions of unprocessed photographs
  -> Render each student's card front and back
  -> Generate a PDF using mPDF
  -> Save the PDF in output/
  -> Log the batch and student-level results in MySQL
  -> Preview, download, print, or audit the batch
```

Each student produces two PDF pages in the following order: front, back, front, back. This is intended for duplex printing.

## Application Structure

| Folder or file | Responsibility |
| --- | --- |
| `include/config.php` | Central database, filesystem, card-dimension, photo-size, and mPDF configuration. |
| `class/Database.php` | Database access for colleges, programmes, students, batches, audit records, and reports. |
| `class/PhotoProcessor.php` | Crops, resizes, lightly normalizes, compresses, saves, and records print-ready student photos. |
| `class/Renderer.php` | Loads templates and creates the PDF card pages and batch log records. |
| `biometric/` | Dedicated permanent, temporary, selective, awaiting-printing, and report pages. |
| `include/biometric/` | Shared layout/bootstrap and page-specific data/views. |
| `assets/css/biometric.css`, `assets/js/` | Shared styles and common/page-specific scripts. |
| `biometric/generate_batch.php` | Web/CLI generation endpoint that orchestrates photo preparation and PDF production. |
| `assets/idcardtemplates/` | Reusable front, back, header, middle, and footer card templates. |
| `assets/images/` | University logo, Registrar signature/barcode, building image, and college visual assets. |
| `uploads/photos/` | Original student passport photographs. |
| `uploads/photos_processed/` | Optimized print-ready student photographs. |
| `output/` | Generated batch PDF files. |
| `tmp/mpdf/` | Temporary working/cache area used by mPDF while generating PDFs. |
| `vendor/` | Composer-installed third-party libraries, including mPDF. |
| `schema.sql` | Simplified schema and sample data. |
| `idcard_system (3).sql` | Complete database dump, including departments and programmes. |
| `README.md` | Setup and operational notes. |

## Database Relationship

```text
College
  -> Department
      -> Programme
          -> Student

College and selected students
  -> ID-card batch
      -> Batch-item result for each student
```

The system uses the following main records:

- `colleges`: College name, code, visual template key, logo path, and theme colour.
- `departments`: Departments within a college.
- `programmes`: Academic programmes within a department.
- `students`: Student identity, academic details, level, photo paths, validity period, and status.
- `id_card_batches`: One record for every PDF-generation run.
- `id_card_batch_items`: Individual success or failure result for every student in a batch.

Note: The dashboard requires the department/programme structure. The complete `idcard_system (3).sql` dump contains it; the simplified `schema.sql` does not contain all of those tables.

## Dashboard User Flow

### 1. Permanent ID Card Generation

This is the default dashboard view.

1. The administrator selects a college.
2. The page reloads and loads programmes for that college.
3. The administrator selects a programme.
4. The administrator selects a level: 100, 200, 300, 400, or 500.
5. The dashboard counts active students matching the selections.
6. When matching students are available, the administrator can download a PDF, open/print the PDF, or review the inline PDF preview.

### 2. Temporary ID Card Generation

This view is reached through `temporary-id.php`.

The college, programme, and level selection process is identical to permanent card generation. The generator receives a temporary-card setting, which removes the matric-number line and adds a `TEMPORARY ID` label to the rendered front card.

### 3. Selective Printing

This view is reached through `selective-printing.php`.

1. The administrator searches active students by name or matriculation number.
2. Matching students are displayed in the selected-students area.
3. The administrator may remove individual students or clear all selections.
4. Clicking **Preview Cards** sends the selected student IDs to the batch generator.
5. The dashboard receives a PDF URL and displays an embedded PDF preview, download option, and full-viewer option.

Selective batches use the `SEL` filename/reference prefix.

### 4. Reports and Audit

This view is reached through `reports.php`.

The administrator may filter batch history by college, generation status, and date range. The report displays the batch reference, date/time, college, cards requested, successful cards, failures, batch status, and a link to the stored PDF.

## PDF Generation Process

When `biometric/generate_batch.php` receives a request, it:

1. Validates that a college or selected student list was supplied.
2. Loads active students from the database.
3. Processes photos that do not already have a usable processed version.
4. Creates a pending `id_card_batches` record.
5. For each student, loads college details, selects the photo, renders the front and back templates, and writes both pages to the PDF.
6. Logs a success or failure result in `id_card_batch_items`.
7. Marks the batch failed if no card succeeds; otherwise saves the PDF and marks the batch completed.

A single bad student record or missing/corrupt photo does not stop other students' cards from being generated. The problem is recorded against that student in the batch audit log.

## Card Template Flow

`Renderer.php` chooses a college-specific front template when one exists at `assets/idcardtemplates/front/{template_key}.php`; otherwise it uses `front/shared_front.php`.

The shared front template includes:

1. `partials/header_logo.php` — University branding.
2. `partials/middle.php` — Student details and building image.
3. `partials/footer2.php` — Photo, validity period, student label, and college graphic.

All cards use `back/shared_back.php` for the reverse side. This template is intentionally black and white for printer ribbon efficiency.

## Current Operational Limitations

- Authentication and authorization are not implemented.
- The dashboard administrator identity is currently hard-coded.
- The top dashboard search field is visual only; it does not perform a search.
- Student photo upload and validation are not implemented in this application.
- Barcode/QR-code generation is not implemented; the card back uses a supplied barcode image.
- Individual reprinting is handled through selective printing rather than a separate reprint workflow.
