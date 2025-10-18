<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RideExportController extends Controller
{
    public function adminRidesPdf()
    {
        $rides = Ride::with(['user', 'driver.user', 'payment'])
            ->orderByDesc('created_at')
            ->get();

        $data = [
            'title' => 'All Rides History',
            'subtitle' => 'Complete list of rides and payments',
            'scope' => 'admin',
            'rides' => $rides,
            'generatedAt' => now(),
        ];

        try {
            return Pdf::loadView('pdf.rides', $data)
                ->setPaper('a4', 'portrait')
                ->download('btransit-rides-all.pdf');
        } catch (\Throwable $e) {
            // Fallback to HTML view if PDF library is not installed yet
            return response()->view('pdf.rides', $data);
        }
    }

    public function driverRidesPdf()
    {
        $user = Auth::user();
        $driver = $user->driver ?? null;
        if (!$driver) {
            abort(403, 'No driver profile associated with this account.');
        }

        $rides = Ride::with(['user', 'driver.user', 'payment'])
            ->where('driver_id', $driver->id)
            ->orderByDesc('created_at')
            ->get();

        $data = [
            'title' => 'My Ride History',
            'subtitle' => 'Driver rides and payment summary',
            'scope' => 'driver',
            'rides' => $rides,
            'generatedAt' => now(),
            'ownerName' => $driver->user?->name ?? 'Driver',
        ];

        try {
            return Pdf::loadView('pdf.rides', $data)
                ->setPaper('a4', 'portrait')
                ->download('btransit-rides-driver-' . $driver->id . '.pdf');
        } catch (\Throwable $e) {
            return response()->view('pdf.rides', $data);
        }
    }

    public function userRidesPdf()
    {
        $user = Auth::user();

        $rides = Ride::with(['user', 'driver.user', 'payment'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $data = [
            'title' => 'My Ride History',
            'subtitle' => 'Your rides and payment summary',
            'scope' => 'user',
            'rides' => $rides,
            'generatedAt' => now(),
            'ownerName' => $user->name ?? 'User',
        ];

        try {
            return Pdf::loadView('pdf.rides', $data)
                ->setPaper('a4', 'portrait')
                ->download('btransit-rides-user-' . $user->id . '.pdf');
        } catch (\Throwable $e) {
            return response()->view('pdf.rides', $data);
        }
    }
}