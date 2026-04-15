<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoom;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.room.{id}', function ($user, $id) {
    $chatRoom = ChatRoom::find($id);
    if (!$chatRoom) return false;
    
    return $user->id == $chatRoom->customer_id || $user->id == $chatRoom->vet_id;
});

Broadcast::channel('user.notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('driver.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id && $user->role === 'driver';
});

Broadcast::channel('ride.{id}', function ($user, $id) {
    $ride = \App\Models\Ride::find($id);
    if (!$ride) return false;
    return (int)$user->id === (int)$ride->rider_id || (int)$user->id === (int)$ride->driver_id;
});
