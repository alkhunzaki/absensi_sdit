# Panduan Migrasi ke Shared Hosting (cPanel / DirectAdmin)

Gunakan panduan ini untuk memindahkan website Anda dari Vercel/Lokal ke Hosting baru agar aksesnya sangat cepat.

## Langkah 1: Persiapan Database
1. Masuk ke **cPanel** hosting baru Anda.
2. Cari menu **MySQL® Databases**.
3. Buat database baru (contoh: `u123_absensi`).
4. Buat user database baru dan buat password yang kuat.
5. Tambahkan user tersebut ke database dengan akses **ALL PRIVILEGES**.
6. Simpan informasi berikut: `Nama Database`, `Username Database`, dan `Password`.

## Langkah 2: Impor Data (SQL)
1. Cari menu **phpMyAdmin** di hosting Anda.
2. Pilih nama database yang baru dibuat di sebelah kiri.
3. Klik tab **Import** di bagian atas.
4. Pilih file [database_dump.sql](database_dump.sql) dari folder project Anda.
5. Klik **Go** atau **Import** di bagian bawah.

## Langkah 3: Upload File
1. Masuk ke **File Manager** di cPanel.
2. Masuk ke folder `public_html`.
3. Upload seluruh file project Anda ke dalam folder tersebut.
   *Tip: Compress dulu folder project Anda menjadi .zip di komputer, upload zip-nya, lalu extract di File Manager.*

## Langkah 4: Konfigurasi Terakhir
1. Di dalam File Manager, cari file `config.php`.
2. Klik kanan > **Edit**.
3. Sesuaikan baris 18-21 dengan data hosting Anda:
   ```php
   $db_host = 'localhost'; // Biasanya localhost di hosting
   $db_user = 'u123_user'; // Username database Anda
   $db_pass = 'PasswordAnda'; // Password database Anda
   $db_name = 'u123_absensi'; // Nama database Anda
   ```
4. Klik **Save Changes**.

## Langkah 5: Selesai
Akses website Anda di domain Anda (contoh: `sekolahanda.com`). Website sekarang sudah berjalan di server lokal Indonesia dengan kecepatan maksimal!

---
*Catatan: Jika Anda menggunakan SSL (HTTPS), website akan otomatis menyesuaikan pengaturannya.*
