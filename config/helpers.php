<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function is_admin(): bool {
    return isset($_SESSION['admin_id']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function is_superadmin(): bool {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin';
}

function require_superadmin(): void {
    require_admin();
    if (!is_superadmin()) {
        http_response_code(403);
        exit('Akses ditolak. Hanya akun utama lembaga kemahasiswaan yang dapat mengelola akun admin.');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Silakan kembali dan coba lagi.');
    }
}

function excerpt(string $text, int $length = 135): string {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '…';
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
