<?php
require_once 'includes/db.php';
$pageTitle = 'My Applications — TechAdmit';
$user = requireLogin();

$uid  = $user['id'];
$stmt = $conn->prepare(
    "SELECT a.*, c.name AS course_name, c.code AS course_code
     FROM applications a
     JOIN courses c ON a.course_id = c.id
     WHERE a.user_id = ?
     ORDER BY a.submitted_at DESC"
);
$stmt->bind_param('i', $uid);
$stmt->execute();
$apps = $stmt->get_result();

// fetch user name
$urow = $conn->query("SELECT name FROM users WHERE id = $uid")->fetch_assoc();

include 'includes/header.php';
?>

<div class="container">
  <div class="page-title">My Applications</div>
  <div class="page-sub">Welcome, <?= htmlspecialchars($urow['name']) ?>. Here are all your submitted applications.</div>

  <?php if (isset($_GET['submitted'])): ?>
    <div class="alert alert-success">
      Application submitted! Your Application Number is <strong><?= htmlspecialchars($_GET['submitted']) ?></strong>. Save this for reference.
    </div>
  <?php endif; ?>

  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;">
    <a href="apply.php" class="btn btn-primary">+ New Application</a>
  </div>

  <?php if ($apps->num_rows === 0): ?>
    <div class="card" style="text-align:center;padding:3rem;">
      <div style="font-size:3rem;margin-bottom:1rem;">📋</div>
      <p style="color:var(--muted);font-size:15px;">You haven't applied yet.</p>
      <a href="apply.php" class="btn btn-primary" style="margin-top:1rem;">Apply Now</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>App No.</th>
            <th>Course</th>
            <th>Class 12 %</th>
            <th>JEE Percentile</th>
            <th>Status</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($a = $apps->fetch_assoc()):
            $badgeClass = [
              'Pending'      => 'badge-pending',
              'Under Review' => 'badge-review',
              'Accepted'     => 'badge-accepted',
              'Rejected'     => 'badge-rejected',
            ][$a['status']] ?? 'badge-pending';
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($a['app_number']) ?></strong></td>
            <td><?= htmlspecialchars($a['course_name']) ?></td>
            <td><?= $a['score_12'] ?>%</td>
            <td><?= $a['jee_percentile'] ?>%ile</td>
            <td><span class="badge <?= $badgeClass ?>"><?= $a['status'] ?></span></td>
            <td style="color:var(--muted);font-size:12px;"><?= date('d M Y', strtotime($a['submitted_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
