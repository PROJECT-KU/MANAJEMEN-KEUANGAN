<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\CategoriesAnalisisBibliometrik;
use App\AnalisisBibliometrik;
use App\Mail\AnalisisBibliometrikMail;
use Illuminate\Support\Facades\Mail;

class PublicClinikScopusController extends Controller
{

    // <!--================== MENAMPILKAN DATA ==================-->
    public function index(Request $request)
    {

        return view('public.clinik_scopus.index');
    }
    // <!--================== END ==================-->

}
