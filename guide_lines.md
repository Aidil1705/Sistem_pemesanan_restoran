# Guidelines Pengembangan talk caffe

Dokumen ini berisi pedoman penulisan dan pengembangan source code Sistem Pemesanan Restoran talk caffe.

## 1. Struktur Proyek

Gunakan pembagian tanggung jawab berikut:

- `index.php`: entry point, routing halaman, dan pemrosesan action form.
- `classes/`: class untuk autentikasi, database, menu, meja, pelanggan, pesanan, dan laporan.
- `views/`: template tampilan HTML dan Bootstrap.
- `views/layout/`: layout bersama seperti header, sidebar, dan footer.
- `config/`: konfigurasi koneksi database.
- `public/assets/`: logo dan gambar menu yang diunggah.
- `tests/`: pengujian unit PHPUnit.
- `logs/`: pencatatan error aplikasi.

## 2. Pemrograman Terstruktur

Gunakan alur program yang jelas dan terurut:

- Gunakan `if`, `else`, `switch`, dan `match` untuk percabangan.
- Gunakan `foreach` untuk memproses kumpulan data.
- Gunakan fungsi kecil dengan satu tanggung jawab.
- Hindari pengulangan kode yang tidak diperlukan.
- Gunakan validasi sebelum data diproses atau disimpan.
- Gunakan nama variabel yang menjelaskan isi datanya.

Contoh perulangan pada `views/menu.php`:

```php
<?php foreach ($menus as $menu): ?>
    <tr>
        <td><?= e($menu['name']) ?></td>
        <td><?= e($menu['category']) ?></td>
    </tr>
<?php endforeach; ?>
```

Alurnya adalah mengambil data menu satu per satu, menampilkan data, lalu mengulanginya sampai seluruh menu selesai diproses.

## 3. Pemrograman Berorientasi Objek

Gunakan class sesuai tanggung jawabnya:

- `Auth`: login, session, pembuatan akun, dan logout.
- `Database`: koneksi dan migrasi database.
- `MenuItem`: data menu dan ketersediaan menu.
- `Customer`: data pelanggan.
- `Order`: pembuatan pesanan, status, riwayat, dan aktivitas.
- `Report`: ekspor laporan ke PDF.

Setiap class harus:

- Memiliki tanggung jawab yang jelas.
- Menggunakan method dengan nama yang deskriptif.
- Menggunakan visibility `private`, `protected`, atau `public` sesuai kebutuhan.
- Memiliki PHPDoc untuk class dan method penting.
- Tidak mencampurkan logika tampilan dengan logika database.

## 4. Penamaan Kode

Gunakan konvensi berikut:

- Class: `PascalCase`, contoh `MenuItem`.
- Method dan variabel: `camelCase`, contoh `createEmployee`.
- Nama tabel dan kolom database: `snake_case`, contoh `order_items`.
- Konstanta: `UPPER_SNAKE_CASE` jika diperlukan.
- Gunakan nama yang bermakna dan hindari nama satu huruf.

## 5. Database

- Gunakan PDO untuk koneksi database.
- Gunakan prepared statement untuk query yang menerima input pengguna.
- Jangan menyusun query menggunakan penggabungan input mentah.
- Gunakan transaksi untuk proses yang terdiri dari beberapa insert atau update.
- Pastikan relasi foreign key tetap valid.
- Jangan menambahkan data demo otomatis ke `schema.sql`.
- Gunakan `schema.sql` untuk struktur tabel, bukan data produksi.

## 6. Validasi dan Keamanan Input

- Validasi semua data dari `$_POST`, `$_GET`, dan `$_FILES`.
- Gunakan `trim()` untuk input teks.
- Gunakan `filter_var()` untuk validasi email.
- Gunakan `e()` saat menampilkan data ke HTML.
- Gunakan `password_hash()` saat menyimpan password.
- Gunakan `password_verify()` saat login.
- Jangan menampilkan password atau data sensitif pada halaman.
- Untuk upload file, validasi ukuran, MIME type, dan lokasi penyimpanan.
- Gunakan nama file acak untuk mencegah benturan nama dan penyalahgunaan path.

## 7. Aturan Upload Gambar

Upload gambar menu harus mengikuti aturan berikut:

- Format yang diterima: JPG, PNG, WEBP, dan GIF.
- Ukuran maksimum: 5 MB.
- Simpan file di `public/assets/`.
- Jangan menggunakan nama file asli sebagai nama penyimpanan.
- Simpan path relatif file ke database.
- Tampilkan gambar menggunakan `e()` untuk menghindari masalah output HTML.

## 8. Frontend dan Bootstrap

- Gunakan Bootstrap 5 melalui CDN yang sudah dipakai proyek.
- Gunakan class Bootstrap seperti `container`, `row`, `col-*`, `card`, `btn`, `form-control`, dan `table`.
- Gunakan class custom hanya jika komponen Bootstrap tidak mencukupi.
- Pertahankan tampilan responsif untuk desktop dan mobile.
- Gunakan icon Lucide yang sudah tersedia.
- Pisahkan struktur HTML ke beberapa baris agar mudah dibaca.
- Hindari menulis satu blok HTML/PHP yang terlalu panjang dalam satu baris.

## 9. Form dan Routing

- Setiap form harus memiliki `method` dan `action` yang jelas.
- Gunakan hidden input `action` untuk membedakan proses form.
- Pertahankan nama field yang sudah digunakan backend.
- Tambahkan `enctype="multipart/form-data"` untuk form upload file.
- Setelah proses berhasil, gunakan redirect untuk mencegah submit ulang saat refresh.
- Tampilkan pesan error yang mudah dipahami pengguna.

## 10. Penanganan Error

- Gunakan `try-catch` pada proses yang berhubungan dengan database atau file.
- Catat error teknis di `logs/error.log`.
- Tampilkan pesan yang aman dan mudah dipahami pengguna.
- Jangan menampilkan query database atau credential kepada pengguna.
- Setelah memperbaiki error, lakukan pengecekan sintaks PHP.

## 11. Dokumentasi

Setiap fitur baru sebaiknya memiliki:

- Penjelasan singkat di `panduan.md` jika berhubungan dengan penggunaan.
- PHPDoc pada class atau method baru.
- Nama variabel dan method yang jelas.
- Catatan validasi atau batasan fitur jika diperlukan.

## 12. Pengujian

Sebelum perubahan dianggap selesai:

1. Jalankan lint PHP pada file yang diubah:

```bash
php -l nama-file.php
```

2. Pastikan tidak ada error pada VS Code.
3. Uji alur utama melalui browser.
4. Jalankan PHPUnit pada saat diperlukan:

```bash
php vendor/bin/phpunit tests
```

5. Pastikan fungsi lama tetap berjalan setelah perubahan tampilan atau fitur.

## 13. Checklist Sebelum Selesai

- [ ] Source code sudah memiliki indentasi yang rapi.
- [ ] Tidak ada kode penting yang ditulis dalam satu baris terlalu panjang.
- [ ] Input pengguna sudah divalidasi.
- [ ] Query database menggunakan prepared statement.
- [ ] Output HTML menggunakan escaping.
- [ ] Form upload menggunakan validasi file.
- [ ] Tampilan tetap responsif.
- [ ] PHP lint berhasil.
- [ ] Fitur yang sudah ada tidak rusak.
- [ ] Dokumentasi sudah diperbarui jika diperlukan.
