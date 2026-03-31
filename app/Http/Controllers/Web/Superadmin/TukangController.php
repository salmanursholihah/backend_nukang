<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\TukangProfile;
use Illuminate\Http\Request;

class TukangController extends Controller
{
public function index()
    {
        $tukangs = TukangProfile::with('user')->latest()->get();

        return view('admin.tukang.index', compact('tukangs'));
    }

    public function verify($id)
    {
        $tukang = TukangProfile::findOrFail($id);

        $tukang->update([
            'is_verified' => true
        ]);

        return back()->with('success', 'Tukang verified');
    }
}