<?php
/**
 * Web Technologies (U21CS501) - Assignment 02: Question 2
 * Add New Student Record with Secure File Uploads (Create Operation)
 */

require_once 'db.php';

$errors = [];
$success = "";

// Ensure upload directory exists
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    // 1. Basic validation
    if (empty($name) || empty($roll_no) || empty($email) || empty($course)) {
        $errors[] = "All required text fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Check if roll number or email already exists
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM students WHERE roll_no = ? OR email = ?");
        $checkStmt->execute([$roll_no, $email]);
        if ($checkStmt->fetch()) {
            $errors[] = "A student with this Roll Number or Email already exists.";
        }
    }

    // File validation helper
    function processUpload($fileKey, $category, $allowedTypes, $maxBytes, $uploadDir) {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // File is optional or not provided
        }

        $file = $_FILES[$fileKey];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed with error code: " . $file['error']);
        }

        // Validate file size
        if ($file['size'] > $maxBytes) {
            throw new Exception("File for $category exceeds maximum allowed size of " . round($maxBytes / (1024 * 1024)) . " MB.");
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedTypes)) {
            throw new Exception("Invalid file type ($mime) for $category. Allowed: " . implode(', ', $allowedTypes));
        }

        // Generate unique filename to prevent overwrite and directory traversal attacks
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueName = $category . '_' . uniqid() . '_' . time() . '.' . $ext;
        $destination = $uploadDir . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Failed to move uploaded file to destination directory.");
        }

        // Return relative path for web and DB storage
        return [
            'file_name' => $file['name'],
            'file_path' => 'uploads/' . $uniqueName,
            'file_type' => $mime,
            'file_size' => $file['size'],
            'doc_category' => $category
        ];
    }

    $uploadedFiles = [];
    if (empty($errors)) {
        try {
            // Process Profile Photo (JPG/PNG, Max 2MB)
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $photo = processUpload('profile_image', 'profile_image', ['image/jpeg', 'image/png'], 2 * 1024 * 1024, $uploadDir);
                if ($photo) $uploadedFiles[] = $photo;
            }

            // Process Document / Certificate (PDF/JPG/PNG, Max 5MB)
            if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
                $doc = processUpload('document', 'certificate', ['application/pdf', 'image/jpeg', 'image/png'], 5 * 1024 * 1024, $uploadDir);
                if ($doc) $uploadedFiles[] = $doc;
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Save to database using transaction
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert student record
            $insertStudent = $pdo->prepare("INSERT INTO students (name, roll_no, email, course) VALUES (?, ?, ?, ?)");
            $insertStudent->execute([$name, $roll_no, $email, $course]);
            $studentId = $pdo->lastInsertId();

            // Insert document metadata
            $insertDoc = $pdo->prepare("INSERT INTO student_documents (student_id, file_name, file_path, file_type, file_size, doc_category) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($uploadedFiles as $docMeta) {
                $insertDoc->execute([
                    $studentId,
                    $docMeta['file_name'],
                    $docMeta['file_path'],
                    $docMeta['file_type'],
                    $docMeta['file_size'],
                    $docMeta['doc_category']
                ]);
            }

            $pdo->commit();
            header("Location: index.php?msg=" . urlencode("Student registered successfully with documents!"));
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Student Record - Admin Portal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>KPRIET - Admin Portal</h1>
    <nav>
      <a href="index.php">&larr; Back to Directory</a>
    </nav>
  </header>

  <main class="container">
    <div class="card">
      <h3>Register New Student & Upload Credentials</h3>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="add_student.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="name">Student Full Name *</label>
          <input type="text" name="name" id="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. Dharamveer A">
        </div>

        <div class="form-group">
          <label for="roll_no">Roll Number *</label>
          <input type="text" name="roll_no" id="roll_no" required value="<?= htmlspecialchars($_POST['roll_no'] ?? '') ?>" placeholder="e.g. 24CS048">
        </div>

        <div class="form-group">
          <label for="email">Institutional Email Address *</label>
          <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="e.g. dharamveer@kpriet.ac.in">
        </div>

        <div class="form-group">
          <label for="course">Course / Department *</label>
          <select name="course" id="course" required>
            <option value="">-- Select Degree & Branch --</option>
            <option value="B.E. Computer Science and Engineering">B.E. Computer Science and Engineering</option>
            <option value="B.Tech Artificial Intelligence and Data Science">B.Tech Artificial Intelligence and Data Science</option>
            <option value="B.E. Electronics and Communication Engineering">B.E. Electronics and Communication Engineering</option>
            <option value="B.Tech Information Technology">B.Tech Information Technology</option>
          </select>
        </div>

        <div class="form-group">
          <label for="profile_image">Profile Photograph (JPG/PNG, Max 2MB)</label>
          <input type="file" name="profile_image" id="profile_image" accept="image/jpeg,image/png">
        </div>

        <div class="form-group">
          <label for="document">ID Proof / Certificate (PDF/JPG/PNG, Max 5MB)</label>
          <input type="file" name="document" id="document" accept="application/pdf,image/jpeg,image/png">
        </div>

        <div style="margin-top: 25px;">
          <button type="submit" class="btn btn-success">Save Student Record</button>
          <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2026-2027 KPR Institute of Engineering and Technology | Web Technology (U21CS501)</p>
  </footer>
</body>
</html>
