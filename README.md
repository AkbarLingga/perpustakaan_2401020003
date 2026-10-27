# Sistem Data Buku Perpustakaan

Aplikasi Laravel untuk mengelola buku berdasarkan kategori, sesuai soal UTS
Praktikum Pemrograman Web Tipe A. Aplikasi menggunakan dua tabel bisnis:
`kategori_buku` sebagai tabel master dan `buku` sebagai tabel utama. Proyek
ini memakai PostgreSQL (`pgsql`) seperti koneksi lokal di `.env`, dan juga
menyediakan skrip SQL MySQL/MariaDB sesuai spesifikasi UTS.

## Spesifikasi

- `kategori_buku`: `id` BIGINT UNSIGNED primary key dan `nama_kategori`
  VARCHAR(100) NOT NULL UNIQUE.
- `buku`: `id` BIGINT UNSIGNED primary key, `kode_buku` CHAR(10) UNIQUE,
  `judul` VARCHAR(100), `isbn` CHAR(13) UNIQUE, `tahun_terbit` SMALLINT
  UNSIGNED, `kategori_buku_id` foreign key, serta `created_at` dan
  `updated_at`.
- Relasi satu-ke-banyak: satu kategori memiliki banyak buku. Foreign key
  menggunakan ON UPDATE CASCADE dan ON DELETE RESTRICT.
- Tahun terbit dibatasi ke rentang 2000–2026 oleh validasi aplikasi dan
  constraint database pada MySQL, MariaDB, dan PostgreSQL.
- Status tahun: `Baru` untuk tahun 2023 ke atas dan `Lama` untuk tahun
  sebelum 2023.
- Kategori data awal: Teknologi dan Kemaritiman.
- Data awal wajib: BK00000001 / Pemrograman Web Dasar / 9786020000011 / 2022
  / Teknologi; BK00000002 / Basis Data Praktis / 9786020000028 / 2023 /
  Teknologi; BK00000003 / Sistem Informasi Maritim / 9786020000035 / 2024 /
  Kemaritiman.

## Menjalankan aplikasi

1. Buat basis data MySQL atau MariaDB bernama `perpustakaan_2401020003`.
2. Salin `.env.example` menjadi `.env`, lalu sesuaikan koneksi database
   lokal. Jangan memasukkan `.env` ke repository.
3. Jalankan `composer install`.
4. Jalankan `php artisan key:generate`.
5. Jalankan `php artisan migrate --seed`.
6. Jalankan `php artisan serve`, lalu buka `/buku`.

Migrasi dan seeder Laravel dapat dijalankan pada PostgreSQL maupun
MySQL/MariaDB. Untuk PostgreSQL, buat database lebih dulu, lalu jalankan
`psql -d perpustakaan_2401020003 -f database/sql/perpustakaan_2401020003.pgsql.sql`.
Untuk MySQL/MariaDB, gunakan
[`database/sql/perpustakaan_2401020003.sql`](database/sql/perpustakaan_2401020003.sql).
Pilih migrasi + seeder **atau** skrip SQL untuk satu database, jangan jalankan
keduanya pada database yang sama.

## Route

| Method | URL | Kegunaan |
| --- | --- | --- |
| GET | `/buku` | Daftar, pencarian, dan filter kategori |
| POST | `/buku` | Tambah buku |
| GET | `/buku/{id}/edit` | Form ubah buku |
| PUT | `/buku/{id}` | Simpan perubahan |
| DELETE | `/buku/{id}` | Hapus buku |

Pencarian memeriksa `kode_buku`, `judul`, dan `isbn`. Form memakai CSRF,
method spoofing untuk PUT/DELETE, validasi server-side, sanitasi `trim` dan
`strip_tags`, serta nilai lama dan pesan kesalahan. Query aplikasi menggunakan
PDO dari koneksi Laravel dengan prepared statement; kesalahan database dicatat
dan pengguna hanya menerima pesan aman.

## Pengujian

Jalankan `php artisan test`. Pengujian memakai PostgreSQL terpisah bernama
`perpustakaan_2401020003_test` agar tidak menghapus data database aplikasi;
buat database tersebut dan pastikan pengguna PostgreSQL dari `.env` dapat
mengaksesnya.

## Git dan pengumpulan

File `.env` dikecualikan oleh `.gitignore`. Buat issue GitHub dan commit
terstruktur sesuai instruksi UTS setelah meninjau perubahan ini; belum ada
commit yang dibuat.
