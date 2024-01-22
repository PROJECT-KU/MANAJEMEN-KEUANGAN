<?php

namespace App\Http\Controllers\recruitment;

use App\Recruitment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    /**
     * PenyewaanController constructor.
     */

    public function index(Request $request)
    {

        return view('recruitment.index');
    }
}
