<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RideExportController extends Controller
{
    public function adminRidesPdf(Request $request)
    {
        $fromInput = $request->query('from');
        $toInput = $request->query('to');
        $from = null;
        $to = null;
        try { if (!empty($fromInput)) { $from = Carbon::parse($fromInput); } } catch (\Throwable $e) { $from = null; }
        try { if (!empty($toInput)) { $to = Carbon::parse($toInput); } } catch (\Throwable $e) { $to = null; }

        $query = Ride::with(['user', 'driver.user', 'payment'])
            ->where('status', 'completed');
        if ($from) { $query->where('created_at', '>=', $from); }
        if ($to) { $query->where('created_at', '<=', $to); }

        $rides = $query->orderByDesc('created_at')->get();

        $data = [
            // 'title' => 'Completed Rides History',
            'subtitle' => 'Filtered by selected date range',
            'scope' => 'completed',
            'rides' => $rides,
            'generatedAt' => now(),
            'filters' => [
                'from' => $from ? $from->format('M j, Y g:ia') : null,
                'to' => $to ? $to->format('M j, Y g:ia') : null,
            ],
        ];

        try {
            return Pdf::loadView('pdf.rides', $data)
                ->setPaper('a4', 'portrait')
                ->download('btransit-rides-' . now()->format('Ymd-His') . '.pdf');
        } catch (\Throwable $e) {
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