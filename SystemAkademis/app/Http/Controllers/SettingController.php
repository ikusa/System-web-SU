<?php

namespace app\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use app\mahasiswa;
use app\course;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $biodata = \app\mahasiswa::select('*')
            ->join('studi_program', 'studi_program.id', '=', 'mahasiswa.id_program_studi')
            ->where('user_id', Auth::id())
            ->first();

        return view('setting', ['biodata'=>$biodata]);
    }
    public function coloumn(Request $request)
    {
        $email = $request->input('email');

        $table = \app\mahasiswa::select('*')
             ->where('email', $email)
             ->orderBy('id', 'desc')
             ->take(1)
             ->get();
        return $table ;
    }
}
