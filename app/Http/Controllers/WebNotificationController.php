<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class WebNotificationController extends Controller
{
        public function getDeliveryNotification(){
            $notify =  auth()->user()->unreadNotifications()->paginate(50);
            $count = auth()->user()->unreadNotifications()->count();
            $notification = [
                'count'=>$count,
                'notification'=>$notify
            ];
            return $notification;
        }

        public function readNotification(DatabaseNotification $notification){
            $notification->markAsRead();
            return response()->json(['success'=>'notification read']);
        }
}
