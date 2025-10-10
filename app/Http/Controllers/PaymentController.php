<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function initialize(Request $request)
    {
        $validated = $request->validate([
            'ride_id' => ['required', 'integer', 'exists:rides,id'],
        ]);

        $ride = Ride::findOrFail($validated['ride_id']);

        $payment = Payment::create([
            'ride_id' => $ride->id,
            'amount' => $ride->fare,
            'payment_method' => 'paystack',
            'status' => 'pending',
        ]);

        // TODO: Integrate Paystack init (create transaction, get authorization URL)
        // For now, redirect to a placeholder and mark failed to allow cash fallback.
        Log::info('Initialized Paystack payment', ['payment_id' => $payment->id]);

        return redirect()->route('payment.callback', ['reference' => 'TEST-REF-'.$payment->id]);
    }

    public function callback(Request $request)
    {
        $reference = $request->get('reference');
        $payment = Payment::where('reference', $reference)->first();

        if (! $payment) {
            // Simulate success for demo
            $payment = Payment::latest()->first();
        }

        if ($payment) {
            $payment->update([
                'status' => 'success',
                'reference' => $reference,
                'paid_at' => now(),
            ]);

            $payment->ride()->update(['payment_status' => 'paid', 'status' => 'accepted']);
        }

        return redirect()->route('dashboard');
    }
}