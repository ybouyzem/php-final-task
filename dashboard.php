<?php
session_start();
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/SavedPassword.php';

Auth::requireLogin();

$sp       = new SavedPassword();
$key      = Auth::encryptionKey();
$userId   = Auth::userId();
$passwords = $sp->getAllForUser($userId, $key);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $sp->delete((int)$_POST['delete_id'], $userId);
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Password Manager</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/pages/nav.php'; ?>

<div class="container-wide">
    <div class="flex align-center justify-between" style="margin-bottom:1.5rem">
        <h1 class="page-title" style="margin:0">🗄️ My Passwords</h1>
        <div class="flex gap-1">
            <a href="/generator.php" class="btn btn-ghost btn-sm">⚡ Generator</a>
            <a href="/add_password.php" class="btn btn-primary btn-sm">+ Add Password</a>
        </div>
    </div>

    <?php if (empty($passwords)): ?>
      <div class="card" style="text-align:center;padding:3rem">
        <p style="font-size:3rem">🔒</p>
        <p class="text-muted mt-2">No passwords saved yet.</p>
        <a href="/add_password.php" class="btn btn-primary mt-2">Add your first password</a>
      </div>
    <?php else: ?>
      <div class="card" style="padding:0;overflow:hidden">
        <table class="pw-table">
          <thead>
            <tr>
              <th>Site / App</th>
              <th>Username</th>
              <th>Password</th>
              <th>Saved</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($passwords as $pw): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($pw['site_name']) ?></strong>
                <?php if ($pw['site_url']): ?>
                  <br><a href="<?= htmlspecialchars($pw['site_url']) ?>"
                         target="_blank" style="color:var(--accent);font-size:.8rem">
                    <?= htmlspecialchars($pw['site_url']) ?>
                  </a>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($pw['username'] ?: '—') ?></td>
              <td>
                <div class="pw-field">
                  <span class="pw-hidden" id="pw-<?= $pw['id'] ?>">••••••••</span>
                  <button class="btn btn-ghost btn-sm"
                          onclick="togglePw(<?= $pw['id'] ?>, '<?= addslashes(htmlspecialchars($pw['password_plain'])) ?>')">
                    👁
                  </button>
                  <button class="btn btn-ghost btn-sm"
                          onclick="copyToClipboard('<?= addslashes($pw['password_plain']) ?>', this)">
                    📋
                  </button>
                </div>
              </td>
              <td class="text-muted" style="font-size:.8rem">
                <?= date('M j, Y', strtotime($pw['created_at'])) ?><br>
                <?= date('H:i', strtotime($pw['created_at'])) ?>
              </td>
              <td>
                <a href="/edit_password.php?id=<?= $pw['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                <form method="POST" style="display:inline"
                      onsubmit="return confirm('Delete this entry?')">
                  <input type="hidden" name="delete_id" value="<?= $pw['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Del</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
</div>

<script>
function togglePw(id, plain) {
    const el = document.getElementById('pw-' + id);
    el.textContent = el.textContent === '••••••••' ? plain : '••••••••';
    el.classList.toggle('pw-hidden');
}
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text);
    const orig = btn.textContent;
    btn.textContent = '✓';
    setTimeout(() => btn.textContent = orig, 1500);
}
</script>
</body>
</html>
