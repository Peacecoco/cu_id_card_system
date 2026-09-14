# ID Card Management and Printing System

## Setup

1. Install dependencies:
   ```
   composer install
   ```
2. Create the database:
   ```
   mysql -u root -p < schema.sql
   ```
3. Edit `include/config.php` with your real DB credentials and confirm
   `CARD_WIDTH_MM` / `CARD_HEIGHT_MM` match the actual card stock.
4. Confirm the image paths in the `colleges` table point to files under
   `/assets/images/`.
5. Upload student photos to `uploads/photos/`, with `photo_path`
   set on each student row.

## Generating a batch

```
php biometric/generate_batch.php <college_id>
```

This will:
1. Pre-process any student photos for that college that haven't
   been processed yet (resize to print size, normalize brightness,
   compress).
2. Render every active student's front and back card pages into
   one PDF, written incrementally rather than merged afterward.
3. Record the batch in `id_card_batches` and log a per-student
   result in `id_card_batch_items`, so one bad record doesn't
   fail the whole run and you have an audit trail of what was
   generated and when.

## Reports and audit

Open **Report > Reports and Audit** in the dashboard to review batch history.
The report supports filtering by college, status, and date range. It displays
requested cards, successful cards, failed students, generation status, and a
link to each available PDF.

Batch references are displayed in a readable format while the numeric database
ID remains the underlying record key:

- `ENG/001` - Engineering batch 1
- `LAW/002` - Law batch 2
- `SEL/003` - selective student batch 3

## Folder structure

```
/templates
    /front       shared_front.php - ONE layout for every college,
                 themed via colleges.primary_color (blue/yellow/green/etc).
                 Drop a file named {template_key}.php here only if a
                 specific college ever needs a genuinely different layout;
                 Renderer.php picks it up automatically over the shared one.
    /back        shared_back.php - identical for every college
   /partials    header_logo.php, footer2.php, middle.php - shared card parts
/lib
    Database.php
    PhotoProcessor.php
    Renderer.php
/output          generated batch PDFs land here
/uploads
    /photos            raw uploaded photos
    /photos_processed  resized/normalized/compressed photos
```

## Design notes worth remembering

- **Back-of-card template is deliberately pure black/white**, no
  color values anywhere. This is intentional: if the ribbon in use
  is YMCKOK (has a dedicated second black panel), a genuinely
  monochrome back side can print on the K-only panel instead of
  a full color pass. Do not add any color styling to
  `assets/idcardtemplates/back/shared_back.php`.

- **Front layout is fully shared** (`assets/idcardtemplates/front/shared_front.php`).
   `primary_color` and `logo_path` are loaded from the college record. Adding
  a new college is a single row insert, no new file. If a college's
  layout ever genuinely diverges, add `assets/idcardtemplates/front/{template_key}.php`
  and it overrides the shared template automatically for that college
  only, nothing else changes.

- **Photo pre-processing is a separate step from PDF generation**
  on purpose. A corrupted or missing photo fails that one student
  and gets logged, it does not abort the batch.

- **Front/back pages are interleaved per student** (front, back,
  front, back...) in the output PDF. Confirm this page order
  matches what the Magicard 300's duplex driver expects before
  running a full production batch, some duplex drivers expect
  all fronts then all backs instead.

## Verifying monochrome back printing

The back template is coded with black and white values only, but the source
code cannot prove how a Magicard 300 driver consumes ribbon panels. Verification
must use the actual printer, driver, and ribbon:

1. Confirm whether the installed ribbon is YMCKO or YMCKOK.
2. Print a small test batch containing the normal front and back pages.
3. Check the printer driver settings and job information for panel routing,
   monochrome, black-only, or K-panel options.
4. Print a back-only black test page if the driver supports duplex test pages.
5. Compare ribbon panel usage before and after enabling black-only routing,
   using the printer utility, ribbon counter, or physical ribbon panels.
6. Repeat the test with the same card quantity and coverage, and record the
   ribbon count and print quality.

The result is verified only when the driver routes the back through a black
panel and repeated tests show the expected reduction. A black-looking PDF or
card does not by itself prove that colour panels were not consumed.

## Not yet included (next steps)

- Photo upload handling/validation endpoint.
- Barcode/QR code generation on the front or back.
- Reprint workflow for individual students without regenerating
  the whole college batch.
- Authentication, authorization, and user management.
- Reports beyond batch-generation and per-student audit records.
