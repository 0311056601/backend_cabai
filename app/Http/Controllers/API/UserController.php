<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Notifikasi;
use App\Models\Profile;
use App\Models\Saldo;
use App\Models\User;
use Storage;
use Auth;
use File;
use DB;

class UserController extends Controller {
    
    public $successStatus = 401;

    // authorization
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }
    // end authorization

    public function index() {

        $user = Auth::user();

        $all = User::orderBy('created_at', 'desc')->where('gapoktan', $user->gapoktan)->get();
        $petani = User::where('role', 'petani')->where('gapoktan', $user->gapoktan)->get();
        $gapoktan = User::where('role', 'gapoktan')->where('gapoktan', $user->gapoktan)->get();
        $konsumen = User::where('role', 'konsumen')->where('gapoktan', $user->gapoktan)->get();
        $petaniActive = User::where('role', 'petani')->where('status', 1)->where('gapoktan', $user->gapoktan)->get();
        $gapoktanActive = User::where('role', 'gapoktan')->where('status', 1)->where('gapoktan', $user->gapoktan)->get();
        $konsumenActive = User::where('role', 'konsumen')->where('status', 1)->where('gapoktan', $user->gapoktan)->get();

        $this->successStatus       = 200;
        $success['success']        = true;
        $success['all']            = $all;
        $success['petani']         = $petani;
        $success['gapoktan']       = $gapoktan;
        $success['konsumen']       = $konsumen;
        $success['petaniActive']   = $petaniActive;
        $success['gapoktanActive'] = $gapoktanActive;
        $success['konsumenActive'] = $konsumenActive;

        return response()->json($success, $this->successStatus);
    }

    public function daftarPetani(Request $request) {

        $user = Auth::user();

        $cek = User::where('email', $request->email)->first();

        if($cek) {

            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Email sudah terdaftar!";

            return response()->json($success, $this->successStatus);

        } else {

            $data = new User;
            $data->gapoktan = $user->gapoktan;
            $data->username = $request->username;
            $data->email = $request->email;
            $data->password = Hash::make($request->password);
            $data->role = 'petani';
            $data->status = 1;
            $data->save();

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['data']        = $data;

            return response()->json($success, $this->successStatus);

        }

    }

    public function changeStatus($userId) {

        $data = User::find($userId);

        // jika data ditemukan
        if($data) {

            // jika statusnya 1 (aktif) maka ubah menjadi 0 (tidak aktif) dan sebaliknya
            if($data->status == 1) {
                $data->status = 0;
            } else {
                $data->status = 1;
            }
            $data->save();

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['data']        = $data;

            return response()->json($success, $this->successStatus);

        } else { // jika data tidak ditemukan

            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Data user tidak ditemukan!";

            return response()->json($success, $this->successStatus);

        }

    }

    public function detailUser() {
        $auth = Auth::user();

        if($auth) {
            $user = User::find($auth->id);
            $profile = Profile::where('user_id', $user->id)->first();

            $this->successStatus    = 200;
            $success['user']        = $user;
            $success['profile']     = $profile;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Data user tidak ditemukan!";

            return response()->json($success, $this->successStatus);
        }

        
    }

    public function insertProfile(Request $request) {
        $auth = Auth::user();

        if($auth) {
            if($request->email) {
                $user = User::where('id', $auth->id)->first();
                if($user){
                    if($request->nama) {
                        $user->username = $request->nama;
                    } else {
                        $user->username = $user->username;
                    }
                    if($request->email) {
                        $user->email = $request->email;
                    } else {
                        $user->email = $user->email;
                    }
                    if($request->password) {
                        $user->password = Hash::make($request->password);
                        $user->save();
                    } else {
                        $this->successStatus    = 200;
                        $success['success']     = false;
                        $success['message']     = "Harap isi email dan password jika ingin mengubah user login!";

                        return response()->json($success, $this->successStatus);
                    }
                } else {
                    $this->successStatus    = 200;
                    $success['success']     = false;
                    $success['message']     = "User tidak ditemukan!";

                    return response()->json($success, $this->successStatus);
                }

            } else {
                $user = $auth;
                $user->username = $request->nama;
                $user->save();
            }

            $cekProfile = Profile::where('user_id', $user->id)->first();

            if($cekProfile) {
                $profile = $cekProfile;
            } else {
                $profile = new Profile;
                $profile->user_id = $user->id;
            }
            $profile->role = $user->role;

            if($request->nama) {
                $profile->nama = $request->nama;
            }

            if($request->kontak) {
                $profile->kontak = $request->kontak;
            }

            if($request->alamat) {
                $profile->alamat = $request->alamat;
            }

            if($request->kota) {
                $profile->kota = $request->kota;
            }

            if ($request->hasFile('files')) {
                $files = $request->file('files');
    
                $name = $files[0]->getClientOriginalName();
                $files[0]->move(public_path("images/profile/") . $user->id . '/', $name);

                $profile->profile_photo = "images/profile/" . $user->id . '/' . $name;
            }

            if($request->bank) {
                $profile->bank = $request->bank;
                
                if($request->rekening) {
                    $profile->no_rekening = $request->rekening;
                } else {
                    $this->successStatus    = 200;
                    $success['success']     = false;
                    $success['message']     = "Harap isi nomor rekening!";

                    return response()->json($success, $this->successStatus);
                }

                if($request->atas_nama) {
                    $profile->atas_nama = $request->atas_nama;                    
                } else {
                    $this->successStatus    = 200;
                    $success['success']     = false;
                    $success['message']     = "Harap isi atas nama!";

                    return response()->json($success, $this->successStatus);
                }
            } else { // jika tidak menginput bank
                if ($request->rekening) { // jika diinput rekening tapi tidak input bank
                    if($profile->no_rekening) { // jika rekening sudah ada
                        $profile->no_rekening = $request->rekening;
                    } else { // jika rekening belum ada
                        $this->successStatus    = 200;
                        $success['success']     = false;
                        $success['message']     = "Harap pilih bank!";

                        return response()->json($success, $this->successStatus);
                    }
                } else if($request->atas_nama) { // jika diinput atas nama tapi tidak input bank
                    if($profile->atas_nama) { // jika atas nama sudah ada
                        $profile->atas_nama = $request->atas_nama;
                    } else { // jika atas nama belum ada
                        $this->successStatus    = 200;
                        $success['success']     = false;
                        $success['message']     = "Harap pilih bank!";

                        return response()->json($success, $this->successStatus);
                    }
                }
            }

            $profile->save();

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['user']        = $user;
            $success['profile']     = $profile;

            return response()->json($success, $this->successStatus);
        } else {
            $this->successStatus    = 200;
            $success['success']     = false;
            $success['message']     = "Harap login, dan coba lagi!";

            return response()->json($success, $this->successStatus);
        }
    }

    public function getNotif() {

        $user = Auth::user();

        $notif = Notifikasi::where('user_id', $user->id)->with('getPenerima', 'getProfile')->get();
        // $unread = Notifikasi::where('user_id', $user->id)->where('status', 'Belum Dibaca')->with('getPenerima', 'getProfile')->get();
        $unread = Notifikasi::where('user_id', $user->id)->where('status', 'Belum Dibaca')->with('getPenerima', 'getProfile')->get();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $notif;
        $success['unread']      = $unread;

        return response()->json($success, $this->successStatus);

    }

    public function ListNotifikasi() {

        $user = Auth::user();
        $all = Notifikasi::orderBy('updated_at', 'desc')->get();
        $notifByUser = Notifikasi::where('user_id', $user->id)->with('getPenerima', 'getProfile')->get();
        $notifUnreadByUser = Notifikasi::where('user_id', $user->id)->where('status', 'Belum Dibaca')->with('getPenerima', 'getProfile')->get();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['all']         = $all;
        $success['listNotif']   = $notifByUser;
        $success['unread']      = $notifUnreadByUser;

        return response()->json($success, $this->successStatus);

    }

    public function NotifikasiDetail($notifId) {

        $user = Auth::user();
        $data = Notifikasi::where('user_id', $user->id)->where('id', $notifId)->with('getPenerima', 'getProfile')->first();        
        if($data) {
            $data->status = "Sudah Dibaca";
            $data->save();

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['data']        = $data;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['message']     = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);

        }

    }

    public function HapusNotif($notifId) {

        $data = Notifikasi::find($notifId);

        if($data) {
            $data->delete();
            
            $this->successStatus    = 200;
            $success['success']     = true;

            return response()->json($success, $this->successStatus);

        } else {

            $this->successStatus    = 200;
            $success['success']     = true;
            $success['message']     = "Data tidak ditemukan";

            return response()->json($success, $this->successStatus);

        }

    }

    public function detailSaldo() {

        $user = Auth::user();

        $data = Saldo::where('user_id', $user->id)->with('getDetail')->orderBy('created_at', 'desc')->get();

        $this->successStatus    = 200;
        $success['success']     = true;
        $success['data']        = $data;

        return response()->json($success, $this->successStatus);

    }

}
