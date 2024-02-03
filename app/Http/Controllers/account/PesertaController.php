<?php

namespace App\Http\Controllers\account;

use App\Peserta;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesertaController extends Controller
{
    /**
     * PenyewaanController constructor.
     */
    function generateRandomToken($length = 50)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_+=<>?';
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }


    public function index(Request $request)
    {

        return view('account.peserta.form');
    }

    // Add this method to your PesertaController class
    public function testimoni($id)
    {
        // Retrieve the Peserta data based on the provided ID
        $peserta = Peserta::findOrFail($id);

        // Pass the Peserta data to the view
        return view('account.peserta.testimoni', compact('peserta'));
    }


    public function store(Request $request)
    {
        $token = $this->generateRandomToken(30);
        $save = Peserta::create([
            'email'                     => $request->input('email'),
            'nama'                      => $request->input('nama'),
            'afiliasi'                  => $request->input('afiliasi'),
            'judul'                     => $request->input('judul'),
            'jurnal'                    => $request->input('jurnal'),
            'refrensi'                  => $request->input('refrensi'),
            'digital_writing'           => $request->input('digital_writing'),
            'mendeley'                  => $request->input('mendeley'),
            'persentase_penyelesaian'   => $request->input('persentase_penyelesaian'),
            'submit'                    => $request->input('submit'),
            'token'                     => $token,
        ]);

        // Redirect with success or error message
        $pesertaId = $save->id;
        if ($save) {
            // Redirect to testimoni route with peserta ID
            return redirect()->route('account.peserta.testimoni', ['id' => $pesertaId, 'token' => $token]);
        } else {
            // Redirect with an error message if data creation fails
            return redirect()->route('account.peserta.form')->with('error', 'Lembar Kerja Scopus Camp Gagal Disimpan!');
        }
    }

    public function update(Request $request, $id)
    {
        $peserta = Peserta::findOrFail($id);
        $peserta->update([
            'scopus_camp'       => $request->input('scopus_camp'),
            'materi'            => $request->input('materi'),
            'makanan'           => $request->input('makanan'),
            'pelayanan'         => $request->input('pelayanan'),
            'tempat'            => $request->input('tempat'),
            'terfavorit'        => $request->input('terfavorit'),
            'terbaik'           => $request->input('terbaik'),
            'menyebalkan'       => $request->input('menyebalkan'),
            'kritik'            => $request->input('kritik'),
        ]);

        // Redirect with success or error message
        if ($peserta) {
            $request->session()->flash('updateSuccess', true);
            return redirect()->route('account.peserta.form')->with('success', 'Lembar Kerja & Evaluasi Scopus Camp Berhasil Disimpan!');
        } else {
            // Redirect with an error message if data creation fails
            return redirect()->route('account.peserta.form')->with('error', 'Lembar Kerja & Evaluasi Scopus Camp Gagal Disimpan!');
        }
    }
}
