<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function initialize(Request $request)
    {
        $validated = $request->validate([
            'ride_id' => ['required', 'integer', 'exists:rides,id'],
        ]);

        $ride = Ride::findOrFail($validated['ride_id']);

        // Allow initializing payment for accepted/in-progress/completed rides with positive fare and pending payment
        if (! in_array($ride->status, ['accepted', 'in_progress', 'completed'], true) || (float) $ride->fare <= 0 || $ride->payment_status !== 'pending') {
            return back()->with('error', 'Payment cannot be initialized for this ride.');
        }

        // Update ride payment method to paystack now
        $ride->update(['payment_method' => 'paystack']);

        // Create or update payment with unique reference
        $reference = 'PAY_' . time() . '_' . $ride->id . '_' . ($ride->user_id ?? 'guest');
        $payment = Payment::updateOrCreate(
            ['ride_id' => $ride->id],
            [
                'amount' => $ride->fare,
                'payment_method' => 'paystack',
                'status' => 'pending',
                'reference' => $reference,
            ]
        );

        // Initialize Paystack transaction
        try {
            $secret = env('PAYSTACK_SECRET_KEY');
            $baseUrl = rtrim(env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'), '/');
            if (! $secret) {
                throw new \RuntimeException('Missing PAYSTACK_SECRET_KEY');
            }

            $response = Http::withToken($secret)
                ->post($baseUrl . '/transaction/initialize', [
                    'amount' => (int) round($payment->amount * 100),
                    'email' => optional($ride->user)->email ?? 'guest@example.com',
                    'reference' => $reference,
                    'callback_url' => route('payment.callback'),
                    'metadata' => [
                        'ride_id' => $ride->id,
                        'driver_id' => $ride->driver_id,
                        'user_id' => $ride->user_id,
                    ],
                ]);

            $result = $response->json();
            if (($result['status'] ?? false) && isset($result['data']['authorization_url'])) {
                Log::info('Paystack init success', ['payment_id' => $payment->id, 'reference' => $reference]);
                return redirect()->away($result['data']['authorization_url']);
            }

            Log::error('Paystack init failed', ['payment_id' => $payment->id, 'body' => $result]);
            return back()->with('error', 'Payment initialization failed. Please try again.');
        } catch (\Throwable $e) {
            Log::error('Payment init exception', ['error' => $e->getMessage()]);
            return back()->with('error', 'Payment system error. Please try again later.');
        }
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        if (! $reference) {
            return redirect()->route('dashboard')->with('error', 'No reference provided.');
        }

        $payment = Payment::where('reference', $reference)->first();
        if (! $payment) {
            // Fallback: attempt to match latest payment for user by metadata ride_id if present (not stored in DB)
            Log::warning('Payment not found for reference', ['reference' => $reference]);
        }

        try {
            $secret = env('PAYSTACK_SECRET_KEY');
            $baseUrl = rtrim(env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'), '/');
            if (! $secret) {
                throw new \RuntimeException('Missing PAYSTACK_SECRET_KEY');
            }

            $response = Http::withToken($secret)->get($baseUrl . '/transaction/verify/' . $reference);
            $result = $response->json();

            if (($result['status'] ?? false) && ($result['data']['status'] ?? '') === 'success') {
                $amountInNaira = (float) ($result['data']['amount'] ?? 0) / 100.0;

                if ($payment) {
                    $payment->update([
                        'status' => 'success',
                        'paid_at' => now(),
                        'amount' => $amountInNaira,
                    ]);

                    $payment->ride()->update(['payment_status' => 'paid']);
                }

                return redirect()->route('dashboard')->with('success', 'Payment successful!');
            }

            // Log failed payment
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                ]);
            }
            return redirect()->route('dashboard')->with('error', 'Payment failed or could not be verified.');
        } catch (\Throwable $e) {
            Log::error('Payment verify exception', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard')->with('error', 'Payment verification error.');
        }
    }
}