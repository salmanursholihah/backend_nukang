<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\TukangProfile;
use Illuminate\Http\Request;

class TukangProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('tukangProfile');

        return response()->json([
            'data' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'string'],
        ]);

        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? $user->phone,
        ]);

        TukangProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $data['address'] ?? null,
                'photo' => $data['photo'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'data' => $user->load('tukangProfile'),
        ]);
    }
}
