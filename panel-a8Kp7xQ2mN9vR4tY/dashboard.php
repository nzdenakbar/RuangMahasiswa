<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

require_admin();

$total = (int)$conn->query("SELECT COUNT(*) c FROM news")->fetch_assoc()['c'];
$published = (int)$conn->query("SELECT COUNT(*) c FROM news WHERE status='published'")->fetch_assoc()['c'];
$drafts = (int)$conn->query("SELECT COUNT(*) c FROM news WHERE status='draft'")->fetch_assoc()['c'];

$items = $conn->query("SELECT n.*, a.name author_name FROM news n JOIN admins a ON a.id = n.author_id ORDER BY n.created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Foto profil admin yang sedang login (untuk avatar di sidebar)
$adminId = (int)$_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT profile_image FROM admins WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $adminId);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();
$profileImage = $me['profile_image'] ?? null;

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin — Ruang Mahasiswa UPY</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="dashboard-body">

    <aside class="admin-sidebar">
        <a class="brand" href="../index.php">
            <img class="brand-logo" src="../assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta">
            <span>
                ruang<span class="brand-accent">mahasiswa</span>
                <small>UPY / ADMIN</small>
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
            <div class="sidebar-label">WORKSPACE</div>

            <a class="sidebar-link active" href="dashboard.php"><span>▦</span> Dashboard</a>
            <a class="sidebar-link" href="tambah-berita.php"><span>＋</span> Tulis informasi</a>

            <?php if (is_superadmin()): ?>
                <!-- Hanya tampil untuk akun utama (superadmin) -->
                <a class="sidebar-link" href="akun-admin.php"><span>♙</span> Kelola akun admin</a>
            <?php endif; ?>

            <a class="sidebar-link" href="profil.php"><span>◉</span> Kelola profil</a>
            <a class="sidebar-link" href="../index.php" target="_blank" rel="noopener"><span>↗</span> Lihat website</a>

            <div class="sidebar-bottom">
                <a class="sidebar-profile" href="profil.php" title="Kelola profil">
                    <div class="admin-avatar">
                        <?php if ($profileImage): ?>
                            <img src="../uploads/profiles/<?= rawurlencode(basename($profileImage)) ?>" alt="Foto profil">
                        <?php else: ?>
                            <?= e(strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div class="admin-user">
                        <strong><?= e($_SESSION['admin_name'] ?? 'Admin') ?></strong>
                        <small><?= is_superadmin() ? 'Akun utama' : 'Administrator' ?></small>
                    </div>
                </a>

                <a class="btn-logout" href="logout.php">Keluar</a>
            </div>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <span class="section-kicker">CONTENT MANAGEMENT / OVERVIEW</span>
                <h1>Dashboard</h1>
            </div>
            <a class="btn btn-primary" href="tambah-berita.php">＋ Tulis informasi</a>
        </header>

        <div class="admin-content">
            <?php if ($flash): ?>
                <div class="alert alert-success"><?= e($flash) ?></div>
            <?php endif; ?>

            <div class="welcome-panel">
                <div>
                    <span class="welcome-tag">RUANG MAHASISWA UPY</span>
                    <h2>Halo, <?= e($_SESSION['admin_name'] ?? 'Admin') ?>. 👋</h2>
                    <p>Kelola informasi yang ingin dibagikan kepada mahasiswa.</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <span>Total informasi</span>
                    <strong><?= $total ?></strong>
                    <small>Semua konten tersimpan</small>
                    <div class="stat-icon">▤</div>
                </div>
                <div class="stat-card">
                    <span>Dipublikasikan</span>
                    <strong><?= $published ?></strong>
                    <small>Tampil di website</small>
                    <div class="stat-icon stat-green">↗</div>
                </div>
                <div class="stat-card">
                    <span>Draft</span>
                    <strong><?= $drafts ?></strong>
                    <small>Belum ditampilkan</small>
                    <div class="stat-icon stat-orange">◷</div>
                </div>
            </div>

            <section class="admin-table-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Informasi terbaru</h2>
                        <p>Daftar konten yang dikelola di portal.</p>
                    </div>
                    <a class="text-link" href="tambah-berita.php">Tambah baru <span>↗</span></a>
                </div>

                <?php if (!$items): ?>
                    <div class="table-empty">
                        <span>▤</span>
                        <h3>Belum ada informasi</h3>
                        <p>Mulai dengan membuat berita atau pengumuman pertama.</p>
                        <a class="btn btn-primary" href="tambah-berita.php">＋ Buat informasi</a>
                    </div>
                <?php else: ?>
                    <div class="table-scroll">
                        <table class="admin-table admin-table-cards">
                            <thead>
                                <tr>
                                    <th>INFORMASI</th>
                                    <th>KATEGORI</th>
                                    <th>TANGGAL</th>
                                    <th>STATUS</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="cell-title" data-label="Informasi">
                                            <div class="table-title">
                                                <img src="<?= e($item['image'] ? '../uploads/' . rawurlencode($item['image']) : '../assets/img/news-placeholder.svg') ?>" alt="">
                                                <div>
                                                    <strong><?= e($item['title']) ?></strong>
                                                    <small>Oleh <?= e($item['author_name']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Kategori"><?= e($item['category']) ?></td>
                                        <td data-label="Tanggal"><?= date('d M Y', strtotime($item['published_at'] ?? $item['created_at'])) ?></td>
                                        <td data-label="Status">
                                            <span class="status status-<?= e($item['status']) ?>">
                                                <?= $item['status'] === 'published' ? 'Terbit' : 'Draft' ?>
                                            </span>
                                        </td>
                                        <td data-label="Aksi">
                                            <div class="table-actions">
                                                <a class="btn-action btn-action-edit"
                                                   href="edit-berita.php?id=<?= (int)$item['id'] ?>"
                                                   aria-label="Edit informasi <?= e($item['title']) ?>">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M12 20h9"/>
                                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                                    </svg>
                                                    Edit
                                                </a>

                                                <?php if ($item['status'] === 'published'): ?>
                                                    <a class="btn-action btn-action-view"
                                                       href="../detail-berita.php?id=<?= (int)$item['id'] ?>"
                                                       target="_blank" rel="noopener"
                                                       aria-label="Lihat informasi <?= e($item['title']) ?>">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                            <circle cx="12" cy="12" r="3"/>
                                                        </svg>
                                                        Lihat
                                                    </a>
                                                <?php endif; ?>

                                                <button type="button"
                                                        class="btn-action btn-action-delete"
                                                        data-delete-trigger
                                                        data-id="<?= (int)$item['id'] ?>"
                                                        data-name="<?= e($item['title']) ?>"
                                                        aria-label="Hapus informasi <?= e($item['title']) ?>">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <polyline points="3 6 5 6 21 6"/>
                                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                        <path d="M10 11v6M14 11v6"/>
                                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <p class="admin-footnote">Pastikan setiap informasi sudah diperiksa sebelum dipublikasikan.</p>
        </div>
    </main>

    <!-- MODAL KONFIRMASI HAPUS INFORMASI -->
    <div class="modal-overlay" id="deleteModal" aria-hidden="true">
        <div class="modal-box"
             role="alertdialog"
             aria-modal="true"
             aria-labelledby="deleteModalTitle"
             aria-describedby="deleteModalDesc">
            <div class="modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </div>

            <h3 id="deleteModalTitle">Apakah yakin ingin menghapus informasi ini?</h3>

            <p id="deleteModalDesc">
                <strong id="deleteModalName"></strong> akan dihapus
                secara permanen. Tindakan ini tidak bisa dibatalkan.
            </p>

            <form method="post" action="hapus-berita.php" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" id="deleteModalId" value="">

                <button type="button" class="btn btn-outline" data-modal-cancel>Tidak</button>
                <button type="submit" class="btn btn-danger">Yakin</button>
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

            if (e.key === 'Escape') { closeModal(); return; }

            // Fokus keyboard tetap di dalam kotak dialog
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