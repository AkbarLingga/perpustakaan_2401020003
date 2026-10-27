<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku Perpustakaan</title>
</head>
<body>
    <h1>Data Buku Perpustakaan</h1>

    @if (session('success'))
        <p role="status">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p role="alert">{{ session('error') }}</p>
    @endif

    @if ($errors->any())
        <div role="alert">
            <p>Data belum dapat diproses:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h2>Tambah Buku</h2>
    <form method="POST" action="{{ route('buku.store') }}">
        @csrf

        <p>
            <label for="kode_buku">Kode buku</label>
            <input id="kode_buku" name="kode_buku" type="text" maxlength="10" value="{{ old('kode_buku') }}" required>
        </p>
        <p>
            <label for="judul">Judul</label>
            <input id="judul" name="judul" type="text" maxlength="100" value="{{ old('judul') }}" required>
        </p>
        <p>
            <label for="isbn">ISBN</label>
            <input id="isbn" name="isbn" type="text" maxlength="13" value="{{ old('isbn') }}" required>
        </p>
        <p>
            <label for="tahun_terbit">Tahun terbit</label>
            <input id="tahun_terbit" name="tahun_terbit" type="number" min="2000" max="2026" value="{{ old('tahun_terbit') }}" required>
        </p>
        <p>
            <label for="kategori_buku_id">Kategori</label>
            <select id="kategori_buku_id" name="kategori_buku_id" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}" @selected(old('kategori_buku_id') == $category['id'])>
                        {{ $category['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Simpan buku</button>
    </form>

    <h2>Cari dan Filter Buku</h2>
    <form method="GET" action="{{ route('buku.index') }}">
        <p>
            <label for="search">Cari kode, judul, atau ISBN</label>
            <input id="search" name="search" type="search" value="{{ $search }}">
        </p>
        <p>
            <label for="filter_kategori_buku_id">Filter kategori</label>
            <select id="filter_kategori_buku_id" name="kategori_buku_id">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}" @selected((string) $selectedCategory === (string) $category['id'])>
                        {{ $category['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Terapkan</button>
        <a href="{{ route('buku.index') }}">Hapus pencarian dan filter</a>
    </form>

    <h2>Daftar Buku</h2>
    <p>Nama tabel master: <code>kategori_buku</code></p>
    <table>
        <thead>
            <tr>
                <th scope="col">Kode</th>
                <th scope="col">Judul</th>
                <th scope="col">ISBN</th>
                <th scope="col">Tahun terbit</th>
                <th scope="col">Status tahun</th>
                <th scope="col">Kategori</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($books as $book)
                <tr>
                    <td>{{ $book['kode_buku'] }}</td>
                    <td>{{ $book['judul'] }}</td>
                    <td>{{ $book['isbn'] }}</td>
                    <td>{{ $book['tahun_terbit'] }}</td>
                    <td>
                        @if (statusTahunBuku($book['tahun_terbit']) === 'Baru')
                            Baru
                        @else
                            Lama
                        @endif
                    </td>
                    <td>{{ $book['nama_kategori'] }}</td>
                    <td>
                        <a href="{{ route('buku.edit', $book['id']) }}">Edit</a>
                        <form method="POST" action="{{ route('buku.destroy', $book['id']) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Tidak ada buku yang sesuai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
