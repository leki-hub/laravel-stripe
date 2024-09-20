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
        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');

        $session->checkout->sessions->create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => 'T-shirt'],
                        'unit_amount' => 2000,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => 'http://localhost:4242/success.html',
            'cancel_url' => 'http://localhost:4242/cancel.html',
        ]);
        return redirect($session->url);
    }
}
