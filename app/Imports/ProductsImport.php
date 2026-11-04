<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        // Lewati row jika category_id atau price tidak valid
        if (empty($row['name']) || empty($row['category_id']) || !is_numeric($row['price'])) {
            return null;
        }

        return Product::updateOrCreate(
            [
                'name'        => $row['name'],
                'category_id' => $row['category_id'],
            ],
            [
                'price' => (int) $row['price'],
            ]
        );
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}