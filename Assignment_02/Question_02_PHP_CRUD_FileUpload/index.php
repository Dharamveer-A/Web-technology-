<?php
/**
 * Web Technologies (U21CS501) - Assignment 02: Question 2
 * Admin Dashboard - View All Student Records (Read Operation)
 */

require_once 'db.php';

// Fetch all students along with their profile image if uploaded
$sql = "SELECT s.*, d.file_path AS profile_path 
        FROM students s 
        LEFT JOIN student_documents d 
               ON s.id = d.student_id AND d.doc_category = 'profile_image'
        ORDER BY s.id DESC";
$stmt = $pdo->query($sql);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Student Records</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>KPRIET - Admin Portal</h1>
    <nav>
      <a href="index.php">Students Directory</a>
      <a href="add_student.php">+ Add New Student</a>
    </nav>
  </header>

  <main class="container">
    <div class="top-bar">
      <h2>Student Records Management</h2>
      <a href="add_student.php" class="btn btn-success">+ Register New Student</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
      <div class="alert alert-error"><?= htmlspecialchars($_GET['err']) ?></div>
    <?php endif; ?>

    <div class="card">
      <h3>Enrolled Students (<?= count($students) ?>)</h3>
      <?php if (empty($students)): ?>
        <p>No student records found. Click above to add a new record.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Photo</th>
              <th>Roll No</th>
              <th>Full Name</th>
              <th>Course / Branch</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $student): ?>
              <tr>
                <td>
                  <?php if (!empty($student['profile_path']) && file_exists($student['profile_path'])): ?>
                    <img src="<?= htmlspecialchars($student['profile_path']) ?>" alt="Photo" class="thumbnail">
                  <?php else: ?>
                    <div class="thumbnail-placeholder"><?= strtoupper(substr($student['name'], 0, 1)) ?></div>
                  <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($student['roll_no']) ?></strong></td>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td><span class="badge badge-course"><?= htmlspecialchars($student['course']) ?></span></td>
                <td><?= htmlspecialchars($student['email']) ?></td>
                <td>
                  <div class="btn-group">
                    <a href="view_student.php?id=<?= $student['id'] ?>" class="btn btn-primary">View</a>
                    <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-warning">Edit</a>
                    <a href="delete_student.php?id=<?= $student['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete student <?= htmlspecialchars($student['name']) ?> and all uploaded files?');">Delete</a>
                  </div>
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
