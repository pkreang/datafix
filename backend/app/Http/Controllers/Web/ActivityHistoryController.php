<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ActivityHistoryController extends Controller
{
    public function index()
    {
        return view('settings.activity-history.index');
    }
}
