<?php
require_once 'includes/db.php';
$pageTitle = 'Register — TechAdmit';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($conn, $_POST['name'] ?? '');
    $email = sanitize($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (!$name || !$email || !$pass) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        // check duplicate email
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = hash('sha256', $pass);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $name, $email, $hashed);
            if ($stmt->execute()) {
                $userId = $conn->insert_id;
                setLoginCookie($userId, 'student');
                header('Location: index.php?registered=1');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container-sm">
  <div class="card" style="margin-top:1rem;">
    <h2 style="font-family:'Syne',sans-serif;font-size:1.4rem;margin-bottom:4px;">Create Account</h2>
    <p style="font-size:14px;color:var(--muted);margin-bottom:1.25rem;">Register to apply for B.Tech admissions.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Riya Sharma" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="riya@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Min 6 characters" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="password2" placeholder="Re-enter password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Register →</button>
    </form>

    <p style="text-align:center;margin-top:1rem;font-size:13px;color:var(--muted);">
      Already have an account? <a href="login.php" style="color:var(--teal);font-weight:500;">Login here</a>
    </p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
