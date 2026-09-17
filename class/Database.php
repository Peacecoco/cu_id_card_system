<?php
// ============================================================
// class/Database.php - thin PDO wrapper for the ID card system
// ============================================================

class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec("SET time_zone = '+01:00'");
    }

    public function connection(): PDO { return $this->pdo; }

    public function getBatch(int $id): ?array
    {
        $q=$this->pdo->prepare('SELECT * FROM id_card_batches WHERE id=?');
        $q->execute([$id]);
        return $q->fetch() ?: null;
    }

    public function getCollege(int $collegeId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM colleges WHERE id = ?');
        $stmt->execute([$collegeId]);
        $college = $stmt->fetch();

        if (!$college) {
            throw new RuntimeException("College with id {$collegeId} not found.");
        }

        return $college;
    }

    public function getAllColleges(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM colleges ORDER BY name');
        return $stmt->fetchAll();
    }

    public function getProgrammesByCollege(int $collegeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT p.id, p.name
             FROM programmes p
             INNER JOIN departments d ON d.id = p.department_id
             WHERE d.college_id = ?
             ORDER BY p.name'
        );
        $stmt->execute([$collegeId]);
        return $stmt->fetchAll();
    }

    public function getStudentsByCollege(int $collegeId, ?int $level = null, ?int $programmeId = null): array
    {
        $query = 'SELECT * FROM students WHERE college_id = ? AND status = "active"';
        $parameters = [$collegeId];
        if ($programmeId !== null) {
            $query .= ' AND programme_id = ?';
            $parameters[] = $programmeId;
        }
        if ($level !== null) {
            $query .= ' AND level = ?';
            $parameters[] = $level;
        }
        $query .= ' ORDER BY full_name';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($parameters);
        return $stmt->fetchAll();
    }

    public function searchActiveStudents(string $searchTerm): array
    {
        $term = '%' . $searchTerm . '%';
        $stmt = $this->pdo->prepare(
            'SELECT * FROM students
             WHERE status = "active"
             AND (full_name LIKE ? OR matric_no LIKE ?)
             ORDER BY full_name
             LIMIT 50'
        );
        $stmt->execute([$term, $term]);
        return $stmt->fetchAll();
    }

    public function getActiveStudentsByIds(array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn(int $id): bool => $id > 0)));
        if (empty($studentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT * FROM students WHERE status = 'active' AND id IN ({$placeholders}) ORDER BY full_name"
        );
        $stmt->execute($studentIds);
        return $stmt->fetchAll();
    }

    public function updateStudentProcessedPhoto(int $studentId, string $processedPath): void
    {
        $stmt = $this->pdo->prepare('UPDATE students SET photo_processed_path = ? WHERE id = ?');
        $stmt->execute([$processedPath, $studentId]);
    }

    public function createBatch(int $collegeId, int $studentCount, string $pdfPath, ?string $generatedBy = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO id_card_batches (college_id, generated_by, student_count, pdf_path, status)
             VALUES (?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$collegeId, $generatedBy, $studentCount, $pdfPath]);
        return (int) $this->pdo->lastInsertId();
    }

    public function completeBatch(int $batchId): void
    {
        $stmt = $this->pdo->prepare('UPDATE id_card_batches SET status = "completed" WHERE id = ?');
        $stmt->execute([$batchId]);
    }

    public function markBatchAsPrinted(int $batchId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE id_card_batches
             SET print_status = 'printed', print_method = 'local', printed_at = NOW()
             WHERE id = ? AND status = 'completed' AND print_status = 'awaiting_print'
             AND NOT EXISTS (SELECT 1 FROM id_card_batch_items i WHERE i.batch_id=id_card_batches.id AND i.applicationid IS NOT NULL)"
        );
        $stmt->execute([$batchId]);
        return $stmt->rowCount() === 1;
    }

    /**
     * The application workflow owns the physical-print queue. This mirrors the
     * Full ID Card Module: a paid application awaits printing until an officer
     * explicitly moves it to printed.
     */
    public function getAwaitingPrintApplications(string $searchTerm = ''): array
    {
        $query = "SELECT a.applicationid, a.referencenumber, a.matricnumber,
                         a.applicationtype, a.status, a.photopath, a.createdat,
                         s.id AS student_id, s.status AS student_status,
                         s.full_name AS applicant_name, s.department, s.programme
                  FROM idcardapplications a
                  LEFT JOIN students s ON s.matric_no = a.matricnumber
                  WHERE a.status = 'paid' AND a.paymentstatus IS NULL";
        $parameters = [];

        if ($searchTerm !== '') {
            $query .= ' AND (a.referencenumber LIKE ? OR a.matricnumber LIKE ? OR s.full_name LIKE ? OR s.department LIKE ? OR s.programme LIKE ?)';
            $term = '%' . $searchTerm . '%';
            $parameters = [$term, $term, $term, $term, $term];
        }

        $query .= ' ORDER BY a.createdat DESC, a.applicationid DESC';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($parameters);
        return $stmt->fetchAll();
    }

    public function markApplicationsAsPrinted(array $referenceNumbers, string $printMethod): int
    {
        if (!in_array($printMethod, ['local', 'download'], true)) {
            throw new InvalidArgumentException('Invalid print method.');
        }

        $referenceNumbers = array_values(array_unique(array_filter(
            array_map(static fn($reference): string => trim((string) $reference), $referenceNumbers)
        )));
        if (empty($referenceNumbers)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($referenceNumbers), '?'));
        $stmt = $this->pdo->prepare(
            "UPDATE idcardapplications
             SET status = 'printed', printmethod = ?, printedat = NOW(), updatedat = NOW()
             WHERE status = 'paid' AND paymentstatus IS NULL AND referencenumber IN ({$placeholders})"
        );
        $stmt->execute(array_merge([$printMethod], $referenceNumbers));
        return $stmt->rowCount();
    }

    public function failBatch(int $batchId): void
    {
        $stmt = $this->pdo->prepare('UPDATE id_card_batches SET status = "failed" WHERE id = ?');
        $stmt->execute([$batchId]);
    }

    public function logBatchItem(int $batchId, int $studentId, string $status, ?string $errorMessage = null): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO id_card_batch_items (batch_id, student_id, status, error_message)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$batchId, $studentId, $status, $errorMessage]);
    }

    public function getBatchReports(?int $collegeId = null, ?string $status = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        $conditions = [];
        $parameters = [];

        if ($collegeId) {
            $conditions[] = 'b.college_id = ?';
            $parameters[] = $collegeId;
        }
        if ($status && in_array($status, ['pending', 'completed', 'failed'], true)) {
            $conditions[] = 'b.status = ?';
            $parameters[] = $status;
        }
        if ($fromDate !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $conditions[] = 'b.created_at >= ?';
            $parameters[] = $fromDate . ' 00:00:00';
        }
        if ($toDate !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $conditions[] = 'b.created_at <= ?';
            $parameters[] = $toDate . ' 23:59:59';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $this->pdo->prepare(
            "SELECT b.id, b.generated_by, b.student_count, b.pdf_path, b.status, b.print_status, b.printed_at, b.created_at,
                    c.name AS college_name, c.code AS college_code,
                    SUM(i.status = 'success') AS success_count,
                    SUM(i.status = 'failed') AS failure_count
             FROM id_card_batches b
             LEFT JOIN colleges c ON c.id = b.college_id
             LEFT JOIN id_card_batch_items i ON i.batch_id = b.id
             {$where}
             GROUP BY b.id, b.generated_by, b.student_count, b.pdf_path, b.status, b.print_status, b.printed_at, b.created_at, c.name, c.code
             ORDER BY b.created_at DESC, b.id DESC"
        );
        $stmt->execute($parameters);
        return $stmt->fetchAll();
    }
}
