<?php
// ====== Configuration (XAMPP/WAMP par défaut) ======
const DB_HOST = 'localhost';
const DB_NAME = 'gestion_ecole';
const DB_USER = 'root';
const DB_PASS = '';
const ECOLE   = 'Fondation Elfath';
const ECOLE_AR = 'مؤسسة الفتح عروي للتعليم الخصوصي';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
function q(string $sql, array $p = []): PDOStatement { $s = db()->prepare($sql); $s->execute($p); return $s; }
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function check_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403); exit('Requête invalide (CSRF).');
    }
}
function flash(string $msg, string $type = 'ok'): void { $_SESSION['flash'] = [$msg, $type]; }
function redirect(string $to): never { header('Location: ' . $to); exit; }
function user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void { if (!user()) redirect('login.php'); }
function require_role(string ...$roles): void {
    require_login();
    if (!in_array(user()['role'], $roles, true)) { http_response_code(403); exit('Accès refusé.'); }
}
function annee_courante(): ?array { return q('SELECT * FROM annees_scolaires WHERE courante=1 LIMIT 1')->fetch() ?: null; }
function post(string $k): string { return trim($_POST[$k] ?? ''); }
function err_msg(Throwable $x): string {
    if ($x instanceof PDOException) {
        return $x->getCode() === '23000'
            ? 'Opération impossible : donnée déjà existante ou liée à d’autres enregistrements.'
            : 'Erreur base de données.';
    }
    return $x->getMessage();
}
function money($v): string { return number_format((float)$v, 2, ',', ' ') . ' DH'; }
