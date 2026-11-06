<?php
class Auth
{
    public const ROLES = ['employer', 'candidate'];

    /** Register an employer or candidate. Returns [bool ok, string|array errorsOrMessage]. */
    public static function register(array $d): array
    {
        $name  = trim($d['name'] ?? '');
        $email = strtolower(trim($d['email'] ?? ''));
        $pass  = $d['password'] ?? '';
        $role  = $d['role'] ?? '';
        $company = trim($d['company'] ?? '');
        $errors = [];

        if (!in_array($role, self::ROLES, true)) $errors[] = 'Invalid account type.';
        if (mb_strlen($name) < 2)                $errors[] = 'Please enter your name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
        if (strlen($pass) < 8)                   $errors[] = 'Password must be at least 8 characters.';
        if ($pass !== ($d['password2'] ?? ''))   $errors[] = 'Passwords do not match.';
        if ($role === 'employer' && mb_strlen($company) < 2) $errors[] = 'Please enter your company name.';
        if ($errors) return [false, $errors];

        $pdo = Database::conn();
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) return [false, ['That email is already registered.']];

        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
            $uid = (int)$pdo->lastInsertId();
            if ($role === 'employer') {
                $pdo->prepare('INSERT INTO companies (user_id, name) VALUES (?,?)')->execute([$uid, $company]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            return [false, ['Registration failed. Please try again.']];
        }
        self::loginById($uid);
        return [true, $uid];
    }

    public static function attempt(string $email, string $password): bool
    {
        $st = Database::conn()->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([strtolower(trim($email))]);
        $u = $st->fetch();
        if (!$u || !password_verify($password, $u['password'])) return false;
        self::loginById((int)$u['id']);
        return true;
    }

    private static function loginById(int $id): void
    {
        $st = Database::conn()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $st->execute([$id]);
        session_regenerate_id(true);
        $_SESSION['user'] = $st->fetch();
    }

    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function check(): bool  { return isset($_SESSION['user']); }
    public static function id(): ?int     { return isset($_SESSION['user']) ? (int)$_SESSION['user']['id'] : null; }
    public static function role(): ?string { return $_SESSION['user']['role'] ?? null; }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Guard a page by role(s); redirects to login otherwise. */
    public static function requireRole(string ...$roles): void
    {
        if (!self::check() || !in_array(self::role(), $roles, true)) {
            flash('Please sign in with the right account to continue.', 'warning');
            redirect('login');
        }
    }

    public static function home(): string
    {
        // Phase 1: everyone lands on the job listing. Phase 2 will route
        // employers to 'employer/dashboard' and admins to 'admin/jobs'.
        return '';
    }
}
