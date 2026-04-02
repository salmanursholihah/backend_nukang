<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('pages.admin.users.index', compact('users'));
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return back()->with('success', 'User deleted');
    }
}
