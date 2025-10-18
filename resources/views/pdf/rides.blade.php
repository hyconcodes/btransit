<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Ride Export' }}</title>
    <style>
        :root {
            --brand: #0ea5e9; /* sky-500 */
            --brand-dark: #0284c7; /* sky-600 */
            --text: #111827; /* gray-900 */
            --muted: #6b7280; /* gray-500 */
            --border: #e5e7eb; /* gray-200 */
            --bg: #ffffff;
            --bg-muted: #f9fafb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; }
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; color: var(--text); background: var(--bg); margin: 0; }
        .container { padding: 24px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo { width: 28px; height: 28px; background: var(--brand); border-radius: 8px; }
        .title { font-size: 24px; font-weight: 700; }
        .subtitle { color: var(--muted); margin-top: 4px; }
        .meta { margin-top: 6px; font-size: 12px; color: var(--muted); }
        .pill { display:inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background: var(--bg-muted); border: 1px solid var(--border); color: var(--muted); }
        .grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
        .card { border: 1px solid var(--border); border-radius: 12px; padding: 16px; background: var(--bg); }
        .card-title { font-weight: 600; margin-bottom: 8px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; font-size: 12px; color: var(--muted); padding: 10px; border-bottom: 1px solid var(--border); }
        .table td { font-size: 12px; padding: 10px; border-bottom: 1px solid var(--border); }
        .row-alt { background: var(--bg-muted); }
        .badge { display:inline-block; padding: 3px 8px; border-radius: 999px; font-size: 11px; color: #fff; }
        .badge-success { background: var(--success); }
        .badge-warning { background: var(--warning); }
        .badge-danger { background: var(--danger); }
        .right { text-align: right; }
        .muted { color: var(--muted); }
        .footer { margin-top: 16px; font-size: 11px; color: var(--muted); }
    </style>
</head>
<body>
    <div class="container">
        <header class="brand">
            <div class="logo"></div>
            <div>
                <div class="title">{{ $title }}</div>
                <div class="subtitle">{{ $subtitle }} @if(!empty($ownerName)) · {{ $ownerName }} @endif</div>
                <div class="meta">Generated: {{ ($generatedAt ?? now())->format('Y-m-d H:i') }}</div>
            </div>
            <div style="margin-left:auto"><span class="pill">BTransit · PDF Export</span></div>
        </header>

        @php
            $totalRides = $rides->count();
            $totalPaid = $rides->sum(function($ride){ return optional($ride->payment)->status === 'paid' ? (optional($ride->payment)->amount ?? 0) : 0; });
            $currency = '₦';
            $formatAmount = function($amount){ return number_format((float)($amount ?? 0), 2); };
        @endphp

        <section class="grid" style="margin-top:16px">
            <div class="card">
                <div class="card-title">Summary</div>
                <div style="display:flex; gap:16px">
                    <div><div class="muted">Total rides</div><div style="font-weight:700; font-size:18px">{{ $totalRides }}</div></div>
                    <div><div class="muted">Total paid</div><div style="font-weight:700; font-size:18px">{{ $currency }}{{ $formatAmount($totalPaid) }}</div></div>
                    <div><div class="muted">Scope</div><div style="font-weight:700; font-size:18px">{{ ucfirst($scope ?? 'all') }}</div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title">Rides & Payments</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Created</th>
                            <th>Scheduled</th>
                            <th>User</th>
                            <th>Driver</th>
                            <th>Pickup → Destination</th>
                            <th class="right">Fare</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rides as $i => $ride)
                            @php
                                $isAlt = $i % 2 === 1;
                                $paid = optional($ride->payment)->status === 'paid';
                                $paymentBadgeClass = $paid ? 'badge-success' : (optional($ride->payment)->status === 'pending' ? 'badge-warning' : 'badge-danger');
                                $status = strtoupper($ride->status ?? 'unknown');
                                $statusClass = match($ride->status){
                                    'completed' => 'badge-success',
                                    'in_progress' => 'badge-warning',
                                    'rejected', 'cancelled' => 'badge-danger',
                                    default => 'badge-warning'
                                };
                            @endphp
                            <tr class="{{ $isAlt ? 'row-alt' : '' }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $ride->reference }}</td>
                                <td>{{ optional($ride->created_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ optional($ride->scheduled_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>{{ $ride->user?->name ?? '—' }}</td>
                                <td>{{ $ride->driver?->user?->name ?? '—' }}</td>
                                <td>
                                    <div><strong>{{ $ride->pickup }}</strong></div>
                                    <div class="muted">→ {{ $ride->destination }}</div>
                                </td>
                                <td class="right">{{ $currency }}{{ $formatAmount($ride->fare) }}</td>
                                <td>
                                    <div>{{ $currency }}{{ $formatAmount(optional($ride->payment)->amount) }}</div>
                                    <div class="muted">{{ optional($ride->payment)->payment_method ?? '—' }}</div>
                                    <div class="badge {{ $paymentBadgeClass }}">{{ strtoupper(optional($ride->payment)->status ?? 'N/A') }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="muted">No rides found for this export.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="footer">BTransit · {{ now()->year }} · Generated by system</div>
    </div>
</body>
</html>