<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function show()
    {
        return request()->user()->discovery;
    }
}
