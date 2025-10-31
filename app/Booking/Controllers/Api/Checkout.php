<?php

namespace App\Booking\Controllers\Api;

use App\Booking\Requests\Api\Cart\UpdateRequest;
use App\Domains\Checkout\Services\CheckoutService;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

/**
 * Class Checkout.
 */
class Checkout extends Controller
{
    /**
     * Create a new Cart controller instance.
     *
     * @param CheckoutService $checkoutService
     */
    public function __construct(private CheckoutService $checkoutService)
    {
    }

    /**
     * Place an order.
     *
     * @param UpdateRequest $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function order(UpdateRequest $request): JsonResponse
    {
        $validRequestKeys = ['products', 'start', 'end', 'location', 'businessId', 'sessionId'];
        try {
            $order = $this->checkoutService->processCheckout($request->only($validRequestKeys));
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $response = [
            'message' => 'success',
            'orderId' => $order->id,
            'redirectUrl' => config('app.url').route('frontend.order.payment', $order->id, false),
        ];

        return response()->json($response);
    }
}
