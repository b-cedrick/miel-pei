<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    //protected $maxAttempts = 2; //  Default is 5
    //protected $decayMinutes = 1; // Default is 1
    
    /**
     *Create a new AuthController instance 
     * 
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a JWT via given credentials.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        
        $client_ip = $request -> ip();
        $res = DB::table('users')->where('email', '=', $request->email)->get();
        $max_nb_login_attempts = 3;
        $last_login_attemps = Carbon::parse($res[0]->last_login_attemps);
        $now = Carbon::now();
        // $interval = $last_login_attemps->diff($now)->format('%H:%I:%S');
        $interval_in_seconde = $last_login_attemps->diffInSeconds($now);
        $temps_attente = 60; // 60secondes (1 minutes)
        $temps_restant = $temps_attente - $interval_in_seconde;

        if($temps_restant <= 0){

            $custom_message = ['regex'=> 'Votre mot de passe doit etre constitue d\'au moins 8 caracteres et contenir au moins: 1 majuscule, 1 minuscule, 1 caractere special, 1 chiffre'];
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => [
                    'required',
                    'string',
                    'regex: /(?=^.{8,}$)((?=.*\d)(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/',
                    ]
            ],$custom_message);
        
            // $validator = Validator::make($request->all(), [
            //     'email' => 'required|email',
            //     'password' => [
            //         'required',
            //         'string',
            //         'min:6'
            //         ]
            // ]);

            if($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            if(!$token = auth()->attempt($validator->validated())) {
                // $res = DB::table('users')->where('email', '=', $request->email)->get();

                $message = '';   

                if($res[0]->nb_login_attempts < ($max_nb_login_attempts -1) ){ // On prends $max_nb_login_attempts - 1 car la dernière tentative sera dans le elsey
                    
                    $nb_login_attempts = $res[0]->nb_login_attempts+1;
                    $message = 'Accès non autorisé : après '.($max_nb_login_attempts - $nb_login_attempts).' utres tentatives échoué votre compte sera temporairement bloqué!';
                    $affected = DB::table('users')
                    ->where('email', $request->email)
                    ->update(['nb_login_attempts' => $nb_login_attempts, 'ip_client'=>$client_ip]);                  
                }else{
                    //ecrire dans un fichier log
                    Log::info('Adresse Ip de l\'utilisateur :'.$client_ip.' Mr./Mme '.$res[0]->name.' titulaire de l\'email : '.$request->email.' a tenté de se connécté '.$max_nb_login_attempts.' fois avec un movais couple d\'identifiant/mots de passe');
        
                    $message = 'Vous avez atteint le nombre maximal de tentative, veuillez réssayer dans '.$temps_attente.' secondes'; 
                    
                    $affected = DB::table('users')
                    ->where('email', $request->email)
                    ->update(['nb_login_attempts' => 0,'last_login_attemps'=>$now, 'ip_client'=>$client_ip]);               
                }           
     
                // return response()->json(['error' => 'Unauthorized'], 401);
                return response()->json($message, 401);
            }

            $affected = DB::table('users')
            ->where('email', $request->email)
            ->update(['nb_login_attempts' => 0, 'last_login_attemps' => Carbon::now()->subYear(100), 'ip_client'=>$client_ip]);               
            return $this->createNewToken($token);
        }        
        
        return response()->json('Il vous reste '.$temps_restant.' secondes avant de pouvoir vous reconnecter.', 401);
    }

    /**
     * Register a User
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {

        $todayDateTime = Carbon::now();

        $custom_message = ['regex'=> 'Votre mot de passe doit etre constitue d\'au moins 8 caracteres et contenir au moins: 1 majuscule, 1 minuscule, 1 caractere special, 1 chiffre'];
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => [
                'required',
                'string',
                'confirmed',
                'regex: /(?=^.{8,}$)((?=.*\d)(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/',
                ]
        ],$custom_message);

        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password),'nb_login_attempts'=> 0,'ip_client' => '','last_login_attemps' => $todayDateTime]
        ));

        return response()->json([
            'message' => 'User successfuly registered',
            'user' => $user
        ], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
