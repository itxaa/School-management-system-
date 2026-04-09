<!-- Shared Navbar – include after session_start() + requireLogin() -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background:linear-gradient(135deg,#1a73e8,#6c3fe8)">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="dashboard.php">&#127979; Student System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='register_student.php'?'active':'' ?>" href="register_student.php">Register Student</a></li>
      </ul>
      <span class="navbar-text text-white me-3">&#128100; <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>
