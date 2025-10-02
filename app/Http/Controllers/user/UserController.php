<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use app\Models\User;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['permission:users.index|users.create|users.edit|users.delete']);
    // }

    public function index()
    {
        $users = DB::table('users')
        ->leftJoin('master_departements', 'users.department', '=', 'master_departements.id')
        ->select('users.*', 'master_departements.name as nama_dev')
        ->get()
        ->map(function ($user) {
            $userModel = User::find($user->id);
            $userModel->nama_dev = $user->nama_dev; // Mengatur nilai dari join
            return $userModel;
        });

        return view('user.index', compact('users'));

      
    }


    public function staffjson()
	{
		return Datatables::of(User::where('utype', 'ADM'))->make(true);
	}


    public function mhsuser()
    {   
        return view('user.mhsuser');
    }

    public function jsonusermhs()
	{
		return Datatables::of(User::where('utype', 'MHS'))->make(true);
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $roles = Role::latest()->get();
        $roles = Role::where('id','<>','1')->get();
        return view('user.create',compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


public function store(Request $request)
{
    // Validasi input
    $validated = $request->validate([
        'name'                  => ['required', 'string', 'max:150'],
        'email'                 => ['required', 'email', 'max:150', 'unique:users,email'],
        'password'              => ['required', 'string', 'min:8', 'confirmed'],
        'role'                  => ['nullable', 'array'],
        'role.*'                => ['string'] // nama role (Spatie)
    ]);

    DB::beginTransaction();
    try {
        // Create user
        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
        ]);

        // Assign roles (jika ada)
        if (!empty($validated['role'])) {
            $user->syncRoles($validated['role']);
        } else {
            // kosongkan role jika tidak dipilih apa pun
            $user->syncRoles([]);
        }

        DB::commit();

        // redirect sukses (samakan dengan yang dipakai di view: session('pesan'))
        return redirect('/user')->with('pesan', 'Data berhasil ditambahkan.');
        // Jika ingin konsisten dengan update() milikmu: ->with('status','Data Berhasil Ditambah')
    } catch (\Throwable $e) {
        DB::rollBack();

        // Optional: log error untuk debugging
        // \Log::error('Gagal tambah user: '.$e->getMessage());

        return back()
            ->withInput()
            ->with('error', 'Gagal menambahkan data. '.$e->getMessage());
    }
}

    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $roles = Role::get();
        return view('user.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name'      => 'required',
            'email'     => 'required'
        ]);

        $user = User::findOrFail($user->id);

        if ($request->input('password') == "") {
            $user->update([
                'name'      => $request->input('name'),
                'email'     => $request->input('email')
               
            ]);
        } else {
            $user->update([
                'name'      => $request->input('name'),
                'email'     => $request->input('email'),
                'password'  => bcrypt($request->input('password'))
                

            ]);
        }

        //assign role
        $user->syncRoles($request->input('role'));

        if ($user) {
            //redirect dengan pesan sukses
            return redirect('/user')->with('status','Data Berhasil Ditambah');
        }
            else{
                return redirect('/user')->with('error','Gagal Ditambah');
            }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $cek= User::where([
            'id'       =>$user->id
            ])->first();
        User::destroy($user->id);
        return redirect('/user')->with('status','Data Berhasil Dihapus');
  
    }
}
