<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Producer;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProducerResource;

class ProductsController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | General functions
    |--------------------------------------------------------------------------
    */


    /**
     * Display best product selled.
     *
     * @return \Illuminate\Http\Response
     */
    public function bestProduct() {
        $bestProducts = Producer::join("users", "producers.user_id", "=", "users.id")
            ->join("products", "producers.product_id", "=", "products.id")
            ->where("quantity", ">", "0")
            ->orderBy("amountSell", "desc")
            ->limit(5)
            ->get();
        return ProducerResource::collection($bestProducts);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAll()
    {
        $products = Producer::all();
        return ProducerResource::collection($products);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function showProducer($id)
    {
        $product = Producer::where(['user_id' => $id])->get();
        return ProducerResource::collection($product);
    }


    /*
    |--------------------------------------------------------------------------
    | Producer functions
    |--------------------------------------------------------------------------
    */


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $loggedUser   = Auth::user();
        $loggedUserId = (int) $loggedUser->id;

        $searchedWord  = $request->words;

        // all field is empty
        if ($searchedWord == "") {
            $products = Producer::orderBy('id', 'desc')
                ->where(['user_id' => $loggedUserId])->paginate(5);
            return ProducerResource::collection($products);
        }

        // word field is not empty || suspends field is empty
        if ($searchedWord != "") {
            $products = Producer::orderBy('products.id', 'desc')
                ->where(['producers.user_id' => $loggedUserId])
                ->join("products", "products.id", "=", "producers.product_id")
                ->where("products.name", "LIKE", "%" . $searchedWord . "%")
                ->paginate(5);
            return ProducerResource::collection($products);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'      => 'required',
                'price'     => 'required',
                'quantity'  => 'required|min:1',
                'image'     => 'nullable|mimes:jpg,jpeg,png|max:5000',
            ],
            [
                'required'  => 'Le champ :attribute est requis',
                'mimes'     => 'Extension invalide',
                'max'       => '5Mb maximum',
                'min'       => 'La quantité : inférieur ou égale à 1'
            ]
        );

        $errors = $validator->errors();
        if (count($errors) != 0) {
            return response()->json([
                'success' => false,
                'message' => $errors->first()
            ]);
        }

        $loggedUser     = Auth::user();
        $loggedUserId   = (int) $loggedUser->id;
        $name           = $validator->validated()['name'];
        $price          = $validator->validated()['price'];
        $quantity       = $validator->validated()['quantity'];
        $imageUploaded  = $validator->validated()['image'];
    
        $product = Products::create([
            "name"     => $name,
            "price"    => $price,
            "quantity" => $quantity
        ]);
       
        if($imageUploaded != null) {
            $extension      = $imageUploaded->getClientOriginalExtension();
            $image          = time() . rand() . '.' . $extension;
            $imageUploaded->move(public_path('images'), $image);

            $product = Products::create([
                "name"     => $name,
                "price"    => $price,
                "quantity" => $quantity,
                "image"    => $image
            ]);
        }

        $producer = new Producer([
            "user_id" => $loggedUserId,
            "product_id" => $product->id
        ]);
        
        $producer->save();
        return response()->json([
            'success' => true,
            'message' => "Produit ajouté avec succès"
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    
        $loggedUser   = Auth::user();
        $loggedUserId = (int) $loggedUser->id;

        if($loggedUser->role_id == 2){
            $product = Producer::where(["product_id" => $id])->first();
            return new ProducerResource($product);
        }

        $product = Producer::where(['user_id' => $loggedUserId, "product_id" => $id])->first();
        return new ProducerResource($product);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'      => 'required',
                'price'     => 'required',
                'image'     => 'nullable|mimes:jpg,jpeg,png|max:5000',
            ],
            [
                'required'  => 'Le champ :attribute est requis',
                'mimes'     => 'Extension invalide',
                'max'       => '5Mb maximum'
            ]
        );

        $errors = $validator->errors();
        if (count($errors) != 0) {
            return response()->json([
                'success' => false,
                'message' => $errors->first()
            ]);
        }

        $loggedUser     = Auth::user();
        $loggedUserId   = (int) $loggedUser->id;
        $name           = $validator->validated()['name'];
        $price          = $validator->validated()['price'];
        $imageUploaded  = $validator->validated()['image'];

        $producer = Producer::where(['user_id' => $loggedUserId, "product_id" => $id])->first();
        if(!$producer) {
            return response()->json([
                'success' => false,
                'message' => "Action impossible"
            ], 401);
        }

        $product = Products::whereId($id)->first();
        if(!$product) {
            return response()->json([
                'success' => false,
                'message' => "Produit introuvable"
            ]);
        }

        $product->name     = $name;
        $product->price    = $price;

        if ($imageUploaded != null) {
            $oldImage = $product->image;

            if ($oldImage != "default.jpg") {
                $oldFilePath = public_path('images') . '/' . $oldImage;
                unlink($oldFilePath);
            }

            $extension      = $imageUploaded->getClientOriginalExtension();
            $image          = time() . rand() . '.' . $extension;
            $imageUploaded->move(public_path('images'), $image);
            $product->image = $image;
        }

        $product->save();
        return response()->json([
            'success' => true,
            'message' => "Mise à jour effectuée"
        ]);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function stock(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'quantity'  => 'required',
            ],
            [
                'required'  => 'Le champ :attribute est requis',
            ]
        );

        $errors = $validator->errors();
        if (count($errors) != 0) {
            return response()->json([
                'success' => false,
                'message' => $errors->first()
            ]);
        }

        $loggedUser     = Auth::user();
        $loggedUserId   = (int) $loggedUser->id;
        $quantity       = $validator->validated()['quantity'];

        $producer = Producer::where(['user_id' => $loggedUserId, "product_id" => $id])->first();
        if (!$producer) {
            return response()->json([
                'success' => false,
                'message' => "Action impossible"
            ], 401);
        }

        $product = Products::whereId($id)->first();
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => "Produit introuvable"
            ]);
        }

        $product->quantity = $quantity;
        $product->save();
        return response()->json([
            'success' => true,
            'message' => "Mise à jour effectuée"
        ]);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $loggedUser   = Auth::user();
        $loggedUserId = (int) $loggedUser->id;
        $producer = Producer::where(['user_id' => $loggedUserId, "product_id" => $id])->first();
        if (!$producer) {
            return response()->json([
                'success' => false,
                'message' => "Action impossible"
            ], 401);
        }
        Products::destroy($id);
        return response()->json([
            'success' => true,
            'message' => "Suppression effectuée"
        ]);
    }
}
