<?php
session_start();
require_once 'config.php';
requireLogin();

// ── Fetch counts ────────────────────────────────────────────
$total_students = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$total_admins   = $conn->query("SELECT COUNT(*) FROM admins")->fetch_row()[0];

// ── Fetch all students for the table ────────────────────────
$students = $conn->query(
    "SELECT s.*, a.full_name AS admin_name
     FROM students s
     JOIN admins a ON s.registered_by = a.id
     ORDER BY s.created_at DESC"
);

// ── Handle delete ────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param('i', $del_id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php?msg=deleted');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background: #f0f2f5; }
    .stat-card { border: none; border-radius: 10px; color: #fff; padding: 22px; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container-fluid py-4 px-4">
  <h4 class="mb-4 fw-bold">Dashboard</h4>

  <?php if (isset($_GET['msg'])): ?>
    <?php $msgs = ['registered'=>['success','Student registered successfully!'],
                   'updated'   =>['success','Student record updated!'],
                   'deleted'   =>['warning','Student record deleted.']]; ?>
    <?php [$type,$text] = $msgs[$_GET['msg']] ?? ['info','Done.']; ?>
    <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
      <?= $text ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Stats row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#1a73e8,#6c3fe8)">
        <div class="fs-2 fw-bold"><?= $total_students ?></div>
        <div class="opacity-75">Total Students</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#f093fb,#f5576c)">
        <div class="fs-2 fw-bold"><?= $total_admins ?></div>
        <div class="opacity-75">Admin Accounts</div>
      </div>
    </div>
    <div class="col-md-6 d-flex align-items-center">
      <a href="register_student.php" class="btn btn-primary fw-semibold px-4">
        + Register New Student
      </a>
    </div>
  </div>

  <!-- Students table -->
  <div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white fw-semibold">All Students</div>
    <div class="card-body p-0">
      <?php if ($students->num_rows === 0): ?>
        <p class="p-4 text-muted mb-0">No students registered yet.</p>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Student ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Class</th>
              <th>Phone</th>
              <th>Registered By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; while ($s = $students->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($s['student_id']) ?></span></td>
              <td><?= htmlspecialchars($s['full_name']) ?></td>
              <td><?= htmlspecialchars($s['email']) ?></td>
              <td><?= htmlspecialchars($s['class']) ?></td>
              <td><?= htmlspecialchars($s['phone'] ?? '—') ?></td>
              <td><?= htmlspecialchars($s['admin_name']) ?></td>
              <td>
                <a href="update_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="dashboard.php?delete=<?= $s['id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this student?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
