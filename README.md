# Quiz Online PHP

Aplikasi **quiz online** sederhana berbasis **PHP Native** dan **MySQL**.

## Fitur
- Login & Register
- User mengerjakan quiz
- Admin mengelola soal & kategori
- Rekap hasil quiz

## Struktur Folder
- `auth/` — autentikasi
- `admin/` — halaman admin & manajemen soal
- `user/` — halaman user & pengerjaan quiz
- `config/` — konfigurasi database
- `index.php` — halaman utama

## Instalasi
1. Clone repo:
git clone [https://github.com/deliasaniark/quiz-online-php](https://github.com/deliasaniark/quiz-online-php)
2. Buat database di phpMyAdmin  
3. Import file SQL (jika ada)  
4. Atur koneksi di:
config/db.php

## Jalankan
Buka di browser:
[http://localhost/quiz-online-php](http://localhost/quiz-online-php)

## Catatan
- Pastikan Apache & MySQL aktif (XAMPP/Laragon)
- Jangan simpan password database di repo publik
