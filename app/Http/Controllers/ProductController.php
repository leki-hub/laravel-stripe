<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
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

        foreach($products as $product) {
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
            'success_url' => 'http://localhost:4242/success.html',
            'cancel_url' => 'http://localhost:4242/cancel.html',
        ]);
        return redirect($session->url);
    }
    
}
