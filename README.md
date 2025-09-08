<h1 align="center">Hi 👋, I'm Fikar Dwi Ramadhani</h1>
<p align="center">
  <a href="https://github.com/InstaCodex/"><img src="https://img.shields.io/badge/-Github-FFA116?style=for-the-badge&logo=Github&logoColor=black"/> </a>
</p>

# 🍽️ Restoran Digital – Sistem Pemesanan dan Admin Panel

Aplikasi restoran digital dengan pemilihan meja, menu digital bergambar, manajemen pesanan, dan notifikasi WhatsApp (otomatis via WhatsApp Cloud API atau manual via tautan WhatsApp).

## 🚀 Fitur Utama

### 👥 Pelanggan
- **Pilih Meja**: Memilih meja yang tersedia.
- **Menu Digital**: Lihat menu dan harga, tambah ke keranjang.
- **Buat Pesanan**: Pesanan tercatat dan dapat dikirim ke WhatsApp.

### ⚙️ Admin
- **Kategori, Menu, Meja**: CRUD lengkap.
- **Upload Gambar Menu**: Input file (bukan URL) dengan validasi ukuran/format.
- **Kelola Pesanan**: Lihat dan ubah status pesanan.
- **Notifikasi WhatsApp**: Otomatis via Cloud API jika dikonfigurasi; fallback manual via tautan.

## 🧭 Diagram Alur

```
Pelanggan (menu.php)
    └─ Pilih meja ──> API select_table
    └─ Pilih menu + jumlah
    └─ Buat pesanan ──> API create_pesanan ──> DB: pesanan + detail_pesanan
                                   └─ (opsional) tampilkan konfirmasi

Admin (admin.php)
    └─ Login
    └─ Kelola kategori/menu/meja (CRUD)
          └─ Menu: unggah gambar -> simpan di /uploads
    └─ Lihat pesanan
    └─ Ubah status ──> API update_status_pesanan ──> DB update
                                 └─ Kirim WA otomatis (Cloud API) atau kembalikan URL wa.me (fallback)
```

## 📁 Struktur Proyek

```
produk/
├── admin.php            # Admin panel (form multipart untuk upload gambar)
├── api.php              # REST API (JSON)
├── config.php           # Konfigurasi aplikasi & WhatsApp
├── database.php         # Koneksi & query PDO
├── index.php            # Landing page
├── js/
│   └── api.js          # Client API helper (fetch JSON)
├── login.php, logout.php
├── menu.php             # Halaman pelanggan (pesan)
├── order_confirmation.php
├── setup_database.php   # Inisialisasi DB & seed (hapus setelah dipakai)
└── README.md
```

## 🛠️ Setup Cepat

1) Konfigurasi database (opsional, default cocok untuk XAMPP):
   - `config.php` sudah menyediakan default; `database.php` juga meng-guard konstanta `DB_*`.

2) Buat database & tabel:
- Jalankan di browser: `http://localhost/produk/setup_database.php`
- Pastikan sukses, lalu HAPUS file `setup_database.php` untuk keamanan.

3) Konfigurasi WhatsApp:
- Manual (default): set `WHATSAPP_NUMBER` di `config.php` (dipakai pada alur pelanggan). Notifikasi status pelanggan memakai nomor pelanggan yang diinput saat pesanan.
- Otomatis (opsional): isi `WHATSAPP_CLOUD_PHONE_NUMBER_ID` dan `WHATSAPP_CLOUD_ACCESS_TOKEN`. Jika diisi, server mengirim pesan otomatis via Cloud API saat status pesanan di-update. Jika kosong/gagal, fallback ke URL `wa.me`.

## 🎯 Cara Penggunaan

### Pelanggan
1. Buka `index.php` → klik “Mulai Pesan” ke `menu.php`.
2. Pilih meja, pilih menu, atur jumlah, buat pesanan.
3. Ikuti instruksi konfirmasi (opsional WhatsApp jika diperlukan).

### Admin
1. Buka `admin.php` dan login.
2. Kelola kategori, menu (unggah gambar file), meja.
3. Buka tab Pesanan → ubah status. Sistem akan:
   - Mengirim notifikasi otomatis via Cloud API (jika dikonfigurasi), atau
   - Mengembalikan URL `wa.me` yang otomatis dibuka sebagai fallback.

## 📊 Skema Database (aktual)

- `kategori`
  - id (INT UNSIGNED, PK, AI)
  - nama (VARCHAR, UNIQUE)
  - deskripsi (TEXT, NULL)
  - created_at (TIMESTAMP)

- `menu`
  - id (INT UNSIGNED, PK, AI)
  - nama (VARCHAR)
  - deskripsi (TEXT, NULL)
  - harga (INT UNSIGNED)
  - gambar (VARCHAR, default '')
  - kategori_id (INT UNSIGNED, FK -> kategori.id)
  - status (ENUM: 'tersedia','habis')
  - created_at (TIMESTAMP)

- `meja`
  - id (INT UNSIGNED, PK, AI)
  - nomor (INT UNSIGNED, UNIQUE)
  - kapasitas (INT UNSIGNED, default 4)
  - status (ENUM: 'tersedia','terisi','nonaktif')
  - created_at (TIMESTAMP)

- `pesanan`
  - id (INT UNSIGNED, PK, AI)
  - meja_id (INT UNSIGNED, FK -> meja.id)
  - nomor_pelanggan (VARCHAR(20), NULL)
  - total (INT UNSIGNED)
  - status (ENUM: 'pending','dikonfirmasi','diproses','selesai','dibatalkan')
  - created_at (TIMESTAMP)

- `detail_pesanan`
  - id (INT UNSIGNED, PK, AI)
  - pesanan_id (INT UNSIGNED, FK -> pesanan.id, CASCADE delete)
  - menu_id (INT UNSIGNED, FK -> menu.id)
  - quantity (INT UNSIGNED)
  - harga (INT UNSIGNED)
  - subtotal (INT UNSIGNED)

- `users`
  - id, username (UNIQUE), password (hash), nama, email (NULL), role ('admin'|'kasir'), status ('active'|'inactive'), created_at

## 🔧 API (aktual singkat)

- Kategori: `get_kategori`, `create_kategori` (POST), `update_kategori` (PUT), `delete_kategori` (DELETE)
- Menu: `get_menu`, `create_menu` (POST), `update_menu` (PUT), `delete_menu` (DELETE), `get_menu_by_kategori`
- Meja: `get_meja`, `create_meja` (POST), `update_meja` (PUT), `delete_meja` (DELETE)
- Pesanan: `get_pesanan`, `get_pesanan_detail`, `create_pesanan` (POST), `update_status_pesanan` (PUT)
- Auth: `login` (POST), `logout` (POST), `check_auth`

Semua endpoint melalui `api.php?action=...` dan mengembalikan JSON.

## 🖼️ Upload Gambar Menu

- Form di `admin.php` menggunakan `enctype="multipart/form-data"` dan input `type=file`.
- File disimpan di folder `uploads/` (dibuat otomatis) dengan validasi format (jpg, jpeg, png, gif) dan ukuran maks 2MB.
- Saat edit, jika tidak pilih file baru, gambar lama dipertahankan.

## 🧩 Catatan Teknis

- Koneksi DB via PDO, prepared statements, fetch associative.
- `config.php` dimuat sebelum `database.php` di `api.php` agar kredensial WA tersedia.
- Jika kredensial Cloud API kosong/gagal, sistem fallback ke URL `wa.me`.

## 🚀 Jalankan Lokal (XAMPP)

1. Salin folder ke `C:/xampp/htdocs/produk`.
2. Start Apache + MySQL.
3. Buka `http://localhost/produk/setup_database.php` → pastikan sukses → hapus file setup.
4. Buka `http://localhost/produk/index.php` (pelanggan) atau `http://localhost/produk/admin.php` (admin).
5. Login dengan username : admin dan password : admin123

---

<h1>Demo Aplikasi</h1>
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/front-1.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/front-2.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/front-3.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/front-4.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/front-5.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/back-1.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/back-2.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/back-3.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/back-4.png" alt="Profile Image" width="100%">
<img src="https://raw.githubusercontent.com/InstaCodex/InstaCodex/refs/heads/main/Assets/back-5.png" alt="Profile Image" width="100%">




