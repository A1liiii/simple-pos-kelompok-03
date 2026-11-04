# Pengukuran Memori: get() vs lazyById()

Dataset: sekitar 52.500 row pada tabel transactions.

| Cara baca data | Peak memory (MB) |
|---|---|
| `Transaction::lazyById()` | 70.2 MB |
| `Transaction::get()` | 128.5 MB |

### Kesimpulan kelompok:
Penggunaan `get()` memuat seluruh 52.500 record sekaligus ke dalam memori PHP dan menginstansiasi masing-masing baris menjadi model Eloquent secara bersamaan, sehingga konsumsi memori melonjak hingga 128.5 MB dan berisiko memicu `Allowed memory size exhausted` pada skala data yang lebih besar.

Sebaliknya, `lazyById()` membaca data secara bertahap (chunking berbasis ID) menggunakan generator/`LazyCollection`, memungkinkan Garbage Collector membebaskan memori baris yang telah diproses sehingga pemakaian memori puncak jauh lebih rendah (70.2 MB) dan relatif stabil.
