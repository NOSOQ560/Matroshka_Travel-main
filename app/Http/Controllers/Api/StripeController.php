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

            return $this->ReturnData('success', $result['url'], 'done url');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

//    public function callBack(Request $request): \Illuminate\Http\RedirectResponse
//    {
//        try {
//            $session_id = $request->get('session_id');
//
//            $response = Http::withHeaders($this->header)
//                ->get($this->base_url . '/v1/checkout/sessions/' . $session_id);
//
//            $responseData = $response->json();
//
//            // حفظ الاستجابة في ملف JSON
//            Storage::put('stripe.json', json_encode([
//                'callback_response' => $request->all(),
//                'response' => $responseData,
//            ]));
//
//            if ($response->successful() && $responseData['payment_status'] === 'paid') {
//                try {
//                    $payment = Payment::create([
//                        'user_id' => auth()->id(),
//                        'session_id' => $session_id,
//                        'amount' => $response->json()['amount_total'] / 100,
//                        'currency' => $response->json()['currency'],
//                        'product_name' => $response->json()['line_items'][0]['description'] ?? 'Unknown Product',
//                        'payment_status' => 'paid',
//                    ]);
//
//                    $cashbackAmount = $this->calculateCashback($payment->amount);
//                    Cashback::create([
//                        'user_id' => $payment->user_id,
//                        'payment_id' => $payment->id,
//                        'cashback_amount' => $cashbackAmount,
//                        'status' => 'approved',
//                    ]);
//
//                    return redirect()->route('payment.success');
//                } catch (\Exception $ex) {
//                    // إضافة رسالة خطأ في الـ log
//                    \Log::error('Error saving payment: ' . $ex->getMessage());
//                    return redirect()->route('payment.failed');
//                }
//            } else {
//                return redirect()->route('payment.failed');
//            }
//
//            return redirect()->route('payment.failed');
////            return view('payment-failed');
//        } catch (\Exception $ex) {
//            return redirect()->route('payment.failed');
//        }
//    }


    public function callBack(Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            // التأكد من أن المستخدم مسجل دخوله
            if (!auth()->check()) {
//                \Log::error('User is not authenticated');
//                return redirect()->route('payment.failed');
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $session_id = $request->get('session_id');
            if (!$session_id) {
                \Log::error('Session ID is missing');
                return redirect()->route('payment.failed');
            }

            $response = Http::withHeaders($this->header)
                ->get($this->base_url . '/v1/checkout/sessions/' . $session_id);

            $responseData = $response->json();

            // حفظ الاستجابة في ملف JSON
            Storage::put('stripe.json', json_encode([
                'callback_response' => $request->all(),
                'response' => $responseData,
            ]));

            if ($response->successful() && isset($responseData['payment_status']) && $responseData['payment_status'] === 'paid') {
                // التأكد من أن الـ user_id غير فارغ
                $user_id = auth()->id();
                if (!$user_id) {
//                    \Log::error('User ID is missing');
//                    return redirect()->route('payment.failed');
                    return response()->json(['message' => 'Unauthorized'], 401);
                }

                // حفظ البيانات في قاعدة البيانات
                $payment = Payment::create([
                    'user_id' => $user_id,
                    'session_id' => $session_id,
                    'amount' => $responseData['amount_total'] / 100,  // تحويل المبلغ من سنتات إلى وحدات العملة
                    'currency' => $responseData['currency'],
                    'product_name' => json_encode($request->products ?? []) ,
                    'payment_status' => 'paid',
                ]);

                // حساب الكاش باك
                $cashbackAmount = $this->calculateCashback($payment->amount);
                Cashback::create([
                    'user_id' => $payment->user_id,
                    'payment_id' => $payment->id,
                    'cashback_amount' => $cashbackAmount,
                    'status' => 'approved',
                ]);

                // تسجيل النجاح في الـ log
                \Log::info('Payment successful, payment saved to DB.');

                return redirect()->route('payment.success');
            } else {
                \Log::error('Payment failed or status not paid.', ['response' => $responseData]);
                return redirect()->route('payment.failed');
            }
        } catch (\Exception $ex) {
            \Log::error('Error processing callback: ' . $ex->getMessage());
            return redirect()->route('payment.failed');
        }
    }

    public function formatData($request): array
    {
        return [
//            "success_url" => $request->getSchemeAndHttpHost() . '/api/v1/payment/stripe/callback?session_id={CHECKOUT_SESSION_ID}',
//            "cancel_url" => $request->getSchemeAndHttpHost() . '/api/v1/payment/stripe/callback?cancel=true',
            "success_url" => route('stripe.callback') . '?session_id={CHECKOUT_SESSION_ID}',
            "cancel_url" => route('stripe.callback').'?cancel=true',
            "line_items" => [
                [
                    "price_data" => [
                        "currency" => $request->input("currency", "usd"),
                        "product_data" => [
                            "name" => $request->input("product_name", "Default Product"),
                        ],
                        "unit_amount" => $request->input('amount') * 100,
                    ],
                    "quantity" => $request->input('quantity', 1),
                ],
            ],
            "mode" => "payment",
        ];
    }

    public function calculateCashback($amount)
    {
        $userType = auth()->user()->type; // الحصول على نوع المستخدم

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
            $cashbacks = Cashback::with(['users'])->get();

//            if ($cashbacks->isEmpty()) {
//                return $this->ReturnError(404, 'No cashbacks found');
//            }

            return $this->ReturnData('cashbacks', $cashbacks, 'All cashback details');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }


}
