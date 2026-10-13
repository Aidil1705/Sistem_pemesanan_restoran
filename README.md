# talk caffe - Sistem Pemesanan Restoran

Aplikasi web native PHP 8.4 untuk operasional pemesanan restoran. Dibangun dengan PHP OOP, PDO MySQL, HTML, Bootstrap CDN, Composer, Dompdf, dan PHPUnit.

## Akun Awal

Database baru tidak lagi berisi akun atau data demo. Buat akun pegawai pertama secara manual di tabel `employees`, dengan password yang sudah dibuat menggunakan `password_hash()`.

## Menjalankan

1. Aktifkan Apache/MySQL Laragon, atau gunakan server PHP built-in.
2. Buka proyek di browser.
3. Database `pemesanan_restoran` otomatis dibuat dengan struktur tabel kosong saat halaman pertama kali diakses.

```bash
php -S 127.0.0.1:8000
```

## Fitur

- Login pegawai dengan `password_hash` dan `password_verify`.
- Dashboard status meja dan pesanan aktif.
- Form pemesanan dengan katalog menu yang hanya menampilkan item tersedia.
- Manajemen menu dan toggle visibilitas.
- Riwayat pemesanan dan ekspor PDF via Dompdf.
- PDO prepared statements dan logging error ke `logs/error.log`.
