<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Shoppingcart;
use App\Models\ShoppingcartProducts;
use App\Http\Resources\ShoppingcartProductsResource;


class ShoppingcartProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $loggedUser     = Auth::user();
        $shoppingcart = $loggedUser->shoppingcart->shoppingcartRow;
        return ShoppingcartProductsResource::collection($shoppingcart);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addToCart(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'product'   => 'required|integer',
                'quantity'  => 'required|integer',
            ],
            [
                'required'  => 'Le champ :attribute est requis',
                'integer'   => 'Le champ :attribute est invalide',
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
        $shoppingCartId = $loggedUser->shoppingcart->id;
        $product        = $validator->validated()['product'];
        $quantity       = (int) $validator->validated()['quantity'];
        
        $productInfo     = Products::whereId($product)->first();

        if(!$productInfo){
            return response()->json([
                'success' => false,
                'message' => 'Produit introuvable'
            ]);
        }

        $productQuantity = (int) $productInfo->quantity;

        if($productQuantity != 0) {
            if($quantity > $productQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantité trop importante'
                ]);
            }

            $alreadyExist = ShoppingcartProducts::where(['product_id' => $productInfo->id])->first();
            if($alreadyExist){
                return response()->json([
                    'success' => false,
                    'message' => 'Ce produit est déjà dans votre panier'
                ]);
            }

            $finalQuantity = $productQuantity - $quantity;
            $productInfo->quantity = $finalQuantity;
            $productInfo->save();

            ShoppingcartProducts::create([
                "quantity"          => $quantity,
                "shoppingcart_id"   => $shoppingCartId,
                "product_id"        => $product
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté au panier'
            ]);
        } 

        return response()->json([
            'success' => false,
            'message' => 'Stock épuisé'
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param   $id
     * @return \Illuminate\Http\Response
     */
    public function updateOne(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'quantity'  => 'required|integer',
            ],
            [
                'required'  => 'Le champ :attribute est requis',
                'integer'   => 'Le champ :attribute est invalide',
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
        $shoppingCartId = $loggedUser->shoppingcart->id;
        $quantity       = (int) $validator->validated()['quantity'];

        $shoppingCartRow = ShoppingcartProducts::where(['product_id' => $id, "shoppingcart_id" => $shoppingCartId])->first();
        if(!$shoppingCartRow){
            return response()->json([
                'success' => false,
                'message' => 'Un problème est survenu'
            ]);
        }

        $product = Products::whereId($shoppingCartRow->product_id)->first();
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit introuvable'
            ]);
        }

        $productInfoQuantity = (int) $product->quantity;
        if ($productInfoQuantity != 0) {
            if ($quantity > $productInfoQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantité trop importante'
                ]);
            }

            $quantityEdited =  (int) $shoppingCartRow->quantity - $quantity;
            
            if($quantityEdited < 0) {
                $finalQuantity = $productInfoQuantity - $quantity;
                $product->quantity = $finalQuantity;
                $product->save();
            } else {
                $finalQuantity = $productInfoQuantity + $quantityEdited;
                $product->quantity = $finalQuantity;
                $product->save();
            }

            $shoppingCartRow->quantity = $quantity;
            $shoppingCartRow->save();

            return response()->json([
                'success' => true,
                'message' => 'Mise à jour effectuée'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Stock épuisé'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param   $id
     * @return \Illuminate\Http\Response
     */
    public function deleteOne($id)
    {
        $loggedUser     = Auth::user();
        $shoppingCartId = $loggedUser->shoppingcart->id;
        $shoppingCartRow = ShoppingcartProducts::where(['product_id' => $id, "shoppingcart_id" => $shoppingCartId])->first();

        if (!$shoppingCartRow) {
            return response()->json([
                'success' => false,
                'message' => "Une erreur s'est produite"
            ]);
        }

        $productInfo     = Products::whereId($shoppingCartRow->product_id)->first();
        (int) $productInfo->quantity += $shoppingCartRow->quantity;
        $productInfo->save();
        
        $shoppingCartRow->delete();
        return response()->json([
            'success' => true,
            'message' => "Suppression effectuée"
        ]);
    }


    /**
     * Remove all resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        $loggedUser     = Auth::user();
        $shoppingCartId = $loggedUser->shoppingcart->id;
        $shoppingCartRow = ShoppingcartProducts::where(["shoppingcart_id" => $shoppingCartId])->get();
        foreach($shoppingCartRow as $prod) {
            $prod->delete();
        }
        return response()->json([
            'success' => true,
            'message' => "Panier vider !"
        ]);
    }
}
