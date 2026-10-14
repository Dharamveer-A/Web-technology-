<?php
/**
 * Web Technologies (U21CS501) - Assignment 02: Question 2
 * Edit Student Record & Manage Files (Update Operation)
 */

require_once 'db.php';

$studentId = $_GET['id'] ?? null;
if (!$studentId || !is_numeric($studentId)) {
    header("Location: index.php?err=" . urlencode("Invalid Student ID"));
    exit;
}

// Fetch student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: index.php?err=" . urlencode("Student record not found."));
    exit;
}

$errors = [];
$uploadDir = __DIR__ . '/uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    if (empty($name) || empty($roll_no) || empty($email) || empty($course)) {
        $errors[] = "All required fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Check unique roll_no / email excluding current student
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM students WHERE (roll_no = ? OR email = ?) AND id != ?");
        $checkStmt->execute([$roll_no, $email, $studentId]);
        if ($checkStmt->fetch()) {
            $errors[] = "Roll number or email is already registered by another student.";
        }
    }

    // Optional profile image replacement
    if (empty($errors) && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_image'];
        if ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "Profile photo exceeds 2 MB limit.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!in_array($mime, ['image/jpeg', 'image/png'])) {
                $errors[] = "Only JPG and PNG images are allowed for profile photo.";
            } else {
                // Delete previous profile photo from disk and DB
                $oldDocStmt = $pdo->prepare("SELECT id, file_path FROM student_documents WHERE student_id = ? AND doc_category = 'profile_image'");
                $oldDocStmt->execute([$studentId]);
                $oldDoc = $oldDocStmt->fetch();
                if ($oldDoc && file_exists($oldDoc['file_path'])) {
                    @unlink($oldDoc['file_path']);
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $uniqueName = 'profile_' . uniqid() . '_' . time() . '.' . $ext;
                $dest = $uploadDir . $uniqueName;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    if ($oldDoc) {
                        $updateDoc = $pdo->prepare("UPDATE student_documents SET file_name=?, file_path=?, file_type=?, file_size=?, uploaded_at=CURRENT_TIMESTAMP WHERE id=?");
                        $updateDoc->execute([$file['name'], 'uploads/' . $uniqueName, $mime, $file['size'], $oldDoc['id']]);
                    } else {
                        $insertDoc = $pdo->prepare("INSERT INTO student_documents (student_id, file_name, file_path, file_type, file_size, doc_category) VALUES (?, ?, ?, ?, ?, 'profile_image')");
                        $insertDoc->execute([$studentId, $file['name'], 'uploads/' . $uniqueName, $mime, $file['size']]);
                    }
                }
            }
        }
    }

    // Save student details
    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE students SET name = ?, roll_no = ?, email = ?, course = ? WHERE id = ?");
            $updateStmt->execute([$name, $roll_no, $email, $course, $studentId]);
            header("Location: view_student.php?id=$studentId&msg=" . urlencode("Student details updated successfully!"));
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database update error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Student - <?= htmlspecialchars($student['name']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>KPRIET - Admin Portal</h1>
    <nav>
      <a href="index.php">&larr; Back to Directory</a>
      <a href="view_student.php?id=<?= $student['id'] ?>">View Profile</a>
    </nav>
  </header>

  <main class="container">
    <div class="card">
      <h3>Edit Student Information</h3>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="edit_student.php?id=<?= $student['id'] ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="name">Student Full Name *</label>
          <input type="text" name="name" id="name" required value="<?= htmlspecialchars($student['name']) ?>">
        </div>

        <div class="form-group">
          <label for="roll_no">Roll Number *</label>
          <input type="text" name="roll_no" id="roll_no" required value="<?= htmlspecialchars($student['roll_no']) ?>">
        </div>

        <div class="form-group">
          <label for="email">Institutional Email *</label>
          <input type="email" name="email" id="email" required value="<?= htmlspecialchars($student['email']) ?>">
        </div>

        <div class="form-group">
          <label for="course">Course / Department *</label>
          <select name="course" id="course" required>
            <?php
            $courses = [
                "B.E. Computer Science and Engineering",
                "B.Tech Artificial Intelligence and Data Science",
                "B.E. Electronics and Communication Engineering",
                "B.Tech Information Technology",
                "B.E. Mechanical Engineering"
            ];
            foreach ($courses as $c):
            ?>
              <option value="<?= $c ?>" <?= ($student['course'] === $c) ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="profile_image">Update Profile Photograph (Optional, JPG/PNG)</label>
          <input type="file" name="profile_image" id="profile_image" accept="image/jpeg,image/png">
        </div>

        <div style="margin-top: 25px;">
          <button type="submit" class="btn btn-warning">Update Changes</button>
          <a href="view_student.php?id=<?= $student['id'] ?>" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2026-2027 KPR Institute of Engineering and Technology | Web Technology (U21CS501)</p>
  </footer>
</body>
</html>
