# Panduan Penggunaan talk caffe

Dokumen ini menjelaskan cara menjalankan dan menggunakan Sistem Pemesanan Restoran talk caffe.

## 1. Persiapan Sistem

Pastikan sudah tersedia:

- Laragon atau web server PHP.
- PHP 8.4 atau versi yang kompatibel.
- MySQL atau MariaDB.
- Composer dan dependensi pada folder `vendor/`.

### Menjalankan dengan Laragon

1. Simpan proyek di folder `C:\laragon\www\pemesanan_restoran`.
2. Jalankan Apache dan MySQL dari Laragon.
3. Pastikan konfigurasi database pada `config/database.php` sesuai dengan MySQL lokal.
4. Buka alamat proyek melalui Laragon, atau jalankan server PHP:

```bash
php -S 127.0.0.1:8000
```

5. Buka `http://127.0.0.1:8000` pada browser.

Database akan dibuat otomatis beserta struktur tabelnya saat aplikasi pertama kali dibuka. Schema tidak lagi mengisi data demo secara otomatis.

## 2. Membuat Akun Pegawai Pertama

Karena database baru tidak memiliki akun demo, buat akun pegawai pertama secara manual melalui MySQL.

Buat hash password terlebih dahulu:

```bash
php -r "echo password_hash('password-anda', PASSWORD_DEFAULT), PHP_EOL;"
```

Kemudian jalankan query berikut dan ganti nilai sesuai kebutuhan:

```sql
INSERT INTO employees (name, username, email, password_hash, role)
VALUES (
    'Nama Pegawai',
    'nama.username',
    'pegawai@example.com',
    'HASIL_HASH_PASSWORD',
    'Pegawai'
);
```

Setelah berhasil, gunakan email atau username dan password tersebut pada halaman login.

## 3. Login

1. Isi kolom **Email atau username**.
2. Isi kolom **Kata sandi**.
3. Klik **Masuk ke ruang kerja**.
4. Setelah berhasil login, aplikasi menampilkan dashboard.

Nama pegawai yang sedang login tampil pada bagian bawah sidebar. Gunakan tombol logout pada bagian tersebut untuk mengakhiri sesi.

## 4. Dashboard

Dashboard menampilkan:

- Jumlah meja kosong.
- Pesanan yang menunggu diproses.
- Pesanan yang sedang dibuat.
- Pesanan yang sudah selesai.
- Daftar status meja.
- Daftar pesanan aktif.

Klik meja yang memiliki pesanan untuk membuka detail pesanan. Dari panel detail, status pesanan dapat diubah menjadi:

- Menunggu.
- Sedang dibuat.
- Selesai.
- Kosong, setelah pesanan selesai.

## 5. Membuat Pesanan

1. Buka menu **Pemesanan**.
2. Isi nama pembeli.
3. Pilih meja yang masih kosong.
4. Gunakan kolom pencarian untuk mencari nama menu.
5. Gunakan filter **Semua**, **Makanan**, **Minuman**, atau **Camilan**.
6. Klik **Tambah** pada menu yang dipilih.
7. Atur jumlah item menggunakan tombol `+` dan `-` pada ringkasan pesanan.
8. Periksa subtotal dan total pesanan.
9. Klik **Simpan pesanan**.

Menu yang tidak tersedia tidak akan ditampilkan pada halaman pemesanan.

## 6. Mengelola Menu

1. Buka menu **Menu**.
2. Klik **Tambah menu**.
3. Isi nama menu, kategori, harga, dan deskripsi.
4. Pilih gambar dari komputer pada kolom **Gambar makanan**.
5. Format gambar yang didukung: JPG, PNG, WEBP, dan GIF.
6. Ukuran gambar maksimal 5 MB.
7. Aktifkan switch **Tampilkan di katalog pemesanan** jika menu ingin langsung tersedia untuk pesanan.
8. Klik **Simpan menu**.

Gambar yang berhasil diunggah disimpan pada folder `public/assets/` dan alamatnya dicatat pada database.

Pada tabel menu, gunakan tombol **Sembunyikan** untuk membuat menu tidak muncul pada halaman pemesanan. Gunakan tombol **Tampilkan** untuk mengaktifkannya kembali.

## 7. Riwayat Pemesanan

Buka menu **Riwayat** untuk melihat transaksi yang sudah tersimpan.

Filter yang tersedia:

- **Dari tanggal** untuk menentukan tanggal awal.
- **Sampai tanggal** untuk menentukan tanggal akhir.
- **Cari pesanan** untuk mencari nama pembeli atau nomor pesanan.

Klik **Filter** untuk menerapkan pencarian. Klik tombol reset untuk menghapus semua filter.

Klik **Ekspor PDF** untuk mengunduh laporan riwayat pemesanan.

## 8. Menambah Akun Pegawai

Jika sudah login sebagai pegawai, buka menu **Tambah akun**.

Isi:

- Nama lengkap.
- Username.
- Email.
- Kata sandi minimal 8 karakter.
- Jabatan.

Klik **Simpan akun**. Password akan disimpan dalam bentuk hash dan tidak disimpan sebagai teks biasa.

## 9. Struktur Folder Penting

- `index.php`: routing dan proses utama aplikasi.
- `classes/`: class autentikasi, database, menu, pesanan, dan laporan.
- `views/`: halaman tampilan aplikasi.
- `views/layout/header.php`: sidebar, header, dan style layout internal.
- `views/login.php`: halaman login.
- `public/assets/`: penyimpanan gambar menu dan logo.
- `schema.sql`: struktur tabel database tanpa data demo.
- `logs/error.log`: catatan error aplikasi.

## 10. Troubleshooting

### Login gagal

Pastikan akun sudah ada pada tabel `employees`, email atau username benar, dan password yang digunakan sesuai dengan password saat membuat hash.

### Data demo muncul kembali

Pastikan menggunakan `schema.sql` versi terbaru yang tidak memiliki perintah `INSERT` data demo. Data lama yang sudah tersimpan di database harus dihapus secara manual satu kali.

### Upload gambar gagal

Periksa hal berikut:

- Ukuran file tidak lebih dari 5 MB.
- Format file adalah JPG, PNG, WEBP, atau GIF.
- Folder `public/assets/` dapat ditulis oleh web server.

### Nomor pesanan duplikat

Nomor pesanan dibuat berdasarkan nomor terbesar yang tersimpan pada tabel `orders`. Pastikan tidak ada proses pembuatan pesanan ganda yang berjalan bersamaan.

### Database belum siap

Pastikan MySQL aktif, konfigurasi database benar, dan user database memiliki izin untuk membuat database serta tabel.
