<?php
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
try {
    if (!$officer) throw new DomainException('Sign in required.');
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
