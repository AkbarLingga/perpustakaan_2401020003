<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDO;
use PDOException;
use PDOStatement;
use Symfony\Component\HttpFoundation\Response;

class BukuController extends Controller
{
    public function index(Request $request): Response
    {
        $this->sanitizeTextInputs($request, ['search']);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'kategori_buku_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = $filters['search'] ?? '';
        $kategoriBukuId = $filters['kategori_buku_id'] ?? null;

        try {
            $pdo = DB::connection()->getPdo();

            $categoryStatement = $pdo->prepare(
                'SELECT id, nama_kategori FROM kategori_buku ORDER BY nama_kategori'
            );
            $categoryStatement->execute();
            $categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

            $conditions = [];
            $bindings = [];

            if ($search !== '') {
                $conditions[] = '(b.kode_buku LIKE ? OR b.judul LIKE ? OR b.isbn LIKE ?)';
                $pattern = '%'.$search.'%';
                array_push($bindings, $pattern, $pattern, $pattern);
            }

            if ($kategoriBukuId !== null) {
                $conditions[] = 'b.kategori_buku_id = ?';
                $bindings[] = (int) $kategoriBukuId;
            }

            $sql = 'SELECT b.id, b.kode_buku, b.judul, b.isbn, b.tahun_terbit, '
                .'b.kategori_buku_id, k.nama_kategori '
                .'FROM buku b INNER JOIN kategori_buku k ON k.id = b.kategori_buku_id';

            if ($conditions !== []) {
                $sql .= ' WHERE '.implode(' AND ', $conditions);
            }

            $sql .= ' ORDER BY b.id';

            $bookStatement = $pdo->prepare($sql);
            $bookStatement->execute($bindings);
            $books = $bookStatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            report($exception);

            return response(
                'Layanan data buku sedang tidak tersedia. Silakan coba kembali.',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        return response()->view('buku.index', [
            'books' => $books,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $kategoriBukuId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $book = $this->validatedBook($request);
            $statement = DB::connection()->getPdo()->prepare(
                'INSERT INTO buku '
                .'(kode_buku, judul, isbn, tahun_terbit, kategori_buku_id, created_at, updated_at) '
                .'VALUES (:kode_buku, :judul, :isbn, :tahun_terbit, :kategori_buku_id, '
                .'CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
            );
            $this->bindBookValues($statement, $book);
            $statement->execute();
        } catch (PDOException $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Data buku gagal disimpan. Silakan coba kembali.');
        }

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function edit(int $buku): Response
    {
        try {
            $pdo = DB::connection()->getPdo();
            $bookStatement = $pdo->prepare(
                'SELECT id, kode_buku, judul, isbn, tahun_terbit, kategori_buku_id '
                .'FROM buku WHERE id = :id'
            );
            $bookStatement->bindValue(':id', $buku, PDO::PARAM_INT);
            $bookStatement->execute();
            $book = $bookStatement->fetch(PDO::FETCH_ASSOC);

            abort_if($book === false, Response::HTTP_NOT_FOUND);

            $categoryStatement = $pdo->prepare(
                'SELECT id, nama_kategori FROM kategori_buku ORDER BY nama_kategori'
            );
            $categoryStatement->execute();
            $categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            report($exception);

            return response(
                'Layanan data buku sedang tidak tersedia. Silakan coba kembali.',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        return response()->view('buku.edit', [
            'book' => $book,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $buku): RedirectResponse
    {
        try {
            $book = $this->validatedBook($request, $buku);
            $pdo = DB::connection()->getPdo();

            $existsStatement = $pdo->prepare('SELECT id FROM buku WHERE id = :id');
            $existsStatement->bindValue(':id', $buku, PDO::PARAM_INT);
            $existsStatement->execute();
            abort_if($existsStatement->fetchColumn() === false, Response::HTTP_NOT_FOUND);

            $statement = $pdo->prepare(
                'UPDATE buku SET kode_buku = :kode_buku, judul = :judul, isbn = :isbn, '
                .'tahun_terbit = :tahun_terbit, kategori_buku_id = :kategori_buku_id, '
                .'updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $this->bindBookValues($statement, $book);
            $statement->bindValue(':id', $buku, PDO::PARAM_INT);
            $statement->execute();
        } catch (PDOException $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Perubahan buku gagal disimpan. Silakan coba kembali.');
        }

        return redirect()->route('buku.index')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(int $buku): RedirectResponse
    {
        try {
            $statement = DB::connection()->getPdo()->prepare('DELETE FROM buku WHERE id = :id');
            $statement->bindValue(':id', $buku, PDO::PARAM_INT);
            $statement->execute();

            abort_if($statement->rowCount() === 0, Response::HTTP_NOT_FOUND);
        } catch (PDOException $exception) {
            report($exception);

            return back()->with('error', 'Buku gagal dihapus. Silakan coba kembali.');
        }

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus.');
    }

    /**
     * @return array{kode_buku: string, judul: string, isbn: string, tahun_terbit: int, kategori_buku_id: int}
     */
    private function validatedBook(Request $request, ?int $ignoreId = null): array
    {
        $this->sanitizeTextInputs($request, ['kode_buku', 'judul', 'isbn']);

        $kodeBukuRule = Rule::unique('buku', 'kode_buku');
        $isbnRule = Rule::unique('buku', 'isbn');

        if ($ignoreId !== null) {
            $kodeBukuRule->ignore($ignoreId);
            $isbnRule->ignore($ignoreId);
        }

        return $request->validate([
            'kode_buku' => [
                'required',
                'string',
                'size:10',
                'regex:/\A[A-Za-z0-9]{10}\z/',
                $kodeBukuRule,
            ],
            'judul' => ['required', 'string', 'min:3', 'max:100'],
            'isbn' => ['required', 'string', 'size:13', 'regex:/\A[0-9]{13}\z/', $isbnRule],
            'tahun_terbit' => ['required', 'integer', 'between:2000,2026'],
            'kategori_buku_id' => ['required', 'integer', 'exists:kategori_buku,id'],
        ], [
            'kode_buku.size' => 'Kode buku harus tepat 10 karakter.',
            'kode_buku.regex' => 'Kode buku hanya boleh berisi huruf dan angka.',
            'kode_buku.unique' => 'Kode buku sudah digunakan.',
            'judul.min' => 'Judul buku harus terdiri dari 3 sampai 100 karakter.',
            'isbn.size' => 'ISBN harus tepat 13 digit.',
            'isbn.regex' => 'ISBN hanya boleh berisi 13 digit.',
            'isbn.unique' => 'ISBN sudah digunakan.',
            'tahun_terbit.between' => 'Tahun terbit harus antara 2000 dan 2026.',
            'kategori_buku_id.exists' => 'Kategori buku yang dipilih tidak tersedia.',
        ]);
    }

    /**
     * @param  array{kode_buku: string, judul: string, isbn: string, tahun_terbit: int, kategori_buku_id: int}  $book
     */
    private function bindBookValues(PDOStatement $statement, array $book): void
    {
        $statement->bindValue(':kode_buku', $book['kode_buku'], PDO::PARAM_STR);
        $statement->bindValue(':judul', $book['judul'], PDO::PARAM_STR);
        $statement->bindValue(':isbn', $book['isbn'], PDO::PARAM_STR);
        $statement->bindValue(':tahun_terbit', (int) $book['tahun_terbit'], PDO::PARAM_INT);
        $statement->bindValue(':kategori_buku_id', (int) $book['kategori_buku_id'], PDO::PARAM_INT);
    }

    /**
     * @param  list<string>  $fields
     */
    private function sanitizeTextInputs(Request $request, array $fields): void
    {
        foreach ($fields as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $request->merge([$field => trim(strip_tags($value))]);
            }
        }
    }
}
