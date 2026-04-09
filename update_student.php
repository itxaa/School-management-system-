<?php
session_start();
require_once 'config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: dashboard.php'); exit; }

// ── Fetch existing record ────────────────────────────────────
$fetch = $conn->prepare("SELECT * FROM students WHERE id = ?");
$fetch->bind_param('i', $id);
$fetch->execute();
$result = $fetch->get_result();
$student = $result->fetch_assoc();
$fetch->close();

if (!$student) { header('Location: dashboard.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name']     ?? '');
    $email      = trim($_POST['email']         ?? '');
    $class      = trim($_POST['class']         ?? '');
    $dob        = $_POST['date_of_birth']      ?? '';
    $phone      = trim($_POST['phone']         ?? '');
    $address    = trim($_POST['address']       ?? '');

    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($class))     $errors[] = 'Class/Program is required.';

    if (empty($errors)) {
        // Ensure no other student owns that email
        $chk = $conn->prepare("SELECT id FROM students WHERE email=? AND id!=?");
        $chk->bind_param('si', $email, $id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $errors[] = 'Another student already uses that email address.';
        }
        $chk->close();
    }

    if (empty($errors)) {
        $dob_val = $dob ?: null;
        $upd = $conn->prepare(
            "UPDATE students SET full_name=?, email=?, class=?, date_of_birth=?, phone=?, address=?
             WHERE id=?"
        );
        $upd->bind_param('ssssssi', $full_name, $email, $class, $dob_val, $phone, $address, $id);
        if ($upd->execute()) {
            header('Location: dashboard.php?msg=updated');
            exit;
        } else {
            $errors[] = 'Update failed. Please try again.';
        }
        $upd->close();
    }

    // Merge posted values back for re-display
    $student['full_name']     = $full_name;
    $student['email']         = $email;
    $student['class']         = $class;
    $student['date_of_birth'] = $dob;
    $student['phone']         = $phone;
    $student['address']       = $address;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Update Student</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>body{background:#f0f2f5;}</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4" style="max-width:700px">
  <div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white fw-bold fs-5">
      Update Student &mdash; <span class="text-muted fs-6"><?= htmlspecialchars($student['student_id']) ?></span>
    </div>
    <div class="card-body p-4">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2">
          <?php foreach ($errors as $e) echo '<div>&#x26A0; ' . htmlspecialchars($e) . '</div>'; ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label fw-semibold">Student ID</label>
            <input type="text" class="form-control bg-light"
                   value="<?= htmlspecialchars($student['student_id']) ?>" disabled>
            <small class="text-muted">Student ID cannot be changed.</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control"
                   value="<?= htmlspecialchars($student['full_name']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($student['email']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Class / Program <span class="text-danger">*</span></label>
            <input type="text" name="class" class="form-control"
                   value="<?= htmlspecialchars($student['class']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control"
                   value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Phone</label>
            <input type="text" name="phone" class="form-control"
                   value="<?= htmlspecialchars($student['phone'] ?? '') ?>">
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Address</label>
            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
          </div>

          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-success px-4 fw-semibold">Save Changes</button>
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
