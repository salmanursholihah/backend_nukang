<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
public function index()
    {
        $services = Service::with('category')->latest()->get();
        $categories = Category::all();

        return view('admin.services.index', compact('services', 'categories'));
    }

    public function store(Request $request)
    {
        Service::create($request->all());

        return back();
    }

    public function update(Request $request, $id)
    {
        Service::findOrFail($id)->update($request->all());

        return back();
    }

    public function destroy($id)
    {
        Service::findOrFail($id)->delete();

        return back();
    }
}