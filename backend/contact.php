<?php
require __DIR__ . '/config.php';
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Vary: Origin');
function out($code, $ok, $msg = '') {
    http_response_code($code);
    echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => $msg]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(405, false, 'Méthode non autorisée');
if (!empty($_POST['website'])) out(400, false, 'Soumission refusée'); // honeypot

$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
if ($name === '' || mb_strlen($name) > 100) out(422, false, 'Nom invalide');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) out(422, false, 'E-mail invalide');
if ($subject === '' || mb_strlen($subject) > 150) out(422, false, 'Sujet invalide');
if (mb_strlen($message) < 10 || mb_strlen($message) > 2000) out(422, false, 'Message invalide');

try {
    $pdo = pdo();
    $ip = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . IP_SALT);
    $st = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE ip_hash = ? AND created_at > NOW() - INTERVAL 10 MINUTE');
    $st->execute([$ip]);
    if ((int)$st->fetchColumn() >= 3) out(429, false, 'Trop de messages, réessayez plus tard');
    $pdo->prepare('INSERT INTO messages (name, email, subject, message, ip_hash) VALUES (?, ?, ?, ?, ?)')
        ->execute([$name, $email, $subject, $message, $ip]);
    out(200, true);
} catch (PDOException $e) {
    error_log($e->getMessage());
    out(500, false, 'Erreur serveur');
}
