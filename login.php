<?php
require_once 'includes/db.php';
$pageTitle = 'Login — TechAdmit';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $error = 'Both fields are required.';
    } else {
        $hashed = hash('sha256', $pass);
        $stmt = $conn->prepare("SELECT id, role, name FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param('ss', $email, $hashed);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            setLoginCookie($row['id'], $row['role']);
            $_SESSION['user_name'] = $row['name'];
            if ($row['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container-sm">
  <div class="card" style="margin-top:1rem;">
    <h2 style="font-family:'Syne',sans-serif;font-size:1.4rem;margin-bottom:4px;">Welcome Back</h2>
    <p style="font-size:14px;color:var(--muted);margin-bottom:1.25rem;">Login to manage your application.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Account created! You are now logged in.</div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Your password" required>
      </div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:1rem;">
        <input type="checkbox" name="remember" id="rem" style="width:auto;">
        <label for="rem" style="font-size:13px;text-transform:none;color:var(--muted);letter-spacing:0;">Remember me for 7 days (cookie)</label>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Login →</button>
    </form>

    <p style="text-align:center;margin-top:1rem;font-size:13px;color:var(--muted);">
      New here? <a href="register.php" style="color:var(--teal);font-weight:500;">Create an account</a>
    </p>
    <p style="text-align:center;font-size:12px;color:#aaa;margin-top:6px;">
      Admin login: admin@techadmit.in / admin123
    </p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
