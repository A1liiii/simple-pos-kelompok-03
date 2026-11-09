<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (!is_string($key) || trim($key) === '') {
            return response()->json([
                'message' => 'Idempotency-Key wajib diisi.',
            ], 400);
        }

        if (strlen($key) > 255) {
            return response()->json([
                'message' => 'Idempotency-Key terlalu panjang.',
            ], 400);
        }

        $userId = $request->user()->id;

        $payload = $request->all();
        $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));

        return DB::transaction(function () use (
            $request, $next, $key, $userId, $hash
        ) {
            // Serialisasi request dengan key yang sama per pengguna.
            // Pada SQLite, transaksi write membantu mencegah dua
            // request paralel membuat transaksi ganda.
            IdempotencyKey::query()
                ->where('user_id', $userId)
                ->where('key', $key)
                ->first();

            $record = IdempotencyKey::query()
                ->where('user_id', $userId)
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if ($record) {
                if ($record->request_hash !== $hash) {
                    return response()->json([
                        'message' => 'Key sudah dipakai untuk payload berbeda.',
                    ], 422);
                }

                if ($record->response_status === null) {
                    return response()->json([
                        'message' => 'Request masih diproses.',
                    ], 409);
                }

                return response()->json(
                    $record->response_body,
                    $record->response_status
                )->header('Idempotent-Replayed', 'true');
            }

            $record = IdempotencyKey::create([
                'user_id' => $userId,
                'key' => $key,
                'request_hash' => $hash,
            ]);

            $response = $next($request);

            if ($response->getStatusCode() >= 500) {
                $record->delete();
                return $response;
            }

            $body = json_decode($response->getContent(), true);

            $record->update([
                'response_status' => $response->getStatusCode(),
                'response_body' => $body,
            ]);

            return $response;
        });
    }
}