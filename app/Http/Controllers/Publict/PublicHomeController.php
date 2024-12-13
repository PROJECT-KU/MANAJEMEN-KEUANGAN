<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicHomeController extends Controller
{

    public function home(Request $request)
    {
        return view('public.home');
    }
}
