<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // <!--================== TAMPILAN DATA ==================-->
    public function index(Request $request)
    {
        $status = $request->get('status');
        $verified = $request->get('verified');
        $email = $request->get('email');
        $start = $request->get('start');
        $end = $request->get('end');
        $perPage = $request->get('per_page', 10);

        $users = DB::table('users')
            ->where('level', 'user')
            ->when($status, function ($q, $status) {
                if ($status === 'active') {
                    $q->where('status', 'active');
                } elseif ($status === 'non active') {
                    $q->whereNull('status')->orWhere('status', 'non active');
                }
            })
            ->when($verified, function ($q, $verified) {
                if ($verified === 'verified') {
                    $q->whereNotNull('email_verified_at');
                } elseif ($verified === 'unverified') {
                    $q->whereNull('email_verified_at');
                }
            })
            ->when($email, function ($q, $email) {
                $q->where('email', $email);
            })
            ->when($start, function ($q, $start) {
                $q->whereDate('created_at', '>=', $start);
            })
            ->when($end, function ($q, $end) {
                $q->whereDate('created_at', '<=', $end);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage)
            ->appends($request->all());

        $allUsers = DB::table('users')->where('level', 'user')->get();

        return view('account.customer.index', compact(
            'users',
            'allUsers',
            'status',
            'verified',
            'email',
            'start',
            'end',
            'perPage'
        ));
    }
    // <!--================== END ==================-->

    // <!--================== REAL TIME DATA  ==================-->
    public function pollData(Request $request)
    {
        $lastId = $request->get('last_id');

        $newUsers = DB::table('users')
            ->where('level', 'user')
            ->when($lastId, function ($q) use ($lastId) {
                $q->where('id', '>', $lastId);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json($newUsers);
    }
    // <!--================== END ==================-->

    // <!--================== SEARCH ==================-->
    public function search(Request $request)
    {
        $search = $request->get('q');
        $user = Auth::user();

        $users = DB::table('users')
            ->select(
                'users.*',
                DB::raw("COALESCE(users.status, 'non active') as status"),
                DB::raw("
                CASE 
                    WHEN users.email_verified_at IS NOT NULL 
                    THEN 'Sudah Diverifikasi' 
                    ELSE 'Belum Diverifikasi' 
                END as verifikasi_status
            ")
            )
            ->where('level', 'user')
            ->where(function ($query) use ($search) {
                $query->where('users.full_name', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('users.username', 'LIKE', "%{$search}%")
                    ->orWhere('users.email_verified_at', 'LIKE', "%{$search}%")
                    ->orWhere('users.jenis', 'LIKE', "%{$search}%")
                    ->orWhere('users.level', 'LIKE', "%{$search}%")
                    ->orWhere('users.status', 'LIKE', "%{$search}%")
                    ->orWhere('users.telp', 'LIKE', "%{$search}%")
                    ->orWhereRaw("
                    CASE 
                        WHEN users.email_verified_at IS NOT NULL 
                        THEN 'Sudah Diverifikasi' 
                        ELSE 'Belum Diverifikasi' 
                    END LIKE ?
                ", ["%{$search}%"]);
            })
            ->orderBy('users.created_at', 'DESC')
            ->paginate(10);

        $users->appends(['q' => $search]);

        $notFound = $users->isEmpty();

        return view('account.customer.index', compact('users', 'search', 'notFound'));
    }
    // <!--================== END ==================-->

    // <!--================== FILTER ==================-->
    public function filter(Request $request)
    {
        $status = $request->get('status');
        $verified = $request->get('verified');
        $email = $request->get('email');
        $start = $request->get('datestart'); // dari input id="dateStart"
        $end = $request->get('dateend');     // dari input id="dateEnd"
        $perPage = $request->get('per_page', 10);

        $users = DB::table('users')
            ->select('users.*', DB::raw("COALESCE(users.status, 'non active') AS status"))
            ->where('level', 'user')
            ->when($status, function ($query, $status) {
                if ($status === 'active') {
                    $query->where('users.status', 'active');
                } elseif ($status === 'non active') {
                    $query->where(function ($q) {
                        $q->whereNull('users.status')
                            ->orWhere('users.status', 'non active');
                    });
                }
            })
            ->when($verified, function ($query, $verified) {
                if ($verified === 'verified') {
                    $query->whereNotNull('users.email_verified_at');
                } elseif ($verified === 'unverified') {
                    $query->whereNull('users.email_verified_at');
                }
            })
            ->when($email, function ($q, $email) {
                $q->where('email', $email);
            })
            ->when($start, function ($q, $start) {
                $q->whereDate('users.created_at', '>=', $start);
            })
            ->when($end, function ($q, $end) {
                $q->whereDate('users.created_at', '<=', $end);
            })
            ->orderBy('users.created_at', 'DESC')
            ->paginate($perPage)
            ->appends($request->all());

        if ($request->ajax()) {
            return view('account.customer.partials.table-body', compact('users'))->render();
        }

        return view('account.customer.index', compact('users'));
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('account.customer.edit', compact('user'));
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE FOTO PROFIL ==================-->
    public function updatePhoto(Request $request, $id)
    {
        $user = User::find($id);

        // Menghapus foto lama jika ada
        if ($user->gambar && file_exists(public_path('assets/img/profil/' . $user->gambar))) {
            unlink(public_path('assets/img/profil/' . $user->gambar));
        }

        // Menyimpan foto baru di assets/public/img/profil
        $fileName = time() . '.' . $request->gambar->extension();
        $request->gambar->move(public_path('assets/img/profil'), $fileName);

        // Update nama file gambar di database
        $user->gambar = $fileName;
        $user->save();

        // Redirect dengan session success
        return redirect()->back()->with('success', 'Foto profil berhasil diperbarui.');
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA DIRI ==================-->
    public function updatediri(Request $request, $id)
    {
        $user = User::find($id);

        // Validate input data
        try {
            $request->validate([
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'telp' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Check if the validation error is for the email field
            if ($e->validator->errors()->has('email')) {
                // Return back with SweetAlert message for duplicate email
                return redirect()->back()->with('erroremailterpakai', 'Email sudah terdaftar.')->withErrors($e->validator);
            }

            // Handle other validation errors
            throw $e;
        }

        // Update email only if provided and different from the current email
        if ($request->has('email') && $request->input('email') !== $user->email) {
            $user->email = $request->input('email');
            $user->email_verified_at = null; // Reset email verification if email changes
        }

        if ($request->has('telp')) {
            $user->telp = $request->input('telp');
        }

        // Save user data
        $user->save();

        // Return success message
        return redirect()->back()->with('statusdataprofil', 'Data profil berhasil diperbarui.');
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA DIRI PENGGUNA ==================-->
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Use old data if no new input is provided
        $user->full_name = $request->input('full_name') ?? $user->full_name;
        $user->username = $request->input('username') ?? $user->username;
        $user->status = $request->input('status') ?? $user->status;

        // Save the updated user data
        $user->save();

        // Redirect with a success message
        return redirect()->back()->with('statusdataprofil', 'Data profil berhasil diperbarui.');
    }
    // <!--================== END ==================-->

    // <!--================== VERIFIKASI EMAIL ==================-->
    public function verifyEmail($id)
    {
        $user = User::findOrFail($id);
        $user->email_verified_at = now(); // Mark email as verified
        $user->status = 'active';
        $user->save();

        // Redirect with success message
        return redirect()->back()->with('statusverifikasiemail', 'Email berhasil diverifikasi.');
    }

    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan.'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data customer berhasil dihapus!'
        ]);
    }
    // <!--================== END ==================-->
}
