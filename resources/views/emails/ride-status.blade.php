<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BTransit Ride Update</title>
  <style>
    /* Base reset */
    body { margin:0; padding:0; background:#fdf6e3; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
    img { border:0; outline:none; text-decoration:none; }
    table { border-collapse:collapse; }
    /* Container */
    .wrapper { width:100%; background:#fdf6e3; padding:24px 0; }
    .container { width:100%; max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.08); overflow:hidden; }
    .brand-bar { height:6px; background:#d4af37; }
    /* Header */
    .header { padding:24px; background:#065f46; color:#ffffff; }
    .brand { font-size:20px; font-weight:700; letter-spacing:0.4px; }
    .sub { font-size:13px; color:#f0f0d8; margin-top:4px; }
    /* Status */
    .status { padding:16px 24px; display:flex; align-items:center; gap:12px; }
    .badge { display:inline-block; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:600; color:#111827; background:#f0f0d8; }
    .badge.pending { background:#fde68a; }
    .badge.accepted { background:#93c5fd; }
    .badge.in_progress { background:#c4b5fd; }
    .badge.completed { background:#86efac; }
    .badge.cancelled { background:#fecaca; }
    .status-title { font-size:16px; font-weight:600; color:#111827; }
    /* Highlight time */
    .highlight { background:#fefdf8; padding:12px 24px; border-left:4px solid #d4af37; }
    .highlight-title { font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; }
    .highlight-time { font-size:18px; font-weight:700; color:#111827; margin-top:6px; }
    /* Body */
    .body { padding:24px; color:#374151; line-height:1.6; font-size:15px; }
    .row { display:flex; gap:16px; }
    .card { flex:1; background:#fefdf8; border:1px solid #e5e7eb; border-radius:8px; padding:12px 14px; }
    .card-title { font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:0.06em; }
    .card-value { font-size:16px; color:#111827; font-weight:600; margin-top:2px; }
    .details { margin-top:16px; }
    .cta { text-align:center; padding:16px 24px 28px; }
    .cta a { display:inline-block; background:#d4af37; color:#ffffff !important; text-decoration:none; font-weight:700; padding:12px 18px; border-radius:8px; box-shadow:0 6px 18px rgba(212,175,55,0.35); }
    .footer { padding:16px 24px 24px; font-size:12px; color:#6b7280; text-align:center; }
    /* Responsive */
    @media (max-width: 600px) {
      .status { padding:12px 16px; }
      .body { padding:16px; }
      .row { flex-direction:column; }
      .cta { padding:16px; }
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <div class="brand-bar"></div>
      <div class="header">
        <div class="brand">BTransit</div>
        <div class="sub">Ride update for {{ $recipientName }}</div>
      </div>

      <div class="status">
        <span class="badge {{ $status }}">{{ $statusLabel }}</span>
        <div class="status-title">
          Your ride status is now: <strong>{{ $statusLabel }}</strong>
        </div>
      </div>

      <div class="highlight">
        <div class="highlight-title">Time</div>
        <div class="highlight-time">{{ $scheduledAtHuman }}</div>
      </div>

      <div class="body">
        <div class="row">
          <div class="card">
            <div class="card-title">Pickup</div>
            <div class="card-value">{{ $pickup }}</div>
          </div>
          <div class="card">
            <div class="card-title">Destination</div>
            <div class="card-value">{{ $destination }}</div>
          </div>
        </div>
        <div class="row details">
          <div class="card">
            <div class="card-title">Reference</div>
            <div class="card-value">{{ $reference }}</div>
          </div>
          <div class="card">
            <div class="card-title">Payment</div>
            <div class="card-value">{{ ucfirst($paymentStatus) }}</div>
          </div>
        </div>

        <p style="margin-top:16px;">
          @if($recipientType === 'driver')
            Please review and manage the ride in your driver dashboard.
          @else
            You can review your ride details and make changes if needed.
          @endif
        </p>
      </div>

      <div class="cta">
        <a href="{{ $ctaUrl }}" target="_blank" rel="noopener">View Ride</a>
      </div>

      <div class="footer">
        You’re receiving this because you’re involved in ride {{ $reference }}. If you believe this is a mistake, please contact support.
      </div>
    </div>
  </div>
</body>
</html>