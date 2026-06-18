<?php
// includes/header.php — shared nav header
$user = getLoggedInUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? 'TechAdmit Engineering Portal' ?></title>
<link rel="stylesheet" href="<?= $base ?? '' ?>css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar">
  <div class="nav-inner">
    <a class="logo" href="<?= $base ?? '' ?>index.php">Tech<span>Admit</span></a>
    <div class="nav-links">
      <a href="<?= $base ?? '' ?>index.php">Home</a>
      <a href="<?= $base ?? '' ?>courses.php">Courses</a>
      <?php if ($user): ?>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="<?= $base ?? '' ?>admin/dashboard.php">Admin Panel</a>
        <?php else: ?>
          <a href="<?= $base ?? '' ?>my_applications.php">My Applications</a>
          <a href="<?= $base ?? '' ?>apply.php">Apply Now</a>
        <?php endif; ?>
        <a href="<?= $base ?? '' ?>logout.php" class="btn-nav">Logout</a>
      <?php else: ?>
        <a href="<?= $base ?? '' ?>login.php">Login</a>
        <a href="<?= $base ?? '' ?>register.php" class="btn-nav">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
