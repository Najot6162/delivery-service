<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarModel;
use App\Http\Resources\BranchResource;
use App\Models\CarTerm;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    public function createCar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|unique:car_models',
            'model' => 'required',
            'active' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->errors()
            ], 400);
        }
        $car = new CarModel();
        $car->number = $request->number;
        $car->model = $request->model;
        $car->active = $request->active;
        if ($car->save()) {
            return response()->json([
                'status_code' => 201,
                'message' => 'saved'
            ], 201);
        };
    }

    public function getAllCars(Request $request)
    {
        $cars = CarModel::with('car_term')->paginate($request->perPage);
        return BranchResource::collection($cars);
    }

    public function updateCar(Request $request, $id)
    {
            $request->validate([
                'number' => 'required',
                'model' => 'required',
                'active' => 'required'
            ]);
            $car = CarModel::findOrFail($id);
            $car->number = $request->number;
            $car->model = $request->model;
            $car->active = $request->active;
            if ($car->save()) {
                return response()->json(['success'=>'car updated']);
            };
    }
    public function updateOnlyActiveCar(Request $request,$id){
        $car = CarModel::findOrFail($id);
        $car->active = $request->active;
        if ($car->save()) {
            return response()->json(['success'=>'car updated']);
        };
    }
    public function getCar($id){
        $car = CarModel::with('car_term')->findOrFail($id);
        return $car;
    }
    public function deleteCar($id)
    {
        $car = CarModel::findOrFail($id);
        if ($car->delete()) {
            return response()->json(['success'=>'car Deleted']);
        }
    }

    public function getCarTerm($id)
    {
        $car_term = CarTerm::findOrFail($id);
        return BranchResource::collection($car_term);
    }

    public function updateCarTerm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'insure_date' => 'required',
            'attorney_date' => 'required',
            'attorney' => 'required',
            'adver_date' => 'required',
            'technical_date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->errors()
            ], 400);
        }

        $car_term = CarTerm::where('car_model_id',$request->car_model_id)->first();
        if ($car_term){
            $car_term = CarTerm::findOrFail($car_term->id);
            $car_term->car_model_id = $request->car_model_id;
            $car_term->insure_date = $request->insure_date;
            $car_term->attorney_date = $request->attorney_date;
            $car_term->adver_date = $request->adver_date;
            $car_term->technical_date = $request->technical_date;
            $car_term->attorney = $request->attorney;
            if ($car_term->save()) {
                return response()->json(['success'=>'car_term updated']);
            }
        }else{
            $car_term = new CarTerm();
            $car_term->car_model_id = $request->car_model_id;
            $car_term->insure_date = $request->insure_date;
            $car_term->attorney_date = $request->attorney_date;
            $car_term->adver_date = $request->adver_date;
            $car_term->technical_date = $request->technical_date;
            $car_term->attorney = $request->attorney;
            if ($car_term->save()) {
                return response()->json(['success'=>'car_term updated']);
            }
        }
    }
}
