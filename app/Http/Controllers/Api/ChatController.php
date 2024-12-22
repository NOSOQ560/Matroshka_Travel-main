<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\conversations;
use App\Models\messages;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use GeneralTrait;

    public function sendMessage(Request $request)
    {
        try {
            // التحقق من صحة المدخلات
            $request->validate([
                'message' => 'required|string',
            ]);

            // استرجاع معرف المستخدم الحالي
            $userId = auth()->id();
            $userType = auth()->user()->type; // معرفة نوع المستخدم (عميل أو خدمة عملاء)

            // البحث عن محادثة حالية بين العميل وخدمة العملاء
            $conversation = conversations::where(function ($query) use ($userId, $userType) {
                if ($userType === 'customerServices') {
                    $query->where('customer_service_id', $userId);
                } else {
                    $query->where('user_id', $userId);
                }
            })->first();

            // إذا كانت المحادثة موجودة بين العميل وخدمة العملاء
            if ($conversation) {
                // إرسال الرسالة في المحادثة الموجودة
                messages::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'message' => $request->message,
                    'message_type' => 'text',
                ]);

                // إرجاع استجابة ناجحة
                return $this->ReturnSuccess('201', 'Message sent successfully');
            }

            // إذا لم تكن المحادثة موجودة، البحث عن محادثات سابقة بين العميل وخدمة العملاء
            if ($userType !== 'customerServices') {
                // البحث عن خدمة العملاء التي كانت جزءًا من المحادثات السابقة
                $customerService = conversations::where('user_id', $userId)
                    ->whereNotNull('customer_service_id') // التأكد من أن المحادثة تحتوي على خدمة العملاء
                    ->latest() // الحصول على آخر محادثة
                    ->first();

                if ($customerService) {
                    // إذا كانت هناك محادثة سابقة، استخدم نفس خدمة العملاء
                    $customerService = $customerService->customer_service_id;
                } else {
                    // إذا لم تكن هناك محادثة سابقة، اختيار أقل خدمة عملاء لديها محادثات
                    $customerService = User::where('type', 'customerServices')
                        ->withCount('conversations')
                        ->orderBy('conversations_count', 'asc')
                        ->first()->id;
                }

                // إنشاء محادثة جديدة مع خدمة العملاء المحددة
                $conversation = conversations::create([
                    'user_id' => $userId,
                    'customer_service_id' => $customerService,
                ]);

                // إرسال الرسالة في المحادثة الجديدة
                messages::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'message' => $request->message,
                    'message_type' => 'text',
                ]);

                // إرجاع استجابة ناجحة
                return $this->ReturnSuccess('201', 'Message sent successfully');
            }

            // في حالة عدم وجود محادثة بين العميل وخدمة العملاء
            return response()->json(['message' => 'No conversation found'], 404);
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }


    public function sendImage(Request $request)
    {
        try {
            // التحقق من صحة المدخلات
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            ]);

            // استرجاع معرف المستخدم الحالي
            $userId = auth()->id();
            $userType = auth()->user()->type; // معرفة نوع المستخدم (عميل أو خدمة عملاء)

            // البحث عن محادثة حالية بين العميل وخدمة العملاء
            $conversation = conversations::where(function ($query) use ($userId, $userType) {
                if ($userType === 'customerServices') {
                    $query->where('customer_service_id', $userId);
                } else {
                    $query->where('user_id', $userId);
                }
            })->first();

            // إذا كانت المحادثة موجودة بين العميل وخدمة العملاء
            if ($conversation) {
                // إرسال الصورة في المحادثة الموجودة
                $path = $request->file('image')->store('images', 'public');

                // إنشاء الرسالة مع الصورة
                messages::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'message' => $path,
                    'message_type' => 'image',
                ]);

                // إرجاع استجابة ناجحة
                return $this->ReturnSuccess('201', 'Image sent successfully');
            }

            // إذا لم تكن المحادثة موجودة، إنشاء محادثة جديدة فقط إذا كان المستخدم ليس من نوع خدمة العملاء
            if ($userType !== 'customerServices') {
                // اختيار أقل خدمة عملاء لديها محادثات
                $customerService = User::where('type', 'customerServices')
                    ->withCount('conversations')
                    ->orderBy('conversations_count', 'asc')
                    ->first();

                if (!$customerService) {
                    return response()->json(['message' => 'No customer service available'], 404);
                }

                // إنشاء محادثة جديدة
                $conversation = conversations::create([
                    'user_id' => $userId,
                    'customer_service_id' => $customerService->id,
                ]);

                // تخزين الصورة في مجلد public/images
                $path = $request->file('image')->store('images', 'public');

                // إرسال الصورة في المحادثة الجديدة
                messages::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'message' => $path,
                    'message_type' => 'image',
                ]);

                // إرجاع استجابة ناجحة
                return $this->ReturnSuccess('201', 'Image sent successfully');
            }

            // في حالة عدم وجود محادثة بين العميل وخدمة العملاء
            return response()->json(['message' => 'No conversation found'], 404);
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

//    public function createOrGetConversationAuto(Request $request)
//    {
//        try
//        {
//            $userId = auth()->id();
//
//            // اختيار أقل خدمة عملاء لديها محادثات
//            $customerService = User::where('type', 'customerServices')
//                ->withCount('conversations')
//                ->orderBy('conversations_count', 'asc')
//                ->first();
//
//            if (!$customerService) {
//                return response()->json(['message' => 'No customer service available'], 404);
//            }
//
//            // البحث عن محادثة موجودة
//            $conversation = conversations::where('user_id', $userId)
//                ->where('customer_service_id', $customerService->id)
//                ->first();
//
//            if (!$conversation) {
//                // إذا لم تكن موجودة، إنشاء محادثة جديدة
//                $conversation = conversations::create([
//                    'user_id' => $userId,
//                    'customer_service_id' => $customerService->id,
//                ]);
//            }
//
//            return $this->ReturnData('conversation_id',$conversation->id,'Conversation retrieved or created successfully');
//        }
//        catch (\Exception $ex){
//            return $this->ReturnError($ex->getCode(),$ex->getMessage());
//        }
//
//    }

    public function createOrGetConversationAuto(Request $request)
    {
        try
        {
            $userId = auth()->id();
            $userType = auth()->user()->type; // معرفة نوع المستخدم (عميل أو خدمة عملاء)

            // البحث عن محادثة موجودة بين العميل وخدمة العملاء
            $conversation = conversations::where(function ($query) use ($userId, $userType) {
                if ($userType === 'customerServices') {
                    $query->where('customer_service_id', $userId);
                } else {
                    $query->where('user_id', $userId);
                }
            })->first();

            // إذا كانت المحادثة موجودة بين العميل وخدمة العملاء
            if ($conversation) {
                // إرجاع المحادثة الموجودة
                return $this->ReturnData('conversation_id', $conversation->id, 'Conversation retrieved successfully');
            }

            // إذا لم تكن المحادثة موجودة، البحث عن محادثات سابقة بين العميل وخدمة العملاء
            if ($userType !== 'customerServices') {
                // البحث عن خدمة العملاء التي كانت جزءًا من المحادثات السابقة
                $customerService = conversations::where('user_id', $userId)
                    ->whereNotNull('customer_service_id') // التأكد من أن المحادثة تحتوي على خدمة العملاء
                    ->latest() // الحصول على آخر محادثة
                    ->first();

                if ($customerService) {
                    // إذا كانت هناك محادثة سابقة، استخدم نفس خدمة العملاء
                    $customerService = $customerService->customer_service_id;
                } else {
                    // إذا لم تكن هناك محادثة سابقة، اختيار أقل خدمة عملاء لديها محادثات
                    $customerService = User::where('type', 'customerServices')
                        ->withCount('conversations')
                        ->orderBy('conversations_count', 'asc')
                        ->first()->id;
                }

                // إنشاء محادثة جديدة مع خدمة العملاء المحددة
                $conversation = conversations::create([
                    'user_id' => $userId,
                    'customer_service_id' => $customerService,
                ]);

                // إرجاع المحادثة الجديدة
                return $this->ReturnData('conversation_id', $conversation->id, 'Conversation created successfully');
            }

            // في حالة عدم وجود محادثة بين العميل وخدمة العملاء
            return response()->json(['message' => 'No conversation found'], 404);
        }
        catch (\Exception $ex){
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function getUserConversations()
    {
        try
        {
            $userId = auth()->id();
            $userType = auth()->user()->type; // معرفة نوع المستخدم (عميل أو خدمة عملاء)

            // إذا كان المستخدم من نوع خدمة العملاء
            if ($userType === 'customerServices') {
                // البحث عن المحادثات التي تحتوي على هذا المستخدم كخدمة عملاء
                $conversations = conversations::where('customer_service_id', $userId)->get();
            } else {
                // إذا كان المستخدم عميلًا، البحث عن المحادثات التي تحتوي على هذا المستخدم كعميل
                $conversations = conversations::where('user_id', $userId)->get();
            }

            // إرجاع المحادثات
            return $this->ReturnData('conversations', $conversations, 'User conversations retrieved successfully');
        }
        catch (\Exception $ex)
        {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

//    public function getCustomerServiceConversations()
//    {
//        try
//        {
//            if (auth()->user()->type === 'customerServices') {
//                $conversations = conversations::all();
//                return $this->ReturnData('conversations',$conversations,'getCustomerServiceConversations');
//            }
//
//            return $this->ReturnError('403','Unauthorized');
//        }
//        catch (\Exception $ex)
//        {
//            return $this->ReturnError($ex->getCode(),$ex->getMessage());
//        }
//
//    }
//

    public function getMessages($conversation_id)
    {
        try {
            $userId = auth()->id();
            $userType = auth()->user()->type; // معرفة نوع المستخدم (عميل أو خدمة عملاء)

            // البحث عن المحادثة
            $conversation = conversations::find($conversation_id);

            if (!$conversation) {
                return $this->ReturnError('404', 'Conversation not found');
            }

            // التأكد من أن المستخدم جزء من المحادثة بناءً على نوع المستخدم
            if ($userType === 'customerServices') {
                // إذا كان المستخدم خدمة عملاء، يجب أن يكون جزءًا من المحادثة كـ customer_service_id
                if ($conversation->customer_service_id !== $userId) {
                    return $this->ReturnError('403', 'Unauthorized');
                }
            } else {
                // إذا كان المستخدم عميلًا، يجب أن يكون جزءًا من المحادثة كـ user_id
                if ($conversation->user_id !== $userId) {
                    return $this->ReturnError('403', 'Unauthorized');
                }
            }

            // جلب الرسائل المرتبطة بالمحادثة
            $messages = $conversation->messages()->with(['user'])->get();

            // تحديث حالة الرسائل إلى "مقروءة"
            foreach ($messages as $message) {
                if (is_null($message->read_at)) {
                    // إذا كان المستخدم هو الطرف الآخر فقط
                    if ($message->user_id !== $userId) {
                        $message->update(['read_at' => now()]);
                    }
                }
                if ($message->message_type === 'image') {
                    $message->message = asset('storage/' . $message->message);
                }
            }

            return $this->ReturnData('messages', $messages, 'getMessages');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function createOrGetConversationWithMessages(Request $request)
    {
        try {
            $userId = auth()->id();
            $userType = auth()->user()->type; // معرفة نوع المستخدم (عميل أو خدمة عملاء)

            // اختيار أقل خدمة عملاء لديها محادثات
            $customerService = User::where('type', 'customerServices')
                ->withCount('conversations')
                ->orderBy('conversations_count', 'asc')
                ->first();

            if (!$customerService) {
                return response()->json(['message' => 'No customer service available'], 404);
            }

            // إذا كان المستخدم من نوع خدمة العملاء
            if ($userType === 'customerServices') {
                // جلب المحادثات الخاصة بخدمة العملاء مع العملاء
                $conversations = conversations::where('customer_service_id', $userId)->get();

                if ($conversations->isEmpty()) {
                    return response()->json(['message' => 'No conversations found for this customer service'], 404);
                }

                return $this->ReturnData('conversations', $conversations, 'Conversations retrieved successfully');
            } else {
                // إذا كان المستخدم من نوع العميل، البحث عن محادثة موجودة بينه وبين خدمة العملاء
                $conversation = conversations::where('user_id', $userId)
                    ->where('customer_service_id', $customerService->id)
                    ->first();

                if (!$conversation) {
                    // إذا لم تكن المحادثة موجودة، إنشاء محادثة جديدة
                    $conversation = conversations::create([
                        'user_id' => $userId,
                        'customer_service_id' => $customerService->id,
                    ]);
                }

                // جلب الرسائل المرتبطة بالمحادثة
                $messages = $conversation->messages()->with(['user'])->get();

                // تحديث حالة الرسائل إلى "مقروءة"
                foreach ($messages as $message) {
                    if (is_null($message->read_at)) {
                        // إذا كان المستخدم هو الطرف الآخر فقط
                        if ($message->user_id !== $userId) {
                            $message->update(['read_at' => now()]);
                        }
                    }
                    if ($message->message_type === 'image') {
                        $message->message = asset('storage/' . $message->message);
                    }
                }

                // إضافة الرسالة الجديدة إذا تم إرسالها
                if ($request->has('message')) {
                    $message = messages::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $userId,
                        'message' => $request->message,
                        'message_type' => 'text',
                    ]);
                }

                // إضافة الصورة إذا تم إرسالها
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('images', 'public');

                    $message = messages::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $userId,
                        'message' => $path,
                        'message_type' => 'image',
                    ]);
                }

                $data = [
                    'conversation_id' => $conversation->id,
                    'messages' => $messages,
                ];

                return $this->ReturnData('data', $data, 'Conversation and messages retrieved successfully');
            }
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function getUnreadMessagesCount($conversation_id)
    {
        try {
            // البحث عن المحادثة
            $conversation = conversations::find($conversation_id);

            if (!$conversation) {
                return $this->ReturnError('404', 'Conversation not found');
            }

            // تأكد أن المستخدم جزء من المحادثة
            if ($conversation->user_id === auth()->id() || $conversation->customer_service_id === auth()->id()) {
                // حساب عدد الرسائل غير المقروءة
                $unreadMessagesCount = $conversation->messages()
                    ->whereNull('read_at') // الرسائل التي لا تحتوي على وقت قراءة
                    ->where('user_id', '!=', auth()->id()) // استبعاد رسائل المستخدم الحالي
                    ->count();

                return $this->ReturnData('unread_messages_count', $unreadMessagesCount, 'Unread messages count');
            }

            return $this->ReturnError('403', 'Unauthorized');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function getChats()
    {
        try {
            $userId = auth()->id();

            // جلب المحادثات التي ينتمي إليها المستخدم
            $conversations = conversations::where('user_id', $userId)
                ->orWhere('customer_service_id', $userId)
                ->with(['messages' => function ($query) {
                    $query->latest()->limit(1); // آخر رسالة في المحادثة
                }])
                ->get();

            // تنسيق البيانات لإرجاع المحادثات مع التفاصيل المطلوبة
            $data = $conversations->map(function ($conversation) use ($userId) {
                // تحديد الطرف الآخر في المحادثة
                $otherParty = $conversation->user_id === $userId
                    ? $conversation->customerService
                    : $conversation->user;

                // جلب آخر رسالة
                $lastMessage = $conversation->messages->first();

                // حساب عدد الرسائل غير المقروءة
                $unreadMessagesCount = $conversation->messages()
                    ->whereNull('read_at')
                    ->where('user_id', '!=', $userId)
                    ->count();

                return [
                    'conversation_id' => $conversation->id,
                    'otherUser' => $otherParty,

                    'lastMessage' => $lastMessage ? [
//                        'content' => $lastMessage->message,
//                        'message_type' => $lastMessage->message_type,
//                        'read_at' => $lastMessage->read_at,
//                        'created_at' => $lastMessage->created_at,
                    'lastMessage'=>$lastMessage,
                        'from_user' => $lastMessage->user,
                        ] : null,
                    'unread_messages_count' => $unreadMessagesCount,
                ];
            });

            return $this->ReturnData('conversations', $data, 'User conversations retrieved successfully');
        } catch (\Exception $ex) {
            return $this->ReturnError($ex->getCode(), $ex->getMessage());
        }
    }


}
