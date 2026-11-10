<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTransactionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        $category = Category::forceCreate(['name' => 'Minuman']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Kopi',
            'price' => 15000,
            'stock' => 10,
        ]);

        return ['items' => [['product_id' => $product->id, 'qty' => 2]]];
    }

    public function test_guest_cannot_create_transaction(): void
    {
        // 401: Guest tanpa token tidak boleh membuat transaksi
        $this->postJson('/api/v1/transactions', $this->payload())
            ->assertUnauthorized();
    }

    public function test_token_without_ability_is_forbidden(): void
    {
        // 403: Token tanpa ability transactions:create ditolak
        Sanctum::actingAs(User::factory()->create(), ['products:read']);

        $this->postJson('/api/v1/transactions', $this->payload(), ['Idempotency-Key' => 'k1'])
            ->assertForbidden();
    }

    public function test_invalid_items_are_rejected(): void
    {
        // 422: Validasi input gagal jika product_id tidak terdaftar
        Sanctum::actingAs(User::factory()->create(), ['transactions:create']);

        $this->postJson('/api/v1/transactions', [
            'items' => [['product_id' => 99999, 'qty' => 1]],
        ], ['Idempotency-Key' => 'k2'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items.0.product_id');
    }

    public function test_transaction_is_created_once_even_when_repeated(): void
    {
        // 201: Idempotency memastikan transaksi kedua memutar ulang response pertama
        Sanctum::actingAs(User::factory()->create(), ['transactions:create']);

        $payload = $this->payload();
        $headers = ['Idempotency-Key' => 'k3'];

        $first = $this->postJson('/api/v1/transactions', $payload, $headers)
            ->assertCreated()
            ->assertJsonPath('data.total', 30000);

        $this->postJson('/api/v1/transactions', $payload, $headers)
            ->assertCreated()
            ->assertHeader('Idempotent-Replayed', 'true')
            ->assertJsonPath('data.id', $first->json('data.id'));

        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_cashier_cannot_view_another_cashiers_transaction(): void
    {
        // Policy: Kasir lain dilarang melihat detail transaksi (403)
        $owner = User::factory()->create();
        $transaction = Transaction::create(['user_id' => $owner->id, 'total' => 1000]);

        Sanctum::actingAs(User::factory()->create(), ['transactions:read']);
        $this->getJson("/api/v1/transactions/{$transaction->id}")
            ->assertForbidden();

        // Pemilik transaksi boleh melihat datanya sendiri (200)
        Sanctum::actingAs($owner, ['transactions:read']);
        $this->getJson("/api/v1/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $transaction->id);
    }

        public function test_expired_token_returns_unauthorized(): void
    {
        // 1. Atur masa kedaluwarsa token Sanctum menjadi 60 menit
        config(['sanctum.expiration' => 60]);

        // 2. Kunci waktu awal pembuatan token
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));

        $user = User::factory()->create();
        $token = $user->createToken('test', ['transactions:create'])->plainTextToken;

        // Gunakan Sanctum::actingAs sesuai instruksi jobsheet
        Sanctum::actingAs($user, ['transactions:create']);

        // 3. Majukan waktu melampaui batas kedaluwarsa (2 jam kemudian)
        Carbon::setTestNow(Carbon::parse('2026-01-01 12:00:00'));

        // Reset mock guard agar request mengecek token asli yang sudah expired
        auth()->forgetGuards();

        // 4. Request dengan token yang sudah kedaluwarsa harus mengembalikan 401
        $this->withToken($token)
            ->postJson('/api/v1/transactions', $this->payload(), ['Idempotency-Key' => 'k-expired'])
            ->assertUnauthorized();

        // 5. Kembalikan waktu Carbon ke normal
        Carbon::setTestNow();
    }
}