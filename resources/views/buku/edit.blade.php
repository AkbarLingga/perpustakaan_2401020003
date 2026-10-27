<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Buku</title>
</head>
<body>
    <h1>Ubah Data Buku</h1>

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

    <form method="POST" action="{{ route('buku.update', $book['id']) }}">
        @csrf
        @method('PUT')

        <p>
            <label for="kode_buku">Kode buku</label>
            <input id="kode_buku" name="kode_buku" type="text" maxlength="10" value="{{ old('kode_buku', $book['kode_buku']) }}" required>
        </p>
        <p>
            <label for="judul">Judul</label>
            <input id="judul" name="judul" type="text" maxlength="100" value="{{ old('judul', $book['judul']) }}" required>
        </p>
        <p>
            <label for="isbn">ISBN</label>
            <input id="isbn" name="isbn" type="text" maxlength="13" value="{{ old('isbn', $book['isbn']) }}" required>
        </p>
        <p>
            <label for="tahun_terbit">Tahun terbit</label>
            <input id="tahun_terbit" name="tahun_terbit" type="number" min="2000" max="2026" value="{{ old('tahun_terbit', $book['tahun_terbit']) }}" required>
        </p>
        <p>
            <label for="kategori_buku_id">Kategori</label>
            <select id="kategori_buku_id" name="kategori_buku_id" required>
                @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}" @selected((string) old('kategori_buku_id', $book['kategori_buku_id']) === (string) $category['id'])>
                        {{ $category['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Simpan perubahan</button>
    </form>

    <p><a href="{{ route('buku.index') }}">Kembali ke daftar buku</a></p>
</body>
</html>
