<?php

namespace App\Http\Controllers\Publict;

use App\Meme;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublicScopusKafeController extends Controller
{

    public function public(Request $request)
    {
        $datas = DB::table('meme')
            ->select('meme.id', 'meme.name', 'meme.tanggal', 'meme.sesi', 'meme.waktu_mulai', 'meme.waktu_selesai', 'meme.kuota', 'meme.biaya', 'meme.deskripsi', 'meme.lokasi', 'meme.status', 'meme.gambar', 'meme.created_at')
            ->orderBy('meme.created_at', 'DESC')
            ->paginate(6);

        return view('public.scopus_kafe.index', compact('datas'));
    }

    public function FormPendaftaran(Request $request, $id, $token)
    {
        $data = Meme::findOrFail($id);
        return view('public.scopus_kafe.form_pendaftaran', compact('data'));
    }
}
