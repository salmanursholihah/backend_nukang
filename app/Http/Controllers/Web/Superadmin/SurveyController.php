<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SurveyRequest;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
 public function index()
    {
        $surveys = SurveyRequest::with([
            'customer',
            'tukang',
            'service'
        ])->latest()->paginate();

        return view('pages.admin.surveys.index', compact('surveys'));
    }
}

