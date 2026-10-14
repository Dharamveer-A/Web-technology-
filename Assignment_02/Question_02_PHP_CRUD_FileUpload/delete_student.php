<?php
/**
 * Web Technologies (U21CS501) - Assignment 02: Question 2
 * Delete Student Record & Associated Files from Server (Delete Operation)
 */

require_once 'db.php';

$studentId = $_GET['id'] ?? null;
if (!$studentId || !is_numeric($studentId)) {
    header("Location: index.php?err=" . urlencode("Invalid Student ID"));
    exit;
}

try {
    // 1. Fetch all file paths linked to this student to clean up from filesystem
    $fileStmt = $pdo->prepare("SELECT file_path FROM student_documents WHERE student_id = ?");
    $fileStmt->execute([$studentId]);
    $files = $fileStmt->fetchAll();

    foreach ($files as $file) {
        $fullPath = __DIR__ . '/' . $file['file_path'];
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    // 2. Delete the student record (Foreign key ON DELETE CASCADE clears student_documents rows)
    $delStmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $delStmt->execute([$studentId]);

    header("Location: index.php?msg=" . urlencode("Student record and associated files successfully deleted."));
    exit;
} catch (PDOException $e) {
    header("Location: index.php?err=" . urlencode("Failed to delete student: " . $e->getMessage()));
    exit;
}
?>
