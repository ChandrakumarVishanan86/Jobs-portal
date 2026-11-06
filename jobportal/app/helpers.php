<?php
function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(?string $token): bool
{
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

function flash(?string $msg = null, string $type = 'success')
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 3600)   return max(1, (int)($diff / 60)) . 'm ago';
    if ($diff < 86400)  return (int)($diff / 3600) . 'h ago';
    if ($diff < 2592000) return (int)($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

function format_salary(?int $min, ?int $max): string
{
    $fmt = fn(int $n) => $n >= 1000 ? rtrim(rtrim(number_format($n / 1000, 1), '0'), '.') . 'k' : (string)$n;
    if ($min && $max) return CURRENCY . ' ' . $fmt($min) . ' - ' . $fmt($max);
    if ($min)         return CURRENCY . ' ' . $fmt($min) . '+';
    if ($max)         return 'Up to ' . CURRENCY . ' ' . $fmt($max);
    return 'Negotiable';
}

function logo_url(?string $logo): ?string
{
    return $logo ? url('uploads/logos/' . rawurlencode($logo)) : null;
}
