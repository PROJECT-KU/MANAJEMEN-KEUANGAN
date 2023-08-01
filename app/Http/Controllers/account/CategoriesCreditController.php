<?php

namespace App\Http\Controllers\account;

use App\CategoriesCredit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriesCreditController extends Controller
{
    /**
     * CategoriesCreditController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        // Get categories based on user role and company
        if ($user->level == 'staff') {
            $categories = CategoriesCredit::join('users', 'categories_credit.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
                ->orderBy('categories_credit.created_at', 'DESC')
                ->paginate(10);
        } else {
            // If not a manager or staff, show categories based on user_id
            $categories = CategoriesCredit::where('user_id', $user->id)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        }

        return view('account.categories_credit.index', compact('categories'));
        //$categories = CategoriesCredit::where('user_id', Auth::user()->id)
        //    ->orderBy('created_at', 'DESC')
        //    ->paginate(10);
        //return view('account.categories_credit.index', compact('categories'));
    }


    public function search(Request $request)
    {

        $search = $request->get('q');
        $user = Auth::user();

        if ($user->level == 'staff') {
            // Search for categories based on the user's company and name
            $categories = CategoriesCredit::join('users', 'categories_credit.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
                ->where('categories_credit.name', 'LIKE', '%' . $search . '%')
                ->orderBy('categories_credit.created_at', 'DESC')
                ->paginate(10);
        } else {
            // If not a manager or staff, search for categories based on user_id and name
            $search = $request->get('q');
            $categories = CategoriesCredit::where('user_id', Auth::user()->id)
                ->where(
                    'name',
                    'LIKE',
                    '%' . $search . '%'
                )
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        }

        return view('account.categories_credit.index', compact('categories'));
        //$search = $request->get('q');
        //$categories = CategoriesCredit::where('user_id', Auth::user()->id)
        //    ->where('name', 'LIKE', '%' .$search. '%')
        //    ->orderBy('created_at', 'DESC')
        //    ->paginate(10);
        //return view('account.categories_credit.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('account.categories_credit.create');
    }


    public function store(Request $request)
    {
        //set validasi required
        $this->validate($request, [
            'name'  => 'required'
        ],
            //set message validation
            [
                'name.required' => 'Masukkan Nama Kategori !',
            ]
        );

        $name = strtoupper($request->input('name'));
        //Eloquent simpan data
        $save = CategoriesCredit::create([
            'user_id'       => Auth::user()->id,
            'name'          => $name
        ]);
        //cek apakah data berhasil disimpan
        if($save){
            //redirect dengan pesan sukses
            return redirect()->route('account.categories_credit.index')->with(['success' => 'Data Berhasil Disimpan!']);
        }else{
            //redirect dengan pesan error
            return redirect()->route('account.categories_credit.index')->with(['error' => 'Data Gagal Disimpan!']);
        }

    }


    public function edit(Request $request, CategoriesCredit $categoriesCredit)
    {
        return view('account.categories_credit.edit', compact('categoriesCredit'));
    }

    
    public function update(Request $request, CategoriesCredit $categoriesCredit)
    {
        //set validasi required
        $this->validate($request, [
            'name'  => 'required'
        ],
            //set message validation
            [
                'name.required' => 'Masukkan Nama Kategori !',
            ]
        );

        $name = strtoupper($request->input('name'));
        //Eloquent simpan data
        $update = CategoriesCredit::whereId($categoriesCredit->id)->update([
            'user_id'       => Auth::user()->id,
            'name'          => $name
        ]);
        //cek apakah data berhasil disimpan
        if($update){
            //redirect dengan pesan sukses
            return redirect()->route('account.categories_credit.index')->with(['success' => 'Data Berhasil Diupdate!']);
        }else{
            //redirect dengan pesan error
            return redirect()->route('account.categories_credit.index')->with(['error' => 'Data Gagal Diupdate!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = CategoriesCredit::find($id)->delete($id);

        if($delete){
            return response()->json([
                'status' => 'success'
            ]);
        }else{
            return response()->json([
                'status' => 'error'
            ]);
        }
    }
}
