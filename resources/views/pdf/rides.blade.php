<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Ride Export' }}</title>
    <style>
        :root {
            --brand: #0ee920; /* sky-500 */
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
        body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 11px; color: var(--text); background: var(--bg); margin: 0; }
        .container { padding: 18px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo { width: 28px; height: 28px; background: var(--brand); border-radius: 8px; }
        .title { font-size: 24px; font-weight: 700; }
        .subtitle { color: var(--muted); margin-top: 4px; }
        .meta { margin-top: 6px; font-size: 11px; color: var(--muted); }
        .pill { display:inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background: var(--bg-muted); border: 1px solid var(--border); color: var(--muted); }
        .grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
        .card { border: 1px solid var(--border); border-radius: 12px; padding: 16px; background: var(--bg); }
        .card-title { font-weight: 600; margin-bottom: 8px; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table th { text-align: left; font-size: 11px; color: var(--muted); padding: 10px; border-bottom: 1px solid var(--border); word-wrap: break-word; white-space: normal; }
        .table td { font-size: 11px; padding: 10px; border-bottom: 1px solid var(--border); word-wrap: break-word; white-space: normal; }
        .row-alt { background: var(--bg-muted); }
        .badge { display:inline-block; padding: 3px 8px; border-radius: 999px; font-size: 11px; color: #fff; }
        .badge-success { background: var(--success); }
        .badge-warning { background: var(--warning); }
        .badge-danger { background: var(--danger); }
        .right { text-align: right; }
        .muted { color: var(--muted); }
        .footer { margin-top: 16px; font-size: 11px; color: var(--muted); }

        @page { margin: 15mm; }
        @media print {
            body { font-size: 11px; }
            .container { padding: 0; }
            .title { font-size: 18px; }
            .table th, .table td { padding: 8px; }
        }
        @media screen and (max-width: 768px) {
            .container { padding: 12px; }
            .title { font-size: 20px; }
            .card { padding: 12px; }
            .table th, .table td { font-size: 11px; padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="brand">
            <div class="logo"></div>
            <div>
                <div class="title">Rides History</div>
                <div class="subtitle">{{ $subtitle }} @if(!empty($ownerName)) · {{ $ownerName }} @endif</div>
                <div class="meta">Generated: {{ ($generatedAt ?? now())->format('M j, Y g:i A') }}</div>
                @if(!empty($filters['from']) || !empty($filters['to']))
                    <div class="meta">Date range: {{ !empty($filters['from']) ? \Carbon\Carbon::parse($filters['from'])->format('M j, Y g:i A') : '—' }} to {{ !empty($filters['to']) ? \Carbon\Carbon::parse($filters['to'])->format('M j, Y g:i A') : '—' }}</div>
                @endif
            </div>
            {{-- <div style="margin-left:auto"><span class="pill">BTransit · PDF Export</span></div> --}}
        </header>

        @php
            $totalRides = $rides->count();
            $totalPaid = $rides->sum(function($ride){
                $payment = optional($ride->payment);
                if (($payment->status ?? null) === 'success') {
                    return (float)($payment->amount ?? 0);
                }
                if (($ride->payment_status ?? null) === 'paid') {
                    return (float)($ride->fare ?? 0);
                }
                return 0;
            });
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
                <div class="card-title">Rides</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Scheduled</th>
                            <th>User</th>
                            <th>Driver</th>
                            <th>Pickup → Destination</th>
                            <th class="right">Fare</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rides as $i => $ride)
                            @php
                                $isAlt = $i % 2 === 1;
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
                                <td>{{ optional($ride->scheduled_at)->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>{{ $ride->user?->name ?? '—' }}</td>
                                <td>{{ $ride->driver?->user?->name ?? '—' }}</td>
                                <td>
                                    <div><strong>{{ $ride->pickup }}</strong></div>
                                    <div class="muted">→ {{ $ride->destination }}</div>
                                </td>
                                <td class="right">{{ $currency }}{{ $formatAmount($ride->fare) }}</td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="muted">No rides found for this export.</td>
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