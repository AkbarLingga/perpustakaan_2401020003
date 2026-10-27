<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

function bukuInput(array $overrides = []): array
{
    return array_merge([
        'kode_buku' => 'BK00000004',
        'judul' => 'Laravel untuk Pemula',
        'isbn' => '9786020000042',
        'tahun_terbit' => 2025,
        'kategori_buku_id' => DB::table('kategori_buku')
            ->where('nama_kategori', 'Teknologi')
            ->value('id'),
    ], $overrides);
}

function bukuId(string $kodeBuku): int
{
    return (int) DB::table('buku')->where('kode_buku', $kodeBuku)->value('id');
}

it('menampilkan tabel master dan tiga buku awal beserta status tahunnya', function () {
    $this->get('/buku')
        ->assertOk()
        ->assertSee('kategori_buku')
        ->assertSee('BK00000001')
        ->assertSee('BK00000002')
        ->assertSee('BK00000003')
        ->assertSee('Pemrograman Web Dasar')
        ->assertSee('Baru')
        ->assertSee('Lama');
});

it('menyimpan buku valid dan menampilkan feedback keberhasilan', function () {
    $this->post('/buku', bukuInput())
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('success', 'Buku berhasil ditambahkan.');

    $this->assertDatabaseHas('buku', [
        'kode_buku' => 'BK00000004',
        'judul' => 'Laravel untuk Pemula',
        'isbn' => '9786020000042',
        'tahun_terbit' => 2025,
    ]);
});

it('menolak input tidak valid dan mempertahankan input yang sudah disanitasi', function () {
    $this->from('/buku')->post('/buku', bukuInput([
        'kode_buku' => 'salah',
        'judul' => '<b>ab</b>',
        'isbn' => '123',
        'tahun_terbit' => 2027,
        'kategori_buku_id' => 999,
    ]))
        ->assertRedirect('/buku')
        ->assertSessionHasErrors([
            'kode_buku',
            'judul',
            'isbn',
            'tahun_terbit',
            'kategori_buku_id',
        ])
        ->assertSessionHas('_old_input', function (array $input): bool {
            return $input['judul'] === 'ab';
        });

    $this->assertDatabaseCount('buku', 3);
});

it('menolak kode buku atau ISBN yang sudah digunakan', function () {
    $this->post('/buku', bukuInput(['kode_buku' => 'BK00000001']))
        ->assertSessionHasErrors('kode_buku');

    $this->post('/buku', bukuInput(['isbn' => '9786020000011']))
        ->assertSessionHasErrors('isbn');

    $this->assertDatabaseCount('buku', 3);
});

it('menampilkan nilai lama pada form edit dan menyimpan perubahan', function () {
    $bukuId = bukuId('BK00000001');

    $this->get(route('buku.edit', $bukuId))
        ->assertOk()
        ->assertSee('value="BK00000001"', false)
        ->assertSee('value="Pemrograman Web Dasar"', false);

    $this->put(route('buku.update', $bukuId), bukuInput([
        'kode_buku' => 'BK00000011',
        'judul' => 'Pemrograman Laravel Dasar',
        'isbn' => '9786020000110',
    ]))
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('success', 'Buku berhasil diperbarui.');

    $this->assertDatabaseHas('buku', [
        'id' => $bukuId,
        'kode_buku' => 'BK00000011',
        'judul' => 'Pemrograman Laravel Dasar',
        'isbn' => '9786020000110',
    ]);
});

it('menolak update dengan kode atau ISBN milik buku lain', function () {
    $bukuId = bukuId('BK00000001');

    $this->put(route('buku.update', $bukuId), bukuInput(['kode_buku' => 'BK00000002']))
        ->assertSessionHasErrors('kode_buku');

    $this->put(route('buku.update', $bukuId), bukuInput(['isbn' => '9786020000028']))
        ->assertSessionHasErrors('isbn');

    $this->assertDatabaseHas('buku', [
        'id' => $bukuId,
        'kode_buku' => 'BK00000001',
        'isbn' => '9786020000011',
    ]);
});

it('menghapus buku melalui route DELETE', function () {
    $bukuId = bukuId('BK00000001');

    $this->delete(route('buku.destroy', $bukuId))
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('success', 'Buku berhasil dihapus.');

    $this->assertDatabaseMissing('buku', ['id' => $bukuId]);
});

it('mencari buku berdasarkan kode, judul, dan ISBN', function () {
    foreach (['BK00000001', 'Pemrograman Web Dasar', '9786020000011'] as $keyword) {
        $this->get(route('buku.index', ['search' => $keyword]))
            ->assertOk()
            ->assertSee('Pemrograman Web Dasar')
            ->assertDontSee('Basis Data Praktis')
            ->assertDontSee('Sistem Informasi Maritim');
    }
});

it('memfilter buku berdasarkan tabel master kategori', function () {
    $teknologiId = DB::table('kategori_buku')->where('nama_kategori', 'Teknologi')->value('id');
    $kemaritimanId = DB::table('kategori_buku')->where('nama_kategori', 'Kemaritiman')->value('id');

    $this->get(route('buku.index', ['kategori_buku_id' => $teknologiId]))
        ->assertOk()
        ->assertSee('Pemrograman Web Dasar')
        ->assertSee('Basis Data Praktis')
        ->assertDontSee('Sistem Informasi Maritim');

    $this->get(route('buku.index', ['kategori_buku_id' => $kemaritimanId]))
        ->assertOk()
        ->assertSee('Sistem Informasi Maritim')
        ->assertDontSee('Basis Data Praktis');
});

it('mencatat detail kegagalan database dan hanya menampilkan respons aman', function () {
    DB::shouldReceive('connection')
        ->once()
        ->andThrow(new PDOException('private database detail'));

    $this->get('/buku')
        ->assertServiceUnavailable()
        ->assertSee('Layanan data buku sedang tidak tersedia.')
        ->assertDontSee('private database detail');
});
