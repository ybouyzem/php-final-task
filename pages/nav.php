<nav>
    <a href="/dashboard.php" class="brand">🔐 VaultPHP</a>
    <a href="/dashboard.php">Passwords</a>
    <a href="/generator.php">Generator</a>
    <a href="/settings.php">Settings</a>
    <span class="spacer"></span>
    <span class="user-info">👤 <?= htmlspecialchars(Auth::username()) ?></span>
    <a href="/logout.php" style="color:var(--danger)">Logout</a>
</nav>
