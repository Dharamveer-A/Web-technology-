<?php
/**
 * Web Technologies (U21CS501) - Assignment 02: Question 2
 * View Student Profile & Display Associated Uploaded Files (Read Operation)
 */

require_once 'db.php';

$studentId = $_GET['id'] ?? null;
if (!$studentId || !is_numeric($studentId)) {
    header("Location: index.php?err=" . urlencode("Invalid Student ID"));
    exit;
}

// Fetch student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: index.php?err=" . urlencode("Student record not found."));
    exit;
}

// Fetch all linked documents
$docStmt = $pdo->prepare("SELECT * FROM student_documents WHERE student_id = ? ORDER BY uploaded_at DESC");
$docStmt->execute([$studentId]);
$documents = $docStmt->fetchAll();

// Find profile image if exists
$profileImage = null;
foreach ($documents as $doc) {
    if ($doc['doc_category'] === 'profile_image' && file_exists($doc['file_path'])) {
        $profileImage = $doc['file_path'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Student Details - <?= htmlspecialchars($student['name']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>KPRIET - Admin Portal</h1>
    <nav>
      <a href="index.php">&larr; Back to Directory</a>
      <a href="edit_student.php?id=<?= $student['id'] ?>">Edit Record</a>
    </nav>
  </header>

  <main class="container">
    <div class="top-bar">
      <h2>Student Profile Overview</h2>
      <div>
        <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-warning">Edit Profile</a>
        <a href="delete_student.php?id=<?= $student['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this student and associated documents?');">Delete</a>
      </div>
    </div>

    <!-- Student Personal Details Card -->
    <div class="card">
      <div class="profile-card">
        <div>
          <?php if ($profileImage): ?>
            <img src="<?= htmlspecialchars($profileImage) ?>" alt="Student Photo" class="profile-large">
          <?php else: ?>
            <div class="profile-large" style="background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-size:40px; color:#64748b;">
              <?= strtoupper(substr($student['name'], 0, 1)) ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="profile-details">
          <h3><?= htmlspecialchars($student['name']) ?></h3>
          <p><strong>Roll Number:</strong> <?= htmlspecialchars($student['roll_no']) ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
          <p><strong>Enrolled Course:</strong> <span class="badge badge-course"><?= htmlspecialchars($student['course']) ?></span></p>
          <p><strong>Registered Date:</strong> <?= date('d M Y, h:i A', strtotime($student['created_at'])) ?></p>
        </div>
      </div>
    </div>

    <!-- Uploaded Documents & Metadata Table -->
    <div class="card">
      <h3>Uploaded Documents & Metadata (<?= count($documents) ?>)</h3>
      <?php if (empty($documents)): ?>
        <p>No documents uploaded for this student.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Category</th>
              <th>Original File Name</th>
              <th>MIME Type</th>
              <th>Size</th>
              <th>Uploaded At</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documents as $doc): ?>
              <tr>
                <td><span class="badge badge-doc"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $doc['doc_category']))) ?></span></td>
                <td><?= htmlspecialchars($doc['file_name']) ?></td>
                <td><code><?= htmlspecialchars($doc['file_type']) ?></code></td>
                <td><?= round($doc['file_size'] / 1024, 1) ?> KB</td>
                <td><?= date('d-m-Y H:i', strtotime($doc['uploaded_at'])) ?></td>
                <td>
                  <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="btn btn-primary" download>Download / View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    <p>&copy; 2026-2027 KPR Institute of Engineering and Technology | Web Technology (U21CS501)</p>
  </footer>
</body>
</html>
