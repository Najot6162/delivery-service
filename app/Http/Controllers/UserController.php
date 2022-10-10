<?php

namespace App\Http\Controllers;

use App\Models\BranchList;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\BranchResource;
use App\Models\DeliveryApp;
use App\Models\Menus;
use App\Models\RoleList;
use App\Models\UserPermission;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function createDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:users',
            'name' => 'required',
            'password' => 'required|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->errors()
            ], 400);
        }
        $user = new User();
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        if ($request->car_model_id) {
            $user->car_model_id = $request->car_model_id;
        }
        if ($request->role_id) {
            $user->role_id = $request->role_id;
        }
        $user->active = $request->active;
        if ($user->save()) {
            return response()->json([
                'status_code' => 201,
                'message' => 'saved'
            ], 201);
        };
    }

    public function updateDriver(Request $request, $id)
    {

        $request->validate([
            'phone' => 'required',
            'name' => 'required',
            'password' => 'required'
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        if ($request->car_model_id) {
            $user->car_model_id = $request->car_model_id;
        }
        $user->active = $request->active;
        if ($user->save()) {
            return response()->json(['success'=>'Driver updated']);
        };
    }

    public function getAllDrivers(Request $request)
    {
        $search = $request['search'] ?? "";
        $pageCount = $request['page'] ?? "10";
        $branchs = BranchList::get();
        $branches = array();
        foreach ($branchs as $branch) {
            array_push($branches, $branch->id);
        }
        $users = User::with('carModel')->withCount([
            'deliveryApp',
            'deliveryApp as count_status_five' => function (Builder $query) use ($request) {
                if ($request->start_date) {
                    $query->where('date_order', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $query->where('date_order', '<=', $request->end_date);
                }
                if ($request->start_date && $request->end_date) {
                    $query->whereBetween('date_order', [$request->start_date, $request->end_date]);
                }
                if ($request->branch_id) {
                    $query->whereIn('branch_id', $request->branch_id);
                }
                $query->where('status', 5);
            },
            'deliveryApp as count_status_ten' => function (Builder $query) use ($request) {
                if ($request->start_date) {
                    $query->where('date_order', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $query->where('date_order', '<=', $request->end_date);
                }
                if ($request->start_date && $request->end_date) {
                    $query->whereBetween('date_order', [$request->start_date, $request->end_date]);
                }
                if ($request->branch_id) {
                    $query->whereIn('branch_id', $request->branch_id);
                }
                $query->where('status', 10);
            },
            'deliveryApp as count_status_forty' => function (Builder $query) use ($request) {
                if ($request->start_date) {
                    $query->where('date_order', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $query->where('date_order', '<=', $request->end_date);
                }
                if ($request->start_date && $request->end_date) {
                    $query->whereBetween('date_order', [$request->start_date, $request->end_date]);
                }
                if ($request->branch_id) {
                    $query->whereIn('branch_id', $request->branch_id);
                }
                $query->where('status', 40);
            },
        ])->where('role_id', 4)->where('active', '1');

        if ($request->search) {
            $users->whereHas('carModel', function ($q) use ($search) {
                $q->where('number', 'LIKE', "%$search%");
                $q->orWhere('model', 'LIKE', "%$search%");
            })
                ->orWhere('name', 'LIKE', "%$search%")
                ->orwhere('phone', 'LIKE', "%$search%")
                ->orwhere('address', 'LIKE', "%$search%");
        }


        return BranchResource::collection($users->paginate($request->perPage));
    }

    public function getAllDriversOnlyActive(Request $request)
    {
        $users = User::with('carModel')->where('role_id', 4)->where("active", 1)->get();
        return BranchResource::collection($users);
    }

    public function getDriver(Request $request, $id)
    {
        $drvier = User::with('carModel')->where('role_id', 4)->findOrFail($id);
        return $drvier;
    }

    public function getDelivery(Request $request, $id)
    {
        $search = $request['search'] ?? "";
        $pageCount = $request['page'] ?? "10";
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $delviery = DeliveryApp::with(['pickup_time', 'pickup_time.user', 'pickup_time.user.carModel', 'pickup_time.branch', 'branch', 'branch_sale',
            'files', 'user', 'config_time', 'delivery_product', 'delivery_client', 'agents'])->where('driver_id', $id)
            ->where('status_time', 'LIKE', "%$search%")
            ->whereIn('status', $request->status ?? [1, 5, 10, 15, 20, 25, 30,35, 40])
            ->whereIn('status_time', $request->status_time ?? [1, 2, 3, 4]);
        if ($start_date) {
            $delviery->where('order_date', '>=', $start_date);
        }
        if ($end_date) {
            $delviery->where('order_date', '<=', $end_date);
        }
        if ($start_date && $end_date) {
            $delviery->whereBetween('order_date', [$start_date, $end_date]);
        }
        if ($request->branch_id) {
            $delviery->whereIn('branch_id', $request->branch_id);
        }
        return BranchResource::collection($delviery->paginate($request->perPage));
    }

    public function roleGroup()
    {
        $roles = RoleList::withCount('users')->get();
        return $roles;
    }

    public function getPermission(Request $request)
    {
        $menus = UserPermission::with('menus')
            ->where('role_id', $request->role_id)
            ->whereHas('menus', function ($q) use ($request) {
                $q->where('type', 1);
            })
            ->orderBy('menu_id', 'asc')
            ->get();
        return $menus;
    }


    public function getPermissionForMobile(Request $request, $id)
    {
        $menus = UserPermission::with('menus')
            ->where('role_id', $id)
            ->whereHas('menus', function ($q) use ($request) {
                $q->where('type', 2);
            })->orderBy('menu_id', 'asc')
            ->get();
        return $menus;
    }

    public function updatePermission(Request $request)
    {
        foreach ($request->toArray() as $req) {
            $user_permission = UserPermission::findOrFail($req['id']);
            $user_permission->value = $req['value'];
            if ($user_permission->save()) {
                return response()->json(["success"=>"updated permission\n"]);
            }
        }
    }

    public function createRoleWithPermission(Request $request)
    {
        $role_list = new RoleList();
        $role_list->name = $request->name;
        $role_list->save();

        foreach ($request['permissions'] as $permit) {
            $user_permission = new UserPermission();
            $user_permission->role_id = $role_list->id;
            $user_permission->menu_id = $permit['menu_id'];
            $user_permission->value = $permit['value'];
            $user_permission->save();
        }
        return response()->json([
            'status_code' => 201,
            'message' => 'create role'
        ], 201);

    }

    public function getUsers(Request $request)
    {
        $users = User::where('role_id', $request->role_id)->get();
        return $users;
    }

    public function updateUserActive(Request $request, $id)
    {
        $users = User::findOrFail($id);
        $users->active = $request->active;
        if ($users->save()) {
            return response()->json(["success"=>"update active in user"]);
        }
    }

    public function UserIsDelete(Request $request, $id)
    {
        $users = User::findOrFail($id);
        $users->is_deleted = $request->is_deleted;
        if ($users->save()) {
            return response()->json(["success"=>"update is deleted in user"]);
        }
    }

    public function updateUserBranch(Request $request, $id)
    {
        $request->validate([
            'branch_id' => 'required'
        ]);

        $users = User::findOrFail($id);
        $users->branch_id = $request->branch_id;
        if ($users->save()) {
            return response()->json(["success"=>"update branch_id in users"]);
        }
    }

    public function updateUser(Request $request, $id)
    {

        $request->validate([
            'phone' => 'required',
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);

        if ($request->is_deleted) {
            $user->is_deleted = $request->is_deleted;
        }
        if ($request->active) {
            $user->active = $request->active;
        }
        if ($user->save()) {
            return response()->json(["success"=>"user updated"]);
        };
    }

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:users',
            'name' => 'required|unique:users',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->errors()
            ], 400);
        }

        $user = new User();
        $user->name = $request->name;
        $user->phone = $request->phone;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        if ($request->active) {
            $user->active = $request->active;
        }
        if ($request->role_id) {
            $user->role_id = $request->role_id;
        }
        if ($request->branch_id) {
            $user->branch_id = $request->branch_id;
        }

        if ($user->save()) {
            return response()->json([
                'status_code' => 201,
                'message' => 'user saved'
            ], 201);
        };
    }

    public function getAllUsers(Request $request)
    {
        $search = $request['search'] ?? "";
        $pageCount = $request['page'] ?? "10";
        $users = User::with(['carModel', 'userPermission'])->where('name', 'LIKE', "%$search%")->paginate($pageCount);
        return $users;
    }

    public function getAllUsersWithBranch(Request $request)
    {
        $search = $request['search'] ?? "";
        $pageCount = $request['page'] ?? "10";
        $users = User::with(['userBranch'])->where('name', 'LIKE', "%$search%")->paginate($pageCount);
        return $users;
    }

    public function getMenus(Request $request)
    {
        $menus = Menus::where('app_type', $request->type)->orderBy('id', 'asc')->get();
        return $menus;
    }

    public function getUser($id)
    {
        $users = User::with(['carModel', 'userPermission'])->findOrFail($id);
        return $users;
    }
}
