<?php

if (! function_exists('statusTahunBuku')) {
    function statusTahunBuku(int|string $tahunTerbit): string
    {
        return (int) $tahunTerbit >= 2023 ? 'Baru' : 'Lama';
    }
}
