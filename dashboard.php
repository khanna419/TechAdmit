<?php
require_once '../includes/db.php';
$pageTitle = 'Admin Dashboard — TechAdmit';
$base = '../';
requireAdmin();

// Stats
$stats = [
  'total'    => $conn->query("SELECT COUNT(*) c FROM applications")->fetch_assoc()['c'],
  'pending'  => $conn->query("SELECT COUNT(*) c FROM applications WHERE status='Pending'")->fetch_assoc()['c'],
  'accepted' => $conn->query("SELECT COUNT(*) c FROM applications WHERE status='Accepted'")->fetch_assoc()['c'],
  'rejected' => $conn->query("SELECT COUNT(*) c FROM applications WHERE status='Rejected'")->fetch_assoc()['c'],
  'users'    => $conn->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch_assoc()['c'],
];

// Applications list
$search = sanitize($conn, $_GET['q'] ?? '');
$statusFilter = sanitize($conn, $_GET['status'] ?? '');
$courseFilter = (int)($_GET['course'] ?? 0);

$where = "WHERE 1";
if ($search) $where .= " AND (a.app_number LIKE '%$search%' OR a.first_name LIKE '%$search%' OR a.last_name LIKE '%$search%')";
if ($statusFilter) $where .= " AND a.status = '$statusFilter'";
if ($courseFilter) $where .= " AND a.course_id = $courseFilter";

$apps = $conn->query(
    "SELECT a.*, c.name AS course_name, u.email
     FROM applications a
     JOIN courses c ON a.course_id = c.id
     JOIN users u ON a.user_id = u.id
     $where
     ORDER BY a.submitted_at DESC"
);

$courses = $conn->query("SELECT id, name FROM courses ORDER BY name");

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $aid    = (int)$_POST['app_id'];
    $status = sanitize($conn, $_POST['new_status']);
    $conn->query("UPDATE applications SET status='$status' WHERE id=$aid");
    header('Location: dashboard.php?' . http_build_query($_GET));
    exit;
}

include '../includes/header.php';
?>

<div class="container">
  <div class="page-title">Admin Dashboard</div>
  <div class="page-sub">Manage all applications, update statuses, and monitor seat fill rates.</div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card"><div class="num"><?= $stats['total'] ?></div><div class="lbl">Total Applications</div></div>
    <div class="stat-card"><div class="num" style="color:var(--amber);"><?= $stats['pending'] ?></div><div class="lbl">Pending Review</div></div>
    <div class="stat-card"><div class="num" style="color:var(--teal);"><?= $stats['accepted'] ?></div><div class="lbl">Accepted</div></div>
    <div class="stat-card"><div class="num" style="color:var(--danger);"><?= $stats['rejected'] ?></div><div class="lbl">Rejected</div></div>
    <div class="stat-card"><div class="num"><?= $stats['users'] ?></div><div class="lbl">Registered Students</div></div>
  </div>

  <!-- Filters -->
  <form method="GET" style="display:flex;gap:10px;margin-bottom:1.25rem;flex-wrap:wrap;">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search name / app no..." style="flex:1;min-width:180px;border-radius:8px;border:0.5px solid var(--line);padding:9px 13px;font-family:inherit;font-size:14px;">
    <select name="status" style="border-radius:8px;border:0.5px solid var(--line);padding:9px 13px;font-family:inherit;font-size:14px;">
      <option value="">All Statuses</option>
      <?php foreach (['Pending','Under Review','Accepted','Rejected'] as $s): ?>
        <option <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    <select name="course" style="border-radius:8px;border:0.5px solid var(--line);padding:9px 13px;font-family:inherit;font-size:14px;">
      <option value="">All Courses</option>
      <?php $courses->data_seek(0); while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>" <?= $courseFilter === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-dark">Filter</button>
    <a href="dashboard.php" class="btn" style="background:var(--faint);color:var(--ink);border:0.5px solid var(--line);">Reset</a>
  </form>

  <!-- Table -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>App No.</th>
          <th>Applicant</th>
          <th>Course</th>
          <th>12th %</th>
          <th>JEE %ile</th>
          <th>Category</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($apps->num_rows === 0): ?>
          <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:2rem;">No applications found.</td></tr>
        <?php endif; ?>
        <?php while ($a = $apps->fetch_assoc()):
          $bc = ['Pending'=>'badge-pending','Under Review'=>'badge-review','Accepted'=>'badge-accepted','Rejected'=>'badge-rejected'][$a['status']] ?? '';
        ?>
        <tr>
          <td><strong style="font-size:12px;"><?= htmlspecialchars($a['app_number']) ?></strong></td>
          <td>
            <strong><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></strong><br>
            <span style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($a['email']) ?></span>
          </td>
          <td style="font-size:12px;"><?= htmlspecialchars($a['course_name']) ?></td>
          <td><?= $a['score_12'] ?>%</td>
          <td><?= $a['jee_percentile'] ?>%ile</td>
          <td><span class="badge badge-pending"><?= $a['category'] ?></span></td>
          <td><span class="badge <?= $bc ?>"><?= $a['status'] ?></span></td>
          <td style="font-size:11px;color:var(--muted);"><?= date('d M Y', strtotime($a['submitted_at'])) ?></td>
          <td>
            <form method="POST" style="display:flex;gap:6px;align-items:center;">
              <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
              <select name="new_status" style="font-size:12px;padding:5px 8px;border-radius:6px;border:0.5px solid var(--line);">
                <?php foreach (['Pending','Under Review','Accepted','Rejected'] as $s): ?>
                  <option <?= $a['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" name="update_status" class="btn btn-sm btn-primary">Save</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

<?php include '../includes/footer.php'; ?>
