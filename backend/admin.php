<?php
require __DIR__ . '/config.php';
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$err = '';
if (isset($_POST['password'])) {
    if (password_verify($_POST['password'], ADMIN_HASH)) { session_regenerate_id(true); $_SESSION['ok'] = true; $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
    else { $err = 'Mot de passe incorrect'; sleep(1); }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }
?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Messages</title>
<style>body{font:15px system-ui;max-width:900px;margin:30px auto;padding:0 16px}article{border:1px solid #ccc;border-radius:8px;padding:12px;margin:12px 0}small{color:#666}pre{white-space:pre-wrap;font:inherit}</style></head><body>
<?php if (empty($_SESSION['ok'])): ?>
<h1>Connexion</h1><form method="post"><input type="password" name="password" placeholder="Mot de passe" required autofocus> <button>Entrer</button></form><p style="color:#b00"><?= $h($err) ?></p>
<?php else:
  $pdo = pdo();
  if (isset($_POST['delete']) && hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
      $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([(int)$_POST['delete']]);
  }
  $rows = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 200')->fetchAll(); ?>
<h1>Messages reçus (<?= count($rows) ?>) <small><a href="?logout=1">Déconnexion</a></small></h1>
<?php foreach ($rows as $r): ?>
<article><strong><?= $h($r['subject']) ?></strong><br><small><?= $h($r['name']) ?> · <a href="mailto:<?= $h($r['email']) ?>"><?= $h($r['email']) ?></a> · <?= $h($r['created_at']) ?></small>
<pre><?= $h($r['message']) ?></pre>
<form method="post" onsubmit="return confirm('Supprimer ce message ?')"><input type="hidden" name="csrf" value="<?= $h($_SESSION['csrf']) ?>"><button name="delete" value="<?= (int)$r['id'] ?>">Supprimer</button></form></article>
<?php endforeach; if (!$rows) echo '<p>Aucun message pour le moment.</p>'; endif; ?>
</body></html>
