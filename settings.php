<?php
session_start();
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/User.php';

Auth::requireLogin();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPw  = $_POST['old_password']     ?? '';
    $newPw  = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$oldPw || !$newPw) {
        $error = 'All fields are required.';
    } elseif ($newPw !== $confirm) {
        $error = 'New passwords do not match.';
    } elseif (strlen($newPw) < 8) {
        $error = 'New password must be at least 8 characters.';
    } else {
        try {
            $user = new User();
            $user->changePassword(Auth::userId(), $oldPw, $newPw);
            // NOTE: This re-encrypts the AES key with the new password.
            // The KEY itself never changes — only the wrapper changes.
            $success = 'Password changed successfully. Your encryption key has been re-wrapped.';
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
}

$userInfo = User::getById(Auth::userId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings – Password Manager</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/pages/nav.php'; ?>

<div class="container">
    <h1 class="page-title">⚙️ Settings</h1>

    <div class="card" style="margin-bottom:1.5rem">
        <h2>Account Info</h2>
        <p><strong>Username:</strong> <?= htmlspecialchars($userInfo['username']) ?></p>
        <p><strong>Email:</strong>    <?= htmlspecialchars($userInfo['email']) ?></p>
        <p class="text-muted" style="font-size:.85rem;margin-top:.5rem">
            Member since: <?= date('F j, Y', strtotime($userInfo['created_at'])) ?>
        </p>
    </div>

    <div class="card">
        <h2>Change Login Password</h2>
        <p class="text-muted" style="font-size:.875rem;margin-bottom:1rem">
            Changing your login password will automatically re-wrap your encryption key.
            Your stored passwords remain safe — the underlying encryption key never changes.
        </p>

        <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <form method="POST">
          <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="old_password" required>
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required>
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>
</body>
</html>
