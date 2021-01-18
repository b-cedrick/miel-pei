<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderProductResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserOrderResource;
use App\Models\Invoice;
use App\Models\Invoicelines;
use App\Models\Order;
use App\Models\UserOrder;
use App\Models\OrderProduct;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Shoppingcart;
use App\Models\ShoppingcartProducts;
use DateTime;

class OrderController extends Controller
{

    /**
     * Confirm shoppingcart product and create order info with it row
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmShoppingcart(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'billing'  => 'required',
                'delivery' => 'required',
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
        $loggedUserId = $loggedUser->id;
        $shoppingcart = Shoppingcart::where(['user_id' => $loggedUserId])->first();
        if (!$shoppingcart) {
            return response()->json([
                'success' => false,
                'message' => 'Panier introuvable'
            ]);
        }

        $billing   = $validator->validated()['billing'];
        $delivery  = $validator->validated()['delivery'];

        $newOrder = Order::create([
            "billing"  => $billing,
            "delivery" => $delivery,
        ]);

        $shoppingcartRow = ShoppingcartProducts::where(['shoppingcart_id' => $shoppingcart->id])->get();
        $total = 0;

        $now = new DateTime($newOrder->created_at);
        $nowFormat = $now->format('d-m-Y');
        
        $invoice = Invoice::create([
            "filename"   => "commande-{$nowFormat}",
            "order_id"   => $newOrder->id,
        ]);

        foreach($shoppingcartRow as $info) {
            OrderProduct::create([
                "quantity"   => $info->quantity,
                "order_id"   => $newOrder->id,
                "product_id" => $info->product_id
            ]);

            $productSelled = Products::whereId($info->product_id)->first();
            $lastAmount = (int)  $productSelled->amountSell;
            $productSelled->amountSell = $lastAmount + 1;
            $productSelled->save();

            $quantityprod = (int) $info->quantity;
            $prodPrice = (float) $productSelled->price;

            $total += $quantityprod * $prodPrice;
            
            Invoicelines::create([
                "name"         => $productSelled->name,
                "quantity"     => $info->quantity,
                "price"        => $productSelled->price,
                "invoice_id"   => $invoice->id,
            ]);

            $info->delete();
        }

        UserOrder::create([
            "user_id"  => $loggedUserId,
            "order_id" => $newOrder->id
        ]);

        $invoice->total = $total;
        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Panier confirmer'
        ]);
    }



    /**
     * Direct order
     *
     * @return \Illuminate\Http\Response
     */
    public function directOrder(Request  $request) {
        $validator = Validator::make(
            $request->all(),
            [
                'billing'  => 'required',
                'delivery' => 'required',
                'quantity' => 'required',
                'product'  => 'required',
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
        $loggedUserId = $loggedUser->id;

        $billing   = $validator->validated()['billing'];
        $delivery  = $validator->validated()['delivery'];
        $quantity  = (int) $validator->validated()['quantity'];
        $product   = $validator->validated()['product'];

        $chosenProd = Products::whereId($product)->first();

        if (!$chosenProd) {
            return response()->json([
                'success' => false,
                'message' => 'Produit introuvable'
            ]);
        }

        $productQuantity = (int) $chosenProd->quantity;

        if ($productQuantity != 0) {
            if ($quantity > $productQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantité trop importante'
                ]);
            }

            $finalQuantity = $productQuantity - $quantity;
            $chosenProd->quantity = $finalQuantity;
            $lastAmount = (int)  $chosenProd->amountSell;
            $chosenProd->amountSell = $lastAmount + 1;
            $chosenProd->save();


            $newOrder = Order::create([
                "billing"  => $billing,
                "delivery" => $delivery,
            ]);

            OrderProduct::create([
                "quantity"   => $quantity,
                "order_id"   => $newOrder->id,
                "product_id" => $product
            ]);

            UserOrder::create([
                "user_id"  => $loggedUserId,
                "order_id" => $newOrder->id
            ]);
        
            return response()->json([
                'success' => true,
                'message' => 'Commande effectuée'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Stock épuisé'
        ]);
    }


    /**
     * Display producer order.
     *
     * @return \Illuminate\Http\Response
     */
    public function producerOrders()
    {
        $loggedUser       = Auth::user();
        $loggedUserId     = $loggedUser->id;
        $loggedUserProd   = $loggedUser->products;

        $loggedUserOrders = UserOrder::join("orders", "user_orders.order_id", "=", "orders.id")
        ->join("order_products", "order_products.order_id", "=", "orders.id")
        ->join("products", "order_products.product_id", "=", "products.id")
        ->join("producers", "producers.product_id", "=", "products.id")
        ->where(['producers.user_id' => $loggedUserId])
        ->get();

        return UserOrderResource::collection($loggedUserOrders);

        // check relation : users , producers, products, orderproduct, orders, usersOrders
    }



    /**
     * Display producer order details.
     *
     * @return \Illuminate\Http\Response
     */
    public function producerOrderDetails($id)
    {
        //
    }


    
    /**
     * Display client order.
     *
     * @return \Illuminate\Http\Response
     */
    public function waiting(Request $request)
    {
        $loggedUser     = Auth::user();
        $loggedUserId = $loggedUser->id;

        $loggedUserOrders = UserOrder::orderBy('user_orders.id', 'desc')
            ->where(["user_orders.user_id" => $loggedUserId])
            ->join("orders", "user_orders.order_id", "=", "orders.id")
            ->where("orders.state", "LIKE", "%en attente%")
            ->select("user_orders.*")
            ->paginate(5);

        return UserOrderResource::collection($loggedUserOrders);
    }

        
    /**
     * Display client order.
     *
     * @return \Illuminate\Http\Response
     */
    public function inprogress(Request $request)
    {
        $loggedUser     = Auth::user();
        $loggedUserId = $loggedUser->id;

        $loggedUserOrders = UserOrder::orderBy('user_orders.id', 'desc')
            ->where(["user_orders.user_id" => $loggedUserId])
            ->join("orders", "user_orders.order_id", "=", "orders.id")
            ->where("orders.state", "LIKE", "%en cours%")
            ->select("user_orders.*")
            ->paginate(5);

        return UserOrderResource::collection($loggedUserOrders);
    }

        
    /**
     * Display client order.
     *
     * @return \Illuminate\Http\Response
     */
    public function finished(Request $request)
    {
        $loggedUser     = Auth::user();
        $loggedUserId = $loggedUser->id;

        $loggedUserOrders = UserOrder::orderBy('user_orders.id', 'desc')
            ->where(["user_orders.user_id" => $loggedUserId])
            ->join("orders", "user_orders.order_id", "=", "orders.id")
            ->where("orders.state", "LIKE", "%termine%")
            ->select("user_orders.*")
        ->paginate(5);

        return UserOrderResource::collection($loggedUserOrders);
    }


    /**
     * Display client order details.
     *
     * @return \Illuminate\Http\Response
     */
    public function clientOrderDetail($id)
    {
        $loggedUser   = Auth::user();
        $loggedUserId = $loggedUser->id;
        $userOrder = UserOrder::where(['order_id' => $id, 'user_id' => $loggedUserId])->first();

        if(!$userOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Commande inexistante'
            ]);
        }

        return new UserOrderResource($userOrder);
    }
}
