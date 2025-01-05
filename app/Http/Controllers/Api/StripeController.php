<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Cashback;
use App\Models\Payment;
use App\Models\User;
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

            return $this->ReturnData('success', $result['url'], 'done url');
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
//            if ($response->successful() && $responseData['payment_status'] === 'paid') {
//                // حفظ بيانات الدفع في قاعدة البيانات
//                $payment = Payment::create([
//                    'user_id' => $responseData['metadata']['user_id'], // استرجاع user_id من metadata
//                    'session_id' => $session_id,
//                    'amount' => $responseData['amount_total'] / 100, // تحويل المبلغ من سنتات إلى وحدات العملة
//                    'currency' => $responseData['currency'],
//                    'product_name' => $responseData['metadata']['products'],
//                    'description'=>$responseData['metadata']['main_product'],
//                    'payment_status' => $responseData['payment_status'],
//                ]);

            if ($response->successful() && $responseData['payment_status'] === 'paid') {
                // استرجاع metadata وتفكيكها من JSON
                $metadata = json_decode($responseData['metadata']['data'], true);
                if (!isset($metadata['user_id'], $metadata['main_product'], $metadata['amount'], $metadata['total'])) {
                    return redirect()->route('payment.failed')->with('error', 'Missing required metadata');
                }

                // حفظ بيانات الدفع في قاعدة البيانات
                $payment = Payment::create([
                    'user_id' => $metadata['user_id'], // استرجاع user_id من metadata
                    'session_id' => $session_id,
                    'amount' => $metadata['total'], // التأكد من صحة المبلغ
                    'discount' => $metadata['cashback'], // التأكد من قيمة الكاش باك
                    'amountAfterCashback' => $responseData['amount_total'] / 100, // تحويل المبلغ من سنتات إلى وحدات العملة
                    'currency' => $responseData['currency'],
                    'description' => $metadata['desc'], // استخدام البيانات من metadata
                    'product_name' => $metadata['main_product'], // المنتج الرئيسي
                    'payment_status' => $responseData['payment_status'],
                ]);
                // حساب الكاش باك
                $userType = $metadata['user_type']; // استرجاع نوع المستخدم من metadata

                $cashbackAmount = $this->calculateCashback($payment->amountAfterCashback, $userType);

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

                $user = User::find($payment->user_id); // استرجاع المستخدم
                $user->notify(new \App\Notifications\PaymentNotification([
                    'amount' => $payment->amountAfterCashback,
                    'transaction_id' => $payment->session_id,
                    'cashback' => $cashbackAmount,
                ]));

                return redirect()->route('payment.success');
            }

            return redirect()->route('payment.failed');
//            return view('payment-failed');
        } catch (\Exception $ex) {
            return redirect()->route('payment.failed');
        }
    }


//    public function formatData($request): array
//    {
//        // معالجة قائمة المنتجات
//        $lineItems = collect($request->input('products', []))->map(function ($product) use ($request) {
//            return [
//                "price_data" => [
//                    "currency" => $request->input('currency', 'usd'), // استخدام العملة من الطلب الرئيسي
//                    "product_data" => [
//                        "name" => $product['name'] ?? $request->input('product_name', 'Unnamed Product'), // إذا لم يكن هناك اسم، استخدم المنتج الرئيسي
//                    ],
//                    "unit_amount" => isset($product['price']) ? $product['price'] * 100 : $request->input('amount', 0) * 100, // إذا لم يكن هناك سعر، استخدم المبلغ الرئيسي
//                ],
//                "quantity" => $product['quantity'] ?? $request->input('quantity'), // إذا لم تكن هناك كمية، استخدم الكمية الرئيسية
//            ];
//        })->toArray();
//
//        return [
//            "success_url" => $request->getSchemeAndHttpHost() . '/api/v1/payment/stripe/callback?session_id={CHECKOUT_SESSION_ID}',
//            "cancel_url" => $request->getSchemeAndHttpHost() . '/api/v1/payment/stripe/callback?cancel=true',
//            "line_items" => $lineItems, // إضافة قائمة المنتجات
//            "mode" => "payment",
//            "metadata" => [
//                "user_id" => $request->user_id, // تمرير معرف المستخدم (مع التحقق من وجود المستخدم)
//                "user_type" => $request->user_type, // تمرير نوع المستخدم
//                "main_product" => $request->main_product, // تمرير المنتج الرئيسي
//                "products" => json_encode($request->input('products', [])), // تمرير قائمة المنتجات كما هي
//            ],
//        ];
//    }

    public function formatData($request): array
    {

        $amount = $request->input('amount', 0); // استرجاع المبلغ المدخل
//
        $lineItems = [
            [
                "price_data" => [
                    "currency" => $request->input('currency', 'usd'), // استخدام العملة من الطلب الرئيسي
                    "product_data" => [
                        "name" => $request->input('product_name', 'Unnamed Product'), // استخدام اسم المنتج إذا كان موجودًا
                    ],
                    "unit_amount" => $amount * 100, // المبلغ المدخل مباشرة (بالسنت)
                ],
                "quantity" => 1, // الكمية 1 في هذه الحالة
            ]
        ];

        // تحضير البيانات التي سيتم تخزينها في metadata بتنسيق JSON
        $metadata = [
            "user_id" => (string) $request->user_id, // تحويل إلى string
            "user_type" => (string) $request->user_type, // تحويل إلى string
            "main_product" => (string) $request->main_product, // تحويل إلى string
            "amount" => (string) $amount, // تحويل إلى string
            "cashback" => (string) $request->cashback, // تحويل إلى string
            "total" => (string) $request->total, // تحويل إلى string
            "desc" => json_encode($request->desc), // تحويل المصفوفة إلى JSON string
        ];

        return [
            "success_url" => $request->getSchemeAndHttpHost() . '/api/v1/payment/stripe/callback?session_id={CHECKOUT_SESSION_ID}',
            "cancel_url" => $request->getSchemeAndHttpHost() . '/api/v1/payment/stripe/callback?cancel=true',
            "line_items" => $lineItems, // إضافة قائمة المنتجات
            "mode" => "payment",
            "metadata" => [
                "data" => json_encode($metadata), // تخزين البيانات في metadata بتنسيق JSON
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
                $payment->description = json_decode($payment->description, true); // تحويل notes إلى مصفوفة
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
                $payment->description = json_decode($payment->description, true); // تحويل notes إلى مصفوفة
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
