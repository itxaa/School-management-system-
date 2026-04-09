<?php
session_start();
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (empty($full_name))           $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6)       $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)      $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check duplicate email
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $ins = $conn->prepare("INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $full_name, $email, $hashed);
            if ($ins->execute()) {
                $success = 'Account created! <a href="login.php">Login now &rarr;</a>';
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
            $ins->close();
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
  <title>Admin Signup</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background: #f0f2f5; }
    .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .brand-header { background: linear-gradient(135deg, #1a73e8, #6c3fe8); color: #fff; border-radius: 12px 12px 0 0; padding: 28px; text-align: center; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
<div class="container" style="max-width:460px">
  <div class="card">
    <div class="brand-header">
      <h4 class="mb-0 fw-bold">Admin Registration</h4>
      <small class="opacity-75">Create your admin account</small>
    </div>
    <div class="card-body p-4">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2">
          <?php foreach ($errors as $e) echo '<div>&#x26A0; ' . htmlspecialchars($e) . '</div>'; ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php else: ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label fw-semibold">Full Name</label>
          <input type="text" name="full_name" class="form-control"
                 value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="John Doe" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Email Address</label>
          <input type="email" name="email" class="form-control"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="admin@example.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Create Account</button>
      </form>
      <?php endif; ?>

      <hr class="my-3">
      <p class="text-center mb-0 small">Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
