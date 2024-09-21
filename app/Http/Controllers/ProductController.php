<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;


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
    
    public function success(){
        return view('product.checkout.success');
    }
    public function cancel(){
        return view('product.cancel');
    }

}
