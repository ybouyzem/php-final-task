<?php
session_start();
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/User.php';

if (Auth::isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    if (!$username || !$password) {
        $error = 'Please fill in both fields.';
    } else {
        $user = new User();
        if ($user->login($username, $password)) {
            header('Location: /dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login – Password Manager</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>🔐 Password Manager</h1>
    <p class="sub">Sign in to your vault</p>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required autofocus autocomplete="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">Log In</button>
    </form>
    <p class="text-muted mt-2" style="font-size:.85rem;text-align:center">
      No account? <a href="/register.php" style="color:var(--accent)">Create one</a>
    </p>
  </div>
</div>
</body>
</html>
