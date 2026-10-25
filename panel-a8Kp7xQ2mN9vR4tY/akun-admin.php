<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

require_superadmin();

$error = '';
$success = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Foto profil admin yang sedang login (untuk avatar di sidebar)
$currentAdminId = (int)$_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT profile_image FROM admins WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $currentAdminId);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();
$profileImage = $me['profile_image'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim((string)($_POST['name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
        $error = 'Nama harus 3–100 karakter.';
    } elseif (!preg_match('/^[A-Za-z][A-Za-z0-9._-]{7,39}$/', $username)) {
        $error = 'Username harus 8–40 karakter, diawali huruf, dan hanya berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    ) {
        $error = 'Password minimal 8 karakter dan harus berisi huruf besar, huruf kecil, angka, serta simbol.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO admins (name, username, password_hash, role)
                 VALUES (?, ?, ?, 'admin')"
            );
            $stmt->bind_param('sss', $name, $username, $hash);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash'] = 'Akun admin berhasil dibuat.';
            redirect('akun-admin.php');
        } catch (mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                $error = 'Username sudah digunakan. Pilih username lain.';
            } else {
                error_log('Gagal membuat akun admin: ' . $e->getMessage());
                $error = 'Akun admin gagal dibuat. Silakan coba kembali.';
            }
        }
    }
}

$accounts = $conn->query(
    "SELECT id, name, username, role, created_at
     FROM admins
     ORDER BY role DESC, created_at DESC"
)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Akun Admin — UPY</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>

<body class="dashboard-body">

    <aside class="admin-sidebar">
        <a class="brand" href="../index.php">
            <img class="brand-logo" src="../assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta">
            <span>
                ruang<span class="brand-accent">mahasiswa</span>
                <small>UPY / PENGELOLAAN</small>
            </span>
        </a>

        <!-- Tombol garis tiga (hanya tampil di mobile, pojok kanan atas) -->
        <button type="button"
                class="admin-menu-toggle"
                id="adminMenuToggle"
                aria-label="Buka menu navigasi"
                aria-controls="adminNav"
                aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="admin-nav" id="adminNav" aria-label="Navigasi admin">
            <div class="sidebar-label">PENGELOLAAN</div>

            <a class="sidebar-link" href="dashboard.php"><span>▦</span> Dashboard</a>
            <a class="sidebar-link" href="tambah-berita.php"><span>＋</span> Tulis informasi</a>
            <a class="sidebar-link active" href="akun-admin.php"><span>♙</span> Kelola akun admin</a>
            <a class="sidebar-link" href="profil.php"><span>◉</span> Kelola profil</a>
            <a class="sidebar-link" href="../index.php" target="_blank" rel="noopener"><span>↗</span> Lihat website</a>

            <div class="sidebar-bottom">
                <a class="sidebar-profile" href="profil.php" title="Kelola profil">
                    <div class="admin-avatar">
                        <?php if ($profileImage): ?>
                            <img src="../uploads/profiles/<?= rawurlencode(basename($profileImage)) ?>" alt="Foto profil">
                        <?php else: ?>
                            <?= e(strtoupper(substr($_SESSION['admin_name'] ?? 'U', 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div class="admin-user">
                        <strong><?= e($_SESSION['admin_name'] ?? 'Akun utama') ?></strong>
                        <small>Akun utama</small>
                    </div>
                </a>

                <a class="btn-logout" href="logout.php">Keluar</a>
            </div>
        </nav>
    </aside>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <span class="section-kicker">AKSES KHUSUS / AKUN UTAMA</span>
                <h1>Kelola akun admin</h1>
            </div>

            <a class="btn btn-outline" href="dashboard.php">
                &larr; Dashboard
            </a>
        </header>

        <div class="admin-content">

            <div class="welcome-panel">
                <div>
                    <span class="welcome-tag">OTORITAS AKUN UTAMA</span>
                    <h2>Kelola akun administrator.</h2>
                    <p>
                        Buat akun admin baru, perbarui informasi akun,
                        atau hapus akun yang sudah tidak digunakan.
                    </p>
                </div>

                <div class="welcome-symbol">♙</div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <!-- FORM TAMBAH AKUN -->
            <section
                class="editor-panel"
                style="max-width:760px;margin-bottom:24px"
            >
                <div class="panel-heading">
                    <div>
                        <h2>Tambah akun admin</h2>
                        <p>
                            Buat akun untuk petugas yang akan mengelola informasi.
                        </p>
                    </div>
                </div>

                <form method="post" class="form-stack">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <label>
                        Nama lengkap
                        <input
                            type="text"
                            name="name"
                            minlength="3"
                            maxlength="100"
                            required
                        >
                    </label>

                    <label>
                        Username unik
                        <input
                            type="text"
                            name="username"
                            minlength="8"
                            maxlength="40"
                            pattern="[A-Za-z][A-Za-z0-9._-]{7,39}"
                            autocomplete="off"
                            required
                        >
                        <small class="field-help">
                            8–40 karakter, diawali huruf. Gunakan username unik.
                        </small>
                    </label>

                    <label>
                        Password
                        <input
                            type="password"
                            name="password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                        <small class="field-help">
                            Minimal 8 karakter, gabungkan huruf besar,
                            huruf kecil, angka, dan simbol.
                        </small>
                    </label>

                    <label>
                        Ulangi password
                        <input
                            type="password"
                            name="confirm_password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                    </label>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">
                            Buat akun admin &nearr;
                        </button>
                    </div>
                </form>
            </section>

            <!-- DAFTAR AKUN -->
            <section class="admin-table-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Daftar akun</h2>
                        <p>
                            Akun utama dan seluruh akun admin terdaftar.
                        </p>
                    </div>
                </div>

                <?php if (empty($accounts)): ?>
                    <div class="table-empty">
                        <span>♙</span>
                        <h3>Belum ada akun</h3>
                        <p>Silakan buat akun admin baru.</p>
                    </div>
                <?php else: ?>

                    <div class="table-scroll">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>NAMA</th>
                                    <th>USERNAME</th>
                                    <th>JENIS AKUN</th>
                                    <th>DIBUAT</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($accounts as $account): ?>
                                    <tr>
                                        <td>
                                            <div class="table-title">
                                                <div>
                                                    <strong>
                                                        <?= e($account['name']) ?>
                                                    </strong>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <?= e($account['username']) ?>
                                        </td>

                                        <td>
                                            <span class="status <?= $account['role'] === 'superadmin' ? 'status-published' : 'status-draft' ?>">
                                                <?= $account['role'] === 'superadmin' ? 'Akun utama' : 'Admin' ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= e(date(
                                                'd M Y',
                                                strtotime($account['created_at'])
                                            )) ?>
                                        </td>

                                        <td>
                                            <?php if ($account['role'] === 'admin'): ?>
                                                <div class="table-actions">
                                                    <a
                                                        class="btn-action btn-action-edit"
                                                        href="edit-admin.php?id=<?= (int)$account['id'] ?>"
                                                        aria-label="Edit akun <?= e($account['name']) ?>"
                                                    >
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M12 20h9"/>
                                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                                        </svg>
                                                        Edit
                                                    </a>

                                                    <button
                                                        type="button"
                                                        class="btn-action btn-action-delete"
                                                        data-delete-trigger
                                                        data-id="<?= (int)$account['id'] ?>"
                                                        data-name="<?= e($account['name']) ?>"
                                                        aria-label="Hapus akun <?= e($account['name']) ?>"
                                                    >
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <polyline points="3 6 5 6 21 6"/>
                                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                            <path d="M10 11v6M14 11v6"/>
                                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="field-help">
                                                    Akun utama dilindungi
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>
            </section>

            <p class="admin-footnote">
                Akun utama tidak dapat diedit atau dihapus melalui halaman ini.
                Penghapusan akun admin yang masih memiliki berita terkait
                akan ditolak oleh proses penghapusan.
            </p>

        </div>
    </main>

    <!-- MODAL KONFIRMASI HAPUS AKUN -->
    <div class="modal-overlay" id="deleteModal" aria-hidden="true">
        <div
            class="modal-box"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="deleteModalTitle"
            aria-describedby="deleteModalDesc"
        >
            <div class="modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </div>

            <h3 id="deleteModalTitle">Apakah yakin ingin menghapus akun?</h3>

            <p id="deleteModalDesc">
                Akun <strong id="deleteModalName"></strong> akan dihapus
                secara permanen. Tindakan ini tidak bisa dibatalkan.
            </p>

            <form method="post" action="hapus-admin.php" class="modal-actions">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >
                <input type="hidden" name="id" id="deleteModalId" value="">

                <button type="button" class="btn btn-outline" data-modal-cancel>
                    Tidak
                </button>

                <button type="submit" class="btn btn-danger">
                    Yakin
                </button>
            </form>
        </div>
    </div>

    <script>
    (function () {
        var toggle = document.getElementById('adminMenuToggle');
        var nav = document.getElementById('adminNav');
        if (!toggle || !nav) return;

        function setOpen(open) {
            nav.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
        }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            setOpen(!nav.classList.contains('is-open'));
        });

        // Klik di luar menu menutupnya
        document.addEventListener('click', function (e) {
            if (nav.classList.contains('is-open') && !nav.contains(e.target) && !toggle.contains(e.target)) {
                setOpen(false);
            }
        });

        // Tombol Esc menutup menu
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });

        // Klik salah satu menu menutup panel
        nav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () { setOpen(false); });
        });

        // Kembali ke layar lebar: pastikan panel tertutup
        var mq = window.matchMedia('(min-width: 761px)');
        var onChange = function (e) { if (e.matches) setOpen(false); };
        if (mq.addEventListener) { mq.addEventListener('change', onChange); }
        else if (mq.addListener) { mq.addListener(onChange); }
    })();
    </script>

    <script>
    (function () {
        var modal = document.getElementById('deleteModal');
        if (!modal) return;

        var nameEl = document.getElementById('deleteModalName');
        var idInput = document.getElementById('deleteModalId');
        var cancelBtn = modal.querySelector('[data-modal-cancel]');
        var confirmBtn = modal.querySelector('button[type="submit"]');
        var form = modal.querySelector('form');
        var lastTrigger = null;

        function openModal(trigger) {
            lastTrigger = trigger;
            nameEl.textContent = trigger.getAttribute('data-name') || '';
            idInput.value = trigger.getAttribute('data-id') || '';
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Yakin';

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            cancelBtn.focus(); // fokus awal ke "Tidak" agar lebih aman
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            if (lastTrigger) lastTrigger.focus();
        }

        document.querySelectorAll('[data-delete-trigger]').forEach(function (btn) {
            btn.addEventListener('click', function () { openModal(btn); });
        });

        cancelBtn.addEventListener('click', closeModal);

        // Klik area gelap di luar kotak = batal
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (!modal.classList.contains('is-open')) return;

            if (e.key === 'Escape') {
                closeModal();
                return;
            }

            // Fokus keyboard tetap berada di dalam kotak dialog
            if (e.key === 'Tab') {
                if (e.shiftKey && document.activeElement === cancelBtn) {
                    e.preventDefault();
                    confirmBtn.focus();
                } else if (!e.shiftKey && document.activeElement === confirmBtn) {
                    e.preventDefault();
                    cancelBtn.focus();
                }
            }
        });

        // Cegah klik ganda saat proses hapus berjalan
        form.addEventListener('submit', function () {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Menghapus...';
        });
    })();
    </script>

    <script src="../assets/js/script.js"></script>
</body>
</html>