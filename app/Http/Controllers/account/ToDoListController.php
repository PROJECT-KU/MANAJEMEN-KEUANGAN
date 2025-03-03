<?php

namespace App\Http\Controllers\account;

use App\User;
use App\Todolist;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\AssignTaskTodolist;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ToDoListController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function generateRandomId($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $id;
    }

    // <!--================== TAMPILAN DATA ==================-->
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // ✅ Update status otomatis jika melewati deadline
        DB::table('todolist')
            ->where('tanggal_deadline', '<', $today)
            ->whereNotIn('status', ['Completed', 'Melebihi Deadline']) // ✅ Completed tetap utuh
            ->update(['status' => 'Melebihi Deadline']);

        // ✅ Status yang akan diambil
        $statuses = ['Assign Task', 'In Progress', 'Testing', 'Revisi', 'Completed', 'Melebihi Deadline'];
        $data = [];

        foreach ($statuses as $status) {
            $varName = 'Datas' . str_replace(' ', '', $status);

            $query = DB::table('todolist')
                ->leftJoin('users as user1', 'todolist.user_id', '=', 'user1.id')
                ->leftJoin('users as user2', 'todolist.user_id_kedua', '=', 'user2.id')
                ->select('todolist.*', 'user1.full_name as full_name', 'user2.full_name as full_name_kedua')
                ->where('todolist.status', $status)
                ->orderBy('todolist.created_at', 'DESC');

            // ✅ Jika bukan manajer, filter berdasarkan `user_id` atau `user_id_kedua`
            if ($user->level !== 'manager') {
                $query->where(function ($q) use ($user) {
                    $q->where('todolist.user_id', $user->id)
                        ->orWhere('todolist.user_id_kedua', $user->id);
                });
            }

            $tasks = $query->get();

            // 🔹 Hitung progress checklist untuk setiap task
            foreach ($tasks as $task) {
                $totalTasks = count(explode(',', $task->tasklist));
                $checkedTasks = count(json_decode($task->checked, true) ?? []);
                $task->progressPercent = $totalTasks > 0 ? round(($checkedTasks / $totalTasks) * 100) : 0;
            }

            $data[$varName] = $tasks;
        }

        // ✅ Hitung jumlah tugas dengan status 'Assign Task'
        $totalAssignTaskQuery = DB::table('todolist')->where('status', 'Assign Task');

        if ($user->level !== 'manager') {
            $totalAssignTaskQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('user_id_kedua', $user->id);
            });
        }

        $totalAssignTask = $totalAssignTaskQuery->count();

        return view('account.todolist.index', $data + [
            'user' => $user,
            'totalAssignTask' => $totalAssignTask
        ]);
    }
    // <!--================== END ==================-->

    // <!--================== CREATE DATA ==================-->
    public function create()
    {
        $user = Auth::user();

        if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'admin') {

            $datas = DB::table('users')
                ->select('users.id', 'users.full_name')
                ->where('users.company', $user->company)
                ->groupBy('users.id', 'users.full_name')
                ->orderBy('users.created_at', 'DESC')
                ->get();

            return view('account.todolist.create', compact('datas'));
        } else {
            $users = User::where('id', $user->id)
                ->select('id', 'full_name')
                ->get();
            return view('account.todolist.create', compact('users'));
        }
    }

    public function store(Request $request)
    {
        $id_task = $this->generateRandomId(5);

        // Maksimal ukuran file dalam byte (10 MB)
        $maxFileSize = 10 * 1024 * 1024;

        // Handle File Anatomy Upload
        if ($request->hasFile('file_task')) {
            $FileReferensi = $request->file('file_task');
            if ($FileReferensi->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file To Do List melebihi 10 MB.');
            }
            $ReferensiName = time() . '_' . Str::uuid() . 'todolist.' . $FileReferensi->getClientOriginalExtension();
            $FileTasskPathFirst = 'todolist/' . $ReferensiName;
            $FileReferensi->move(public_path('todolist'), $ReferensiName);
        }

        $save = Todolist::create([
            'id_task'                       => $id_task,
            'user_id'                       => $request->input('user_id'),
            'user_id_kedua'                 => $request->input('user_id_kedua'),
            'tanggal_assign'                => $request->input('tanggal_assign'),
            'tanggal_deadline'              => $request->input('tanggal_deadline'),
            'status'                        => $request->input('status'),
            'prioritas_task'                => $request->input('prioritas_task'),
            'judul_task'                    => $request->input('judul_task'),
            'deskripsi'                     => $request->input('deskripsi'),
            'tasklist'                      => $request->input('tasklist'),
            'link_akses'                    => $request->input('link_akses'),
            'file_task'                     => $FileTasskPathFirst ?? null,
        ]);

        if ($save) {
            $user = User::findOrFail($request->input('user_id'));
            $appName = 'Rumah Scopus Foundation';
            $isStatus = $request->input('status') == 'Assign Task';
            if ($isStatus) {
                $user1 = User::find($request->input('user_id'));
                $user2 = User::find($request->input('user_id_kedua'));

                if ($user1) {
                    Mail::to($user1->email)->send(new AssignTaskTodolist($user1, $save, $appName, $isStatus));
                }

                if ($user2) {
                    Mail::to($user2->email)->send(new AssignTaskTodolist($user2, $save, $appName, $isStatus));
                }
            }
            return redirect()->route('account.todolist.index')->with('success', 'Data To Do List Paper Berhasil Disimpan!');
        } else {
            return redirect()->route('account.todolist.index')->with('error', 'Data To Do List Paper Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE STATUS TASK OTOMATIS ==================-->
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:todolist,id',
            'status' => 'required|string|in:Assign Task,In Progress,Testing,Revisi,Completed'
        ]);

        $task = Todolist::find($request->id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task tidak ditemukan!'], 404);
        }

        $task->status = $request->status;
        $task->save();

        return response()->json(['success' => true, 'message' => 'Status diperbarui!']);
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function edit($id)
    {
        $user = Auth::user(); // Pastikan ini instance dari model User
        $todolist = Todolist::findOrFail($id);

        $todolist->tanggal_assign = $todolist->tanggal_assign ? Carbon::parse($todolist->tanggal_assign) : null;
        $todolist->tanggal_deadline = $todolist->tanggal_deadline ? Carbon::parse($todolist->tanggal_deadline) : null;

        $datas = DB::table('users')
            ->select('users.id', 'users.full_name', 'users.level') // Pastikan level diambil
            ->where('users.company', $user->company)
            ->groupBy('users.id', 'users.full_name', 'users.level')
            ->orderBy('users.created_at', 'DESC')
            ->get();

        return view('account.todolist.edit', compact('todolist', 'datas', 'user'));
    }

    public function update(Request $request, $id)
    {
        $todolist = Todolist::findOrFail($id); // Ambil data lama

        // Maksimal ukuran file dalam byte (10 MB)
        $maxFileSize = 10 * 1024 * 1024;
        $FileTasskPathFirst = $todolist->file_task; // Default tetap menggunakan file lama

        // Handle File Upload jika ada file baru
        if ($request->hasFile('file_task')) {
            $FileReferensi = $request->file('file_task');

            // Cek ukuran file
            if ($FileReferensi->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file To Do List melebihi 10 MB.');
            }

            // Simpan file baru
            $ReferensiName = time() . '_' . Str::uuid() . 'todolist.' . $FileReferensi->getClientOriginalExtension();
            $FileTasskPathFirst = 'todolist/' . $ReferensiName;
            $FileReferensi->move(public_path('todolist'), $ReferensiName);
        }

        // Update data tanpa membuat duplikasi
        $todolist->update([
            'user_id'         => $request->input('user_id') ?? $todolist->user_id,
            'user_id_kedua'   => $request->input('user_id_kedua') ?? $todolist->user_id_kedua,
            'tanggal_assign'  => $request->input('tanggal_assign') ?? $todolist->tanggal_assign,
            'tanggal_deadline' => $request->input('tanggal_deadline') ?? $todolist->tanggal_deadline,
            'status'          => $request->input('status') ?? $todolist->status,
            'prioritas_task'  => $request->input('prioritas_task') ?? $todolist->prioritas_task,
            'judul_task'      => $request->input('judul_task') ?? $todolist->judul_task,
            'deskripsi'       => $request->input('deskripsi') ?? $todolist->deskripsi,
            'tasklist'        => $request->input('tasklist') ?? $todolist->tasklist,
            'link_akses'      => $request->input('link_akses') ?? $todolist->link_akses,
            'file_task'       => $FileTasskPathFirst, // Gunakan file lama jika tidak ada update
        ]);

        return redirect()->route('account.todolist.index')->with('success', 'Checklist berhasil diperbarui!');
    }

    public function updateChecklist(Request $request)
    {
        $todolist = Todolist::findOrFail($request->id);
        $task = $request->task;
        $isChecked = $request->checked; // 1 jika dicentang, 0 jika tidak

        // Decode data checked lama
        $checkedTasks = json_decode($todolist->checked, true) ?? [];

        if ($isChecked) {
            // Tambahkan task ke checked list jika belum ada
            if (!in_array($task, $checkedTasks)) {
                $checkedTasks[] = $task;
            }
        } else {
            // Hapus task dari checked list jika dicentang kembali
            $checkedTasks = array_filter($checkedTasks, function ($item) use ($task) {
                return $item !== $task;
            });
        }

        // Simpan kembali ke database dalam format JSON
        $todolist->checked = json_encode(array_values($checkedTasks));
        $todolist->save();

        return response()->json(['message' => 'Checklist updated successfully']);
    }

    public function addTask(Request $request)
    {
        $todolist = TodoList::findOrFail($request->id);

        // Ambil tasklist lama dan tambahkan task baru
        $tasklistArray = explode(',', $todolist->tasklist);
        $tasklistArray[] = $request->input('task'); // Tambah task baru
        $tasklistArray = array_unique(array_filter(array_map('trim', $tasklistArray))); // Hilangkan duplikat & spasi

        $todolist->update([
            'tasklist' => implode(', ', $tasklistArray),
        ]);

        return response()->json([
            'message' => 'Check List Task berhasil diperbarui',
            'tasklist' => $todolist->tasklist
        ]);
    }

    public function removeTask(Request $request)
    {
        $todolist = Todolist::findOrFail($request->id);
        $task = $request->task;

        // Ubah tasklist ke array, hapus task yang sesuai
        $tasks = explode(',', $todolist->tasklist);
        $tasks = array_map('trim', $tasks);
        $tasks = array_filter($tasks, function ($t) use ($task) {
            return $t !== $task;
        });

        // Simpan kembali data yang telah dihapus
        $todolist->tasklist = implode(', ', $tasks);
        $todolist->save();

        return response()->json(['message' => 'Task berhasil dihapus!']);
    }
    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy(Request $request, $id)
    {
        // Temukan data berdasarkan ID
        $data = Todolist::findOrFail($id);

        $files = [
            $data->file_task,
        ];

        // Iterasi untuk menghapus file
        foreach ($files as $filePath) {
            if ($filePath && file_exists(public_path($filePath))) {
                unlink(public_path($filePath));
            }
        }

        // Hapus data dari database
        $data->delete();

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus beserta file terkait.',
        ]);
    }
    // <!--================== END ==================-->
}
