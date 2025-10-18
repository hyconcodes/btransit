<?php

use App\Models\Driver;
use App\Models\Ride;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public array $drivers = [];
    public bool $showDriverRidesModal = false;
    public ?int $selectedDriverId = null;
    public array $driverRides = [];
    public array $paymentsByRide = [];

    public function mount(): void
    {
        $this->refreshDrivers();
    }

    public function refreshDrivers(): void
    {
        $this->drivers = Driver::with('user')->orderByDesc('created_at')->get()->toArray();
    }

    public function toggleApproval(int $id): void
    {
        try {
            $driver = Driver::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('toast', type: 'error', message: 'Driver not found.');
            return;
        }
        $driver->status = $driver->status === 'approved' ? 'pending' : 'approved';
        $driver->save();
        $this->refreshDrivers();
        $this->dispatch('toast', type: 'success', message: $driver->status === 'approved' ? 'Driver approved.' : 'Driver set to pending.');
    }

    public function toggleAvailability(int $id): void
    {
        try {
            $driver = Driver::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('toast', type: 'error', message: 'Driver not found.');
            return;
        }
        $driver->is_available = !(bool) $driver->is_available;
        $driver->save();
        $this->refreshDrivers();
        $this->dispatch('toast', type: 'success', message: $driver->is_available ? 'Driver set available.' : 'Driver set unavailable.');
    }

    public function openDriverRides(int $id): void
    {
        try {
            $driver = Driver::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('toast', type: 'error', message: 'Driver not found.');
            return;
        }
        $this->selectedDriverId = $id;
        $this->driverRides = Ride::where('driver_id', $id)->orderByDesc('created_at')->get()->toArray();
        $this->paymentsByRide = [];
        foreach ($this->driverRides as $ride) {
            $this->paymentsByRide[$ride['id']] = Payment::where('ride_id', $ride['id'])->orderByDesc('created_at')->get()->toArray();
        }
        $this->showDriverRidesModal = true;
        $this->dispatch('toast', type: 'success', message: 'Loaded ' . count($this->driverRides) . ' rides.');
    }

    public function closeDriverRides(): void
    {
        $this->showDriverRidesModal = false;
        $this->selectedDriverId = null;
        $this->driverRides = [];
        $this->paymentsByRide = [];
    }
}; ?>

<div class="p-4 md:p-6 space-y-4 md:space-y-6">
    <h2 class="tw-heading text-lg md:text-xl">Driver Management</h2>

    <div class="grid gap-3">
        @forelse ($drivers as $d)
            <div class="card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm md:text-base truncate">{{ $d['user']['name'] ?? 'Unknown' }}</div>
                    <div class="tw-body text-xs md:text-sm">Vehicle: {{ $d['vehicle_name'] }} | Plate: {{ $d['plate_number'] }}</div>
                    <div class="tw-body text-xs md:text-sm">Status: <span class="font-semibold">{{ $d['status'] }}</span></div>
                    <div class="tw-body text-xs md:text-sm">Available: <span
                            class="font-semibold">{{ $d['is_available'] ?? false ? 'Yes' : 'No' }}</span></div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <flux:button wire:click="toggleApproval({{ $d['id'] }})" variant="primary" class="btn-primary text-xs md:text-sm">
                        {{ $d['status'] === 'approved' ? 'Set Pending' : 'Approve' }}
                    </flux:button>
                    <flux:button wire:click="toggleAvailability({{ $d['id'] }})" variant="ghost"
                        class="btn-outline-primary text-xs md:text-sm">
                        {{ $d['is_available'] ?? false ? 'Set Unavailable' : 'Set Available' }}
                    </flux:button>
                    <flux:button wire:click="openDriverRides({{ $d['id'] }})" variant="outline"
                        class="btn-outline-primary text-xs md:text-sm">
                        View Rides
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="tw-body text-sm md:text-base">No drivers yet.</div>
        @endforelse
    </div>

    <flux:modal name="driver-rides-modal" variant="dialog" class="max-w-full sm:max-w-2xl md:max-w-4xl" wire:model="showDriverRidesModal"
        @close="$set('showDriverRidesModal', false)">
        <div class="grid gap-4 p-4 md:p-6">
            <div class="tw-heading text-base md:text-lg">Driver Rides</div>
            <div class="grid gap-3 max-h-[60vh] overflow-y-auto">
                @forelse($driverRides as $r)
                    <div class="card p-3 md:p-4">
                        <div class="font-medium text-sm md:text-base">{{ $r['pickup'] }} → {{ $r['destination'] }}</div>
                        <div class="tw-body text-xs md:text-sm">Fare: ₦{{ number_format($r['fare'], 2) }} · Status: {{ $r['status'] }}</div>
                        <div class="tw-body text-xs md:text-sm">Payment: {{ $r['payment_status'] }} ({{ $r['payment_method'] }})</div>
                        <div class="tw-heading mt-2 text-sm md:text-base">Payment History</div>
                        <div class="grid gap-2 mt-2">
                            @forelse(($paymentsByRide[$r['id']] ?? []) as $p)
                                <div class="card p-2 md:p-3">
                                    <div class="tw-body text-xs md:text-sm">Amount: ₦{{ number_format($p['amount'], 2) }}</div>
                                    <div class="tw-body text-xs md:text-sm">Method: {{ $p['payment_method'] }}</div>
                                    <div class="tw-body text-xs md:text-sm">Status: {{ $p['status'] }}</div>
                                    <div class="tw-body text-xs md:text-sm">Date: {{ Carbon::parse($p['created_at'])->format('M j, Y g:ia') }}
                                    </div>
                                </div>
                            @empty
                                <div class="tw-body text-xs md:text-sm">No payments yet.</div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="tw-body text-sm md:text-base">No rides for this driver.</div>
                @endforelse
            </div>
            <div class="flex items-center gap-2">
                <flux:button variant="outline" wire:click="closeDriverRides">Close</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
