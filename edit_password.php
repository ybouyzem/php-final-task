<?php
session_start();
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/SavedPassword.php';

Auth::requireLogin();

$sp     = new SavedPassword();
$id     = (int)($_GET['id'] ?? 0);
$userId = Auth::userId();
$key    = Auth::encryptionKey();
$entry  = $sp->getById($id, $userId, $key);

if (!$entry) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = trim($_POST['site_name'] ?? '');
    $siteUrl  = trim($_POST['site_url']  ?? '');
    $username = trim($_POST['username']  ?? '');
    $password = $_POST['password']       ?? '';
    $notes    = trim($_POST['notes']     ?? '');

    if (!$siteName || !$password) {
        $error = 'Site name and password are required.';
    } else {
        $sp->update($id, $userId, $siteName, $password, $key, $siteUrl, $username, $notes);
        header('Location: /dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Password – Password Manager</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/pages/nav.php'; ?>

<div class="container">
    <h1 class="page-title">✏️ Edit Password</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label>Site / App Name *</label>
              <input type="text" name="site_name" required
                     value="<?= htmlspecialchars($_POST['site_name'] ?? $entry['site_name']) ?>">
            </div>
            <div class="form-group">
              <label>URL (optional)</label>
              <input type="url" name="site_url"
                     value="<?= htmlspecialchars($_POST['site_url'] ?? $entry['site_url']) ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Account Username</label>
            <input type="text" name="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? $entry['username']) ?>">
          </div>
          <div class="form-group">
            <label>Password *</label>
            <input type="text" name="password" required style="font-family:monospace"
                   value="<?= htmlspecialchars($_POST['password'] ?? $entry['password_plain']) ?>">
          </div>
          <div class="form-group">
            <label>Notes</label>
            <textarea name="notes"><?= htmlspecialchars($_POST['notes'] ?? $entry['notes']) ?></textarea>
          </div>
          <p class="text-muted" style="font-size:.8rem;margin-bottom:1rem">
            Originally saved: <?= date('M j, Y H:i', strtotime($entry['created_at'])) ?>
          </p>
          <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="/dashboard.php" class="btn btn-ghost">Cancel</a>
          </div>
        </form>
    </div>
</div>
</body>
</html>
