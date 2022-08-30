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
            'title' => 'required',
            'token' => 'required',
        ]);

        $branchList = new BranchList();

        $branchList->title = $request->title;
        $branchList->token = $request->token;
        if ($request->region_id) {
            $branchList->region_id = $request->region_id;
        }
        if ($branchList->save()) {
            echo "branchList saved  ";
        };

    }

    public function updateBranch(Request $request, $id)
    {

        $branch = BranchList::findOrFail($id);
        $branch->region_id = $request->region_id;

        if ($branch->save()) {
            echo "branchList updated  ";
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
}
