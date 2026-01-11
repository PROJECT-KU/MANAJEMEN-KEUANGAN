<?php

namespace App\Http\Controllers\account;

use App\ClinikScopusPromo;
use App\Clinikscopus;
use App\ClinikscopusPromoSesi;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClinikScopusPromoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // <!--================== MENAMPILKAN DATA ==================-->
    public function index(Request $request)
    {
        // 🔥 AUTO NON-AKTIFKAN PROMO YANG SUDAH LEWAT TANGGAL + JAM
        ClinikscopusPromo::where('status', 'active')
            ->where('tanggal_selesai_promo', '<', Carbon::now())
            ->update([
                'status' => 'non active'
            ]);

        $promos = ClinikscopusPromo::with(['events', 'sesi'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('account.clinik_scopus_promo.index', compact('promos'));
    }
    // <!--================== END ==================-->

    // <!--================== CREATE DATA ==================-->
    public function create()
    {
        $events = Clinikscopus::activeToday()
            ->with([
                'user',
                'biayaPersesi' => function ($q) {
                    $q->where('status', 'active');
                }
            ])
            ->orderBy('tanggal')
            ->get();

        return view('account.clinik_scopus_promo.create', compact('events'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            // 1️⃣ Simpan Promo
            $promo = ClinikscopusPromo::create([
                'id' => Str::uuid(),
                'nama_promo' => $request->nama_promo,
                'status' => $request->status,
                'tanggal_mulai_promo' => $request->tanggal_mulai_promo,
                'tanggal_selesai_promo' => $request->tanggal_selesai_promo,
                'total_kuota_promo' => $request->total_kuota_promo,
                'harga_normal' => str_replace('.', '', $request->harga_normal),
                'ppn' => $request->ppn,
                'tipe_diskon' => $request->tipe_diskon,
                'diskon_persentase' => $request->diskon_persentase,
                'nominal_diskon' => str_replace('.', '', $request->nominal_diskon),
                'kode_diskon' => $request->kode_diskon,
                'total_biaya' => str_replace('.', '', $request->total_biaya),
            ]);

            // 2️⃣ Relasikan Event
            if ($request->clinikscopus_ids) {
                $promo->events()->sync($request->clinikscopus_ids);
            }

            // 3️⃣ Simpan Sesi Promo
            if ($request->sesi_promo) {
                foreach ($request->sesi_promo as $eventId => $sesiList) {
                    foreach ($sesiList as $index => $sesiKey) {
                        ClinikscopusPromoSesi::create([
                            'promo_id' => $promo->id,
                            'clinikscopus_id' => $eventId,
                            'sesi_key' => $sesiKey,
                            'sesi_nomor' => $index + 1
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('account.Clinik-Scopus-Promo.index')
            ->with('success', 'Promo berhasil disimpan');
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function edit($id)
    {
        $promo = ClinikscopusPromo::with(['events', 'sesi'])->findOrFail($id);

        // 🔥 Mapping sesi: [event_id][sesi_key] => true
        $selectedSesi = [];
        foreach ($promo->sesi as $sesi) {
            $selectedSesi[$sesi->clinikscopus_id][$sesi->sesi_key] = true;
        }

        $events = Clinikscopus::activeToday()
            ->with('user')
            ->orderBy('tanggal')
            ->get();

        return view(
            'account.clinik_scopus_promo.edit',
            compact('promo', 'events', 'selectedSesi')
        );
    }

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $promo = ClinikscopusPromo::findOrFail($id);
            // 1️⃣ Update Promo 
            $promo->update([
                'nama_promo' => $request->nama_promo,
                'status' => $request->status,
                'tanggal_mulai_promo' => $request->tanggal_mulai_promo,
                'tanggal_selesai_promo' => $request->tanggal_selesai_promo,
                'total_kuota_promo' => $request->total_kuota_promo,
                'harga_normal' => str_replace('.', '', $request->harga_normal),
                'ppn' => $request->ppn,
                'tipe_diskon' => $request->tipe_diskon,
                'diskon_persentase' => $request->diskon_persentase,
                'nominal_diskon' => str_replace('.', '', $request->nominal_diskon),
                'kode_diskon' => $request->kode_diskon,
                'total_biaya' => str_replace('.', '', $request->total_biaya),
            ]);

            // 2️⃣ Sync Event 
            $promo->events()->sync($request->clinikscopus_ids ?? []);

            // 3️⃣ Reset & Simpan Ulang Sesi 
            ClinikscopusPromoSesi::where('promo_id', $promo->id)->delete();
            if ($request->sesi_promo) {
                foreach ($request->sesi_promo as $eventId => $sesiList) {
                    foreach ($sesiList as $sesiKey) {
                        ClinikscopusPromoSesi::create([
                            'promo_id' => $promo->id,
                            'clinikscopus_id' => $eventId,
                            'sesi_key' => $sesiKey,
                            'sesi_nomor' => $sesiKey,
                        ]);
                    }
                }
            }
        });
        return redirect()->route('account.Clinik-Scopus-Promo.index')->with('success', 'Promo berhasil diperbarui');
    }

    // <!--================== END ==================-->

}
