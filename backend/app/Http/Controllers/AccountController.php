<?php

namespace App\Http\Controllers;

use App\Mail\ForgottenPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    /**
     * Handle request for forgotten password.
     *
     * @return \Illuminate\Http\Response
     */
    public function guestForgottenPassRequest(Request $request)
    {
        $email = $request->resetMail;
        $user = User::where(['email' => $email])->first();

        if ($user) {
            $resetToken =  Str::random(15);
            $resetUrl =  request()->getSchemeAndHttpHost() . "/reinitialisation/" . $resetToken;
            $user->resetToken = $resetToken;
            $user->save();
            Mail::to($user->email)->send(new ForgottenPassword($user->identity, $resetUrl));

            return response()->json([
                "success" => true,
                "message" => "L'e-mail de réinitialisation a été envoyé"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Vous ne pouvez pas effectuer cette action"
        ]);
    }


    /**
     * Handle user reset password.
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $newPassword        = $request->newPassword;
        $newPasswordConfirm = $request->newPasswordConfirm;
        $resetToken         = $request->resetToken;
        $email              = $request->email;

        if (Auth::check()) {
            $loggedUser   = Auth::user();
            $userId = $loggedUser->id;
        }

        if ($resetToken) {
            $user = User::where(["resetToken" => $resetToken])->first();
            $user->resetToken = null;
        }

        if ($email) {
            $user = User::where(['email' => $email, 'id' => $userId])->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Token invalide"
            ]);
        }

        if ($newPassword != $newPasswordConfirm) {
            return response()->json([
                'success' => false,
                'message' => "Les mots de passe ne sont pas identique"
            ]);
        }

        $user->password   = Hash::make($newPassword);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Mise à jour effectuée"
        ]);
    }


    /**
     * Handle edit name.
     *
     * @return \Illuminate\Http\Response
     */
    public function editName(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'identity'       => 'required',
            ],
            [
                'required' => 'Le champ :attribute est requis',
            ]
        );

        $errors = $validator->errors();
        if (count($errors) != 0) {
            return response()->json([
                'success' => false,
                'message' => $errors->first()
            ]);
        }

        $loggedUser   = Auth::user();
        $userId = $loggedUser->id;
        $identity = $validator->validated()['identity'];

        $user = User::whereId($userId)->first();
        $user->identity = $identity;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Mise à jour effectuée"
        ]);
    }
}
