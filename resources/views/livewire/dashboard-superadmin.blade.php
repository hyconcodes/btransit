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

        // Last 7 days labels
        $days = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $format = fn ($d) => $d->format('M j');
        $this->driverLabels = $days->map($format)->toArray();
        $this->passengerLabels = $this->driverLabels;

        // Driver activity: completed rides per day
        $this->driverValues = $days->map(fn ($d) => Ride::where('status', 'completed')->whereDate('updated_at', $d)->count())->toArray();
        // Passenger activity: rides created per day
        $this->passengerValues = $days->map(fn ($d) => Ride::whereDate('created_at', $d)->count())->toArray();
    }
}; ?>

<div class="p-6 space-y-6 bg-[var(--bg-light)] dark:bg-[var(--bg-dark)]">
    <h2 class="tw-heading text-[var(--neutral-text)] dark:text-white">Superadmin Dashboard</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="flex items-center justify-between">
                <div class="tw-body font-semibold dark:text-zinc-200">Roles Distribution</div>
            </div>
            <canvas id="rolePie" class="mt-4" height="140"></canvas>
        </div>
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="tw-body dark:text-zinc-200">Total Revenue</div>
            <div class="text-2xl font-bold">₦{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="tw-body dark:text-zinc-200">Approved Drivers</div>
            <div class="text-2xl font-bold">{{ $approvedDrivers }}</div>
        </div>
    </div>
    <div class="grid gap-6 md:grid-cols-2">
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Driver Activity (7 days)</div>
            </div>
            <canvas id="driverLine" class="mt-4"></canvas>
        </div>
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Passenger Activity (7 days)</div>
            </div>
            <canvas id="passengerLine" class="mt-4"></canvas>
        </div>
    </div>
    <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
        <flux:link :href="route('admin.drivers')" variant="primary" class="btn-primary">Manage Drivers</flux:link>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#404040' : '#e5e7eb';
        const fontColor = isDark ? '#e5e7eb' : '#374151';
        const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary-green').trim() || '#007F5F';
        const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent-gold').trim() || '#F4C430';

        // Role Pie
        const rolePieCtx = document.getElementById('rolePie');
        if (rolePieCtx) {
            new Chart(rolePieCtx, {
                type: 'pie',
                data: {
                    labels: @json($roleLabels),
                    datasets: [{
                        data: @json($roleValues),
                        backgroundColor: [primary, accent, '#60A5FA'],
                        borderColor: isDark ? '#111827' : '#ffffff',
                        borderWidth: 2,
                    }]
                },
                options: {
                    plugins: { legend: { labels: { color: fontColor } } }
                }
            });
        }

        // Driver Line
        const driverLineCtx = document.getElementById('driverLine');
        if (driverLineCtx) {
            new Chart(driverLineCtx, {
                type: 'line',
                data: {
                    labels: @json($driverLabels),
                    datasets: [{
                        label: 'Completed Rides',
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