<?php

use App\Models\Driver;
use App\Models\Ride;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLogger;

new class extends Component {
    public array $drivers = [];
    public bool $showDriverRidesModal = false;
    public ?int $selectedDriverId = null;
    public array $driverRides = [];
    public array $paymentsByRide = [];

    public bool $showDriverInfoModal = false;
    public array $selectedDriver = [];

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
        // Audit log
        AuditLogger::log(auth()->user(), 'driver.approval.toggled', $driver, [
            'status' => $driver->status,
        ]);
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
        // Audit log
        AuditLogger::log(auth()->user(), 'driver.availability.toggled', $driver, [
            'is_available' => (bool) $driver->is_available,
        ]);
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

    public function openDriverInfo(int $id): void
    {
        try {
            $driver = Driver::with('user')->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('toast', type: 'error', message: 'Driver not found.');
            return;
        }
        $this->selectedDriverId = $id;
        $this->selectedDriver = $driver->toArray();
        $this->showDriverInfoModal = true;
    }

    public function closeDriverInfo(): void
    {
        $this->showDriverInfoModal = false;
        $this->selectedDriverId = null;
        $this->selectedDriver = [];
    }
}; ?>

<div class="p-4 md:p-6 space-y-4 md:space-y-6">
    <h2 class="tw-heading text-lg md:text-xl">Driver Management</h2>

    <div class="grid gap-3">
        @forelse ($drivers as $d)
            <div class="card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    @if (!empty($d['vehicle_photo_path']))
                        <img src="{{ Storage::disk('public')->url($d['vehicle_photo_path']) }}" alt="Vehicle"
                            class="h-12 w-12 rounded object-cover border dark:border-zinc-700" />
                    @endif
                    <div class="min-w-0">
                        <div class="font-medium text-sm md:text-base truncate">{{ $d['user']['name'] ?? 'Unknown' }}
                        </div>
                        <div class="tw-body text-xs md:text-sm">Vehicle: {{ $d['vehicle_name'] }} | Plate:
                            {{ $d['plate_number'] }}</div>
                        <div class="tw-body text-xs md:text-sm">Status: <span
                                class="font-semibold">{{ $d['status'] }}</span></div>
                        <div class="tw-body text-xs md:text-sm">Available: <span
                                class="font-semibold">{{ $d['is_available'] ?? false ? 'Yes' : 'No' }}</span></div>
                    </div>
                </div>
                <div class="flex flex-col gap-2 w-full sm:w-48">
                    <flux:button wire:click="toggleApproval({{ $d['id'] }})" variant="primary"
                        class="btn-primary text-xs md:text-sm">
                        {{ $d['status'] === 'approved' ? 'Disable' : 'Approve' }}
                    </flux:button>
                    <button type="button"
    wire:click="toggleAvailability({{ $d['id'] }})"
    wire:loading.attr="disabled"
    wire:target="toggleAvailability"
    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded border dark:border-zinc-700 {{ $d['is_available'] ?? false ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300' }}"
    aria-label="Toggle Availability"
>
    <span class="flex items-center gap-2" wire:loading.delay wire:target="toggleAvailability">
        <svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <span class="text-xs md:text-sm">Updating...</span>
    </span>

    <span class="flex items-center gap-2" wire:loading.remove wire:target="toggleAvailability">
        @if ($d['is_available'] ?? false)
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 10-1.414 1.414L9 13.414l4.707-4.707z" />
            </svg>
            <span class="text-xs md:text-sm">Available</span>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm-3.536-9.536a1 1 0 011.414-1.414L10 8.586l2.121-2.121a1 1 0 111.414 1.414L11.414 10l2.121 2.121a1 1 0 01-1.414 1.414L10 11.414l-2.121 2.121a1 1 0 01-1.414-1.414L8.586 10 6.464 7.879z" />
            </svg>
            <span class="text-xs md:text-sm">Unavailable</span>
        @endif
    </span>
</button>
                    <flux:button wire:click="openDriverRides({{ $d['id'] }})" variant="outline"
                        class="w-full text-xs md:text-sm">
                        View Rides
                    </flux:button>
                    <flux:button wire:click="openDriverInfo({{ $d['id'] }})" variant="ghost"
                        class="w-full text-xs md:text-sm">
                        View Details
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="tw-body text-sm md:text-base">No drivers yet.</div>
        @endforelse
    </div>

    <flux:modal name="driver-rides-modal" variant="dialog" class="max-w-full sm:max-w-2xl md:max-w-4xl"
        wire:model="showDriverRidesModal">
        <div class="grid gap-4 p-4 md:p-6">
            <div class="tw-heading text-base md:text-lg">Driver Rides</div>
            <div class="grid gap-3 max-h-[60vh] overflow-y-auto">
                @forelse($driverRides as $r)
                    <div class="card p-3 md:p-4">
                        <div class="font-medium text-sm md:text-base">{{ $r['pickup'] }} → {{ $r['destination'] }}
                        </div>
                        <div class="tw-body text-xs md:text-sm">Fare: ₦{{ number_format($r['fare'], 2) }} · Status:
                            {{ $r['status'] }}</div>
                        <div class="tw-body text-xs md:text-sm">Payment: {{ $r['payment_status'] }}
                            ({{ $r['payment_method'] }})</div>
                        <div class="tw-heading mt-2 text-sm md:text-base">Payment History</div>
                        <div class="grid gap-2 mt-2">
                            @forelse(($paymentsByRide[$r['id']] ?? []) as $p)
                                <div class="card p-2 md:p-3">
                                    <div class="tw-body text-xs md:text-sm">Amount:
                                        ₦{{ number_format($p['amount'], 2) }}</div>
                                    <div class="tw-body text-xs md:text-sm">Method: {{ $p['payment_method'] }}</div>
                                    <div class="tw-body text-xs md:text-sm">Status: {{ $p['status'] }}</div>
                                    <div class="tw-body text-xs md:text-sm">Date:
                                        {{ Carbon::parse($p['created_at'])->format('M j, Y g:ia') }}
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


    <flux:modal name="driver-info-modal" variant="dialog" class="max-w-full sm:max-w-2xl"
        wire:model="showDriverInfoModal">
        <div class="grid gap-4 p-4 md:p-6">
            <div class="tw-heading text-base md:text-lg">Driver Details</div>
            @if (!empty($selectedDriver))
                <div class="grid gap-2">
                    <div class="tw-body text-sm md:text-base"><span class="font-medium">Name:</span>
                        {{ $selectedDriver['user']['name'] ?? 'Unknown' }}</div>
                    <div class="tw-body text-sm md:text-base"><span class="font-medium">Email:</span>
                        {{ $selectedDriver['user']['email'] ?? 'N/A' }}</div>
                    <div class="tw-body text-sm md:text-base"><span class="font-medium">Phone:</span>
                        {{ $selectedDriver['user']['phone'] ?? 'N/A' }}</div>
                    <div class="tw-body text-sm md:text-base"><span class="font-medium">Status:</span>
                        {{ $selectedDriver['status'] ?? 'N/A' }}</div>
                    <div class="tw-body text-sm md:text-base"><span class="font-medium">Available:</span>
                        {{ $selectedDriver['is_available'] ?? false ? 'Yes' : 'No' }}</div>
                </div>
                <div class="tw-heading mt-3 text-sm md:text-base">Vehicle</div>
                <div class="grid gap-2">
                    @if (!empty($selectedDriver['vehicle_photo_path']))
                        <img src="{{ Storage::disk('public')->url($selectedDriver['vehicle_photo_path']) }}"
                            alt="Vehicle photo" class="h-24 w-24 rounded object-cover border dark:border-zinc-700" />
                    @endif
                    <div class="tw-body text-sm md:text-base"><span class="font-medium">Name:</span>
                        {{ $selectedDriver['vehicle_name'] ?? 'N/A' }}</div>
                    <div class="tw-body text-sm md:text-base"><span class="font-medium">Plate:</span>
                        {{ $selectedDriver['plate_number'] ?? 'N/A' }}</div>
                </div>
            @else
                <div class="tw-body text-sm md:text-base">No driver selected.</div>
            @endif
            <div class="flex items-center gap-2">
                <flux:button variant="outline" wire:click="closeDriverInfo">Close</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
