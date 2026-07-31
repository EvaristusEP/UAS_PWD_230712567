# Atmapotek — Aplikasi Apotek Online

Atmapotek adalah aplikasi web *full-stack* sederhana untuk mengelola katalog obat dan proses pemesanan obat secara online. Proyek ini dibangun sebagai demonstrasi alur e-commerce: dari registrasi pelanggan, pencarian produk, keranjang, checkout, hingga administrasi pesanan dan inventori.

> **Catatan:** Proyek ini dibuat untuk kebutuhan pembelajaran dan portfolio. Ini bukan layanan kesehatan atau apotek produksi.

## Fitur utama

### Pelanggan

- Registrasi dan login menggunakan username atau email.
- Melihat katalog obat beserta harga, kategori, stok, dan gambar produk.
- Mencari dan memfilter produk berdasarkan kategori.
- Menambahkan produk ke keranjang serta mengubah jumlahnya.
- Checkout dengan pilihan metode pembayaran.
- Melihat riwayat dan detail pesanan.
- Mengelola profil akun.

### Admin

- Dashboard ringkasan pengguna, produk, pesanan, dan pendapatan.
- Menambah, mengubah, serta menghapus data obat.
- Mengunggah gambar produk.
- Melihat detail pesanan dan memperbarui statusnya.

## Teknologi

- PHP 8+
- MySQL / MariaDB
- HTML5, CSS3, dan JavaScript vanilla
- Session PHP untuk autentikasi dan keranjang belanja

## Menjalankan secara lokal

### Prasyarat

- PHP 8 atau lebih baru
- MySQL atau MariaDB
- Web server lokal seperti XAMPP, Laragon, atau PHP built-in server

### Instalasi

1. Clone repository ini dan masuk ke folder proyek.

   ```bash
   git clone https://github.com/<username>/<repository>.git
   cd Tubes_PWD
   ```

2. Buat database dengan nama `tubes_pwd_apotek`.

   ```sql
   CREATE DATABASE tubes_pwd_apotek;
   ```

3. Import skema sekaligus data demo dari [`database.sql`](database.sql).

   ```bash
   mysql -u root -p tubes_pwd_apotek < database.sql
   ```

4. Sesuaikan kredensial MySQL pada [`config/database.php`](config/database.php) bila konfigurasi lokal Anda berbeda.

5. Jalankan aplikasi menggunakan salah satu cara berikut.

   - Letakkan folder proyek di `htdocs` (XAMPP) atau `www` (Laragon), lalu akses `http://localhost/Tubes_PWD/`.
   - Atau jalankan PHP built-in server dari folder proyek:

     ```bash
     php -S localhost:8000
     ```

     Kemudian buka `http://localhost:8000`.

## Akun demo

| Peran | Username | Password |
| --- | --- | --- |
| Admin | `admin` | `Admin123!` |
| Pelanggan | `pelanggan` | `User12345!` |

Kredensial ini hanya untuk lingkungan lokal dan sebaiknya diganti sebelum aplikasi dipublikasikan.

## Struktur proyek

```text
├── admin/       # Dashboard dan pengelolaan produk/pesanan admin
├── api/         # Endpoint AJAX untuk keranjang dan detail pesanan
├── assets/       # CSS dan JavaScript
├── auth/         # Registrasi, login, dan logout
├── config/       # Konfigurasi koneksi database
├── layout/       # Komponen header
├── uploads/      # Gambar produk
├── user/         # Halaman pelanggan: katalog, keranjang, pesanan, profil
├── database.sql  # Skema database dan data dummy
└── index.php     # Halaman katalog publik
```

## Data dummy

Ya. Aplikasi membutuhkan data awal untuk bisa diperagakan dengan baik. File `database.sql` telah menyediakan data dummy yang aman untuk demo, mencakup:

- akun admin dan pelanggan;
- beberapa produk obat dengan kategori, harga, dan stok;
- satu contoh pesanan beserta detailnya.

Jangan gunakan data demo maupun kredensial di atas untuk lingkungan produksi.

## Catatan portfolio

Fokus proyek ini adalah implementasi alur pemesanan, manajemen inventori, dan pemisahan akses pelanggan/admin. Untuk penggunaan produksi, perlu ditambahkan pengamanan dan kesiapan operasional lebih lanjut, seperti validasi input yang lebih ketat, prepared statements, proteksi CSRF, pengelolaan konfigurasi melalui environment variables, serta integrasi pembayaran yang sesungguhnya.

## Lisensi

Belum ditentukan. Tambahkan lisensi yang sesuai sebelum penggunaan atau distribusi lebih lanjut.
