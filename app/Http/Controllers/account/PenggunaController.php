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

class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // <!--================== TAMPILAN DATA ==================-->
    public function index()
    {
        $user = Auth::user();

        if ($user->level == 'manager' || $user->level == 'ceo') {
            // Jika user adalah 'manager', ambil semua data pengguna staff yang memiliki perusahaan yang sama dengan user
            $users = DB::table('users')
                ->where('company', $user->company)
                ->whereIn('level', ['staff', 'karyawan', 'trainer', 'manager', 'ceo'])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        } else {
            // Jika user bukan 'manager', ambil hanya data pengguna itu sendiri
            $users = DB::table('users')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        }

        return view('account.pengguna.index', compact('users'));
    }
    // <!--================== END ==================-->

    // <!--================== SEARCH ==================-->
    public function search(Request $request)
    {
        $search = $request->get('q');
        $user = Auth::user();

        $users = DB::table('users')
            ->where('company', $user->company)
            ->where('jenis', 'bisnis')
            ->where('level', '!=', 'user')
            ->where(function ($query) use ($search) {
                $query->where('full_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('username', 'LIKE', '%' . $search . '%')
                    ->orWhere('jobdesk', 'LIKE', '%' . $search . '%')
                    ->orWhere('status', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        $users->appends(['q' => $search]);

        // JIKA REQUEST DARI AJAX / LIVE SEARCH
        if ($request->ajax() || $request->hasHeader('X-Requested-With')) {
            return view('account.pengguna.index', compact('users'));
        }

        // JIKA AKSES MANUAL VIA URL (Bukan Live Search)
        if ($users->isEmpty()) {
            return redirect()->route('account.pengguna.index')->with('error', 'Data tidak ditemukan.');
        }

        return view('account.pengguna.index', compact('users'));
    }
    // <!--================== END ==================-->

    // <!--================== CREATE DATA ==================-->
    public function create()
    {
        return view('account.pengguna.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'company' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'), // Check email uniqueness in the 'users' table
            ],
            'username' => [
                'required',
                Rule::unique('users', 'username'), // Check username uniqueness in the 'users' table
            ],
            'password' => 'required',
            'level' => 'required',
            'jenis' => 'required',
            'telp' => 'required',
            'tanggal_lahir' => 'required',
            'norek' => 'required',
            'bank' => 'required',
        ], [
            'full_name.required'   => 'Masukkan Nama Lengkap!',
            'company.required'  => 'Masukkan Nama Tempat Anda Bekerja!',
            'username.required'          => 'Masukkan Username Anda!',
            'telp.required'          => 'Masukkan No Telp Anda!',
            'tanggal_lahir.required'          => 'Masukkan Tanggal Lahir Anda!',
            'norek.required'          => 'Masukkan Nomor Rekening Anda!',
            'bank.required'          => 'Masukkan BANK Anda!',
            'gambar.max' => 'Ukuran gambar tidak boleh melebihi 5MB!',
            'jobdesk.required'          => 'Masukkan Jobdesk Anda!',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.pengguna.create')
                ->withErrors($validator)
                ->withInput();
        }


        // Create a new user instance
        $user = new User();
        $user->full_name = $request->input('full_name');
        $user->company = $request->input('company');
        $user->email = $request->input('email');
        $user->username = $request->input('username');
        $user->password = bcrypt($request->input('password'));
        $user->level = $request->input('level');
        $user->jenis = $request->input('jenis');
        $user->telp = $request->input('telp');
        $user->notif = $request->input('notif');
        $user->tenggat = $request->input('tenggat');
        $user->title = $request->input('title');
        $user->tanggal_lahir = $request->input('tanggal_lahir');
        $user->norek = $request->input('norek');
        $user->bank = $request->input('bank');
        $user->jobdesk = $request->input('jobdesk');
        $user->email_verified_at = $request->input('email_verified_at') ? now() : null;

        if ($request->input('status')) {
            $user->status = 'active';
        } else {
            $user->status = 'nonactive';
        }

        // Save the new user
        $user->save();

        // Redirect with success message
        return redirect()->route('account.pengguna.index')->with('success', 'Data pengguna berhasil ditambahkan!');
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function edit($id)
    {
        $user = User::findOrFail($id);

        // Calculate work duration if email is verified and status is active
        $workDuration = '';
        if ($user->email_verified_at && $user->status === 'active') {
            $now = now();
            $diff = $user->created_at->diff($now);

            $years = $diff->y;
            $months = $diff->m;
            $days = $diff->d;

            if ($years > 0) {
                $workDuration .= $years . ($years > 1 ? ' tahun ' : ' tahun ');
            }

            if ($months > 0 || $years > 0) {
                $workDuration .= $months . ($months > 1 ? ' bulan ' : ' bulan ');
            }

            if ($days > 0 || $months == 0 || $years == 0) {
                $workDuration .= $days . ($days > 1 ? ' hari' : ' hari');
            }
        } else {
            $workDuration = 'Email belum diverifikasi atau status tidak aktif';
        }

        return view('account.pengguna.edit', compact('user', 'workDuration'));
    }
    // <!--================== END ==================-->

    // <!--================== DETAIL DATA ==================-->
    public function detail($id)
    {
        $user = User::findOrFail($id);

        return view('account.pengguna.detail', compact('user'));
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
                'jobdesk' => 'nullable|string',
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

        // Update jobdesk and telp if present
        if ($request->has('jobdesk')) {
            $user->jobdesk = $request->input('jobdesk');
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
        $user->company = $request->input('company') ?? $user->company;
        $user->level = $request->input('level') ?? $user->level;
        $user->status = $request->input('status') ?? $user->status;
        $user->jenis = $request->input('jenis') ?? $user->jenis;
        $user->tanggal_lahir = $request->input('tanggal_lahir') ?? $user->tanggal_lahir;
        $user->norek = $request->input('norek') ?? $user->norek;
        $user->bank = $request->input('bank') ?? $user->bank;

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
                'status' => false,
                'message' => 'Pengguna tidak ditemukan!'
            ]);
        }

        // Hapus foto jika ada (Opsional tapi disarankan)
        if ($user->gambar && file_exists(public_path('assets/img/profil/' . $user->gambar))) {
            unlink(public_path('assets/img/profil/' . $user->gambar));
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data pengguna berhasil dihapus!'
        ]);
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE COMPANY ==================-->
    public function company($id)
    {
        $user = User::findOrFail($id);

        return view('account.company.index', compact('user'));
    }
    public function updateCompany(Request $request, $id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Save image to path if provided
        if ($request->hasFile('logo_company')) {
            $image = $request->file('logo_company');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $imageName;
            $image->move(public_path('images'), $imageName); // Store the image
        } else {
            // If no new image uploaded, keep using the old image path
            $imagePath = $user->logo_company;
        }

        // Update user data
        $user->update([
            'company' => $request->input('company'),
            'alamat_company' => $request->input('alamat_company'),
            'telp_company' => $request->input('telp_company'),
            'email_company' => $request->input('email_company'),
            'pj_company' => $request->input('pj_company'),
            'logo_company' => $imagePath ?? null,
        ]);

        // If the user is a manager, update the company data for employees in the same company
        if ($user->level === 'manager') {
            $managerCompany = $user->company;

            // Update company data for all users with the same company
            User::where('company', $managerCompany)->update([
                'alamat_company' => $request->input('alamat_company'),
                'telp_company' => $request->input('telp_company'),
                'email_company' => $request->input('email_company'),
                'pj_company' => $request->input('pj_company'),
                'logo_company' => $imagePath ?? null,
            ]);
        }

        // Redirect with success message
        return redirect()->route('account.company.edit', $user->id)->with('success', 'Data Perusahaan berhasil diperbarui!');
    }
    // <!--================== END ==================-->
}
