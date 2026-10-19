<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use App\Support\RingkasBeranda;
use Illuminate\Http\Request;

class PublicHomeController extends Controller
{
    /**
     * Beranda.
     *
     * Dulu badannya cuma `return view('public.home')` — nol data — sehingga
     * seluruh halaman depan ditulis tangan di markahnya dan tidak tahu apa
     * pun tentang aplikasi di belakangnya. Perakitnya ada di
     * RingkasBeranda; alasan tiap saringannya ditulis di sana.
     */
    public function home(Request $request)
    {
        return view('public.home', RingkasBeranda::rakit());
    }
}
