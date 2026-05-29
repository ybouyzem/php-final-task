<?php
session_start();
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/User.php';

// Already logged in? Go to dashboard
if (Auth::isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    if (!$username || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        try {
            $user = new User();
            $user->register($username, $email, $password);
            $success = 'Account created! You can now log in.';
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – Password Manager</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>🔐 Create Account</h1>
    <p class="sub">Your personal password vault</p>

    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required autocomplete="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" id="reg-pw" required autocomplete="new-password">
        <div class="strength-bar mt-1"><div class="strength-fill" id="strength-fill" style="width:0"></div></div>
        <small class="text-muted" id="strength-label">Enter a password</small>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm" required autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">Create Account</button>
    </form>
    <p class="text-muted mt-2" style="font-size:.85rem;text-align:center">
      Already have an account? <a href="/login.php" style="color:var(--accent)">Log in</a>
    </p>
  </div>
</div>
<script>
// Simple password strength indicator
document.getElementById('reg-pw').addEventListener('input', function() {
    const pw = this.value;
    let score = 0;
    if (pw.length >= 8)  score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const colors = ['#ef4444','#f59e0b','#22c55e','#6c63ff'];
    const labels = ['Weak','Fair','Good','Strong'];
    const fill   = document.getElementById('strength-fill');
    const label  = document.getElementById('strength-label');
    fill.style.width      = (score * 25) + '%';
    fill.style.background = colors[score - 1] || '#2e3148';
    label.textContent     = labels[score - 1] || 'Too short';
});
</script>
</body>
</html>
