<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request){
        // Validacion de datos
        $request->validate([
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'correo' => 'required|string|email|unique:users',
            'password' => 'required',
            'fecha_nacimiento' => 'required|date'  
        ]);

        $user = new User();
        $user->nombres = $request->nombres;
        $user->apellidos = $request->apellidos;
        $user->correo = $request->correo;
        $user->password = Hash::make($request->password);
        $user->fecha_nacimiento = $request->fecha_nacimiento;

        $user->save();

        // Respuesta
        /*return response()->json([
            'message' => 'Metodo register OK'
        ]);*/

        return response($user, Response::HTTP_CREATED);
    }
    public function login(Request $request){

        $data = $request->validate([
            'correo' => 'required|string|email',
            'password' => 'required'
        ]);

        if(Auth::attempt($data)){
            $user = Auth::user();
            $token = $user->createToken('token')->plainTextToken;
            $cookie = cookie('cookie_toker', $token, 60*24); // 1 dia
            return response([
                'token' => $token
            ], Response::HTTP_OK)->withCookie($cookie);

        }else{
            return response([
                'message' => 'Datos incorrectos'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function userProfile(){
       return response()->json([
            'message' => 'Metodo userProfile OK',
            'data' => auth()->user()
        ]);
    }

    public function logout(){
       auth()->user()->tokens()->delete();
       return response()->json([
            'message' => 'Has cerrado sesion',
       ]);
    }

    public function allUsers(){
        $users = User::all();
        return response()->json([
            'message' => 'Usuarios registrados',
            'data' => $users
        ]);
    }

    public function forgetPassword(Request $request){
        try{
            $user = User::where('correo', $request->correo)->get();
            if(count($user)>0){
                $token = rand(10,100000);
                $domain = URL::to('/');
                $url = $domain.'/reset-password?token='.$token;

                $data['url'] = $url;
                $data['token'] = $token;
                $data['correo'] = $request->correo;
                $data['title'] = 'Password Reset';
                $data['body'] = 'Please click the below link to reset your password';

                Mail::send('forgetPasswordMail', ['data'=>$data], function($message) use ($data){
                    $message->to($data['correo'], $data['title'])->subject('Password Reset');
                });

                $datetime = Carbon::now()->format('Y-m-d H:i:s');
                PasswordReset::updateOrCreate(
                    ['email'=>$request->correo],
                    ['email'=>$request->correo, 'token'=>$token, 'created_at'=>$datetime
                ]);

                return response()->json(['success'=>true, 'msg'=>'Please, check your email for reset password']);

            }else{
                return response()->json(['success'=>false, 'msg'=>'El correo no existe']);
            }

        }catch(\Exception $e){
            return response()->json(['success'=>false, 'msg'=>$e->getMessage()]);
        }
    }
}
