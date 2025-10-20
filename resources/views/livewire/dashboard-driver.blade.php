<?php

use App\Models\Ride;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

new class extends Component { use WithFileUploads;
    public int $assigned = 0;
    public int $inProgress = 0;
    public int $completed = 0;
    public bool $isAvailable = false;
    public string $vehicleName = '';
    public string $plateNumber = '';
    public string $driverStatus = '';
    public string $vehiclePhotoPath = '';

    public string $vehicleNameInput = '';
    public string $plateNumberInput = '';
    public $vehiclePhoto = null;

    public bool $canToggle = false;
    public bool $showVehicleModal = false;
    public ?string $vehicleLastUpdatedAt = null;
    public bool $canEditVehicle = true;
    public int $daysUntilNextUpdate = 0;

    public function mount(): void
    {
        $driver = optional(Auth::user())->driver;
        $driverId = optional($driver)->id;
        $this->assigned = Ride::where('driver_id', $driverId)->whereIn('status', ['pending', 'accepted'])->count();
        $this->inProgress = Ride::where('driver_id', $driverId)->where('status', 'in_progress')->count();
        $this->completed = Ride::where('driver_id', $driverId)->where('status', 'completed')->count();
        $this->isAvailable = (bool) optional($driver)->is_available;
        $this->vehicleName = (string) ($driver->vehicle_name ?? '');
        $this->plateNumber = (string) ($driver->plate_number ?? '');
        $this->driverStatus = (string) ($driver->status ?? '');
        $this->vehiclePhotoPath = (string) ($driver->vehicle_photo_path ?? '');

        $this->vehicleNameInput = $this->vehicleName;
        $this->plateNumberInput = $this->plateNumber;

        $this->canToggle = (bool) ($driver && $driver->status === 'approved' && $driver->vehicle_name && $driver->plate_number);

        // Vehicle update lock
        $last = $driver ? $driver->vehicle_last_updated_at : null;
        $this->vehicleLastUpdatedAt = $last ? Carbon::parse($last)->toDateTimeString() : null;
        $diffDays = $last ? Carbon::parse($last)->diffInDays(Carbon::now()) : null;
        $this->canEditVehicle = ! $last || ($diffDays !== null && $diffDays >= 30);
        $this->daysUntilNextUpdate = ($last && $diffDays !== null && $diffDays < 30) ? (30 - $diffDays) : 0;
    }

    public function registerVehicle(): void
    {
        $validated = $this->validate([
            'vehicleNameInput' => ['required', 'string', 'min:2', 'max:100'],
            'plateNumberInput' => ['required', 'string', 'min:3', 'max:20'],
            'vehiclePhoto' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = Auth::user();
        if (! $user) {
            session()->flash('error', 'You must be logged in.');
            return;
        }

        $driver = $user->driver;
        $photoPath = $this->vehiclePhotoPath;
        if ($this->vehiclePhoto) {
            $photoPath = $this->vehiclePhoto->store('vehicle_photos', 'public');
        }

        if (! $driver) {
            $driver = new Driver();
            $driver->user_id = $user->id;
            $driver->vehicle_name = $validated['vehicleNameInput'];
            $driver->plate_number = $validated['plateNumberInput'];
            $driver->status = 'pending';
            $driver->is_available = false;
            $driver->vehicle_photo_path = $photoPath ?: null;
            $driver->vehicle_last_updated_at = Carbon::now();
            $driver->save();
        } else {
            $driver->vehicle_name = $validated['vehicleNameInput'];
            $driver->plate_number = $validated['plateNumberInput'];
            if ($photoPath) {
                $driver->vehicle_photo_path = $photoPath;
            }
            $driver->status = 'pending';
            $driver->is_available = false;
            $driver->vehicle_last_updated_at = Carbon::now();
            $driver->save();
        }

        $this->vehicleName = $driver->vehicle_name;
        $this->plateNumber = $driver->plate_number;
        $this->driverStatus = $driver->status;
        $this->vehiclePhotoPath = (string) ($driver->vehicle_photo_path ?? '');
        $this->canToggle = (bool) ($driver && $driver->status === 'approved' && $driver->vehicle_name && $driver->plate_number);

        // update UI lock and close modal
        $this->vehicleLastUpdatedAt = Carbon::now()->toDateTimeString();
        $this->canEditVehicle = false;
        $this->daysUntilNextUpdate = 30;
        $this->showVehicleModal = false;

        session()->flash('status', 'Vehicle info submitted. Await superadmin approval.');
    }

    public function openVehicleModal(): void
    {
        if (! $this->canEditVehicle) {
            session()->flash('error', $this->daysUntilNextUpdate > 0
                ? ('You can update vehicle info in ' . $this->daysUntilNextUpdate . ' day(s).')
                : 'Vehicle updates are currently locked.'
            );
            return;
        }
        $this->showVehicleModal = true;
    }

    public function closeVehicleModal(): void
    {
        $this->showVehicleModal = false;
    }

    public function toggleAvailability(): void
    {
        $driver = optional(Auth::user())->driver;
        if (! $driver || ! $driver->vehicle_name || ! $driver->plate_number || $driver->status !== 'approved') {
            session()->flash('error', 'You must register your vehicle and be approved before toggling availability.');
            return;
        }
        $driver->is_available = ! (bool) $driver->is_available;
        $driver->save();
        $this->isAvailable = (bool) $driver->is_available;
        $this->canToggle = (bool) ($driver && $driver->status === 'approved' && $driver->vehicle_name && $driver->plate_number);
        session()->flash('status', $driver->is_available ? 'You are now Available' : 'You are now Unavailable');
    }
}; ?>

<main class="p-6 space-y-6">
    
    <h2 class="tw-heading">Driver Dashboard</h2>

    <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-zinc-100">
        <div class="tw-body font-semibold">Your Vehicle</div>
        <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <div class="text-xs text-zinc-600 dark:text-zinc-400">Vehicle Name</div>
                <div class="font-medium">{{ $vehicleName ?: 'Not set' }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-600 dark:text-zinc-400">Plate Number</div>
                <div class="font-medium">{{ $plateNumber ?: 'Not set' }}</div>
            </div>
            <div>
                <div class="text-xs text-zinc-600 dark:text-zinc-400">Status</div>
                <div class="font-medium">{{ $driverStatus ?: 'N/A' }}</div>
            </div>
        </div>
         <div class="mt-3 flex items-center gap-2">
            <flux:button wire:click="openVehicleModal" variant="primary" class="btn-primary" :disabled="!$canEditVehicle">
                {{ ($vehicleName && $plateNumber) ? 'Update Vehicle' : 'Register Vehicle' }}
            </flux:button>
            @if (! $canEditVehicle && $daysUntilNextUpdate > 0)
                <span class="text-xs text-zinc-600 dark:text-zinc-400">You can update in {{ $daysUntilNextUpdate }} day(s).</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-zinc-100">
            <div class="tw-body">Assigned</div>
            <div class="text-2xl font-bold">{{ $assigned }}</div>
        </div>
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-zinc-100">
            <div class="tw-body">In Progress</div>
            <div class="text-2xl font-bold">{{ $inProgress }}</div>
        </div>
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-zinc-100">
            <div class="tw-body">Completed</div>
            <div class="text-2xl font-bold">{{ $completed }}</div>
        </div>
    </div>

    <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-zinc-100">
        <div class="flex items-center justify-between">
            <div class="tw-body font-semibold">Availability</div>
            <button type="button" wire:click="toggleAvailability" class="flex items-center gap-2" :disabled="!$canToggle">
                <span class="text-sm {{ $isAvailable ? 'text-green-600' : 'text-zinc-600 dark:text-zinc-400' }}">{{ $isAvailable ? 'Available' : 'Unavailable' }}</span>
                <span class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $isAvailable ? 'bg-green-600' : 'bg-zinc-300 dark:bg-zinc-700' }}">
                    <span class="inline-block h-4 w-4 rounded-full bg-white shadow transform transition {{ $isAvailable ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </span>
            </button>
        </div>
        <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-400">Toggle your availability so passengers can book you.</div>
        @if (! $canToggle)
            <div class="mt-2 text-xs text-red-600 dark:text-red-400">Vehicle must be registered and approved to toggle.</div>
        @endif
        @if (session()->has('status'))
            <div class="mt-2 text-xs text-green-600 dark:text-green-400">{{ session('status') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="mt-2 text-xs text-red-600 dark:text-red-400">{{ session('error') }}</div>
        @endif
    </div>

    <flux:modal name="vehicle-modal" variant="dialog" wire:model="showVehicleModal" @close="$set('showVehicleModal', false)" class="max-w-lg">
        <div class="grid gap-4 p-4 md:p-6">
            <div class="tw-heading text-base">Register/Update Vehicle</div>
        <form wire:submit.prevent="registerVehicle" class="mt-3 space-y-4">
            <div>
                <label class="block text-sm font-medium">Vehicle Name</label>
                <input type="text" class="mt-1 w-full border rounded px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700" wire:model.defer="vehicleNameInput" placeholder="e.g. Toyota Camry" />
                @error('vehicleNameInput')
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Plate Number</label>
                <input type="text" class="mt-1 w-full border rounded px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700" wire:model.defer="plateNumberInput" placeholder="e.g. ABC-1234" />
                @error('plateNumberInput')
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Vehicle Photo (optional)</label>
                <input type="file" class="mt-1 w-full" wire:model="vehiclePhoto" accept="image/*" />
                @error('vehiclePhoto')
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</div>
                @enderror
                @if ($vehiclePhoto)
                    <div class="mt-2">
                        <span class="text-xs text-zinc-600 dark:text-zinc-400">Preview:</span>
                        <img src="{{ $vehiclePhoto->temporaryUrl() }}" alt="Preview" class="mt-1 h-24 w-24 object-cover rounded border dark:border-zinc-700" />
                    </div>
                @elseif ($vehiclePhotoPath)
                    <div class="mt-2">
                        <span class="text-xs text-zinc-600 dark:text-zinc-400">Current:</span>
                        <img src="{{ Storage::disk('public')->url($vehiclePhotoPath) }}" alt="Vehicle" class="mt-1 h-24 w-24 object-cover rounded border dark:border-zinc-700" />
                    </div>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded" :disabled="!$canEditVehicle">Save Vehicle Info</button>
                @if (! $canEditVehicle && $daysUntilNextUpdate > 0)
                    <span class="text-xs text-zinc-600 dark:text-zinc-400">Next update allowed in {{ $daysUntilNextUpdate }} day(s).</span>
                @else
                    <span class="text-xs text-zinc-600 dark:text-zinc-400">Admin approval required after updates.</span>
                @endif
            </div>
        </form>
        </div>
    </flux:modal>

    <div>
        <flux:link :href="route('driver.rides')" variant="primary" class="btn-primary">Manage Rides</flux:link>
    </div>
</main>