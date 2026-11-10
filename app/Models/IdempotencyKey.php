<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class IdempotencyKey extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'key',
        'request_hash',
        'response_status',
        'response_body',
    ];

    /**
     * Tentukan kriteria record yang boleh dihapus otomatis.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subHours(24));
    }
}