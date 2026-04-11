<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\TukangLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TukangLocationController extends Controller
{
    // =========================================================
    // UPDATE — Update koordinat GPS tukang
    // PUT /api/tukang/location
    // Body:
    //   latitude  : numeric (required)
    //   longitude : numeric (required)
    // =========================================================

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $location = TukangLocation::updateOrCreate(
            ['tukang_id' => $request->user()->id],
            [
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Lokasi berhasil diupdate.',
            'data'    => [
                'latitude'    => $location->latitude,
                'longitude'   => $location->longitude,
                'is_online'   => $location->is_online,
                'last_seen_at' => $location->last_seen_at?->toDateTimeString(),
            ],
        ]);
    }


    // =========================================================
    // TOGGLE — Toggle status online/offline
    // PUT /api/tukang/location/toggle
    // Body:
    //   is_online : boolean (required)
    // =========================================================

    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'is_online' => 'required|boolean',
        ]);

        $location = TukangLocation::updateOrCreate(
            ['tukang_id' => $request->user()->id],
            [
                'is_online'   => $request->is_online,
                'last_seen_at' => now(),
            ]
        );

        // Sync ke tukang_profiles juga
        $request->user()->tukangProfile()->update([
            'is_available' => $request->is_online,
        ]);

        $status = $request->is_online ? 'Online' : 'Offline';

        return response()->json([
            'status'  => true,
            'message' => "Status kamu sekarang {$status}.",
            'data'    => [
                'is_online'    => $location->is_online,
                'is_available' => $request->is_online,
                'last_seen_at' => $location->last_seen_at?->toDateTimeString(),
            ],
        ]);
    }
}
