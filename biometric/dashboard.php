<?php
session_start();
require __DIR__ . '/../include/config.php';
require __DIR__ . '/../class/Database.php';

$section = filter_input(INPUT_GET, 'section', FILTER_UNSAFE_RAW) ?: '';
$isAwaitingPrint = $section === 'awaiting-print';
$isSelectivePrinting = $section === 'selective-printing';
$isPermanentId = $section === 'permanent-id' || $section === '';
$isTemporaryId = $section === 'temporary-id';
$isReport = $section === 'reports';
$studentSearch = trim((string) (filter_input(INPUT_GET, 'student_search', FILTER_UNSAFE_RAW) ?: ''));
$selectedIds = isset($_GET['selected_ids']) && is_array($_GET['selected_ids']) ? $_GET['selected_ids'] : [];
$searchResults = [];
$selectedStudents = [];
$searchError = '';
$collegeOptions = [
    1 => 'Engineering',
    2 => 'Management Sciences',
    3 => 'Science',
    4 => 'Leadership',
];
$selectedCollegeId = filter_input(INPUT_GET, 'college_id', FILTER_VALIDATE_INT) ?: 0;
$selectedProgrammeId = filter_input(INPUT_GET, 'programme_id', FILTER_VALIDATE_INT) ?: 0;
$programmeOptions = [];
$selectedLevel = filter_input(INPUT_GET, 'level', FILTER_VALIDATE_INT) ?: 0;
$levelOptions = [100, 200, 300, 400, 500];
$selectedLevel = in_array($selectedLevel, $levelOptions, true) ? $selectedLevel : 0;
$studentCount = 0;
$studentLoadError = '';
$reportRows = [];
$reportError = '';
$reportCollegeId = filter_input(INPUT_GET, 'report_college_id', FILTER_VALIDATE_INT) ?: 0;
$reportStatus = filter_input(INPUT_GET, 'report_status', FILTER_UNSAFE_RAW) ?: '';
$reportFromInput = trim((string) (filter_input(INPUT_GET, 'report_from', FILTER_UNSAFE_RAW) ?: ''));
$reportToInput = trim((string) (filter_input(INPUT_GET, 'report_to', FILTER_UNSAFE_RAW) ?: ''));
$reportFrom = '';
$reportTo = '';
$awaitingPrintSearch = trim((string) (filter_input(INPUT_GET, 'awaiting_print_search', FILTER_UNSAFE_RAW) ?: ''));
$awaitingPrintApplications = [];
$awaitingPrintError = '';
$awaitingPrintMessage = '';
$batchPrintError = '';
$batchPrintMessage = '';

foreach ([['input' => $reportFromInput, 'output' => 'reportFrom'], ['input' => $reportToInput, 'output' => 'reportTo']] as $reportDate) {
    if ($reportDate['input'] === '') {
        continue;
    }

    $parsedDate = DateTime::createFromFormat('d/m/Y', $reportDate['input']);
    $dateErrors = DateTime::getLastErrors();
    if ($parsedDate !== false && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))) {
        ${$reportDate['output']} = $parsedDate->format('Y-m-d');
    }
}

if ($isPermanentId || $isTemporaryId) {
    try {
        $database = new Database();
        $collegeRows = $database->getAllColleges();
        foreach ($collegeRows as $college) {
            $collegeId = (int) ($college['id'] ?? 0);
            if (isset($collegeOptions[$collegeId])) {
                $collegeOptions[$collegeId] = $college['name'] ?? $collegeOptions[$collegeId];
            }
        }
        if ($selectedCollegeId > 0) {
            $programmeOptions = $database->getProgrammesByCollege($selectedCollegeId);
        }
        if ($selectedCollegeId > 0 && $selectedProgrammeId > 0 && $selectedLevel > 0) {
            $studentCount = count($database->getStudentsByCollege($selectedCollegeId, $selectedLevel, $selectedProgrammeId));
        }
    } catch (Throwable $exception) {
        $studentLoadError = 'Unable to load college or student data.';
    }
}

if ($isSelectivePrinting) {
    try {
        $database = new Database();
        $selectedStudents = $database->getActiveStudentsByIds($selectedIds);
        if ($studentSearch !== '') {
            $searchResults = $database->searchActiveStudents($studentSearch);
            $studentsById = [];
            foreach (array_merge($selectedStudents, $searchResults) as $student) {
                $studentsById[(int) $student['id']] = $student;
            }
            $selectedStudents = array_values($studentsById);
        }
    } catch (Throwable $exception) {
        $searchError = 'Unable to search students right now.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm-batch-printed') {
    try {
        $database = new Database();
        $batchId = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT);
        $referenceNumbers = isset($_POST['reference_numbers']) && is_array($_POST['reference_numbers'])
            ? $_POST['reference_numbers']
            : [];

        if (!$batchId || !$database->markBatchAsPrinted($batchId)) {
            $batchPrintError = 'This batch is not available for print confirmation.';
        } else {
            $applicationCount = $database->markApplicationsAsPrinted($referenceNumbers, 'local');
            $batchPrintMessage = 'Batch confirmed as physically printed.' . ($applicationCount > 0 ? ' ' . $applicationCount . ' application(s) moved to printed.' : '');
        }
    } catch (Throwable $exception) {
        $batchPrintError = 'Unable to confirm the batch as printed.';
    }
}

if ($isAwaitingPrint) {
    try {
        $database = new Database();

        $awaitingPrintApplications = $database->getAwaitingPrintApplications($awaitingPrintSearch);
    } catch (Throwable $exception) {
        $awaitingPrintError = 'Unable to load the awaiting-print queue right now.';
    }
}

if ($isReport) {
    try {
        $database = new Database();
        $collegeRows = $database->getAllColleges();
        $reportRows = $database->getBatchReports($reportCollegeId ?: null, $reportStatus, $reportFrom, $reportTo);
    } catch (Throwable $exception) {
        $reportError = 'Unable to load report data right now.';
        $collegeRows = [];
    }
} elseif (!isset($collegeRows)) {
    $collegeRows = [];
}

$currentUser = [
    'name' => 'Mr. Adeshina',
    'role' => 'Administrator',
];

$menuGroups = [
    [
        'label' => 'Biometric',
        'expanded' => true,
        'items' => [
            ['label' => 'Awaiting Printing', 'active' => $isAwaitingPrint, 'children' => [], 'href' => 'dashboard.php?section=awaiting-print'],
            ['label' => 'Temporary ID card', 'active' => $isTemporaryId, 'children' => [], 'href' => 'dashboard.php?section=temporary-id'],
            ['label' => 'Permanent ID card', 'active' => $isPermanentId, 'children' => [], 'href' => 'dashboard.php?section=permanent-id'],
            ['label' => 'Selective Printing', 'active' => $isSelectivePrinting, 'children' => [], 'href' => 'dashboard.php?section=selective-printing'],
        ],
    ],
    ['label' => 'Report', 'expanded' => $isReport, 'href' => 'dashboard.php?section=reports', 'items' => [
        ['label' => 'Reports and Audit', 'active' => $isReport, 'children' => [], 'href' => 'dashboard.php?section=reports'],
    ]],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permanent ID Card Generator</title>
    <style>
        :root {
            --page-bg: #f5f3f4;
            --panel-bg: #f3f1f3;
            --panel-strong: #f0e9ef;
            --sidebar-panel: #efedf0;
            --sidebar-item: #e9e5e8;
            --sidebar-active: #d9b9ea;
            --field-bg: #f0eef0;
            --field-line: #d8d1d7;
            --primary: #d8b1ea;
            --primary-dark: #c28fe0;
            --primary-soft: #ebd7f4;
            --text: #2f2d30;
            --muted: #7b757c;
            --muted-strong: #5b565c;
            --heading: #2d2b2f;
            --success: #4c9d75;
            --danger: #d06a65;
            --shadow: 0 18px 34px rgba(59, 47, 82, 0.08);
            --border-soft: rgba(123, 117, 124, 0.18);
            --brand: #2d2d2d;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--page-bg);
            color: var(--text);
        }

        body {
            min-height: 100vh;
        }

        button,
        input,
        select {
            font: inherit;
        }

        .app-shell {
            display: flex;
            min-height: 100vh;
            background: var(--page-bg);
        }

        .sidebar {
            width: 330px;
            background: rgba(248, 245, 247, 0.98);
            border-right: 1px solid var(--border-soft);
            padding: 28px 18px 24px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 8px 12px 18px;
            border-bottom: 1px solid var(--border-soft);
        }

        .crest {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 2px solid rgba(52, 52, 52, 0.85);
            background: linear-gradient(135deg, #f7f2f7, #dfe6e0);
            position: relative;
            box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.6);
        }

        .brand-mark::before,
        .brand-mark::after {
            content: "";
            position: absolute;
            border-radius: 50%;
        }

        .brand-mark::before {
            inset: 8px;
            border: 2px solid rgba(51, 53, 55, 0.8);
            background: rgba(255, 255, 255, 0.25);
        }

        .brand-mark::after {
            inset: 15px 10px 10px 15px;
            background: rgba(51, 53, 55, 0.7);
            transform: rotate(45deg);
        }

        .brand-copy {
            line-height: 1.05;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--brand);
        }

        .brand-copy strong {
            display: block;
            font-size: 1.1rem;
            letter-spacing: 0.08em;
        }

        .brand-copy span {
            display: block;
            font-size: 0.72rem;
            letter-spacing: 0.18em;
            font-weight: 600;
        }

        .profile-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 6px 6px 12px 8px;
        }

        .profile-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--sidebar-active);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #2b2b2b;
            font-weight: 700;
            box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.66);
        }

        .profile-name {
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--heading);
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 4px;
        }

        .nav-item {
            width: 100%;
            display: flex;
            flex-direction: column;
            background: transparent;
            border: 0;
            padding: 0;
            text-align: left;
            color: inherit;
        }

        .nav-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            padding: 14px 16px 14px 18px;
            border-radius: 10px;
            background: rgba(208, 205, 209, 0.35);
            color: var(--text);
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 2px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .nav-label:hover {
            background: rgba(200, 190, 203, 0.3);
        }

        .nav-item.active>.nav-label {
            background: linear-gradient(90deg, var(--sidebar-active), rgba(217, 185, 234, 0.82));
            border-color: rgba(165, 116, 194, 0.18);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.24);
        }

        .caret {
            font-size: 1.05rem;
            color: var(--muted-strong);
            line-height: 1;
        }

        .nav-submenu {
            display: none;
            flex-direction: column;
            gap: 6px;
            margin: 4px 6px 0 18px;
            padding-left: 8px;
            border-left: 1px solid rgba(117, 110, 125, 0.2);
        }

        .nav-item.open>.nav-submenu {
            display: flex;
        }

        .nav-subitem {
            display: block;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 0.98rem;
            color: var(--muted-strong);
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .nav-subitem:hover,
        .nav-subitem.active {
            background: rgba(143, 118, 170, 0.08);
            color: var(--text);
        }

        .content {
            flex: 1;
            padding: 26px 36px 34px 32px;
        }

        .topbar {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 28px;
        }

        .searchbar {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(180deg, rgba(220, 199, 225, 0.85), rgba(217, 196, 226, 0.7));
            border: 1px solid rgba(165, 138, 182, 0.1);
            border-radius: 20px;
            padding: 16px 20px 15px 20px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .search-icon {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(40, 40, 40, 0.7);
            border-radius: 50%;
            position: relative;
            opacity: 0.9;
            flex-shrink: 0;
        }

        .search-icon::after {
            content: "";
            position: absolute;
            width: 9px;
            height: 2px;
            background: rgba(40, 40, 40, 0.7);
            right: -5px;
            bottom: -1px;
            transform: rotate(45deg);
            border-radius: 2px;
        }

        .searchbar input {
            width: 100%;
            background: transparent;
            border: 0;
            outline: none;
            color: var(--muted-strong);
            font-size: 1.08rem;
            letter-spacing: 0.02em;
        }

        .searchbar input::placeholder {
            color: rgba(69, 62, 75, 0.72);
        }

        .user-panel {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 4px 8px 10px;
            white-space: nowrap;
            margin-left: auto;
        }

        .user-name {
            text-align: right;
            line-height: 1.2;
            color: var(--heading);
        }

        .user-name strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .user-name small {
            display: block;
            color: var(--muted);
            font-size: 0.79rem;
        }

        .user-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(119, 92, 131, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(119, 92, 131, 0.18);
            font-size: 0.9rem;
        }

        .page {
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 620px;
        }

        .crumbs {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: var(--muted);
            margin: 4px 0 22px;
            flex-wrap: wrap;
        }

        .crumbs .sep {
            color: var(--muted);
            opacity: 0.7;
        }

        .page h1 {
            margin: 0;
            font-size: clamp(2.2rem, 2.4vw, 3.25rem);
            line-height: 1.08;
            font-weight: 700;
            letter-spacing: -0.04em;
            color: var(--heading);
        }

        .subtitle {
            margin: 22px 0 28px;
            font-size: clamp(1.1rem, 1.3vw, 1.7rem);
            line-height: 1.4;
            color: rgba(77, 69, 83, 0.75);
            font-weight: 400;
        }

        .generator-panel {
            width: min(100%, 1200px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(126, 118, 127, 0.15);
            border-radius: 10px;
            padding: 10px 0 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 22px;
            margin-bottom: 12px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field-label {
            font-size: 1.02rem;
            color: var(--muted-strong);
            font-weight: 600;
            margin: 0;
        }

        .input-shell {
            width: 100%;
            position: relative;
        }

        .input-shell input,
        .input-shell select {
            width: 100%;
            height: 54px;
            padding: 14px 16px;
            border-radius: 6px;
            border: 1px solid var(--field-line);
            background: rgba(232, 230, 232, 0.5);
            color: var(--text);
            outline: none;
            font-size: 1.02rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .input-shell input::placeholder {
            color: rgba(72, 66, 76, 0.65);
        }

        .input-shell input:focus,
        .input-shell select:focus {
            border-color: rgba(150, 117, 172, 0.42);
            box-shadow: 0 0 0 4px rgba(214, 188, 229, 0.18);
            background: rgba(241, 236, 242, 0.76);
        }

        .field-head {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border-radius: 6px 6px 0 0;
            background: linear-gradient(180deg, var(--primary), rgba(201, 168, 222, 0.94));
            border: 1px solid rgba(155, 118, 171, 0.18);
            color: rgba(54, 52, 59, 0.88);
            font-size: 1.02rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            padding: 12px 18px;
        }

        .field-head+.input-shell input,
        .field-head+.input-shell select {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            border-top: 0;
        }

        .field-head+.input-shell input,
        .field-head+.input-shell select {
            background: rgba(225, 221, 227, 0.58);
        }

        .helper-row {
            font-size: 0.98rem;
            color: rgba(74, 68, 80, 0.7);
            margin: 10px 0 18px;
            min-height: 22px;
        }

        .action-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        .btn {
            appearance: none;
            border: 1px solid rgba(96, 87, 100, 0.4);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
            font-size: 1.05rem;
            font-weight: 600;
            min-width: 162px;
            height: 52px;
            padding: 0 28px;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(56, 48, 68, 0.08);
        }

        .btn.primary {
            border-color: rgba(152, 118, 174, 0.18);
            background: linear-gradient(180deg, rgba(221, 197, 231, 0.76), rgba(206, 176, 222, 0.78));
            color: rgba(41, 38, 43, 0.9);
        }

        .btn.secondary {
            background: rgba(238, 235, 238, 0.45);
        }

        .preview-box {
            width: min(100%, 1200px);
            min-height: 260px;
            background: rgba(240, 237, 240, 0.78);
            border: 1px solid rgba(126, 118, 127, 0.12);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            margin-top: 6px;
            position: relative;
        }

        .preview-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-align: center;
            color: rgba(95, 87, 101, 0.76);
            width: 100%;
        }

        .placeholder-icon {
            width: 54px;
            height: 54px;
            border: 2px solid rgba(100, 97, 103, 0.55);
            border-radius: 10px;
            position: relative;
            background: rgba(255, 255, 255, 0.14);
        }

        .placeholder-icon::before {
            content: "";
            position: absolute;
            inset: 15px 12px 12px 12px;
            border: 2px solid rgba(100, 97, 103, 0.75);
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.12);
        }

        .placeholder-icon::after {
            content: "";
            position: absolute;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(100, 97, 103, 0.75);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            right: 11px;
            top: 9px;
        }

        .preview-message {
            font-size: 0.98rem;
            color: rgba(98, 89, 103, 0.68);
        }

        .pdf-preview-frame {
            width: 100%;
            height: min(74vh, 920px);
            min-height: 520px;
            border: 1px solid rgba(45, 45, 45, 0.42);
            border-radius: 3px;
            background: #3a3a3a;
        }

        .pdf-preview-note {
            margin: 0;
            font-size: 0.9rem;
            color: rgba(98, 89, 103, 0.72);
        }

        .selective-preview-frame {
            width: 100%;
            height: min(74vh, 920px);
            min-height: 520px;
            border: 1px solid rgba(45, 45, 45, 0.42);
            border-radius: 3px;
            background: #3a3a3a;
        }

        .preview-download {
            display: inline-block;
            margin-top: 8px;
            padding: 8px 14px;
            border-radius: 6px;
            background: var(--primary-soft);
            color: var(--text);
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
        }

        .preview-open {
            margin-left: 8px;
            color: var(--muted-strong);
            font-size: 0.8rem;
        }

        .status-message {
            min-height: 22px;
            margin-top: 8px;
            font-size: 0.96rem;
            font-weight: 600;
            color: var(--success);
        }

        .status-message.error {
            color: var(--danger);
        }

        .selective-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 270px;
            gap: 22px;
            align-items: start;
        }

        .selective-search {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .selective-search input {
            flex: 1;
            height: 42px;
            padding: 0 14px;
            border: 1px solid var(--field-line);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.72);
            color: var(--text);
            outline: none;
        }

        .selective-search .btn {
            min-width: 104px;
            height: 42px;
        }

        .selected-panel {
            border: 1px solid var(--field-line);
            border-radius: 8px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.45);
        }

        .selected-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border-bottom: 1px solid var(--field-line);
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--heading);
        }

        .clear-selection {
            border: 1px solid rgba(208, 106, 101, 0.45);
            border-radius: 3px;
            background: transparent;
            color: var(--danger);
            cursor: pointer;
            font-size: 0.65rem;
            padding: 3px 6px;
        }

        .student-result {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--field-line);
        }

        .student-result:last-child {
            border-bottom: 0;
        }

        .student-avatar {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--primary-soft);
            color: var(--heading);
            font-size: 0.7rem;
            font-weight: 700;
        }

        .student-result strong {
            display: block;
            font-size: 0.76rem;
            color: var(--heading);
        }

        .student-result small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 0.65rem;
        }

        .student-remove {
            margin-left: auto;
            border: 1px solid rgba(208, 106, 101, 0.45);
            border-radius: 3px;
            background: transparent;
            color: var(--danger);
            cursor: pointer;
            font-size: 0.72rem;
            line-height: 1;
            padding: 2px 5px;
        }

        .selection-actions {
            display: flex;
            gap: 8px;
            padding: 10px 12px;
            border-top: 1px solid var(--field-line);
        }

        .selection-actions .btn {
            flex: 1;
            width: auto;
            min-width: 0;
            height: 38px;
            padding: 0 8px;
            font-size: 0.84rem;
            white-space: nowrap;
        }

        .empty-results {
            padding: 20px 14px;
            color: var(--muted);
            font-size: 0.76rem;
            text-align: center;
        }

        .report-toolbar {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 18px;
            border: 1px solid var(--field-line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.45);
        }

        .report-toolbar label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            color: var(--muted-strong);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .report-toolbar select,
        .report-toolbar input {
            width: 100%;
            height: 40px;
            padding: 0 10px;
            border: 1px solid var(--field-line);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.72);
            color: var(--text);
        }

        .report-toolbar .btn {
            align-self: end;
            min-width: 0;
            height: 40px;
        }

        .report-summary {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .report-stat {
            flex: 1 1 160px;
            padding: 14px 16px;
            border: 1px solid var(--field-line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.45);
        }

        .report-stat strong {
            display: block;
            font-size: 1.35rem;
            color: var(--heading);
        }

        .report-stat span {
            color: var(--muted);
            font-size: 0.78rem;
        }

        .report-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--field-line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.45);
        }

        .report-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.8rem;
        }

        .report-table th,
        .report-table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--field-line);
            white-space: nowrap;
        }

        .report-table th {
            color: var(--muted-strong);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .report-table tr:last-child td {
            border-bottom: 0;
        }

        .report-status {
            font-weight: 700;
            text-transform: capitalize;
        }

        .report-status.completed {
            color: var(--success);
        }

        .report-status.failed {
            color: var(--danger);
        }

        .report-link {
            color: var(--muted-strong);
            font-weight: 600;
        }

        @media (max-width: 800px) {
            .selective-layout {
                grid-template-columns: 1fr;
            }

            .report-toolbar {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 980px) {
            .app-shell {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: 0;
                border-bottom: 1px solid var(--border-soft);
            }

            .content {
                padding: 20px 20px 28px;
            }

            .topbar {
                flex-wrap: wrap;
            }

        }

        @media (max-width: 720px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .action-row {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }

            .searchbar {
                min-width: 100%;
            }

            .user-panel {
                width: 100%;
                justify-content: flex-end;
            }

            .report-toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar" aria-label="Sidebar navigation">
            <div class="brand-row">
                <img
                    src="../assets/images/university-logo.png"
                    class="crest"
                    alt="University Logo"
                >
                <div class="brand-copy">
                    <strong>Covenant</strong>
                    <span>University</span>
                </div>
            </div>

            <div class="profile-box">
                <div class="profile-meta">
                    <div class="avatar" aria-label="User avatar">MA</div>
                    <div>
                        <div class="profile-name"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav" aria-label="Main navigation">
                <?php foreach ($menuGroups as $group): ?>
                    <div class="nav-item <?php echo !empty($group['expanded']) ? 'open' : ''; ?> <?php echo !empty($group['active']) ? 'active' : ''; ?>">
                        <?php if (!empty($group['href'])): ?>
                            <a class="nav-label" href="<?php echo htmlspecialchars($group['href']); ?>" aria-label="<?php echo htmlspecialchars($group['label']); ?>" data-nav-toggle>
                                <span><?php echo htmlspecialchars($group['label']); ?></span>
                                <span class="caret">▾</span>
                            </a>
                        <?php else: ?>
                            <div class="nav-label" tabindex="0" aria-label="<?php echo htmlspecialchars($group['label']); ?>" data-nav-toggle>
                                <span><?php echo htmlspecialchars($group['label']); ?></span>
                                <span class="caret">▾</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($group['items'])): ?>
                            <div class="nav-submenu">
                                <?php foreach ($group['items'] as $item): ?>
                                    <a class="nav-subitem <?php echo !empty($item['active']) ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($item['href'] ?? '#'); ?>"><?php echo htmlspecialchars($item['label']); ?></a>
                                    <?php if (!empty($item['children'])): ?>
                                        <?php foreach ($item['children'] as $child): ?>
                                            <a class="nav-subitem <?php echo !empty($child['active']) ? 'active' : ''; ?>" href="#"><?php echo htmlspecialchars($child['label']); ?></a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="content">
            <header class="topbar" aria-label="Primary header">
                
                <div class="user-panel" aria-label="User profile">
                    <div class="user-name">
                        <strong><?php echo htmlspecialchars($currentUser['name']); ?></strong>
                        <small><?php echo htmlspecialchars($currentUser['role']); ?></small>
                    </div>
                    <div class="user-icon" aria-hidden="true">◉</div>
                </div>
            </header>

            <?php if ($batchPrintMessage !== ''): ?>
                <script>window.alert(<?php echo json_encode($batchPrintMessage); ?>);</script>
            <?php endif; ?>
            <?php if ($batchPrintError !== ''): ?>
                <div class="status-message error"><?php echo htmlspecialchars($batchPrintError); ?></div>
            <?php endif; ?>

            <?php if ($isAwaitingPrint): ?>
                <section class="page" aria-labelledby="page-title">
                    <div class="crumbs" aria-label="Breadcrumb">
                        <span>Home</span>
                        <span class="sep">›</span>
                        <span>Biometric</span>
                        <span class="sep">›</span>
                        <span>Awaiting Printing</span>
                    </div>

                    <h1 id="page-title">Awaiting Printing</h1>
                    <p class="subtitle">Paid ID-card applications waiting to be physically printed.</p>

                    <form class="report-toolbar" method="get" action="dashboard.php">
                        <input type="hidden" name="section" value="awaiting-print">
                        <label>Search queue
                            <input type="search" name="awaiting_print_search" value="<?php echo htmlspecialchars($awaitingPrintSearch); ?>" placeholder="Name, matric number, reference, or programme">
                        </label>
                        <button class="btn primary" type="submit">Search</button>
                    </form>

                    <?php if ($awaitingPrintMessage !== ''): ?>
                        <div class="status-message"><?php echo htmlspecialchars($awaitingPrintMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($awaitingPrintError !== ''): ?>
                        <div class="status-message error"><?php echo htmlspecialchars($awaitingPrintError); ?></div>
                    <?php endif; ?>

                    <?php if (empty($awaitingPrintApplications)): ?>
                        <div class="preview-box">
                            <div class="preview-message">No paid applications are awaiting printing.</div>
                        </div>
                    <?php else: ?>
                        <form id="awaitingPrintForm" method="post" action="dashboard.php?section=awaiting-print<?php echo $awaitingPrintSearch !== '' ? '&amp;awaiting_print_search=' . rawurlencode($awaitingPrintSearch) : ''; ?>">
                            <div class="report-table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Reference</th>
                                            <th>Student</th>
                                            <th>Programme / Department</th>
                                            <th>Application type</th>
                                            <th>Submitted</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($awaitingPrintApplications as $application): ?>
                                        <?php $canGenerateCard = !empty($application['student_id']) && $application['student_status'] === 'active'; ?>
                                        <tr>
                                            <td><input type="checkbox" name="reference_numbers[]" value="<?php echo htmlspecialchars($application['referencenumber']); ?>" data-student-id="<?php echo (int) $application['student_id']; ?>" <?php echo $canGenerateCard ? '' : 'disabled'; ?> aria-label="Select <?php echo htmlspecialchars($application['referencenumber']); ?>"></td>
                                            <td><strong><?php echo htmlspecialchars($application['referencenumber']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($application['applicant_name'] ?: $application['matricnumber']); ?><br><small><?php echo htmlspecialchars($application['matricnumber']); ?></small></td>
                                            <td><?php echo htmlspecialchars($application['programme'] ?: ($application['department'] ?: 'Not available')); ?><?php if (!$canGenerateCard): ?><br><small>Matching active student record required for batch generation.</small><?php endif; ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst($application['applicationtype'])); ?></td>
                                            <td><?php echo htmlspecialchars((new DateTime($application['createdat']))->format('d/m/Y H:i')); ?></td>
                                            <td class="report-status">Awaiting print</td>
                                            <td hidden>
                                                    <span aria-hidden="true"> · </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="action-row">
                                <button class="btn primary" type="submit" name="preview_action" value="preview-cards">Preview Cards</button>
                                <button class="btn secondary confirm-batch-print" type="button" disabled>Confirm print</button>
                            </div>
                        </form>
                        <div class="preview-box" aria-live="polite">
                            <div class="preview-inner" id="awaitingPrintPreviewState">
                                <div class="placeholder-icon" aria-hidden="true"></div>
                                <div class="preview-message">Select eligible applications, then choose Preview Cards to generate their ID-card batch.</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            <?php elseif ($isSelectivePrinting): ?>
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
                        <form class="generator-panel" id="searchForm" method="get">
                            <input type="hidden" name="section" value="selective-printing">
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
            <?php elseif ($isPermanentId || $isTemporaryId): ?>
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

                    <form id="collegeForm" class="generator-panel" method="get" action="dashboard.php">
                        <input type="hidden" name="section" value="<?php echo $isTemporaryId ? 'temporary-id' : 'permanent-id'; ?>">
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
            <?php elseif ($isReport): ?>
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

                    <form class="report-toolbar" method="get" action="dashboard.php">
                        <input type="hidden" name="section" value="reports">
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
                                            <td class="report-status <?php echo $row['print_status'] === 'printed' ? 'completed' : ''; ?>"><?php echo htmlspecialchars(str_replace('_', ' ', $row['print_status'])); ?></td>
                                            <td><?php if (is_file($row['pdf_path'])): ?><a class="report-link" href="../output/<?php echo rawurlencode(basename($row['pdf_path'])); ?>" target="_blank" rel="noopener">Open PDF</a><?php else: ?>Unavailable<?php endif; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script>
        const batchConfirmationAction = <?php echo json_encode('dashboard.php?section=' . ($section !== '' ? $section : 'permanent-id')); ?>;
        let pendingBatchPrint = null;

        function setPendingBatchPrint(batchId, referenceNumbers) {
            if (!batchId) {
                return;
            }

            pendingBatchPrint = {
                batchId: batchId,
                referenceNumbers: referenceNumbers || []
            };
            document.querySelectorAll('.confirm-batch-print').forEach(function(button) {
                button.disabled = false;
            });
        }

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.confirm-batch-print');
            if (!button || !pendingBatchPrint) {
                return;
            }

            if (!window.confirm('Are you sure you have physically printed these cards?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'post';
            form.action = batchConfirmationAction;

            [['action', 'confirm-batch-printed'], ['batch_id', String(pendingBatchPrint.batchId)]].forEach(function(field) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = field[0];
                input.value = field[1];
                form.appendChild(input);
            });

            pendingBatchPrint.referenceNumbers.forEach(function(referenceNumber) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'reference_numbers[]';
                input.value = referenceNumber;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });

        document.querySelectorAll('[data-nav-toggle]').forEach(function(toggle) {
            function toggleNavigation(event) {
                if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();
                toggle.closest('.nav-item').classList.toggle('open');
            }

            toggle.addEventListener('click', toggleNavigation);
            toggle.addEventListener('keydown', toggleNavigation);
        });

        (function() {
            const selectionForm = document.getElementById('selectionForm');
            if (!selectionForm) {
                return;
            }

            selectionForm.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.student-remove');
                if (!removeButton) {
                    return;
                }

                const row = removeButton.closest('[data-student-row]');
                if (row) {
                    row.remove();
                }

                const heading = selectionForm.querySelector('.selected-heading');
                const count = selectionForm.querySelectorAll('[data-student-row]').length;
                selectionForm.querySelector('#selectedCount').textContent = count;
                const generateButton = selectionForm.querySelector('[type="submit"]');
                if (generateButton) {
                    generateButton.disabled = count === 0;
                }
            });

            document.getElementById('clearSelection').addEventListener('click', function() {
                selectionForm.querySelectorAll('[data-student-row]').forEach(function(row) {
                    row.remove();
                });
                selectionForm.querySelector('#selectedCount').textContent = '0';
                const generateButton = selectionForm.querySelector('[type="submit"]');
                if (generateButton) {
                    generateButton.disabled = true;
                }
            });

            document.getElementById('searchForm').addEventListener('submit', function() {
                this.querySelectorAll('input[name="selected_ids[]"]').forEach(function(input) {
                    input.remove();
                });
                selectionForm.querySelectorAll('input[name="student_ids[]"]').forEach(function(input) {
                    const selectedInput = document.createElement('input');
                    selectedInput.type = 'hidden';
                    selectedInput.name = 'selected_ids[]';
                    selectedInput.value = input.value;
                    document.getElementById('searchForm').appendChild(selectedInput);
                });
            });

            selectionForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const previewState = document.getElementById('selectivePreviewState');
                const generateButton = selectionForm.querySelector('[type="submit"]');
                generateButton.disabled = true;
                previewState.innerHTML = '<div class="preview-message">Generating selected ID cards...</div>';

                fetch(selectionForm.action, {
                        method: 'POST',
                        body: new FormData(selectionForm)
                    })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Unable to generate the selected cards.');
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        const viewerUrl = data.pdf_url + '#toolbar=1&navpanes=0&scrollbar=1';
                        previewState.innerHTML = '<iframe class="selective-preview-frame" title="Selected ID card PDF preview" src="' + viewerUrl + '"></iframe>' +
                            '<a class="preview-download" href="' + data.pdf_url + '" download="' + data.download_name + '">Download PDF</a>' +
                            '<a class="preview-open" href="' + viewerUrl + '" target="_blank" rel="noopener">Open full viewer</a>';
                        setPendingBatchPrint(data.batch_id, []);
                    })
                    .catch(function(error) {
                        previewState.innerHTML = '<div class="preview-message">' + error.message + '</div>';
                    })
                    .finally(function() {
                        generateButton.disabled = selectionForm.querySelectorAll('[data-student-row]').length === 0;
                    });
            });
        })();

        (function() {
            const awaitingPrintForm = document.getElementById('awaitingPrintForm');
            if (!awaitingPrintForm) {
                return;
            }

            awaitingPrintForm.addEventListener('submit', function(event) {
                const submitter = event.submitter;
                if (!submitter || submitter.value !== 'preview-cards') {
                    return;
                }

                event.preventDefault();
                const selectedInputs = Array.from(awaitingPrintForm.querySelectorAll('input[name="reference_numbers[]"]:checked'));
                const studentIds = selectedInputs
                    .map(function(input) { return input.dataset.studentId; })
                    .filter(function(studentId) { return studentId && studentId !== '0'; });
                const previewState = document.getElementById('awaitingPrintPreviewState');

                if (!studentIds.length) {
                    previewState.innerHTML = '<div class="preview-message">Select at least one application with a matching active student record.</div>';
                    return;
                }

                submitter.disabled = true;
                previewState.innerHTML = '<div class="preview-message">Generating selected ID cards...</div>';

                const requestData = new FormData();
                studentIds.forEach(function(studentId) {
                    requestData.append('student_ids[]', studentId);
                });
                requestData.append('preview', '1');
                requestData.append('generated_by', 'awaiting-print');

                fetch('generate_batch.php', {
                        method: 'POST',
                        body: requestData
                    })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Unable to generate the selected cards.');
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        const viewerUrl = data.pdf_url + '#toolbar=1&navpanes=0&scrollbar=1';
                        previewState.innerHTML = '<iframe class="selective-preview-frame" title="Awaiting-print ID card PDF preview" src="' + viewerUrl + '"></iframe>' +
                            '<a class="preview-download" href="' + data.pdf_url + '" download="' + data.download_name + '">Download PDF</a>' +
                            '<a class="preview-open" href="' + viewerUrl + '" target="_blank" rel="noopener">Open full viewer</a>';
                        setPendingBatchPrint(data.batch_id, selectedInputs.map(function(input) { return input.value; }));
                    })
                    .catch(function(error) {
                        previewState.innerHTML = '<div class="preview-message">' + error.message + '</div>';
                    })
                    .finally(function() {
                        submitter.disabled = false;
                    });
            });
        })();

        (function() {
            const collegePrintForm = document.getElementById('collegePrintForm');
            if (!collegePrintForm) {
                return;
            }

            collegePrintForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const previewState = document.getElementById('previewState');
                const generateButton = collegePrintForm.querySelector('[type="submit"]');
                generateButton.disabled = true;
                previewState.innerHTML = '<div class="preview-message">Generating the selected ID-card batch...</div>';

                fetch(collegePrintForm.action, {
                        method: 'POST',
                        body: new FormData(collegePrintForm)
                    })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Unable to generate the selected cards.');
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        const viewerUrl = data.pdf_url + '#toolbar=1&navpanes=0&scrollbar=1';
                        previewState.innerHTML = '<iframe class="pdf-preview-frame" title="ID card PDF preview" src="' + viewerUrl + '"></iframe>' +
                            '<a class="preview-download" href="' + data.pdf_url + '" download="' + data.download_name + '">Download PDF</a>' +
                            '<a class="preview-open" href="' + viewerUrl + '" target="_blank" rel="noopener">Open full viewer</a>';
                        setPendingBatchPrint(data.batch_id, []);
                    })
                    .catch(function(error) {
                        previewState.innerHTML = '<div class="preview-message">' + error.message + '</div>';
                    })
                    .finally(function() {
                        generateButton.disabled = false;
                    });
            });
        })();
    </script>

</body>

</html>
