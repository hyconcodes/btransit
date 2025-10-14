<?php

use App\Models\Driver;
use App\Models\Ride;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $approvedDrivers = [];
    public int $completedRides = 0;
    public array $awaitingPayment = [];

    public function mount(): void
    {
        $this->approvedDrivers = Driver::where('status', 'approved')->where('is_available', true)->orderBy('vehicle_name')->get()->toArray();
        $this->completedRides = Ride::where('user_id', Auth::id())->where('status', 'completed')->count();
        $this->awaitingPayment = Ride::where('user_id', Auth::id())
            ->whereIn('status', ['accepted', 'in_progress', 'completed'])
            ->where('payment_status', 'pending')
            ->where('fare', '>', 0)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}; ?>

<div class="p-6 space-y-6">
    <div class="flex justify-end">
        <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar" class="h-10 w-10 rounded-full border border-secondary" />
    </div>
    <h2 class="tw-heading">User Dashboard</h2>
    @if (session('success'))
        <div class="card bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="card bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif
    <div class="grid gap-6">
        <div class="card">
            <div class="tw-body">Completed Rides</div>
            <div class="text-2xl font-bold">{{ $completedRides }}</div>
        </div>
        <div class="card">
            <div class="tw-heading">Unpaid Rides</div>
            <div class="mt-3 grid gap-3">
                @forelse($awaitingPayment as $r)
                    <div class="card flex items-center justify-between">
                        <div>
                            <div class="font-medium">{{ $r['pickup'] }} → {{ $r['destination'] }}</div>
                            <div class="tw-body">Fare: ₦{{ number_format($r['fare'], 2) }} · Status: {{ $r['status'] }}</div>
                        </div>
                        <form method="POST" action="{{ route('payment.initialize') }}">
                            @csrf
                            <input type="hidden" name="ride_id" value="{{ $r['id'] }}" />
                            <flux:button type="submit" variant="primary" class="btn-primary">Pay with Paystack</flux:button>
                        </form>
                    </div>
                @empty
                    <div class="tw-body">No unpaid rides found.</div>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="tw-heading">Available Drivers</div>
            <ul class="list-disc ms-5">
                @forelse($approvedDrivers as $d)
                    <li class="tw-body">{{ $d['vehicle_name'] }} — ₦{{ number_format($d['charge_rate'], 2) }}</li>
                @empty
                    <li class="tw-body">No drivers available</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div>
        <flux:link :href="route('user.rides.book')" variant="primary" class="btn-primary">Book a Ride</flux:link>
    </div>
</div>