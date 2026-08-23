<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\ChatModule\Conversation;
use App\Models\ChatModule\Message;
use App\Models\ChatModule\MessageAttachment;
use Illuminate\Support\Facades\Storage;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    
    /**
     * @OA\Post(
     *     path="/create-conversations",
     *     summary="Start conversations between provider and seeker",
     *     tags={"Chat Module"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"provider_id","seeker_id"},
     *             @OA\Property(
     *                 property="provider_id",
     *                 type="integer",
     *                 example=3,
     *                 description=""
     *             ),
     *              @OA\Property(
     *                 property="seeker_id",
     *                 type="integer",
     *                 example=2,
     *                 description=""
     *             ),
     *              @OA\Property(
     *                 property="booking_id",
     *                 type="integer",
     *                 example=2,
     *                 description=""
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=""),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function createConversation(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:users,id',
            'seeker_id' => 'required|exists:users,id',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $conversation = Conversation::firstOrCreate([
            'provider_id' => $request->provider_id,
            'seeker_id' => $request->seeker_id,
            'booking_id' => $request->booking_id,
        ]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }

 
     /**
     * @OA\Get(
     *     path="/conversations",
     *     summary="Get Current Converstation",
     *     tags={"Chat Module"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\Parameter(
    *         name="page",
    *         in="query",
    *         required=false,
    *         description="Page number",
    *         @OA\Schema(type="integer", example=1)
    *     ),
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=true),
     *             @OA\Property(property="data", type="object", example=""),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function conversations()
    {
        $userId = auth()->id();

        $conversations = Conversation::with([
            'provider:id,name,image',
            'seeker:id,name,image',
            'latestMessage'
        ])
        ->where('provider_id', $userId)
        ->orWhere('seeker_id', $userId)
        ->orderByDesc('last_message_at')
        ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * @OA\Get(
     *     path="/conversations/{conversation}/messages",
     *     summary="Get Current Converstation messages",
     *     tags={"Chat Module"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\Parameter(
    *         name="page",
    *         in="query",
    *         required=true,
    *         description="Page number",
    *         @OA\Schema(type="integer", example=1)
    *     ),
    *        @OA\Parameter(
    *         name="conversation",
    *         in="path",
    *         required=true,
    *         description="conversation Id",
    *         @OA\Schema(type="integer", example=1)
    *     ),
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=true),
     *             @OA\Property(property="data", type="object", example=""),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function messages(Conversation $conversation)
    {
        $messages = $conversation
            ->messages()
            ->with([
                'sender:id,name,image',
                'attachments'
            ])
            ->latest()
            ->paginate(20);

        return response()->json($messages);
    }

     /**
     * @OA\Post(
     *     path="/messages",
     *     summary="Post messages between provider and seeker",
     *     tags={"Chat Module"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"conversation_id"},
     *             @OA\Property(
     *                 property="conversation_id",
     *                 type="integer",
     *                 example=2,
     *                 description=""
     *             ),
     *              @OA\Property(
     *                 property="body",
     *                 type="string",
     *                 example="Hello",
     *                 description=""
     *             )
     *         ),
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=""),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'required|string',
        ]);

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => auth()->id(),
            'body' => $request->body,
            'type' => 'text',
        ]); 
        $conversation = Conversation::where('id', $request->conversation_id)->first();
        $conversation->update([
                'last_message_at' => now()
            ]);
        broadcast(new MessageSent($message))->toOthers();
        $to = $conversation->provider_id;
        if(auth()->user()->hasRole('provider')){
            $to = $conversation->seeker_id;
        }
        $notification_type = 'new_chat_message';
        try {
            app(FirebaseNotificationService::class)->sendPushNotificationSync(
                [$to],
                "New message from " . auth()->user()->name,
                $request->body,
                false,
                $notification_type,
                [
                    'type' => 'new_chat_message',
                    'entity' => 'conversation',
                    'entity_id' => $request->conversation_id,
                ]
            );
        } catch (\Throwable $e) {
            Log::info('Booking status notification failed', [
                'conversation_id' => $request->conversation_id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
 * @OA\Post(
 *     path="/messages/upload",
 *     summary="Upload chat file/image",
 *     tags={"Chat Module"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"file","conversation_id"},
 *
 *                 @OA\Property(
 *                     property="conversation_id",
 *                     type="integer",
 *                     example=4
 *                 ),
 *
 *                 @OA\Property(
 *                     property="file",
 *                     type="string",
 *                     format="binary"
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="File uploaded successfully"
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error"
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
    public function uploadMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:10240'
        ]);

        $file = $request->file('file');

        $path = $file->store('chat', 's3');

        $type = str_contains(
            $file->getMimeType(),
            'image'
        ) ? 'image' : 'file';

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => auth()->id(),
            'type' => $type,
        ]);

        MessageAttachment::create([
            'message_id' => $message->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        Conversation::where('id', $request->conversation_id)
            ->update([
                'last_message_at' => now()
            ]);

        broadcast(new MessageSent($message))->toOthers();
    
        return response()->json([
            'success' => true,
            'message' => $message->load('attachments')
        ]);
    }

    /**
     * @OA\Post(
     *     path="/conversations/{conversation}/read",
     *     summary="Mark Read",
     *     tags={"Chat Module"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"conversation_id"},
     *             @OA\Property(
     *                 property="conversation_id",
     *                 type="integer",
     *                 example=2,
     *                 description=""
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=""),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function markRead(Conversation $conversation)
    {
        Message::where('conversation_id', $conversation->id)
            ->whereNull('read_at')
            ->where('sender_id', '!=', auth()->id())
            ->update([
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true
        ]);
    }

     /**
     * @OA\Get(
     *     path="/chat/unread-count",
     *     summary="Get Unread messages count",
     *     tags={"Chat Module"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="count", type="integer", example=true),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function unreadCount()
    {
        $count = Message::whereNull('read_at')
            ->where('sender_id', '!=', auth()->id())
            ->whereHas('conversation', function ($q) {
                $q->where('provider_id', auth()->id())
                ->orWhere('seeker_id', auth()->id());
            })
            ->count();

        return response()->json([
            'count' => $count
        ]);
    }
}
