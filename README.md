# KasirKu — POS untuk UMKM Indonesia

KasirKu adalah aplikasi Point of Sale berbasis Laravel + Livewire yang dibuat untuk kebutuhan usaha mikro dan kecil seperti warung, toko kelontong, kafe kecil, dan kios retail. Fokus utamanya adalah kecepatan kasir, integritas stok, serta laporan yang mudah dibaca untuk owner.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)
![Livewire](https://img.shields.io/badge/Livewire-4-4E56A6?logo=livewire)
![Tailwind](https://img.shields.io/badge/Tailwind-3-38B2AC?logo=tailwindcss)
![Tests](https://img.shields.io/badge/Tests-PHPUnit-brightgreen)

---

## Ringkasan proyek

Proyek ini sudah dilengkapi dengan:
- autentikasi role-based owner/cashier dengan Breeze
- POS cashier interface untuk pencarian produk, keranjang, diskon, dan pembayaran
- transaksi penjualan yang aman memakai `DB::transaction` dan `lockForUpdate()`
- stok real-time dan void transaksi yang mengembalikan stok
- dashboard owner untuk KPI penjualan harian dan chart 7 hari
- laporan penjualan dengan filter tanggal dan export Excel/PDF
- akses owner/cashier dengan pembatasan role
- akun owner pertama yang dibuat dari konfigurasi deployment, tanpa akun/password demo

---

## Teknologi utama

- Laravel 12
- PHP 8.2+
- Laravel Breeze
- Livewire 4
- Tailwind CSS
- Alpine.js
- Chart.js
- barryvdh/laravel-dompdf
- maatwebsite/excel
- PHPUnit

---

## Persyaratan lingkungan

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8 (database utama)
- Laravel Sail (opsional)

---

## Setup lokal

1. Clone repositori
2. Install dependency PHP dan NodeJS
3. Copy `.env.example` ke `.env`, lalu isi koneksi MySQL lokal
4. Buat database MySQL `kasirku` dengan charset `utf8mb4`
5. Generate app key
6. Isi `BOOTSTRAP_OWNER_NAME`, `BOOTSTRAP_OWNER_EMAIL`, dan `BOOTSTRAP_OWNER_PASSWORD`
7. Jalankan migrasi dan seeder untuk membuat akun owner pertama
8. Build frontend assets

Contoh:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate

# Buat schema MySQL sesuai DB_DATABASE di .env, lalu isi BOOTSTRAP_OWNER_*
php artisan migrate --seed
npm run build
php artisan serve
```

Test otomatis memakai SQLite in-memory; aplikasi saat dijalankan menggunakan MySQL dari `.env`.

Setelah itu buka:
- `http://localhost:8000`

---

Seeder hanya membuat owner pertama jika belum ada. Seeder tidak menghapus data dan tidak membuat produk, transaksi, atau akun demo. Produk/kategori usaha dimasukkan melalui aplikasi setelah login.

## Hosting produksi

1. Siapkan PHP 8.2+, Composer, Node.js untuk build, database MySQL/PostgreSQL, dan HTTPS.
2. Atur document root domain ke folder `public` Laravel, bukan root repository.
3. Isi `.env` deployment: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` dengan HTTPS, `APP_TIMEZONE=Asia/Jakarta`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, kredensial database, mailer SMTP, dan tiga nilai `BOOTSTRAP_OWNER_*`.
4. Pastikan `ALLOW_REGISTRATION=false`; akun kasir dibuat oleh owner setelah masuk.
5. Jalankan saat deploy:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   php artisan migrate --force
   php artisan db:seed --force
   php artisan optimize
   ```

6. Simpan `.env` di luar direktori publik, aktifkan backup database terjadwal, pantau `storage/logs`, dan uji pemulihan backup.
7. Jangan jalankan `migrate:fresh` pada database produksi. Jangan gunakan `APP_DEBUG=true` di hosting.

Password reset membutuhkan mail transport SMTP yang dikonfigurasi; konfigurasi `MAIL_MAILER=log` hanya sesuai untuk development.

**QRIS dan transfer saat ini dicatat manual oleh kasir setelah pembayaran diterima/diperiksa.** Aplikasi belum terhubung ke payment gateway, jadi jangan menganggap transaksi otomatis terverifikasi oleh bank atau penyedia QRIS.

### Deploy ke Railway

Railway mendeteksi Laravel melalui Railpack; build Composer, build aset dari `package.json`, migrasi, dan seeding dijalankan oleh pipeline Laravel Railpack. Tidak ada akun demo atau seeder transaksi sintetis. Sebelum deploy:

1. Push repository ke GitHub dan buat project Railway dari repository tersebut.
2. Tambahkan service database MySQL Railway. Pada service aplikasi, set `DB_CONNECTION=mysql` dan `DB_URL` memakai private connection URL dari service database tersebut.
3. Set variabel aplikasi:
   - `APP_NAME=KasirKu`, `APP_ENV=production`, `APP_DEBUG=false`
   - `APP_KEY` dari `php artisan key:generate --show` (simpan sebagai Railway secret, jangan commit)
   - `APP_URL` ke domain HTTPS Railway yang di-generate
   - `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`
   - `ALLOW_REGISTRATION=false`
   - `BOOTSTRAP_OWNER_NAME`, `BOOTSTRAP_OWNER_EMAIL`, `BOOTSTRAP_OWNER_PASSWORD` (password minimal 12 karakter)
   - `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`
   - SMTP variables untuk reset password; `LOG_CHANNEL=stderr`
4. Deploy, pastikan proses migration dan pembuatan owner berhasil dari deployment logs, lalu tambahkan domain publik di Networking.
5. Uji login owner, buat kategori dan produk awal, buat akun kasir, uji satu transaksi dan void, lalu atur backup database terjadwal.

Jangan pasang domain publik pada database service. Referensi: [Railway Laravel deployment guide](https://docs.railway.com/guides/laravel) dan [Railpack PHP provider](https://railpack.com/languages/php/).

---

## Fitur utama

### Kasir
- pencarian produk by nama atau SKU
- filter kategori produk
- keranjang belanja dengan pengubahan qty
- diskon nominal atau persen
- metode pencatatan pembayaran: tunai, QRIS, transfer (QRIS/transfer diverifikasi manual oleh kasir)
- modal pembayaran dengan keypad
- tombol shortcut keyboard: `F2`, `F9`, `Esc`
- struk thermal untuk pencetakan

### Operasional pemilik
- kategori produk dapat dibuat, diubah, dan dihapus jika tidak sedang dipakai produk
- akun kasir dapat dibuat, diaktif/nonaktifkan, dan password-nya direset owner
- stok masuk, stok keluar, dan opname menggunakan transaksi terkunci serta catatan mutasi
- pembatalan transaksi hanya untuk owner, wajib menyertakan alasan, dan mengembalikan stok

### Pos & stock integrity
- transaksi dibungkus satu `DB::transaction`
- `lockForUpdate()` untuk mencegah overselling bersamaan
- invoice `INV-YYYYMMDD-XXXX` otomatis
- void transaksi mengembalikan stok dan mencatat aktivitas

### Owner dashboard
- total penjualan hari ini
- transaksi harian
- laba kotor
- perbandingan vs kemarin
- chart 7 hari
- produk terlaris
- stok menipis
- metode pembayaran dominan

### Laporan
- filter tanggal
- ringkasan total, diskon, laba, rata-rata transaksi
- export Excel
- export PDF

---

## Struktur penting

- `app/Services/SaleService.php` — logika penjualan dan void
- `app/Models/Product.php` — model produk dan scope stok
- `app/Models/Sale.php` — model transaksi penjualan
- `app/Livewire/Cashier/Index.php` — halaman kasir utama
- `app/Livewire/Dashboard/Index.php` — dashboard owner
- `app/Livewire/Reports/Index.php` — laporan + export

---

## Test

```bash
php artisan test
```

Saat ini suite yang berjalan sudah valid dan mencakup fitur utama login, profile, serta aturan bisnis transaksi.

---

## CI / GitHub Actions

Project sudah menyertakan workflow CI untuk menjalankan:
- dependency install
- composer validation
- npm install
- build frontend
- php artisan test

File workflow:
- `.github/workflows/ci.yml`

---

## Catatan desain

Desain dibuat dengan pendekatan POS khusus untuk UMKM:
- palet hijau-teal khas aplikasi kasir modern
- kontras tinggi untuk readability di layar kasir
- radius yang lebih tegas dan padat untuk elemen transaksi
- layout desktop sidebar + bottom nav mobile
- reduksi gerak untuk preferensi reduced motion

---

## Lisensi

MIT
