<?php
session_start();
require_once 'config.php';
requireLogin();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $full_name  = trim($_POST['full_name']  ?? '');
    $email      = trim($_POST['email']      ?? '');
    $class      = trim($_POST['class']      ?? '');
    $dob        = $_POST['date_of_birth']   ?? '';
    $phone      = trim($_POST['phone']      ?? '');
    $address    = trim($_POST['address']    ?? '');

    if (empty($student_id)) $errors[] = 'Student ID is required.';
    if (empty($full_name))  $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($class))      $errors[] = 'Class/Program is required.';

    if (empty($errors)) {
        // Check duplicate student_id or email
        $chk = $conn->prepare("SELECT id FROM students WHERE student_id=? OR email=?");
        $chk->bind_param('ss', $student_id, $email);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $errors[] = 'A student with that Student ID or Email already exists.';
        }
        $chk->close();
    }

    if (empty($errors)) {
        $dob_val = $dob ?: null;
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare(
            "INSERT INTO students (student_id, full_name, email, class, date_of_birth, phone, address, registered_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('sssssssi', $student_id, $full_name, $email, $class, $dob_val, $phone, $address, $admin_id);
        if ($stmt->execute()) {
            header('Location: dashboard.php?msg=registered');
            exit;
        } else {
            $errors[] = 'Failed to register student. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register Student</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>body{background:#f0f2f5;}</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4" style="max-width:700px">
  <div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white fw-bold fs-5">Register New Student</div>
    <div class="card-body p-4">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2">
          <?php foreach ($errors as $e) echo '<div>&#x26A0; ' . htmlspecialchars($e) . '</div>'; ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label fw-semibold">Student ID <span class="text-danger">*</span></label>
            <input type="text" name="student_id" class="form-control"
                   value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
                   placeholder="e.g. BC220407460" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                   placeholder="John Doe" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="student@example.com" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Class / Program <span class="text-danger">*</span></label>
            <input type="text" name="class" class="form-control"
                   value="<?= htmlspecialchars($_POST['class'] ?? '') ?>"
                   placeholder="e.g. BSCS – Semester 4" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control"
                   value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Phone</label>
            <input type="text" name="phone" class="form-control"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                   placeholder="+92 300 0000000">
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Address</label>
            <textarea name="address" class="form-control" rows="2"
                      placeholder="Student home address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
          </div>

          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4 fw-semibold">Register Student</button>
            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
