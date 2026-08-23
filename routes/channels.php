<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatModule\Conversation;
use App\Models\Booking;
Broadcast::routes([
    'middleware' => ['web','auth:sanctum'],
]);
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    return $conversation && in_array($user->id, [$conversation->provider_id, $conversation->seeker_id]);
});
Broadcast::channel('payment-successfull.{booking_id}', function ($user, $booking_id) {
    $booking = Booking::find($booking_id);
    if($booking){
        return true;
    }
});
Broadcast::channel('booking.{booking_id}', function ($user, $booking_id) {
    $booking = Booking::find($booking_id);
    if($booking){
        return true;
    }
});
Broadcast::channel('cancel-booking.{booking_id}', function ($user, $booking_id) {
    $booking = Booking::find($booking_id);
    if($booking){
        return true;
    }
});
Broadcast::channel('booking-status.{customerId}', function ($user, $customerId) {
    if($user->id == $customerId){
        return true;
    }
});
