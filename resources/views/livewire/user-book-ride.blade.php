<?php

use App\Models\Driver;
use App\Models\Ride;
use App\Models\Payment;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $drivers = [];
    public ?int $driver_id = null;
    public string $pickup = '';
    public string $destination = '';
    // Payment method is deferred until driver accepts; default is cash
    public string $payment_method = 'cash';
    public int $pendingCount = 0;
    public ?string $limitError = null;

    // My rides management state
    public array $myRides = [];
    public bool $showEditModal = false;
    public bool $showChangeDriverModal = false;
    public ?int $editRideId = null;
    public string $editPickup = '';
    public string $editDestination = '';
    public ?int $new_driver_id = null;
    public bool $showDetailsModal = false;
    public ?int $detailsRideId = null;
    public array $detailsPayments = [];

    // Scheduling state
    public ?string $scheduled_at = null;
    public ?string $editScheduledAt = null;

    // Rating state
    public bool $showRateModal = false;
    public ?int $rateRideId = null;
    public ?int $rating = null;
    public string $ratingComment = '';

    public function mount(): void
    {
        $this->drivers = Driver::where('status', 'approved')
            ->where('is_available', true)
            ->orderBy('vehicle_name')
            ->get()
            ->toArray();

        $this->pendingCount = Ride::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        $this->refreshUserRides();
    }

    public function bookRide(): void
    {
        // Prevent booking if user already has two pending rides
        $pending = Ride::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();
        if ($pending >= 2) {
            $this->limitError = 'You already have 2 pending rides. Please complete or cancel one before booking another.';
            $this->dispatch('toast', type: 'error', message: 'You already have 2 pending rides.');
            return;
        }

        try {
            $this->validate([
                'driver_id' => ['nullable', 'integer'],
                'pickup' => ['required', 'string', 'min:3'],
                'destination' => ['required', 'string', 'min:3'],
                'scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->validator->errors()->all() as $msg) {
                $this->dispatch('toast', type: 'error', message: $msg);
            }
            return;
        }

        // Auto-assign if no driver was explicitly selected
        if (! $this->driver_id) {
            $driver = Driver::where('status', 'approved')
                ->where('is_available', true)
                ->orderBy('vehicle_name')
                ->first();

            if (! $driver) {
                $this->addError('driver_id', 'No available drivers at the moment.');
                $this->dispatch('toast', type: 'error', message: 'No available drivers at the moment.');
                return;
            }
        } else {
            $driver = Driver::where('id', $this->driver_id)
                ->where('status', 'approved')
                ->where('is_available', true)
                ->first();

            if (! $driver) {
                $this->addError('driver_id', 'Selected driver is not available or not approved.');
                $this->dispatch('toast', type: 'error', message: 'Selected driver not available or not approved.');
                return;
            }
        }

        // Fare will be set by the driver upon acceptance
        $fare = 0.0;

        $ride = Ride::create([
            'user_id' => Auth::id(),
            'driver_id' => $driver->id,
            'pickup' => $this->pickup,
            'destination' => $this->destination,
            'fare' => $fare,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'status' => 'pending',
            'scheduled_at' => $this->scheduled_at,
        ]);

        // Update counter for UI in case redirect is delayed
        $this->pendingCount = $pending + 1;

        // Inform UI and redirect; payment happens after driver acceptance
        session()->flash('success', 'Ride requested. Awaiting driver acceptance.');
        $this->dispatch('toast', type: 'success', message: 'Ride requested. Awaiting driver acceptance.');
        $this->dispatch('ride-booked', id: $ride->id);
        $this->redirect(route('user.dashboard'), navigate: true);
    }

    public function refreshUserRides(): void
    {
        $this->myRides = Ride::where('user_id', Auth::id())
            ->with('rating')
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    public function openDetails(int $id): void
    {
        $ride = Ride::findOrFail($id);
        if ($ride->user_id !== Auth::id()) {
            return;
        }
        $this->detailsRideId = $id;
        $this->detailsPayments = Payment::where('ride_id', $id)->orderByDesc('created_at')->get()->toArray();
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->detailsRideId = null;
        $this->detailsPayments = [];
    }

    public function openEdit(int $id): void
    {
        $ride = Ride::findOrFail($id);
        if ($ride->user_id !== Auth::id()) {
            return;
        }
        $this->editRideId = $id;
        $this->editPickup = (string) $ride->pickup;
        $this->editDestination = (string) $ride->destination;
        $this->editScheduledAt = $ride->scheduled_at ? \Illuminate\Support\Carbon::parse($ride->scheduled_at)->format('Y-m-d\\TH:i') : null;
        $this->showEditModal = true;
    }

    public function updateRideDetails(): void
    {
        if (! $this->editRideId) {
            return;
        }
        try {
            $this->validate([
                'editPickup' => ['required', 'string', 'min:3'],
                'editDestination' => ['required', 'string', 'min:3'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->validator->errors()->all() as $msg) {
                $this->dispatch('toast', type: 'error', message: $msg);
            }
            return;
        }

        $ride = Ride::findOrFail($this->editRideId);
        if ($ride->user_id !== Auth::id()) {
            return;
        }
        if ($ride->status !== 'pending') {
            return;
        }

        $scheduled = null;
        if (!empty($this->editScheduledAt)) {
            try {
                $scheduled = \Illuminate\Support\Carbon::parse($this->editScheduledAt);
            } catch (\Exception $e) {
                $this->addError('editScheduledAt', 'Invalid schedule format.');
                $this->dispatch('toast', type: 'error', message: 'Invalid schedule format.');
                return;
            }
            if ($scheduled->lt(\Illuminate\Support\Carbon::now())) {
                $this->addError('editScheduledAt', 'Schedule must be now or later.');
                $this->dispatch('toast', type: 'error', message: 'Schedule must be now or later.');
                return;
            }
        }

        $ride->update([
            'pickup' => $this->editPickup,
            'destination' => $this->editDestination,
            'scheduled_at' => $scheduled,
        ]);
        $this->showEditModal = false;
        $this->editRideId = null;
        $this->refreshUserRides();
        $this->dispatch('toast', type: 'success', message: 'Ride details updated.');
    }

    public function openChangeDriver(int $id): void
    {
        $ride = Ride::findOrFail($id);
        if ($ride->user_id !== Auth::id()) {
            return;
        }
        $this->editRideId = $id;
        $this->new_driver_id = $ride->driver_id;
        $this->showChangeDriverModal = true;
    }

    public function changeDriver(): void
    {
        if (! $this->editRideId || ! $this->new_driver_id) {
            return;
        }
        $ride = Ride::findOrFail($this->editRideId);
        if ($ride->user_id !== Auth::id()) {
            return;
        }
        if ($ride->status !== 'pending') {
            return;
        }

        $driver = Driver::where('id', $this->new_driver_id)
            ->where('status', 'approved')
            ->where('is_available', true)
            ->first();
        if (! $driver) {
            $this->dispatch('toast', type: 'error', message: 'Selected driver not available.');
            return;
        }
        $ride->update(['driver_id' => $driver->id]);
        $this->showChangeDriverModal = false;
        $this->editRideId = null;
        $this->refreshUserRides();
        $this->dispatch('toast', type: 'success', message: 'Driver changed.');
    }

    public function cancelRide(int $id): void
    {
        $ride = Ride::findOrFail($id);
        if ($ride->user_id !== Auth::id()) {
            return;
        }
        if (in_array($ride->status, ['pending', 'accepted'], true) && $ride->payment_status !== 'paid') {
            $ride->update(['status' => 'cancelled']);
            $this->dispatch('toast', type: 'success', message: 'Ride cancelled.');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Unable to cancel this ride.');
        }
        $this->refreshUserRides();
    }

    public function openRate(int $id): void
    {
        $ride = Ride::findOrFail($id);
        if ($ride->user_id !== Auth::id()) {
            return;
        }
        if ($ride->status !== 'completed') {
            $this->dispatch('toast', type: 'error', message: 'Only completed rides can be rated.');
            return;
        }
        if ($ride->rating) {
            // already rated
            $this->dispatch('toast', type: 'error', message: 'You have already rated this ride.');
            return;
        }
        $this->rateRideId = $id;
        $this->rating = null;
        $this->ratingComment = '';
        $this->showRateModal = true;
    }

    public function submitRating(): void
    {
        if (! $this->rateRideId) {
            return;
        }
        try {
            $this->validate([
                'rating' => ['required', 'integer', 'between:1,5'],
                'ratingComment' => ['nullable', 'string', 'max:500'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->validator->errors()->all() as $msg) {
                $this->dispatch('toast', type: 'error', message: $msg);
            }
            return;
        }
        $ride = Ride::findOrFail($this->rateRideId);
        if ($ride->user_id !== Auth::id()) {
            return;
        }
        if ($ride->status !== 'completed') {
            $this->dispatch('toast', type: 'error', message: 'Only completed rides can be rated.');
            return;
        }
        if ($ride->rating) {
            $this->dispatch('toast', type: 'error', message: 'You have already rated this ride.');
            return;
        }

        Rating::create([
            'ride_id' => $ride->id,
            'user_id' => Auth::id(),
            'driver_id' => $ride->driver_id,
            'rating' => (int) $this->rating,
            'comment' => $this->ratingComment ?: null,
        ]);

        $this->showRateModal = false;
        $this->rateRideId = null;
        $this->refreshUserRides();
        $this->dispatch('toast', type: 'success', message: 'Thanks for rating your driver.');
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="tw-heading">Book a Ride</h2>

    <form wire:submit="bookRide" class="card grid gap-4 max-w-xl">
        <div class="{{ ($limitError || $pendingCount >= 2) ? 'text-sm text-red-600' : 'text-xs text-gray-600' }}">
            {{ $limitError ?? ($pendingCount >= 2 ? 'You already have 2 pending rides. Complete or cancel before booking another.' : 'Pending rides: ' . $pendingCount . ' / 2') }}
        </div>

        <label class="grid gap-1">
            <span class="tw-body">Driver (optional)</span>
            <select wire:model="driver_id" class="rounded-lg border border-gray-200 p-2 focus:ring-2 focus:ring-[#007F5F] focus:border-[#007F5F]">
                <option value="">Auto-assign best available</option>
                @foreach($drivers as $d)
                    <option value="{{ $d['id'] }}">{{ $d['vehicle_name'] }}</option>
                @endforeach
            </select>
        </label>

        <flux:input wire:model="pickup" label="Pickup" />
        <flux:input wire:model="destination" label="Destination" />

        <label class="grid gap-1">
            <span class="tw-body">When</span>
            <input type="datetime-local" wire:model="scheduled_at" class="rounded-lg border border-gray-200 p-2 focus:ring-2 focus:ring-[#007F5F] focus:border-[#007F5F]" />
            
        </label>

        <!-- Payment selection is deferred until driver accepts and sets fare -->

        <flux:button type="submit" variant="primary" :disabled="$pendingCount >= 2" class="btn-primary">Book Ride</flux:button>
    </form>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="tw-heading">My Rides</div>
            {{-- Export disabled for users --}}
        </div>
        <div class="grid gap-3">
            @forelse($myRides as $r)
                <div class="card grid gap-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-medium">{{ $r['pickup'] }} → {{ $r['destination'] }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Ref: {{ $r['reference'] ?? 'N/A' }} · When: {{ isset($r['scheduled_at']) && $r['scheduled_at'] ? \Illuminate\Support\Carbon::parse($r['scheduled_at'])->format('M j, Y g:ia') : 'Not set' }} · Driver: #{{ $r['driver_id'] }} · Created: {{ \Carbon\Carbon::parse($r['created_at'])->diffForHumans() }}</div>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                            {{ $r['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                            {{ $r['status'] === 'accepted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                            {{ $r['status'] === 'in_progress' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                            {{ $r['status'] === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                            {{ $r['status'] === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}
                        ">
                            {{ ucfirst($r['status']) }}
                        </span>
                    </div>

                    <div class="tw-body">Fare: ₦{{ number_format($r['fare'], 2) }} · Payment: {{ $r['payment_status'] }} ({{ $r['payment_method'] }})</div>
                    @if(isset($r['rating']) && $r['rating'])
                        <div class="flex items-center gap-1 text-sm">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="text-lg {{ $r['rating']['rating'] >= $i ? 'text-yellow-500' : 'text-gray-300' }}">★</span>
                            @endfor
                            <span class="ml-1">({{ $r['rating']['rating'] }}/5)</span>
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        <flux:button variant="ghost" class="btn-outline-primary" wire:click="openDetails({{ $r['id'] }})">View Details</flux:button>
                        @if(in_array($r['status'], ['pending', 'accepted'], true) && $r['payment_status'] !== 'paid')
                            <flux:button wire:click="cancelRide({{ $r['id'] }})" variant="outline" class="text-red-600">Cancel</flux:button>
                        @endif

                        @if($r['status'] === 'pending')
                            <flux:button wire:click="openEdit({{ $r['id'] }})" variant="outline">Edit Details</flux:button>
                            <flux:button wire:click="openChangeDriver({{ $r['id'] }})" variant="outline">Change Driver</flux:button>
                        @endif

                        @if($r['status'] === 'accepted' && $r['payment_status'] === 'pending' && (float) $r['fare'] > 0)
                            <form method="POST" action="{{ route('payment.initialize') }}" class="inline">
                                @csrf
                                <input type="hidden" name="ride_id" value="{{ $r['id'] }}" />
                                <flux:button type="submit" variant="primary" class="btn-primary">Pay with Paystack</flux:button>
                            </form>
                        @endif

                        @if($r['status'] === 'completed' && empty($r['rating']))
                            <flux:button variant="primary" class="btn-primary" wire:click="openRate({{ $r['id'] }})">Rate Driver</flux:button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="tw-body">No rides yet. Book your first ride above.</div>
            @endforelse
        </div>
    </div>

    <flux:modal
        name="edit-ride-modal"
        variant="dialog"
        class="max-w-lg"
        wire:model="showEditModal"
        @close="$set('showEditModal', false)"
    >
        <div class="grid gap-4">
            <div class="tw-heading">Edit Ride Details</div>
            <flux:input wire:model="editPickup" label="Pickup" />
            <flux:input wire:model="editDestination" label="Destination" />
            <label class="grid gap-1">
                <span class="tw-body">When</span>
                <input type="datetime-local" wire:model="editScheduledAt" class="rounded-lg border border-gray-200 p-2 focus:ring-2 focus:ring-[#007F5F] focus:border-[#007F5F]" />
            </label>
            <div class="flex items-center gap-2">
                <flux:button variant="outline" wire:click="$set('showEditModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="updateRideDetails">Save Changes</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal
        name="change-driver-modal"
        variant="dialog"
        class="max-w-lg"
        wire:model="showChangeDriverModal"
        @close="$set('showChangeDriverModal', false)"
    >
        <div class="grid gap-4">
            <div class="tw-heading">Change Driver</div>
            <label class="grid gap-1">
                <span class="tw-body">Select Driver</span>
                <select wire:model="new_driver_id" class="rounded-lg border border-gray-200 p-2 focus:ring-2 focus:ring-[#007F5F] focus:border-[#007F5F]">
                    @foreach($drivers as $d)
                        <option value="{{ $d['id'] }}">{{ $d['vehicle_name'] }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-center gap-2">
                <flux:button variant="outline" wire:click="$set('showChangeDriverModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="changeDriver">Update Driver</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal
        name="rate-ride-modal"
        variant="dialog"
        class="max-w-lg"
        wire:model="showRateModal"
        @close="$set('showRateModal', false)"
    >
        <div class="grid gap-4">
            <div class="tw-heading">Rate Driver</div>
            <label class="grid gap-1">
                <span class="tw-body">Rating</span>
                <div class="flex items-center gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <button
                            type="button"
                            class="text-2xl leading-none focus:outline-none {{ ($rating ?? 0) >= $i ? 'text-yellow-500' : 'text-gray-300' }}"
                            wire:click="$set('rating', {{ $i }})"
                            aria-label="Rate {{ $i }} star{{ $i > 1 ? 's' : '' }}"
                        >
                            ★
                        </button>
                    @endfor
                    <span class="ml-2 text-sm">{{ $rating ? $rating.'/5' : 'Select rating' }}</span>
                </div>
            </label>
            <label class="grid gap-1">
                <span class="tw-body">Comment (optional)</span>
                <textarea wire:model="ratingComment" rows="3" class="rounded-lg border border-gray-200 p-2 focus:ring-2 focus:ring-[#007F5F] focus:border-[#007F5F]"></textarea>
            </label>
            <div class="flex items-center gap-2">
                <flux:button variant="outline" wire:click="$set('showRateModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="submitRating">Submit Rating</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal
        name="ride-details-modal"
        variant="dialog"
        class="max-w-lg"
        wire:model="showDetailsModal"
        @close="$set('showDetailsModal', false)"
    >
        @if($detailsRideId)
            @php($ride = \App\Models\Ride::find($detailsRideId))
            <div class="grid gap-2">
                <div class="tw-heading">Ride Details</div>
                <div class="tw-body">Reference: {{ $ride?->reference ?? 'N/A' }}</div>
                <div class="tw-body">Pickup: {{ $ride?->pickup }} → Destination: {{ $ride?->destination }}</div>
                <div class="tw-body">When: {{ optional($ride?->scheduled_at) ? \Illuminate\Support\Carbon::parse($ride?->scheduled_at)->format('M j, Y g:ia') : 'Not set' }}</div>
                <div class="tw-body">Driver: {{ optional($ride?->driver)->vehicle_name ?? 'Auto-assign' }}</div>
                <div class="tw-body">Fare: ₦{{ number_format($ride?->fare ?? 0, 2) }}</div>
                <div class="tw-body">Status: <span class="font-semibold">{{ $ride?->status }}</span></div>
                @if($ride && $ride->rating)
                    <div class="tw-body">
                        <span>Rating:</span>
                        <span class="inline-flex items-center gap-0.5 align-middle">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="text-lg {{ $ride->rating->rating >= $i ? 'text-yellow-500' : 'text-gray-300' }}">★</span>
                            @endfor
                        </span>
                        <span class="ml-1">({{ $ride->rating->rating }}/5)</span>
                    </div>
                    @if($ride->rating->comment)
                        <div class="tw-body italic">"{{ $ride->rating->comment }}"</div>
                    @endif
                @elseif($ride && $ride->status === 'completed')
                    <flux:button variant="primary" wire:click="openRate({{ $ride->id }})">Rate Driver</flux:button>
                @endif
                <div class="tw-heading mt-3">Payment History</div>
                <div class="grid gap-2">
                    @forelse($detailsPayments as $p)
                        <div class="card">
                            <div class="tw-body">Amount: ₦{{ number_format($p['amount'], 2) }}</div>
                            <div class="tw-body">Method: {{ $p['payment_method'] }}</div>
                            <div class="tw-body">Status: {{ $p['status'] }}</div>
                            <div class="tw-body">Date: {{ \Illuminate\Support\Carbon::parse($p['created_at'])->format('M j, Y g:ia') }}</div>
                        </div>
                    @empty
                        <div class="tw-body">No payments yet.</div>
                    @endforelse
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <flux:button variant="outline" wire:click="closeDetails">Close</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>