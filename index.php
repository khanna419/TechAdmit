<?php
require_once 'includes/db.php';
$pageTitle = 'TechAdmit — Engineering Admission Portal';
$user = getLoggedInUser();

// fetch stats for hero
$total_apps   = $conn->query("SELECT COUNT(*) c FROM applications")->fetch_assoc()['c'];
$total_courses = $conn->query("SELECT COUNT(*) c FROM courses")->fetch_assoc()['c'];
$courses = $conn->query("SELECT * FROM courses ORDER BY name");

include 'includes/header.php';
?>

<section class="hero">
  <h1>Engineering Admissions<br><span>2025 – 26</span> Open</h1>
  <p>Apply online for B.Tech programmes at TechAdmit Institute of Technology. Transparent, fast, paperless.</p>
  <div class="hero-btns">
    <?php if ($user && $user['role'] === 'student'): ?>
      <a href="apply.php" class="btn btn-primary">Apply Now →</a>
      <a href="my_applications.php" class="btn btn-outline">My Applications</a>
    <?php elseif (!$user): ?>
      <a href="register.php" class="btn btn-primary">Register & Apply</a>
      <a href="login.php" class="btn btn-outline">Login</a>
    <?php endif; ?>
  </div>
</section>

<div class="container">

  <!-- Stats row -->
  <div class="stats-row" style="margin-bottom:2.5rem;">
    <div class="stat-card"><div class="num"><?= $total_courses ?></div><div class="lbl">B.Tech Programmes</div></div>
    <div class="stat-card"><div class="num"><?= $total_apps ?></div><div class="lbl">Applications Received</div></div>
    <div class="stat-card"><div class="num">4 Yrs</div><div class="lbl">Full-time Duration</div></div>
    <div class="stat-card"><div class="num">AICTE</div><div class="lbl">Approved Institute</div></div>
  </div>

  <div class="page-title">Available Programmes</div>
  <div class="page-sub">Choose from <?= $total_courses ?> engineering disciplines — all eligible for JEE / State CET admission.</div>

  <?php
  $icons = ['CSE'=>'💻','ECE'=>'📡','ME'=>'⚙️','CE'=>'🏗️','EE'=>'⚡','AIML'=>'🤖','IT'=>'🌐','CHE'=>'🧪'];
  ?>

  <div class="course-grid">
    <?php while ($c = $courses->fetch_assoc()): ?>
    <div class="course-card">
      <div class="course-icon"><?= $icons[$c['code']] ?? '🎓' ?></div>
      <h3><?= htmlspecialchars($c['name']) ?></h3>
      <p><?= htmlspecialchars($c['description']) ?></p>
      <div class="course-meta">
        <div>Seats: <span><?= $c['seats'] ?></span></div>
        <div>Fee: <span>₹<?= number_format($c['fee_per_yr']) ?>/yr</span></div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <!-- Eligibility notice -->
  <div class="alert alert-info" style="margin-top:2rem;">
    <strong>Eligibility:</strong> Minimum 60% in 10+2 with Physics, Chemistry & Mathematics. Valid JEE Main / State CET score required. Age limit: 17–25 years as on 31st October 2025.
  </div>

</div>

<?php include 'includes/footer.php'; ?>
