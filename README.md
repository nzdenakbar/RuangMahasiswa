# Ruang Mahasiswa UPY — Sistem Informasi Kemahasiswaan

Website PHP + MySQL responsif untuk portal informasi lembaga kemahasiswaan Universitas PGRI Yogyakarta. Dapat diuji melalui XAMPP dan diedit di Visual Studio Code.

## Fitur
- Halaman publik responsif desktop, tablet, dan ponsel.
- Logo UPY resmi ditampilkan dari situs resmi UPY (perlu koneksi internet untuk memuat logo).
- Pencarian, filter kategori, dan halaman detail informasi.
- Dashboard admin: tambah, edit, hapus, draft, dan publikasi informasi beserta gambar/poster.
- Kategori: Kegiatan, Pengumuman, Prestasi, Hibah, Beasiswa, Lembaga Kemahasiswaan, Organisasi Mahasiswa, Informasi Umum, dan Lainnya.
- Validasi upload, prepared statements, escaping output, token CSRF, dan sesi admin.
- Tautan admin tidak ditampilkan di halaman publik.

## Menjalankan di XAMPP
1. Ekstrak folder `sistem-informasi-upy` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, pilih **Import**, lalu impor `database.sql`.
4. Buka `http://localhost/sistem-informasi-upy/`.
5. Halaman login admin lokal: `http://localhost/sistem-informasi-upy/panel-a8Kp7xQ2mN9vR4tY/`. Jangan membagikan alamat ini secara publik.

## Akun utama lembaga dan pembuatan akun admin

Sistem sekarang memiliki dua jenis akun:
- **Akun utama lembaga kemahasiswaan (`superadmin`)**: satu-satunya akun yang dapat membuat akun admin baru.
- **Admin (`admin`)**: dapat mengelola informasi website, tetapi tidak dapat membuat akun admin.

### Setup pertama kali (lokal)
1. Impor `database.sql` ke phpMyAdmin. Jika memakai database lama, jalankan `upgrade-akun-utama.sql` satu kali terlebih dahulu. Jangan jalankan migrasi itu jika kolom `role` sudah ada.
2. Buka `config/secret.php` dan salin nilai `UPY_MASTER_SETUP_KEY` yang panjang. File ini dikecualikan oleh `.gitignore`; jangan unggah atau membagikannya.
3. Buka `http://localhost/sistem-informasi-upy/setup-utama.php`. Masukkan kunci setup, nama penanggung jawab lembaga, username unik (8–40 karakter), dan password kuat minimal 16 karakter.
4. Setelah berhasil, login melalui `http://localhost/sistem-informasi-upy/panel-a8Kp7xQ2mN9vR4tY/`. Di dashboard akun utama akan muncul menu **Kelola akun admin** untuk membuat akun admin lainnya.
5. Setelah akun utama dibuat, `setup-utama.php` otomatis menolak pembuatan akun utama kedua. Hapus file `setup-utama.php` dan `config/secret.php` setelah selesai setup. Simpan kunci setup di tempat aman sebelum menghapus file jika mungkin dibutuhkan untuk instalasi ulang.

### Aturan keamanan akun
- Username harus unik, 8–40 karakter, diawali huruf; jangan gunakan `admin` saja.
- Password minimal 16 karakter dan harus berisi huruf besar, huruf kecil, angka, serta simbol. Password disimpan memakai hash PHP, bukan teks biasa.
- Akun admin biasa tidak bisa mengakses halaman pengelolaan akun admin meskipun membuka URL langsung. Pemeriksaan role dilakukan di server.
- URL acak hanya mengurangi kemudahan menebak lokasi halaman; bukan pengganti autentikasi, HTTPS, rate limiting, pembaruan server, dan pengamanan hosting.
- Jangan unggah `config/secret.php` atau password ke repositori publik. Jika memakai GitHub, pastikan file rahasia tidak ikut commit.

## Catatan arsitektur
Versi ini masih satu aplikasi PHP untuk pengujian lokal XAMPP. Pemisahan sebenarnya menjadi website publik di GitHub Pages dan server admin/API terpisah masih membutuhkan refactor. GitHub Pages tidak menjalankan PHP atau MySQL.

## Logo
Logo dimuat dari halaman resmi lambang UPY: https://www.upy.ac.id/tentang-kami/lambang-universitas-pgri-yogyakarta/


## Mengganti logo dan gambar halaman depan

Semua gambar halaman depan ada di folder `assets/img/` dan dapat diganti dari Visual Studio Code:

- `logo-lembaga.svg` — logo di sebelah tulisan “ruangmahasiswa” pada header, footer, dan area admin. Ganti file ini dengan logo sendiri; jika menggunakan PNG/JPG, ubah nilai `src` pada tag `brand-logo` di file PHP terkait agar mengarah ke nama file tersebut.
- `hero-kampus.svg` — gambar besar di samping teks utama.
- `kategori-kegiatan.svg` — gambar kotak Kegiatan.
- `kategori-pengumuman.svg` — gambar kotak Pengumuman.
- `kategori-prestasi.svg` — gambar kotak Prestasi.
- `kategori-artikel.svg` — gambar kotak Artikel.

Untuk mengganti dengan foto JPG/PNG, salin foto ke `assets/img/`, lalu ubah atribut `src` pada `index.php` sesuai nama foto. Contoh: `src="assets/img/foto-kegiatan.jpg"`. Bagian ilustrasi lingkaran biru-oranye pada hero sudah diganti dengan satu slot gambar lokal.
