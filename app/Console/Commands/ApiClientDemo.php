<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ApiClientDemo extends Command
{
    protected $signature = 'api:demo';

    protected $description = 'Demonstrate consuming the Simple POS API as an external client';

    public function handle(): int
    {
        $baseUrl = 'http://127.0.0.1:8001';

        $this->info('Mencoba login dengan kata sandi yang salah...');

        // Simulasi login gagal dengan password salah
        $login = Http::acceptJson()->post("{$baseUrl}/api/v1/login", [
            'email' => 'kasir@pos.test',
            'password' => 'password-salah',
        ]);

        // Verifikasi login gagal: cegah eksekusi lanjutan dengan token kosong/null
        if ($login->failed()) {
            $this->error('Login gagal: '.$login->json('message'));
            $this->warn('Eksekusi dihentikan. Klien tidak melanjutkan dengan token kosong.');

            return self::FAILURE;
        }

        $token = $login->json('token');
        $this->info('Login berhasil.');

        $products = Http::acceptJson()
            ->withToken($token)
            ->get("{$baseUrl}/api/v1/products");

        if ($products->failed()) {
            $this->error('Token kedaluwarsa/dicabut, atau server sedang bermasalah.');

            return self::FAILURE;
        }

        $this->info('Jumlah produk: '.count($products->json('data')));

        $product = Product::first();

        if (!$product) {
            $this->error('Tidak ada produk untuk transaksi demo.');

            return self::FAILURE;
        }

        $key = (string) Str::uuid();

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 1,
                ],
            ],
        ];

        $transaction = Http::acceptJson()
            ->withToken($token)
            ->withHeaders([
                'Idempotency-Key' => $key,
            ])
            ->post("{$baseUrl}/api/v1/transactions", $payload);

        if ($transaction->status() !== 201) {
            $this->error(
                'Transaksi gagal: '.$transaction->status()
                .' - '.$transaction->json('message')
            );

            return self::FAILURE;
        }

        $this->info(
            'Transaksi tersimpan: '.json_encode($transaction->json('data'))
        );

        $replay = Http::acceptJson()
            ->withToken($token)
            ->withHeaders([
                'Idempotency-Key' => $key,
            ])
            ->post("{$baseUrl}/api/v1/transactions", $payload);

        if (
            $replay->status() !== 201 ||
            $replay->header('Idempotent-Replayed') !== 'true' ||
            $replay->json('data.id') !== $transaction->json('data.id')
        ) {
            $this->error('Replay idempotency gagal.');

            return self::FAILURE;
        }

        $this->info(
            'Replay transaksi: '.$replay->status()
            .', replayed = true'
        );

        return self::SUCCESS;
    }
}