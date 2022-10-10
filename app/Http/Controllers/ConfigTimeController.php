<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigTime;
use Illuminate\Support\Carbon;
use App\Models\DeliveryApp;
use Illuminate\Support\Facades\Validator;

class ConfigTimeController extends Controller
{
    public function checkTime(Request $request)
    {
        $deliveries = DeliveryApp::whereNotIn('status', ['15', '20', '25', '30', '40'])->get();
        $config_time = ConfigTime::where('active', '1')->first();
        $time = $config_time->time;
        $time1 = $time / 3;
        $time2 = $time1 * 2;

        foreach ($deliveries as $delivery) {
            // $start_date = Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $delivery->order_date);
            $start_date = $delivery->order_date;
            $current_date = Carbon::now()->toDateTimeString();
            $to = Carbon::createFromFormat('Y-m-d H:s:i', $current_date);
            $from = Carbon::createFromFormat('Y-m-d H:s:i', $start_date);
            $diff_in_hours = $to->diffInHours($from);
            if ($time1 >= $diff_in_hours) {
                $delivery->status_time = 1;
            }
            if ($time2 >= $diff_in_hours) {
                $delivery->status_time = 2;
            }
            if ($time >= $diff_in_hours) {
                $delivery->status_time = 3;
            } else {
                $delivery->status_time = 4;
            }

            if ($delivery->save()) {
                return response()->json(['success'=>'status time updated']);
            };
        }
        return "ok";
    }

    public function creteConfigTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'time' => 'required|unique:config_times',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->errors()
            ], 400);
        }
        $config_time = new ConfigTime();
        $config_time->user_id = $request->user_id;
        $config_time->time = $request->time;
        $config_time->active = $request->active;
        if ($config_time->save()) {
            $config_times = ConfigTime::whereNotIn('id', [$config_time->id])->get();
            foreach ($config_times as $time) {
                $config_time = ConfigTime::findOrFail($time->id);
                $config_time->active = 0;
                $config_time->save();
            }
            return response()->json([
                'status_code' => 201,
                'message' => 'saved'
            ], 201);
        };
    }

    public function updateConfigTime(Request $request)
    {
        $config_times = ConfigTime::whereNotIn('id', [$request->id])->get();
        foreach ($config_times as $time) {
            $config_time = ConfigTime::findOrFail($time->id);
            $config_time->active = 0;
            if ($config_time->save()) {
                return response()->json(["success"=>" updated inactive config_time \n"]);
            };
        }
        $config_time = ConfigTime::findOrFail($request->id);
        $config_time->active = 1;
        if ($config_time->save()) {
            return response()->json(["success"=>" updated inactive config_time \n"]);
        };
    }

    public function getAllConfigTime(Request $request)
    {
        $config_times = ConfigTime::with('user')->withCount('deliveryApp')->get();
        return $config_times;
    }
}
