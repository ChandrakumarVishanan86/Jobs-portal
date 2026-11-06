<?php
require dirname(__DIR__) . '/config/config.php';
header('Content-Type: application/json');

function respond(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok' => false, 'error' => 'Method not allowed'], 405);
if (!Auth::check())                        respond(['ok' => false, 'error' => 'login_required'], 401);
if (Auth::role() !== 'candidate')          respond(['ok' => false, 'error' => 'Only job seekers can save jobs.'], 403);
if (!csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) respond(['ok' => false, 'error' => 'Invalid token'], 419);

$jobId = (int)($_POST['job_id'] ?? 0);
$pdo = Database::conn();

$st = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND status = 'approved'");
$st->execute([$jobId]);
if (!$st->fetch()) respond(['ok' => false, 'error' => 'Job not found'], 404);

$uid = Auth::id();
$del = $pdo->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?');
$del->execute([$uid, $jobId]);

if ($del->rowCount() > 0) {
    respond(['ok' => true, 'saved' => false]);
}
$pdo->prepare('INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?, ?)')->execute([$uid, $jobId]);
respond(['ok' => true, 'saved' => true]);
