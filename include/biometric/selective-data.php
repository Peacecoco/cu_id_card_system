<?php
$studentSearch = trim((string) (filter_input(INPUT_GET, 'student_search', FILTER_UNSAFE_RAW) ?: ''));
$selectedIds = isset($_GET['selected_ids']) && is_array($_GET['selected_ids']) ? $_GET['selected_ids'] : [];
$searchResults = [];
$selectedStudents = [];
$searchError = '';
try {
    if (!$officer) throw new DomainException('Sign in required.');
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
