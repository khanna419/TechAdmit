<?php
require_once 'includes/db.php';
$pageTitle = 'Courses — TechAdmit';
$courses = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM applications a WHERE a.course_id = c.id) AS applied FROM courses c ORDER BY c.name");
$icons = ['CSE'=>'💻','ECE'=>'📡','ME'=>'⚙️','CE'=>'🏗️','EE'=>'⚡','AIML'=>'🤖','IT'=>'🌐','CHE'=>'🧪'];

include 'includes/header.php';
?>

<div class="container">
  <div class="page-title">All B.Tech Programmes</div>
  <div class="page-sub">Detailed information about each engineering discipline offered for 2025–26 admissions.</div>

  <div class="course-grid">
    <?php while ($c = $courses->fetch_assoc()):
      $fill = $c['seats'] > 0 ? round(($c['applied'] / $c['seats']) * 100) : 100;
    ?>
    <div class="course-card">
      <div class="course-icon"><?= $icons[$c['code']] ?? '🎓' ?></div>
      <h3><?= htmlspecialchars($c['name']) ?></h3>
      <p><?= htmlspecialchars($c['description']) ?></p>

      <!-- seat fill indicator -->
      <div style="height:4px;background:var(--faint);border-radius:2px;margin-bottom:10px;overflow:hidden;">
        <div style="height:100%;width:<?= min($fill, 100) ?>%;background:<?= $fill > 80 ? '#c0392b' : 'var(--teal-mid)' ?>;border-radius:2px;"></div>
      </div>
      <div style="font-size:11px;color:var(--muted);margin-bottom:10px;">
        <?= $c['applied'] ?> / <?= $c['seats'] ?> seats filled (<?= $fill ?>%)
      </div>

      <div class="course-meta">
        <div>Duration: <span><?= $c['duration'] ?></span></div>
        <div>₹<span><?= number_format($c['fee_per_yr']) ?>/yr</span></div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
