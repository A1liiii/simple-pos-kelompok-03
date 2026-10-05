<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <--- Ditambahkan

class Product extends Model
{
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(
            Transaction::class,
            'transaction_details',
            'product_id',
            'transaction_id'
        );
    } // <--- Ditambahkan kurung kurawal tutup
}