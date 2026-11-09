<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->postJson('/api/transactions', $this->payload())
            ->assertUnauthorized();
    }

    public function test_token_without_ability_is_forbidden(): void
    {
        // 403: Token tanpa ability transactions:create ditolak
        Sanctum::actingAs(User::factory()->create(), ['products:read']);

        $this->postJson('/api/transactions', $this->payload(), ['Idempotency-Key' => 'k1'])
            ->assertForbidden();
    }

    public function test_invalid_items_are_rejected(): void
    {
        // 422: Validasi input gagal jika product_id tidak terdaftar
        Sanctum::actingAs(User::factory()->create(), ['transactions:create']);

        $this->postJson('/api/transactions', [
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

        $first = $this->postJson('/api/transactions', $payload, $headers)
            ->assertCreated()
            ->assertJsonPath('data.total', 30000);

        $this->postJson('/api/transactions', $payload, $headers)
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
        $this->getJson("/api/transactions/{$transaction->id}")
            ->assertForbidden();

        // Pemilik transaksi boleh melihat datanya sendiri (200)
        Sanctum::actingAs($owner, ['transactions:read']);
        $this->getJson("/api/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $transaction->id);
    }
}