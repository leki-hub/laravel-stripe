<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        return view('product.index', compact('products'));
    }
    public function checkout()
    {
        $products = Product::all();
        $lineItems = [];
        $totalPrice=0;

        foreach($products as $product) {
            $totalPrice+= $product->price;
            $lineItems[] = [
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $product->name,
                                'images' => [$product->image],
                            
                            ],
                            'unit_amount' => $product->price*100,
                        ],
                        'quantity' => 1,
                    ]
                ]
            ];
        }
        
        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');

        $session->checkout->sessions->create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success',[],true).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel',[],true),
        ]);
         $order= new Order();
         $order->status= 'unpaid';
         $order->total_price= $totalPrice;
         $order->session_id= $session->id;
         $order->save();

        return redirect($session->url);
    }
    
    public function success(Request $request){
        Stripe::setApiKey(env('STRIPE_SECRET'));
         // $sessionId = $request->session_id;
         $sessionId = $request->get('session_id');
        $stripe = new \Stripe\StripeClient('sk_test_51Q11JvBMsvEsQY0veDrPMRok8WBl2I91D16voO8dizOCONAj07ZOUltcTVtWUHxyNcyfMNcwMAI4yqfo3CMXaDIg00MZgR4mmt');
       
        $session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
      
        try {

            if(!$session){
                throw new NotFoundHttpException(404);
            }
            $customer = $stripe->customers->retrieve($session->customer);
            $order = Order::where('session_id', $sessionId)->where('status','unpaid')->first();
            if (!$order) {
               throw new NotFoundHttpException(404);
            }
            $order->status= 'paid';
            $order->save();
            return view('product.checkout.success',compact('customer'));
        } catch (\Throwable $th) {
               throw new NotFoundHttpException(404);
        }
      
       
    
    }
    public function cancel(){
        return view('product.cancel');
    }

    public function webhook(){
        
    }
}
