<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function sendNotification($user_id)
    {
        $user = User::findOrFail($user_id);
        $firebaseToken = array($user->fcm_token);
        //$firebaseToken = ['fXQmFhuGSDCaAr16F9rOok:APA91bGNxTCQ6_-oi1Hvc076ZJl8nwT5pKjqVUgVjtc2QM5ySeA-6yqtIOwvS8pVvnWKDz2wRCB-3g93LIZHE-HwrtHsXnDtkihVHDl1u8abMbfi7d1vHxlNrIE2-YZ3wgJknj1Ydvg_'];

        $SERVER_API_KEY = 'AAAAgkmO-9I:APA91bF0-yitF0ni_yVGEma5eaP_K_1-BlDcpbkT6h874w4EXzY0X8PtS6Bm0Oo9-DIBQm8XUxYzis1UGq9PRqZCXPf4MlR0wQDnBiLIVd3TU_2jdGu4qL_U0K6E7bMFDHJWKjpD-WQ6';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => 'Поступление новый заказ!',
                "body" => 'Доставка до бектемир поста Желателно где то к 14:00',
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        print_r($response);
        return back()->with('success', 'Notification send successfully.');
    }

    public function createFcmToken(Request $request,$id){
        $validator = Validator::make(
            request()->all(),
            [
                'fcm_token' => 'required|unique:users',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 208,
                'message' => $validator->errors()
            ], 208);
        }
        $user = User::findOrFail($id);

        $user->fcm_token = $request->fcm_token;
        if ($user->save()){
            return response()->json(['success'=>'saved fcm token']);
        }
    }
}
