<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchedWord  = $request->words;
        $searchedState = $request->suspended;

        // all field is empty
        if ($searchedWord == "" && $searchedState == "") {
            $users = User::orderBy("identity", "asc")
            ->where("role_id", "!=", "1")
            ->paginate(5);
            return UserResource::collection($users);
        }

        // word field is not empty || suspends field is empty
        if ($searchedWord != "" && $searchedState == "") {
            $users = User::orderBy("identity", "asc")
            ->where("role_id", "!=", "1")
            ->where(function($query) use ($searchedWord){
                $query->where("identity", "LIKE", "%" . $searchedWord . "%")
                ->orWhere("email", "LIKE", "%" . $searchedWord . "%");
            })->paginate(5);
            return UserResource::collection($users);
        }

        // word field is empty || suspends field is not empty
        if ($searchedWord == "" && $searchedState != "") {
            $users = User::orderBy("identity", "asc")
            ->where("suspended", "=", $searchedState)
            ->where("role_id", "!=", "1")
            ->paginate(5);
            return UserResource::collection($users);
        }

         // all field is not empty
        if ($searchedWord != "" && $searchedState != "") {
            $users = User::orderBy("identity", "asc")
            ->where("role_id", "!=", "1")
            ->where("suspended", "=", $searchedState)
            ->where(function ($query) use ($searchedWord) {
                $query->where("identity", "LIKE", "%" . $searchedWord . "%")
                    ->orWhere("email", "LIKE", "%" . $searchedWord . "%");
            })->paginate(5);
            return UserResource::collection($users);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateMail(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email'    => 'required',
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

        $email = $validator->validated()['email'];
        $user  = User::whereId($id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Utilisateur introuvable"
            ]);
        }

        $user->email = $email;
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "Mise à jour effectuée"
        ]);
    }


    /**
     * Change user's role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeRole(Request $request, $id) {
        $validator = Validator::make(
            $request->all(),
            [
                'roleId'    => 'required',
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

        $role = $validator->validated()['roleId'];
        $user  = User::whereId($id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Utilisateur introuvable"
            ]);
        }

        $user->role_id = $role;
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "Mise à jour effectuée"
        ]);
    }


    /**
     * Suspend user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function suspend(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'userId'    => 'required',
                'suspend'   => 'required',
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

        $userId  = $validator->validated()['userId'];
        $suspend = $validator->validated()['suspend'];
        $user  = User::whereId($userId)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Utilisateur introuvable"
            ]);
        }

        $user->suspended = $suspend;
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "Suspension effectuée avec succès"
        ]);
    }
}
