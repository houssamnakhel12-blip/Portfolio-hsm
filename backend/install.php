<?php
// Installateur à usage unique : crée la table, écrit config.php, puis se supprime.
$cfg = __DIR__ . '/config.php';
if (is_file($cfg) && strpos((string)file_get_contents($cfg), '// INSTALLED') !== false) { http_response_code(403); exit('Déjà installé. Supprimez install.php.'); }
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$msg = ''; $done = false;
$origin = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = fn($k) => trim((string)($_POST[$k] ?? ''));
    $host = $v('host'); $name = $v('name'); $user = $v('user'); $pass = (string)($_POST['pass'] ?? '');
    $orig = rtrim($v('origin'), '/'); $admin = (string)($_POST['admin'] ?? '');
    if ($host === '' || $name === '' || $user === '') $msg = 'Remplissez hôte, base et utilisateur.';
    elseif (strlen($admin) < 10) $msg = 'Mot de passe admin : 10 caractères minimum.';
    elseif (!preg_match('#^https?://\S+$#', $orig)) $msg = 'Adresse du site invalide.';
    else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('CREATE TABLE IF NOT EXISTS messages (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL,
              subject VARCHAR(150) NOT NULL, message TEXT NOT NULL, ip_hash CHAR(64) NOT NULL,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_created (created_at), INDEX idx_ip (ip_hash, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            $tpl = <<<'PHPTPL'
<?php
// INSTALLED
const DB_HOST = {{HOST}};
const DB_NAME = {{NAME}};
const DB_USER = {{USER}};
const DB_PASS = {{PASS}};
const ALLOWED_ORIGIN = {{ORIGIN}};
const IP_SALT = {{SALT}};
const ADMIN_HASH = {{HASH}};
function pdo(): PDO {
    return new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
PHPTPL;
            $code = strtr($tpl, [
                '{{HOST}}' => var_export($host, true), '{{NAME}}' => var_export($name, true),
                '{{USER}}' => var_export($user, true), '{{PASS}}' => var_export($pass, true),
                '{{ORIGIN}}' => var_export($orig, true), '{{SALT}}' => var_export(bin2hex(random_bytes(16)), true),
                '{{HASH}}' => var_export(password_hash($admin, PASSWORD_DEFAULT), true),
            ]);
            if (file_put_contents($cfg, $code) === false) throw new RuntimeException("Impossible d'écrire config.php (droits du dossier).");
            $done = true; @unlink(__FILE__);
        } catch (Throwable $e) { $msg = 'Échec : ' . $e->getMessage(); }
    }
}
?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Installation</title>
<style>body{font:16px system-ui;max-width:520px;margin:30px auto;padding:0 16px}label{display:block;margin-top:12px;font-weight:600}input{width:100%;padding:9px;box-sizing:border-box}button{margin-top:18px;padding:10px 18px}.e{color:#b00}</style></head><body>
<h1>Installation du backend</h1>
<?php if ($done): ?>
<p>✅ Installé. <code>install.php</code> a été supprimé (vérifiez qu'il n'est plus sur le serveur).</p>
<p>Vos messages : <a href="admin.php">admin.php</a></p>
<?php else: ?>
<p class="e"><?= $h($msg) ?></p>
<form method="post">
<label>Hôte MySQL<input name="host" value="<?= $h($_POST['host'] ?? 'localhost') ?>" required></label>
<label>Nom de la base<input name="name" value="<?= $h($_POST['name'] ?? '') ?>" required></label>
<label>Utilisateur MySQL<input name="user" value="<?= $h($_POST['user'] ?? '') ?>" required></label>
<label>Mot de passe MySQL<input name="pass" type="password"></label>
<label>Adresse du portfolio<input name="origin" value="<?= $h($_POST['origin'] ?? $origin) ?>" required></label>
<label>Mot de passe admin (10 caractères min.)<input name="admin" type="password" required></label>
<button>Installer</button></form>
<?php endif; ?></body></html>
