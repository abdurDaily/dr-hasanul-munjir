<?php

namespace App\Http\Controllers\Site;

use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreAppointmentRequest;
use App\Services\Booking\AvailabilityService;
use App\Services\Booking\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    /** Live availability for the whole booking window. Never cached. */
    public function availability(AvailabilityService $availability): JsonResponse
    {
        return response()->json([
            'today' => $availability->today()->toDateString(),
            'last' => $availability->lastBookableDay()->toDateString(),
            'slotMinutes' => $availability->slotMinutes(),
            'days' => $availability->calendar(),
        ])->header('Cache-Control', 'no-store');
    }

    public function store(StoreAppointmentRequest $request, BookingService $booking): JsonResponse
    {
        try {
            $appointment = $booking->book($request->bookingData());
        } catch (SlotUnavailableException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['time' => [$e->getMessage()]]], 409);
        }

        return response()->json([
            'reference' => $appointment->reference,
            'message' => 'Your request for '.$appointment->startsAt()->format('l, j M Y \a\t g:i A')
                .' is pending. The clinic will call you on '.$appointment->patient_phone.' to confirm it.',
        ], 201);
    }
}
