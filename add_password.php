<?php
session_start();
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/SavedPassword.php';
require_once __DIR__ . '/classes/PasswordGenerator.php';

Auth::requireLogin();

$error   = '';
$success = '';
$prefill = $_GET['password'] ?? ''; // allow prefill from generator

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName  = trim($_POST['site_name']  ?? '');
    $siteUrl   = trim($_POST['site_url']   ?? '');
    $username  = trim($_POST['username']   ?? '');
    $password  = $_POST['password']        ?? '';
    $notes     = trim($_POST['notes']      ?? '');

    if (!$siteName || !$password) {
        $error = 'Site name and password are required.';
    } else {
        $sp = new SavedPassword();
        $sp->save(Auth::userId(), $siteName, $password, Auth::encryptionKey(), $siteUrl, $username, $notes);
        header('Location: /dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Password – Password Manager</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/pages/nav.php'; ?>

<div class="container">
    <h1 class="page-title">+ Add Password</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label>Site / App Name *</label>
              <input type="text" name="site_name" required placeholder="e.g. Gmail, Facebook"
                     value="<?= htmlspecialchars($_POST['site_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>URL (optional)</label>
              <input type="url" name="site_url" placeholder="https://gmail.com"
                     value="<?= htmlspecialchars($_POST['site_url'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Account Username (optional)</label>
            <input type="text" name="username" placeholder="your login for that site"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Password *</label>
            <div class="pw-field">
              <input type="text" name="password" id="pw-field" required
                     value="<?= htmlspecialchars($prefill ?: ($_POST['password'] ?? '')) ?>"
                     style="font-family:monospace">
              <button type="button" class="btn btn-ghost" onclick="quickGen()">⚡ Generate</button>
            </div>
          </div>
          <div class="form-group">
            <label>Notes (optional)</label>
            <textarea name="notes"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
          </div>
          <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">Save Password</button>
            <a href="/dashboard.php" class="btn btn-ghost">Cancel</a>
            <a href="/generator.php" class="btn btn-ghost">Full Generator →</a>
          </div>
        </form>
    </div>
</div>

<script>
// Quick generate a 16-char password without leaving the page
function quickGen() {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let pw = '';
    const arr = new Uint32Array(16);
    crypto.getRandomValues(arr);
    arr.forEach(n => pw += chars[n % chars.length]);
    document.getElementById('pw-field').value = pw;
}
</script>
</body>
</html>
