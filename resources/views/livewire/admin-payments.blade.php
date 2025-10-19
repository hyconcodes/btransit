<?php

use Livewire\Volt\Component;
use App\Models\Payment;
use Illuminate\Support\Carbon;

new class extends Component {
    public array $payments = [];
    public bool $showPaymentModal = false;
    public ?array $selectedPayment = null;

    // Filters and chart state
    public array $methodOptions = [];
    public ?string $searchRef = '';
    public ?string $statusFilter = '';
    public ?string $methodFilter = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public array $chartLabels = [];
    public array $chartValues = [];

    public function mount(): void
    {
        $this->loadPayments();
        $this->loadMethodOptions();
        $this->computeChart();
    }

    public function loadPayments(): void
    {
        $query = Payment::with(['ride', 'ride.user', 'ride.driver.user'])
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at');

        if (!empty($this->searchRef)) {
            $query->where('reference', 'like', '%' . $this->searchRef . '%');
        }
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }
        if (!empty($this->methodFilter)) {
            $query->where('payment_method', $this->methodFilter);
        }
        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $this->payments = $query->get()->toArray();
    }

    public function loadMethodOptions(): void
    {
        $this->methodOptions = Payment::query()
            ->select('payment_method')
            ->distinct()
            ->pluck('payment_method')
            ->filter()
            ->values()
            ->toArray();
    }

    public function computeChart(): void
    {
        // Determine date range (default: last 7 days)
        $end = !empty($this->dateTo) ? Carbon::parse($this->dateTo)->endOfDay() : Carbon::today()->endOfDay();
        $start = !empty($this->dateFrom) ? Carbon::parse($this->dateFrom)->startOfDay() : $end->copy()->subDays(6)->startOfDay();
        if ($start->gt($end)) {
            $start = $end->copy()->subDays(6)->startOfDay();
        }

        $days = collect(range(0, $start->diffInDays($end)))->map(fn ($i) => $start->copy()->addDays($i));
        $this->chartLabels = $days->map(fn ($d) => $d->format('M j'))->toArray();

        $query = Payment::query();
        if (!empty($this->searchRef)) {
            $query->where('reference', 'like', '%' . $this->searchRef . '%');
        }
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }
        if (!empty($this->methodFilter)) {
            $query->where('payment_method', $this->methodFilter);
        }
        $query->whereDate('created_at', '>=', $start->toDateString())
              ->whereDate('created_at', '<=', $end->toDateString());

        $payments = $query->get();
        $this->chartValues = [];
        foreach ($days as $day) {
            $this->chartValues[] = (float) $payments
                ->filter(fn ($p) => Carbon::parse($p->paid_at ?? $p->created_at)->isSameDay($day))
                ->sum('amount');
        }

        $this->dispatch('payments-chart-update');
    }

    public function updated($property): void
    {
        if (in_array($property, ['searchRef', 'statusFilter', 'methodFilter', 'dateFrom', 'dateTo'])) {
            $this->loadPayments();
            $this->computeChart();
        }
    }

    public function resetFilters(): void
    {
        $this->searchRef = '';
        $this->statusFilter = '';
        $this->methodFilter = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->loadPayments();
        $this->computeChart();
    }

    public function viewPayment(int $id): void
    {
        try {
            $payment = Payment::with(['ride', 'ride.user', 'ride.driver.user'])->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('toast', type: 'error', message: 'Payment not found.');
            return;
        }
        $this->selectedPayment = $payment->toArray();
        $this->showPaymentModal = true;
        $this->dispatch('toast', type: 'success', message: 'Loaded payment details.');
    }

    public function closePayment(): void
    {
        $this->showPaymentModal = false;
        $this->selectedPayment = null;
    }
}; ?>

<div class="p-4 md:p-6 space-y-4 md:space-y-6">
    <h2 class="tw-heading text-lg md:text-xl">Payment Management</h2>

    <!-- Payments Line Chart -->
    <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
        <div class="flex items-center justify-between">
            <div class="font-semibold">Payments (Amount per Day)</div>
        </div>
        <canvas id="paymentsLine" class="mt-4 w-full" height="80"
            data-labels='@json($chartLabels)' data-values='@json($chartValues)'></canvas>
    </div>

    <!-- Filters -->
    <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
        <div class="font-semibold">Filters</div>
        <div class="mt-3 grid gap-3 md:grid-cols-5">
            <input type="text" placeholder="Search reference" wire:model.debounce.500ms="searchRef"
                class="w-full rounded-lg border border-zinc-300 px-3 py-2 focus:ring-2 focus:ring-[var(--primary-green)] focus:border-[var(--primary-green)] dark:bg-zinc-800 dark:border-zinc-700 dark:text-white" />
            <select wire:model="statusFilter"
                class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
            </select>
            <select wire:model="methodFilter"
                class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-white">
                <option value="">All Methods</option>
                @foreach($methodOptions as $m)
                    <option value="{{ $m }}">{{ ucfirst($m) }}</option>
                @endforeach
            </select>
            <input type="date" wire:model="dateFrom"
                class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-white" />
            <input type="date" wire:model="dateTo"
                class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-white" />
        </div>
        <div class="mt-3 flex items-center gap-2">
            <flux:button variant="ghost" class="btn-outline-primary text-xs" wire:click="resetFilters">Reset</flux:button>
            <flux:button variant="primary" class="btn-primary text-xs" wire:click="loadPayments">Refresh</flux:button>
        </div>
    </div>

    <div class="grid gap-3">
        @forelse($payments as $p)
            <div class="card p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm md:text-base truncate">₦{{ number_format($p['amount'] ?? 0, 2) }} — {{ $p['payment_method'] ?? 'N/A' }}</div>
                    <div class="tw-body text-xs md:text-sm">Status: <span class="font-semibold">{{ $p['status'] ?? 'N/A' }}</span> · Ref: {{ $p['reference'] ?? '—' }}</div>
                    <div class="tw-body text-xs md:text-sm">Paid: {{ !empty($p['paid_at']) ? Carbon::parse($p['paid_at'])->format('M j, Y g:ia') : '—' }} · Created: {{ !empty($p['created_at']) ? Carbon::parse($p['created_at'])->format('M j, Y g:ia') : '—' }}</div>
                    @if (!empty($p['ride']))
                        <div class="tw-body text-xs md:text-sm mt-1">Ride: {{ $p['ride']['pickup'] ?? 'N/A' }} → {{ $p['ride']['destination'] ?? 'N/A' }} · Fare ₦{{ number_format($p['ride']['fare'] ?? 0, 2) }}</div>
                    @endif
                </div>
                <div class="flex gap-2">
                    <flux:button wire:click="viewPayment({{ $p['id'] }})" variant="outline" class="btn-outline-primary text-xs md:text-sm">View</flux:button>
                </div>
            </div>
        @empty
            <div class="tw-body text-sm md:text-base">No payments yet.</div>
        @endforelse
    </div>

    <flux:modal name="payment-detail-modal" variant="dialog" class="max-w-full sm:max-w-2xl md:max-w-3xl" wire:model="showPaymentModal" @close="$set('showPaymentModal', false)">
        <div class="grid gap-4 p-4 md:p-6">
            <div class="tw-heading text-base md:text-lg">Payment Details</div>
            @if ($selectedPayment)
                <div class="card p-3 md:p-4">
                    <div class="grid md:grid-cols-2 gap-3">
                        <div class="tw-body text-xs md:text-sm">Amount: ₦{{ number_format($selectedPayment['amount'] ?? 0, 2) }}</div>
                        <div class="tw-body text-xs md:text-sm">Method: {{ $selectedPayment['payment_method'] ?? 'N/A' }}</div>
                        <div class="tw-body text-xs md:text-sm">Status: {{ $selectedPayment['status'] ?? 'N/A' }}</div>
                        <div class="tw-body text-xs md:text-sm">Reference: {{ $selectedPayment['reference'] ?? 'N/A' }}</div>
                        <div class="tw-body text-xs md:text-sm">Paid At: {{ !empty($selectedPayment['paid_at']) ? Carbon::parse($selectedPayment['paid_at'])->format('M j, Y g:ia') : '—' }}</div>
                        <div class="tw-body text-xs md:text-sm">Created: {{ !empty($selectedPayment['created_at']) ? Carbon::parse($selectedPayment['created_at'])->format('M j, Y g:ia') : '—' }}</div>
                    </div>
                </div>

                <div class="tw-heading text-base md:text-lg">Ride Info</div>
                @if (!empty($selectedPayment['ride']))
                    <div class="card p-3 md:p-4">
                        <div class="grid md:grid-cols-2 gap-3">
                            <div class="tw-body text-xs md:text-sm">From: {{ $selectedPayment['ride']['pickup'] ?? 'N/A' }}</div>
                            <div class="tw-body text-xs md:text-sm">To: {{ $selectedPayment['ride']['destination'] ?? 'N/A' }}</div>
                            <div class="tw-body text-xs md:text-sm">Fare: ₦{{ number_format($selectedPayment['ride']['fare'] ?? 0, 2) }}</div>
                            <div class="tw-body text-xs md:text-sm">Status: {{ $selectedPayment['ride']['status'] ?? 'N/A' }}</div>
                            <div class="tw-body text-xs md:text-sm">Payment Status: {{ $selectedPayment['ride']['payment_status'] ?? 'N/A' }}</div>
                            <div class="tw-body text-xs md:text-sm">Scheduled: {{ !empty($selectedPayment['ride']['scheduled_at']) ? Carbon::parse($selectedPayment['ride']['scheduled_at'])->format('M j, Y g:ia') : '—' }}</div>
                            <div class="tw-body text-xs md:text-sm">Passenger: {{ $selectedPayment['ride']['user']['name'] ?? 'N/A' }}</div>
                            <div class="tw-body text-xs md:text-sm">Driver: {{ $selectedPayment['ride']['driver']['user']['name'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                @else
                    <div class="tw-body text-xs md:text-sm">No ride linked.</div>
                @endif
            @endif
            <div class="flex items-center gap-2">
                <flux:button variant="outline" wire:click="closePayment">Close</flux:button>
            </div>
        </div>
    </flux:modal>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let paymentsChartInstance = null;
        function renderPaymentsChart() {
            const ctx = document.getElementById('paymentsLine');
            if (!ctx) return;
            if (paymentsChartInstance) paymentsChartInstance.destroy();

            const labels = JSON.parse(ctx.dataset.labels || '[]');
            const values = JSON.parse(ctx.dataset.values || '[]');

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? '#404040' : '#e5e7eb';
            const fontColor = isDark ? '#e5e7eb' : '#374151';
            const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary-green').trim() || '#007F5F';

            paymentsChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Amount',
                        data: values,
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
        document.addEventListener('DOMContentLoaded', renderPaymentsChart);
        window.addEventListener('payments-chart-update', renderPaymentsChart);
    </script>
</div>