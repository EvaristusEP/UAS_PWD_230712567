# Apotek Online - Tubes PWD

Aplikasi web untuk apotek online dengan fitur manajemen obat dan pemesanan.

## Struktur Project

```
Tubes_PWD/
├── index.php                 # Landing page (katalog obat)
├── config/
│   └── database.php          # Konfigurasi database & helper functions
├── auth/
│   ├── login.php             # Halaman login
│   ├── register.php          # Halaman registrasi
│   └── logout.php            # Logout handler
├── api/
│   ├── check_user.php        # Validasi username/email (AJAX)
│   ├── add_to_cart.php       # Tambah ke keranjang (AJAX)
│   └── get_order_detail.php  # Detail pesanan (AJAX)
├── admin/
│   ├── index.php             # Dashboard admin
│   ├── medicines.php         # CRUD obat
│   └── orders.php            # Manajemen pesanan
├── user/
│   ├── index.php             # Katalog obat user
│   ├── cart.php              # Keranjang belanja
│   ├── orders.php            # Daftar pesanan
│   ├── order-detail.php      # Detail pesanan
│   └── profile.php           # Profil user
├── layout/
│   ├── indexHeader.html      # Header landing page
│   ├── adminHeader.html      # Header admin
│   └── userheader.php        # Header user
├── assets/
│   ├── css/                  # Stylesheet files
│   ├── js/                   # JavaScript files
│   └── img/                  # Gambar static
└── uploads/
    └── profiles/             # Foto profil user

## Database

Database: `tubes_pwd_apotek`

Tables:
- users (id, username, email, password, full_name, address, phone, role, created_at)
- medicines (id, name, description, price, stock, category, created_at)
- orders (id, user_id, order_date, total_price, payment_method, status)
- order_details (id, order_id, medicine_id, quantity, price_at_purchase)

## Role System

1. **Admin**
   - Dashboard statistik
   - CRUD obat
   - Manajemen pesanan

2. **User**
   - Browse & search obat
   - Keranjang belanja
   - Checkout pesanan
   - Riwayat pesanan
   - Update profil

## Features

- ✅ Authentication & Authorization
- ✅ AJAX real-time validation
- ✅ Shopping cart system
- ✅ Order management
- ✅ Toast notifications
- ✅ Modal popups
- ✅ Responsive design
- ✅ Fixed navbar

## Setup

1. Import database SQL
2. Update config/database.php sesuai environment
3. Pastikan folder uploads/ writable
4. Akses via localhost atau web server

## Tech Stack

- PHP (Native)
- MySQL
- JavaScript (ES6+)
- CSS3
- Fetch API (AJAX)

## Security Notes

- SQL injection protection menggunakan mysqli_real_escape_string
- Session-based authentication
- Role-based access control
- Password hashing (jika sudah implementasi)

## Development

Untuk development, gunakan:
- XAMPP/WAMP/LAMP
- PHP 7.4+
- MySQL 5.7+

---
Developed by Alfito - Atma Jaya Yogyakarta
