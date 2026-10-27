CREATE DATABASE IF NOT EXISTS perpustakaan_2401020003
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE perpustakaan_2401020003;

CREATE TABLE kategori_buku (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE buku (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_buku CHAR(10) NOT NULL UNIQUE,
    judul VARCHAR(100) NOT NULL,
    isbn CHAR(13) NOT NULL UNIQUE,
    tahun_terbit SMALLINT UNSIGNED NOT NULL,
    kategori_buku_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX buku_kategori_buku_id_index (kategori_buku_id),
    CONSTRAINT buku_tahun_terbit_check
        CHECK (tahun_terbit BETWEEN 2000 AND 2026),
    CONSTRAINT buku_kategori_buku_id_foreign
        FOREIGN KEY (kategori_buku_id)
        REFERENCES kategori_buku (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO kategori_buku (nama_kategori) VALUES
    ('Teknologi'),
    ('Kemaritiman');

INSERT INTO buku
    (kode_buku, judul, isbn, tahun_terbit, kategori_buku_id, created_at, updated_at)
VALUES
    (
        'BK00000001',
        'Pemrograman Web Dasar',
        '9786020000011',
        2022,
        (SELECT id FROM kategori_buku WHERE nama_kategori = 'Teknologi'),
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    ),
    (
        'BK00000002',
        'Basis Data Praktis',
        '9786020000028',
        2023,
        (SELECT id FROM kategori_buku WHERE nama_kategori = 'Teknologi'),
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    ),
    (
        'BK00000003',
        'Sistem Informasi Maritim',
        '9786020000035',
        2024,
        (SELECT id FROM kategori_buku WHERE nama_kategori = 'Kemaritiman'),
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    );
