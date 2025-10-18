<?php

namespace App\Observers;

use App\Mail\RideStatusMail;
use App\Models\Ride;
use Illuminate\Support\Facades\Mail;

class RideObserver
{
    public function created(Ride $ride): void
    {
        // Send on booking to user
        if ($ride->user && $ride->user->email) {
            Mail::to($ride->user->email)->send(new RideStatusMail($ride, 'user'));
        }
        // Send to driver if already assigned
        if ($ride->driver && $ride->driver->user && $ride->driver->user->email) {
            Mail::to($ride->driver->user->email)->send(new RideStatusMail($ride, 'driver'));
        }
    }

    public function updated(Ride $ride): void
    {
        $statusChanged = $ride->wasChanged('status');
        $timeChanged = $ride->wasChanged('scheduled_at');
        $driverChanged = $ride->wasChanged('driver_id');

        if (!($statusChanged || $timeChanged || $driverChanged)) {
            return; // nothing actionable
        }

        // Notify user of status/time changes
        if ($ride->user && $ride->user->email) {
            Mail::to($ride->user->email)->send(new RideStatusMail($ride, 'user'));
        }

        // Notify driver of status/time or assignment changes
        if ($ride->driver && $ride->driver->user && $ride->driver->user->email) {
            Mail::to($ride->driver->user->email)->send(new RideStatusMail($ride, 'driver'));
        }
    }
}