<?php

namespace App\Http\Controllers;

use App\Models\Files;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function uploadAppFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latest_version' => 'required',
            'code_version' => 'required',
            'app' => 'required',
        ]);

        Storage::deleteDirectory('public/app');
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $name = $request->file('app')->getClientOriginalName();
        $path = Storage::putFileAs('public/app', $request->file('app'), $name);

        $data = [
            "version" => $request->latest_version,
            "code" => $request->code_version,
            "file_path" => $path,
            "url" => 'http://devserv.gx.uz/api/download-app'
        ];
        Storage::disk('public')->put('/app/app.json', json_encode($data));

        return response()->json(['success'=>' file saved']);

    }

    public function getDownload()
    {
        $app_json = json_decode(file_get_contents(storage_path() . "/app/public/app/app.json"), true);
        $file = storage_path() . "/app/" . $app_json['file_path'];

        return response()->file($file, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="idea-delivery.apk"',
        ]);
    }

    public function readAppJsonFile()
    {
        $app_json = json_decode(file_get_contents(storage_path() . "/app/public/app/app.json"), true);

        echo "<pre>";
        print_r($app_json);
    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_uuid' => 'required',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
//        $path = $request->file('image')->store('public/images');
        $today = Carbon::now();
        $path = $request->file('image')->store('public/images/'.$today->year.'-'.$today->month);
        $file = new Files;
        $file->app_uuid = $request->app_uuid;
        $file->order_url = $path;

        if ($file->save()) {
            return response()->json(['success'=>'file saved']);
        };
    }

    public function downloadImageFile($month,$url)
    {
        $filePath = storage_path("app/public/images/"."$month/"."$url");
        echo $filePath;
        return response()->download($filePath);
    }
}
