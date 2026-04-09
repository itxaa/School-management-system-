<?php
session_start();
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Both fields are required.';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM admins WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($id, $full_name, $hashed);
        $stmt->fetch();
        $stmt->close();

        if ($id && password_verify($password, $hashed)) {
            $_SESSION['admin_id']   = $id;
            $_SESSION['admin_name'] = $full_name;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background: #f0f2f5; }
    .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .brand-header { background: linear-gradient(135deg, #1a73e8, #6c3fe8); color: #fff; border-radius: 12px 12px 0 0; padding: 28px; text-align: center; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
<div class="container" style="max-width:420px">
  <div class="card">
    <div class="brand-header">
      <h4 class="mb-0 fw-bold">Admin Portal</h4>
      <small class="opacity-75">Sign in to your account</small>
    </div>
    <div class="card-body p-4">

      <?php if ($error): ?>
        <div class="alert alert-danger py-2">&#x26A0; <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label fw-semibold">Email Address</label>
          <input type="email" name="email" class="form-control"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="admin@example.com" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Your password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Login</button>
      </form>

      <hr class="my-3">
      <p class="text-center mb-0 small">Don't have an account? <a href="signup.php">Sign up</a></p>
    </div>
  </div>
</div>
</body>
</html>
