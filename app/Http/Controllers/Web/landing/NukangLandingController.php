<?php

namespace App\Http\Controllers\Web\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NukangLandingController extends Controller
{
    /**
     * Tampilkan halaman landing page Nukang.
     */
    public function index()
    {
        return view('landing.nukang');
    }
}
