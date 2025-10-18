<?php

namespace App\Mail;

use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RideStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ride $ride;
    public string $recipientType; // 'user' | 'driver'

    public function __construct(Ride $ride, string $recipientType = 'user')
    {
        $this->ride = $ride;
        $this->recipientType = $recipientType;
    }

    public function build(): self
    {
        $status = (string) ($this->ride->status ?? 'pending');
        $statusLabel = $this->labelForStatus($status);
        $subject = "[BTransit] Ride {$statusLabel} – {$this->ride->pickup} → {$this->ride->destination}";

        $driverName = optional($this->ride->driver?->user)->name ?? 'Assigned Driver';
        $userName = optional($this->ride->user)->name ?? 'Passenger';
        $recipientName = $this->recipientType === 'driver' ? $driverName : $userName;

        $ctaUrl = $this->recipientType === 'driver' ? route('driver.rides') : route('user.rides.book');

        return $this->subject($subject)
            ->view('emails.ride-status')
            ->with([
                'recipientName' => $recipientName,
                'statusLabel' => $statusLabel,
                'status' => $status,
                'scheduledAtHuman' => optional($this->ride->scheduled_at)?->format('M j, Y g:ia') ?? 'Not set',
                'pickup' => (string) $this->ride->pickup,
                'destination' => (string) $this->ride->destination,
                'reference' => (string) $this->ride->reference,
                'paymentStatus' => (string) ($this->ride->payment_status ?? 'pending'),
                'ctaUrl' => $ctaUrl,
                'recipientType' => $this->recipientType,
            ]);
    }

    private function labelForStatus(string $status): string
    {
        return match ($status) {
            'in_progress' => 'In Progress',
            'accepted' => 'Accepted',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }
}