# Challenge 4 - Query Listing Transaksi

Query yang diuji:

SELECT * FROM transactions
WHERE created_at BETWEEN datetime('now', '-7 days') AND datetime('now');

Hasil EXPLAIN QUERY PLAN:

SCAN transactions

Kesimpulan:

Query transaksi dalam rentang 7 hari terakhir belum menggunakan index yang relevan pada kolom created_at, sehingga SQLite masih melakukan pemindaian tabel transactions.