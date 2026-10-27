<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->char('kode_buku', 10)->unique();
            $table->string('judul', 100);
            $table->char('isbn', 13)->unique();
            $table->unsignedSmallInteger('tahun_terbit');
            $table->foreignId('kategori_buku_id')
                ->constrained('kategori_buku')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamps();

            $table->index('kategori_buku_id');
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
            DB::statement(
                'ALTER TABLE buku ADD CONSTRAINT buku_tahun_terbit_check '
                .'CHECK (tahun_terbit BETWEEN 2000 AND 2026)'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
