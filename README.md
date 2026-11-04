<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Pengujian Manual RBAC — Jobsheet 7

### Prasyarat

- Migrasi database sudah dijalankan.
- DemoUserSeeder sudah dijalankan.
- Server aplikasi dan Vite sudah berjalan.
- Logout sebelum berganti akun.

### Akun Demo Lokal

| Peran | Email | Password |
|---|---|---|
| Admin | admin@pos.test | password |
| Kasir | kasir@pos.test | password |
| Manager | manager@pos.test | password |

### Skenario 1 — Admin Mengakses Produk

Langkah pengujian:

1. Buka halaman `/login`.
2. Masukkan email `admin@pos.test` dan password `password`.
3. Klik Masuk.
4. Buka `/products` melalui address bar.

Hasil yang diharapkan:

Halaman daftar produk tampil normal tanpa respons 403.

### Skenario 2 — Kasir Ditolak Mengakses Produk

Langkah pengujian:

1. Logout dari akun sebelumnya.
2. Login menggunakan `kasir@pos.test` dan password `password`.
3. Buka `/products` melalui address bar.
4. Setelah memeriksa penolakan akses, buka `/pos`.

Hasil yang diharapkan:

- `/products` menampilkan 403 dengan pesan
  "Halaman ini hanya untuk peran admin."
- `/pos` tetap dapat dibuka oleh kasir.

### Skenario 3 — Manager Mengakses Transaksi

Langkah pengujian:

1. Logout dari akun sebelumnya.
2. Login menggunakan `manager@pos.test` dan password `password`.
3. Buka `/transactions`.
4. Buka `/products` melalui address bar.

Hasil yang diharapkan:

- `/transactions` menampilkan daftar transaksi.
- `/products` menampilkan 403 dengan pesan
  "Halaman ini hanya untuk peran admin."

## Perbandingan: Import Manual vs Laravel Excel

| Aspek | Import Manual (`fopen` / `str_getcsv`) | Laravel Excel (`ToModel`, `WithHeadingRow`, `WithChunkReading`) |
|---|---|---|
| **Keringkasan Kode** | Butuh banyak baris untuk validasi kolom, handling heading array, pemotongan baris, dan penanganan loop manual. | **Jauh lebih ringkas**. Pemisahan baris per chunk dan pembacaan heading ditangani otomatis via interface. |
| **Kemudahan Kontrol** | **Jauh lebih mudah dikontrol**. Developer memiliki kendali penuh atas error handling per baris (`continue`), pelaporan jumlah baris sukses/gagal, validasi manual (`Validator::make`), dan manipulasi struktur array. | Kurang fleksibel jika butuh skip row kondisional tanpa melempar exception, atau saat mencatat row-row spesifik yang gagal masuk ke database. |

**Kesimpulan:**
- **Laravel Excel** unggul untuk kemudahan dan keringkasan kode pada dataset standar/bersih.
- **Import Manual** lebih tepat jika sistem membutuhkan kontrol ketat terhadap validasi spesifik tiap baris, pelaporan error kustom, dan mekanisme penanganan baris cacat tanpa menghentikan proses import.



## Challenge 5 Jobsheet 9— Proses Lain yang Layak Menggunakan Queue

### Proses yang Dipilih
Pembuatan dan pengiriman laporan penjualan otomatis melalui email.

### Alasan Menggunakan Queue

Pada aplikasi Simple POS, admin dapat melihat dan
mengekspor laporan penjualan dalam format CSV, Excel,
dan PDF.

Ketika jumlah transaksi semakin banyak, pembuatan
laporan dapat membutuhkan waktu pemrosesan yang lama.

Proses ini layak menggunakan Laravel Queue karena:

1. Beban proses
   Pembuatan laporan melibatkan pengambilan data
   transaksi, perhitungan total penjualan, dan
   pembuatan file laporan.

2. Waktu tunggu pengguna
   Dengan queue, admin tidak perlu menunggu proses
   pembuatan dan pengiriman laporan selesai dalam
   satu HTTP request.

3. Mekanisme retry
   Jika pengiriman email gagal akibat gangguan
   jaringan atau layanan email, job dapat dicoba
   kembali secara otomatis.

### Rancangan Implementasi

Proses dapat dibuat menggunakan job Laravel bernama
GenerateAndSendSalesReport.

Alur yang diusulkan:

1. Admin meminta laporan penjualan.
2. Aplikasi memvalidasi periode laporan.
3. Sistem mengirim job ke database queue.
4. Queue worker mengambil dan memproses job.
5. Sistem menghasilkan file laporan.
6. Laporan dikirim ke email admin.
7. Jika gagal, Laravel menjalankan retry sesuai
   konfigurasi job.

### Kesimpulan

Pembuatan dan pengiriman laporan penjualan otomatis
merupakan kandidat yang sesuai untuk Laravel Queue
karena dapat mengurangi waktu tunggu pengguna,
memindahkan proses berat ke background, dan
mendukung retry ketika terjadi kegagalan.

Implementasi ini merupakan usulan pengembangan
Simple POS dan belum diterapkan pada aplikasi.
