<?php
session_start();
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/PasswordGenerator.php';

Auth::requireLogin();

$generated = '';
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'count';
    $gen  = new PasswordGenerator();

    try {
        if ($mode === 'count') {
            $lower   = max(0, (int)($_POST['lower_count']   ?? 2));
            $upper   = max(0, (int)($_POST['upper_count']   ?? 3));
            $special = max(0, (int)($_POST['special_count'] ?? 2));
            $numbers = max(0, (int)($_POST['number_count']  ?? 2));

            if ($lower + $upper + $special + $numbers < 1) {
                throw new RuntimeException('At least one character is required.');
            }
            $generated = $gen->generateByCount($lower, $upper, $special, $numbers);
        } else {
            $length  = max(4, min(128, (int)($_POST['length']      ?? 16)));
            $upperP  = max(0, (float)($_POST['upper_pct']   ?? 25));
            $specialP = max(0, (float)($_POST['special_pct'] ?? 25));
            $numP    = max(0, (float)($_POST['number_pct']  ?? 25));

            if ($upperP + $specialP + $numP > 100) {
                throw new RuntimeException('Percentages cannot exceed 100%.');
            }
            $generated = $gen->generateByPercent($length, $upperP, $specialP, $numP);
        }
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generator – Password Manager</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/pages/nav.php'; ?>

<div class="container">
    <h1 class="page-title">⚡ Password Generator</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <!-- Mode tabs -->
        <div class="tabs">
          <button class="tab active" onclick="switchTab('count', this)">By Count</button>
          <button class="tab"        onclick="switchTab('percent', this)">By Percent</button>
        </div>

        <form method="POST" id="gen-form">
          <!-- Count mode -->
          <div class="tab-pane active" id="tab-count">
            <input type="hidden" name="mode" value="count" id="mode-input">
            <p class="text-muted" style="margin-bottom:1rem;font-size:.875rem">
              Set exact character counts. Example: 2 lower + 3 upper + 2 special + 2 numbers = <em>aF$3E.D5s</em>
            </p>
            <div class="form-row">
              <div class="form-group">
                <label>Lowercase (a-z)</label>
                <input type="number" name="lower_count" value="<?= (int)($_POST['lower_count'] ?? 3) ?>"
                       min="0" max="100">
              </div>
              <div class="form-group">
                <label>Uppercase (A-Z)</label>
                <input type="number" name="upper_count" value="<?= (int)($_POST['upper_count'] ?? 3) ?>"
                       min="0" max="100">
              </div>
              <div class="form-group">
                <label>Special (!@#...)</label>
                <input type="number" name="special_count" value="<?= (int)($_POST['special_count'] ?? 2) ?>"
                       min="0" max="100">
              </div>
              <div class="form-group">
                <label>Numbers (0-9)</label>
                <input type="number" name="number_count" value="<?= (int)($_POST['number_count'] ?? 2) ?>"
                       min="0" max="100">
              </div>
            </div>
            <p class="text-muted" style="font-size:.85rem" id="total-display">
              Total: <strong id="total-count">10</strong> characters
            </p>
          </div>

          <!-- Percent mode -->
          <div class="tab-pane" id="tab-percent">
            <p class="text-muted" style="margin-bottom:1rem;font-size:.875rem">
              Set password length and percentage of each character type. Remainder goes to lowercase.
            </p>
            <div class="form-group">
              <label>Total Length</label>
              <input type="number" name="length" value="<?= (int)($_POST['length'] ?? 16) ?>"
                     min="4" max="128">
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Uppercase %</label>
                <input type="number" name="upper_pct" value="<?= (float)($_POST['upper_pct'] ?? 25) ?>"
                       min="0" max="100" step="5">
              </div>
              <div class="form-group">
                <label>Special %</label>
                <input type="number" name="special_pct" value="<?= (float)($_POST['special_pct'] ?? 15) ?>"
                       min="0" max="100" step="5">
              </div>
              <div class="form-group">
                <label>Numbers %</label>
                <input type="number" name="number_pct" value="<?= (float)($_POST['number_pct'] ?? 20) ?>"
                       min="0" max="100" step="5">
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Generate Password</button>
        </form>

        <!-- Result -->
        <?php if ($generated): ?>
        <div class="gen-result" id="gen-result"><?= htmlspecialchars($generated) ?></div>
        <div class="flex gap-1">
          <button class="btn btn-ghost" onclick="copyResult()">📋 Copy</button>
          <a href="/add_password.php?password=<?= urlencode($generated) ?>"
             class="btn btn-success">💾 Save to Vault</a>
        </div>
        <p class="text-muted mt-1" style="font-size:.8rem">
          Length: <?= strlen($generated) ?> chars &nbsp;|&nbsp;
          Entropy: ~<?= round(strlen($generated) * log(95, 2)) ?> bits
        </p>
        <?php endif; ?>
    </div>
</div>

<script>
// Tab switching
function switchTab(name, btn) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + name).classList.add('active');
    document.getElementById('mode-input').value = name;
}

// Live total display for count mode
function updateTotal() {
    const inputs = document.querySelectorAll('#tab-count input[type=number]');
    let total = 0;
    inputs.forEach(i => total += parseInt(i.value) || 0);
    document.getElementById('total-count').textContent = total;
}
document.querySelectorAll('#tab-count input').forEach(i => i.addEventListener('input', updateTotal));
updateTotal();

function copyResult() {
    const text = document.getElementById('gen-result').textContent;
    navigator.clipboard.writeText(text);
}

// If we came back with percent mode active, restore it
<?php if (($_POST['mode'] ?? '') === 'percent'): ?>
switchTab('percent', document.querySelectorAll('.tab')[1]);
<?php endif; ?>
</script>
</body>
</html>
