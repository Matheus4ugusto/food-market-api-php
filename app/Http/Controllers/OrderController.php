<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Stripe;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except('webhook');
        $this->middleware('permission:CUSTOMER')->except('webhook');
    }

    public function index(Request $request)
    {
        $orders = Order::where('user_id', Auth::user()->id)
            ->paginate($request->get('size', 15))
            ->withQueryString();

        return response()->json($orders, Response::HTTP_OK);
    }

    public function store(CreateOrderRequest $request)
    {
        $orderData = $request->validated();
        $user = $request->user();
        $orderData['total_price'] = 0;
        $products = $orderData['products'];
        unset($orderData['products']);

        $order = $user->orders()->create($orderData);

        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            $orderData['total_price'] += $productModel->price * $product['quantity'];

            $order->products()->attach($productModel, [
                'quantity'      => $product['quantity'],
                'unit_price'    => $productModel->price,
                'total_price'  => $productModel->price * $product['quantity']
            ]);
        }

        $order->total_price = $orderData['total_price'];
        $order->save();

        return response()->json($order, Response::HTTP_CREATED);
    }

    public function checkout(int $orderId)
    {
        $order = Order::with('products')->find($orderId);

        if (!$order) {
            return response()
                ->json(
                    ['error' => 'Pedido não encontrado!'],
                    Response::HTTP_NO_CONTENT
                );
        }

        if ($order->status == 'PAID') {
            return response()->json([
                'error' => 'O pedido já está pago!'
            ], Response::HTTP_BAD_REQUEST);
        }

        $stores = collect();

        $lineItems = $order->products->map(function (Product $product) use ($stores) {
            $quantity = $product->pivot->quantity;
            $unitPrice = $product->pivot->unit_price * 100;
            $imagesArray = array_slice($product->images, 0, 4);

            $images = array_map(function ($image) {
                return $image['url'];
            }, $imagesArray);
            $stores->put(
                $product->store->id,
                $product->store->delivery_chage
            );

            return [
                'quantity'              => $quantity,
                'price_data'            => [
                    'currency'          => 'BRL',
                    'unit_amount'       => $unitPrice,
                    'product_data'      => [
                        'name'          => $product->name,
                        'description'   => $product->description,
                        'images'        => $images
                    ]
                ],
            ];
        })->toArray();

        $deliveryCharge = $stores->sum() * 100;

        if ($deliveryCharge > 0) {
            array_push($lineItems, [
                'quantity'              => 1,
                'price_data'            => [
                    'currency'          => 'BRL',
                    'unit_amount'       => $deliveryCharge,
                    'product_data'      => [
                        'name'          => 'Taxa de entrega'
                    ]
                ],
            ]);
        }

        Stripe::setApiVersion('2020-08-27');
        Stripe::setApiKey(env('STRIPE_PRIVATE_KEY'));

        $session = Session::create([
            'mode'                      => 'payment',
            'payment_method_types'      => ['card'],
            'line_items'                => $lineItems,
            'success_url'               => env('APP_FRONT_URL') . '/payment/success',
            'cancel_url'                    => env('APP_FRONT_URL') . '/payment/error',
        ]);

        $order->update([
            'payment_intent_id'        => $session->payment_intent,
            'status'                    => 'AWAITING_PAYMENT',
            'payment_url'                   => $session->url,
        ]);

        return response()
            ->json([
                'payment_url'           => $session->url,
            ], Response::HTTP_OK);
    }

    public function webhook(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_PRIVATE_KEY'));
        $event = Event::constructFrom($request->all());

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $order = Order::where(
                    'payment_intent_id',
                    $event->data->object->id
                )
                    ->first();

                if (!$order) {
                    return response()->json([
                        'status' => 'Pedido não encontrado',
                        'event' => $event->data->object
                    ], Response::HTTP_NOT_FOUND);
                }

                $order->status = 'PAID';
                $order->save();

                return response()
                    ->json([
                        'status' => 'Pedido pago com sucesso'
                    ]);
                break;
            default:
                return response()
                    ->json(['status' => "Evento {$event->type} não classificado."]);
        }
    }
}
