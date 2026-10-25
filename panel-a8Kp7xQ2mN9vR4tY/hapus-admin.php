
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

require_superadmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}

verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    $_SESSION['flash'] = 'ID akun tidak valid.';
    redirect('akun-admin.php');
}

$stmt = $conn->prepare(
    "SELECT id, role FROM admins WHERE id = ? LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account || $account['role'] !== 'admin') {
    $_SESSION['flash'] = 'Akun tidak ditemukan atau tidak boleh dihapus.';
    redirect('akun-admin.php');
}

// Jangan hapus akun yang masih menjadi penulis berita.
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM news WHERE author_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$usage = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ((int)$usage['total'] > 0) {
    $_SESSION['flash'] = 'Akun tidak dapat dihapus karena masih tercatat sebagai penulis berita. Pertahankan akun atau pindahkan kepemilikan berita terlebih dahulu.';
    redirect('akun-admin.php');
}

$stmt = $conn->prepare("DELETE FROM admins WHERE id = ? AND role = 'admin'");
$stmt->bind_param('i', $id);
$stmt->execute();
$deleted = $stmt->affected_rows;
$stmt->close();

$_SESSION['flash'] = $deleted === 1
    ? 'Akun admin berhasil dihapus.'
    : 'Akun tidak terhapus. Silakan muat ulang dan coba kembali.';

redirect('akun-admin.php');
