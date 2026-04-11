<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\TukangProfile;
use App\Models\User;
use Illuminate\Http\Request;

class TukangController extends Controller
{
    public function index()
    {
        $tukangs = TukangProfile::with('user')->latest()->paginate(10);

        return view('pages.admin.tukang.index', compact('tukangs'));
    }

    public function create()
    {
        $users = User::where('role', 'tukang')->get();

        return view('pages.admin.tukang.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:tukang_profiles,user_id',
            'address' => 'nullable',
            'photo' => 'nullable',
            'rating' => 'nullable|numeric',
            'total_jobs' => 'nullable|integer',
            'is_verified' => 'required'
        ]);

        TukangProfile::create([
            'user_id' => $request->user_id,
            'address' => $request->address,
            'photo' => $request->photo,
            'rating' => $request->rating ?? 0,
            'total_jobs' => $request->total_jobs ?? 0,
            'is_verified' => $request->is_verified,
        ]);

        return redirect()->route('tukangs.index')
            ->with('success', 'Tukang created successfully');
    }

    public function edit($id)
    {
        $tukang = TukangProfile::findOrFail($id);

        return view('pages.admin.tukang.edit', compact('tukang'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'address' => 'nullable',
            'photo' => 'nullable',
            'rating' => 'nullable|numeric|min:0|max:5',
            'total_jobs' => 'nullable|integer',
            'is_verified' => 'required'
        ]);

        $tukang = TukangProfile::findOrFail($id);

        $tukang->update([
            'address' => $request->address,
            'photo' => $request->photo,
            'rating' => $request->rating ?? 0,
            'total_jobs' => $request->total_jobs ?? 0,
            'is_verified' => $request->is_verified,
        ]);

        return redirect()->route('tukangs.index')
            ->with('success', 'Tukang updated successfully');
    }
    public function verify($id)
    {
        $tukang = TukangProfile::findOrFail($id);

        $tukang->update([
            'is_verified' => true
        ]);

        return back()->with('success', 'Tukang verified');
    }

    public function reject($id)
    {
        $tukang = TukangProfile::findOrFail($id);

        $tukang->update([
            'is_verified' => false
        ]);

        return back()->with('success', 'Tukang rejected');
    }

    public function destroy($id)
    {
        TukangProfile::findOrFail($id)->delete();

        return redirect()->route('tukangs.index')
            ->with('success', 'Tukang deleted successfully');
    }
}
