<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PDO;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kategori_buku')->insertOrIgnore([
            ['nama_kategori' => 'Teknologi'],
            ['nama_kategori' => 'Kemaritiman'],
        ]);

        $pdo = DB::connection()->getPdo();
        $categoryStatement = $pdo->prepare(
            'SELECT id FROM kategori_buku WHERE nama_kategori = :nama_kategori'
        );
        $categoryIds = [];

        foreach (['Teknologi', 'Kemaritiman'] as $category) {
            $categoryStatement->bindValue(':nama_kategori', $category, PDO::PARAM_STR);
            $categoryStatement->execute();
            $categoryIds[$category] = (int) $categoryStatement->fetchColumn();
        }

        $books = [
            [
                'kode_buku' => 'BK00000001',
                'judul' => 'Pemrograman Web Dasar',
                'isbn' => '9786020000011',
                'tahun_terbit' => 2022,
                'kategori_buku_id' => $categoryIds['Teknologi'],
            ],
            [
                'kode_buku' => 'BK00000002',
                'judul' => 'Basis Data Praktis',
                'isbn' => '9786020000028',
                'tahun_terbit' => 2023,
                'kategori_buku_id' => $categoryIds['Teknologi'],
            ],
            [
                'kode_buku' => 'BK00000003',
                'judul' => 'Sistem Informasi Maritim',
                'isbn' => '9786020000035',
                'tahun_terbit' => 2024,
                'kategori_buku_id' => $categoryIds['Kemaritiman'],
            ],
        ];

        foreach ($books as $book) {
            DB::table('buku')->insertOrIgnore([
                ...$book,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
