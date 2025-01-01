<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Cashback;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class StripeController extends Controller
{
    use GeneralTrait;

    protected $base_url;
    protected $api_key;
    protected $header;
    public function __construct()
    {
        $this->base_url = env("STRIPE_BASE_URL", "https://api.stripe.com");
        $this->api_key = env("STRIPE_SECRET_KEY","sk_test_51QZeLlDCk3zFfUhscfvgTMcUNY1KmRZSrxVYCpm8lekshkp0Yp7vxM8POMWe5tqdKQG7dl60FzxjX4xIzQDSipnZ007ZtHINwB");


        $this->header = [
            'Accept' => 'application/json',
            'Content-Type' =>'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . $this->api_key,
        ];

    }


    public function sendPayment(Request $request): JsonResponse
    {
        try {
            $data = $this->formatData($request);

            $response = Http::asForm() // تأكد من إرسال البيانات بصيغة form
            ->withHeaders($this->header)
                ->post($this->base_url . '/v1/checkout/sessions', $data);

            $result = $response->json();

            if (!$response->successful()) {
                return $this->ReturnError(400, $result['error']['message'] ?? 'Payment failed');
            }

            return $this->ReturnData('success', $result, 'done url');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function callBack(Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            $session_id = $request->get('session_id');

            // استرجاع تفاصيل الجلسة
            $response = Http::withHeaders($this->header)
                ->get($this->base_url . '/v1/checkout/sessions/' . $session_id);

            $responseData = $response->json();

            // حفظ الاستجابة في ملف JSON
            Storage::put('stripe.json', json_encode([
                'callback_response' => $request->all(),
                'response' => $responseData,
            ]));

            // فحص إذا كانت العملية ناجحة
            if ($response->successful() && $responseData['payment_status'] === 'paid') {
                // حفظ بيانات الدفع في قاعدة البيانات
                $payment = Payment::create([
                    'user_id' => $responseData['metadata']['user_id'], // استرجاع user_id من metadata
                    'session_id' => $session_id,
                    'amount' => $responseData['amount_total'] / 100, // تحويل المبلغ من سنتات إلى وحدات العملة
                    'currency' => $responseData['currency'],
                    'product_name' => $responseData['metadata']['products'], // استرجاع قائمة المنتجات
                    'payment_status' => 'paid',
                ]);

                // حساب الكاش باك
                $userType = $responseData['metadata']['user_type']; // استرجاع نوع المستخدم من metadata
                $cashbackAmount = $this->calculateCashback($payment->amount, $userType);

                // تحقق من قيمة الكاش باك
                if ($cashbackAmount > 0) {
                    // حفظ الكاش باك في قاعدة البيانات
                    Cashback::create([
                        'user_id' => $payment->user_id,
                        'payment_id' => $payment->id,
                        'cashback_amount' => $cashbackAmount,
                        'status' => 'approved',
                    ]);
                }

                return redirect()->route('payment.success');
            }

            return redirect()->route('payment.failed');
//            return view('payment-failed');
        } catch (\Exception $ex) {
            return redirect()->route('payment.failed');
        }
    }


    public function formatData($request): array
    {

        // معالجة قائمة المنتجات
        $lineItems = collect($request->input('products', []))->map(function ($product) use ($request) {
            return [
                "price_data" => [
                    "currency" => $request->input('currency', 'usd'), // استخدام العملة من الطلب الرئيسي
                    "product_data" => [
                        "name" => $product['name'], // اسم المنتج
                    ],
                    "unit_amount" => $product['price'] * 100, // تحويل السعر إلى سنتات
                ],
                "quantity" => $product['quantity'], // الكمية
            ];
        })->toArray();

        return [
            "success_url" => $request->getSchemeAndHttpHost() . '/api/v1/payment/stripe/callback?session_id={CHECKOUT_SESSION_ID}',
            "cancel_url" => $request->getSchemeAndHttpHost() . '/api/v1/payment/stripe/callback?cancel=true',
            "line_items" => $lineItems, // إضافة قائمة المنتجات
            "mode" => "payment",
            "metadata" => [
                "user_id" => $request->user_id, // تمرير معرف المستخدم (مع التحقق من وجود المستخدم)
                "user_type" => $request->user_type, // تمرير معرف المستخدم (مع التحقق من وجود المستخدم)
                "products" => json_encode($request->input('products', [])), // تمرير قائمة المنتجات
            ],
        ];
    }

    public function calculateCashback($amount, $userType)
    {
        if ($userType === 'user') {
            return $amount * 0.10; // 10% cashback
        } elseif ($userType === 'company') {
            return $amount * 0.20; // 20% cashback
        }

        return 0; // في حالة لم يتم تحديد نوع المستخدم
    }


    public function getAllPaymentsWithCashbacks(): JsonResponse
    {
        try {
            // استرجاع جميع المدفوعات مع بيانات الكاش باك المرتبطة بها
            $payments = Payment::with('cashback', 'user')->get();
//            if ($payments->isEmpty()) {
//                return $this->ReturnError(404, 'No payments found');
//            }
            foreach ($payments as $payment) {
                $payment->product_name = json_decode($payment->product_name, true); // تحويل notes إلى مصفوفة
            }

            return $this->ReturnData('payments', $payments, 'All payments with cashbacks');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function getUserPaymentsWithCashbacks(): JsonResponse
    {
        try {
            // التأكد من أن المستخدم مسجل دخوله
            if (!auth()->check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = auth()->user();  // الحصول على المستخدم الحالي
            $payments = $user->payments()->with('cashback')->get();
//            if ($payments->isEmpty()) {
//                return $this->ReturnError(404, 'No payments found for this user');
//            }
            foreach ($payments as $payment) {
                $payment->product_name = json_decode($payment->product_name, true); // تحويل notes إلى مصفوفة
            }

            return $this->ReturnData('payments', $payments, 'User payments with cashbacks');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function getUserCashback(): JsonResponse
    {
        try {
            // التأكد من أن المستخدم مسجل دخوله
            if (!auth()->check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = auth()->user();  // الحصول على المستخدم الحالي
            $cashbacks = $user->cashbacks()->latest()->first();

//            if ($cashbacks->isEmpty()) {
//                return $this->ReturnError(404, 'No cashback found for this user');
//            }

            return $this->ReturnData('cashback', $cashbacks, 'User cashback details');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function getAllCashbacks(): JsonResponse
    {
        try {
            // استرجاع جميع الكاش باك
            $cashbacks = Cashback::with(['user'])->get();

//            if ($cashbacks->isEmpty()) {
//                return $this->ReturnError(404, 'No cashbacks found');
//            }

            return $this->ReturnData('cashbacks', $cashbacks, 'All cashback details');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }


}
