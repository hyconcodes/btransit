<?php

use App\Models\Ride;
use App\Models\Payment;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public int $totalRides = 0;
    public int $completedRides = 0;
    public float $totalRevenue = 0.0;
    public int $approvedDrivers = 0;

    public array $roleLabels = [];
    public array $roleValues = [];
    public array $driverLabels = [];
    public array $driverValues = [];
    public array $passengerLabels = [];
    public array $passengerValues = [];

    public function mount(): void
    {
        $this->totalRides = Ride::count();
        $this->completedRides = Ride::where('status', 'completed')->count();
        $this->totalRevenue = (float) Payment::where('status', 'success')->sum('amount');
        $this->approvedDrivers = Driver::where('status', 'approved')->count();

        $this->computeChartsData();
    }

    protected function computeChartsData(): void
    {
        // Role distribution
        $this->roleLabels = ['Superadmin', 'Driver', 'User'];
        $this->roleValues = [
            User::role('superadmin')->count(),
            User::role('driver')->count(),
            User::role('user')->count(),
        ];

        // Driver chart: current month rides per day
        $start = Carbon::now()->startOfMonth();
        $daysInMonth = $start->daysInMonth;
        $monthDays = collect(range(0, $daysInMonth - 1))->map(fn ($i) => $start->copy()->addDays($i));
        $format = fn ($d) => $d->format('M j');
        $this->driverLabels = $monthDays->map($format)->toArray();
        $this->driverValues = $monthDays->map(fn ($d) => Ride::whereDate('created_at', $d)->count())->toArray();

        // Passenger chart: keep last 7 days (unchanged)
        $days = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $this->passengerLabels = $days->map($format)->toArray();
        $this->passengerValues = $days->map(fn ($d) => Ride::whereDate('created_at', $d)->count())->toArray();
    }
}; ?>

<div class="relative p-4 sm:p-4 space-y-6 overflow-hidden rounded">
    <div class="absolute inset-0 -z-20"></div>
    <div class="absolute inset-0 -z-10 bg-white/50 dark:bg-black/40"></div>
    <div class="flex items-center justify-between">
        <h4 class="tw-heading text-[var(--neutral-text)] dark:text-white">Admin Dashboard</h4>
        <form action="{{ route('admin.rides.export.pdf') }}" method="GET" class="flex items-center gap-2">
            <label class="text-sm">From
                <flux:input type="datetime-local" name="from" class="rounded-lg border border-gray-200 p-2 focus:ring-2 focus:ring-[#007F5F] focus:border-[#007F5F]" />
            </label>
            <label class="text-sm">To
                <flux:input type="datetime-local" name="to" class="rounded-lg border border-gray-200 p-2 focus:ring-2 focus:ring-[#007F5F] focus:border-[#007F5F]" />
            </label>
            <flux:button type="submit" class="btn-primary">Export Rides</flux:button>
        </form>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="flex items-center justify-between">
                <div class="tw-body font-semibold dark:text-zinc-200">Roles</div>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-4">
                {{-- <div class="rounded-lg border border-gray-200 dark:border-zinc-700 p-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-gray-500 dark:text-zinc-400">Superadmin</div>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-md" style="background:#F4C430; color:#111827">👑</span>
                    </div>
                    <div class="text-xl font-bold" style="color:#F4C430">{{ $roleValues[0] ?? 0 }}</div>
                </div> --}}
                <div class="rounded-lg border border-gray-200 dark:border-zinc-700 p-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-gray-500 dark:text-zinc-400">Drivers</div>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-md" style="background:#3B82F6; color:#ffffff">🚗</span>
                    </div>
                    <div class="text-xl font-bold" style="color:#3B82F6">{{ $roleValues[1] ?? 0 }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-zinc-700 p-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-gray-500 dark:text-zinc-400">Users</div>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-md" style="background:#8B5CF6; color:#ffffff">👤</span>
                    </div>
                    <div class="text-xl font-bold" style="color:#8B5CF6">{{ $roleValues[2] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="flex items-center justify-between">
                <div class="tw-body dark:text-zinc-200">💰 Total Revenue</div>
            </div>
            <div class="text-2xl font-bold">₦{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="flex items-center justify-between">
                <div class="tw-body dark:text-zinc-200">✅ Approved Drivers</div>
                <flux:link :href="route('admin.drivers')" class="text-xs px-2 py-1 rounded bg-[#007F5F] text-white hover:opacity-90">Manage</flux:link>
            </div>
            <div class="text-2xl font-bold">{{ $approvedDrivers }}</div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Rides (Current Month)</div>
            </div>
            <canvas id="driverLine" class="mt-4 w-full" height="220"></canvas>
        </div>
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Passenger Activity (7 days)</div>
            </div>
            <canvas id="passengerLine" class="mt-4 w-full" height="220"></canvas>
        </div>
    </div>
    <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
        <flux:link :href="route('admin.drivers')" variant="primary" class="btn-primary">Manage Drivers</flux:link>
    </div>

    <style>
        @import url('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
        #bgMap { height: 100%; width: 100%; }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapEl = document.getElementById('bgMap');
            if (!mapEl) return;
            const map = L.map('bgMap', {
                zoomControl: false,
                attributionControl: false,
                dragging: false,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                boxZoom: false,
                keyboard: false,
                tap: false,
            });
            map.setView([6.5244, 3.3792], 12); // Lagos
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#404040' : '#e5e7eb';
        const fontColor = isDark ? '#e5e7eb' : '#374151';
        const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary-green').trim() || '#007F5F';
        const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent-gold').trim() || '#F4C430';



        // Driver Line
        const driverLineCtx = document.getElementById('driverLine');
        if (driverLineCtx) {
            new Chart(driverLineCtx, {
                type: 'line',
                data: {
                    labels: @json($driverLabels),
                    datasets: [{
                        label: 'Rides',
                        data: @json($driverValues),
                        borderColor: primary,
                        backgroundColor: primary,
                        tension: 0.3,
                    }]
                },
                options: {
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: fontColor } },
                        y: { grid: { color: gridColor }, ticks: { color: fontColor } }
                    },
                    plugins: { legend: { labels: { color: fontColor } } }
                }
            });
        }

        // Passenger Line
        const passengerLineCtx = document.getElementById('passengerLine');
        if (passengerLineCtx) {
            new Chart(passengerLineCtx, {
                type: 'line',
                data: {
                    labels: @json($passengerLabels),
                    datasets: [{
                        label: 'New Rides',
                        data: @json($passengerValues),
                        borderColor: accent,
                        backgroundColor: accent,
                        tension: 0.3,
                    }]
                },
                options: {
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: fontColor } },
                        y: { grid: { color: gridColor }, ticks: { color: fontColor } }
                    },
                    plugins: { legend: { labels: { color: fontColor } } }
                }
            });
        }
    </script>
</div>