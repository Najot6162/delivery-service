<?php

namespace App\Http\Controllers;

use App\Models\BranchRegion;
use Illuminate\Http\Request;
use App\Models\BranchList;
use App\Http\Resources\BranchResource;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function createBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:branch_lists',
            'token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->errors()
            ], 400);
        }

        $branchList = new BranchList();
        $branchList->title = $request->title;
        $branchList->token = $request->token;
        if ($request->region_id) {
            $branchList->region_id = $request->region_id;
        }
        if ($branchList->save()) {
            return response()->json(['success'=>'branchList saved']);
        };

    }

    public function updateBranch(Request $request, $id)
    {
        $branch = BranchList::findOrFail($id);
        $branch->region_id = $request->region_id;

        if ($branch->save()) {
            return response()->json(['success'=>'branchList updated']);
        };
    }

    public function getAllBranch(Request $request)
    {
        $search = $request['search'] ?? "";
        $pageCount = $request['page'] ?? "10";
        $br = BranchList::with('region')->where('title', 'LIKE', "%$search%")->paginate($pageCount);
        return BranchResource::collection($br);
    }

    public function getAllBranchRegion(Request $request)
    {
        $branch_regions = BranchRegion::get();
        return $branch_regions;
    }

    public function createRegion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:branch_regions'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->errors()
            ], 400);
        }
        $region = new BranchRegion();
        $region->name = $request->name;
        if ($region->save()) {
            return response()->json([
                'status_code' => 201,
                'message' => 'region created'
            ], 201);
        }

    }
}
