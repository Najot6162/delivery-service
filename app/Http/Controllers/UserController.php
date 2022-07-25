<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\BranchResource;
use App\Models\DeliveryApp;
use App\Models\Menus;
use App\Models\RoleList;
use App\Models\UserPermission;
use Illuminate\Contracts\Database\Eloquent\Builder;

class UserController extends Controller
{
    public function createDriver(Request $request){
        
        $request->validate([
            'phone'=>'required',
            'name'=>'required',
            'password'=>'required'
        ]);
        echo $request;
        $user = new User();
        $user->login = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->car_model_id = $request->car_model_id;
        $user->active = $request->active;
        $user->role = 'driver';
        if($user->save()){
            echo "Driver created";
        };
    }

    public function updateDriver(Request $request, $id){

        $request->validate([
            'phone'=>'required',
            'name'=>'required',
            'password'=>'required',
            'car_model_id'=>'required',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->car_model_id = $request->car_model_id;
        $user->active = $request->active;
        if($user->save()){
            echo "Driver updated";
        };
        return true;
    }

    public function getAllDrivers(Request $request){
        $search = $request['search']??"";
        $pageCount = $request['page']??"10";

       $users = User::withCount([
        'deliveryApp',
        'deliveryApp as count_status_two' => function (Builder $query) use ($request){
                if($request->start_date){
                    $query->where('order_date','>=',$request->start_date);
                }
                if($request->end_date){
                    $query->where('order_date','<=',$request->end_date);
                }
                if($request->start_date&&$request->end_date){
                    $query->whereBetween('order_date', [$request->start_date,$request->end_date]);
                }
                if($request->branch_id){
                    $query->whereIn('branch_id',$request->branch_id);
                }
            $query->where('status',2);
        },
        'deliveryApp as count_status_three' => function (Builder $query)use ($request){
            if($request->start_date){
                $query->where('order_date','>=',$request->start_date);
            }
            if($request->end_date){
                $query->where('order_date','<=',$request->end_date);
            }
            if($request->start_date&&$request->end_date){
                $query->whereBetween('order_date', [$request->start_date,$request->end_date]);
            }
            if($request->branch_id){
                $query->whereIn('branch_id',$request->branch_id);
            }
            $query->where('status',3);
        },
        'deliveryApp as count_status_eight' => function (Builder $query)use ($request){
            if($request->start_date){
                $query->where('order_date','>=',$request->start_date);
            }
            if($request->end_date){
                $query->where('order_date','<=',$request->end_date);
            }
            if($request->start_date&&$request->end_date){
                $query->whereBetween('order_date', [$request->start_date,$request->end_date]);
            }
            if($request->branch_id){
                $query->whereIn('branch_id',$request->branch_id);
            }
            $query->where('status',8);
        },
    ])->where('role_id',3)
    ->where('name','LIKE',"%$search%")  
    ->Where('phone','LIKE',"%$search%")  
    ->join('car_models','car_models.id', '=','users.car_model_id')
    ->Where('car_models.number','LIKE',"%$search%")
    ->Where('car_models.model','LIKE',"%$search%")
    ->paginate($pageCount);

    foreach($users as $user){
         $user->carModel; 
        // $user->deliveryApp;
    }
   
         return BranchResource::collection($users);
    }
    public function getDelivery(Request $request,$id){
        $search = $request['search']??"";
        $pageCount = $request['page']??"10";
        $start_date = $request->start_date;
        $end_date = $request->end_date; 

        $delviery = DeliveryApp::where('driver_id', $id)
        ->where('status_time','LIKE',"%$search%") 
        ->whereIn('status',$request->status??[1,2,3,4,5,6,7,8])
        ->whereIn('status_time',$request->status_time??[1,2,3,4]);

        if($start_date){
            $delviery->where('order_date','>=',$start_date);
        }
        if($end_date){
            $delviery->where('order_date','<=',$end_date);
        }
        if($start_date&&$end_date){
            $delviery->whereBetween('order_date', [$start_date,$end_date]);
        }
        if($request->branch_id){
            $delviery->whereIn('branch_id',$request->branch_id);
        }

        return BranchResource::collection($delviery->paginate($pageCount));
    }

    public function roleGroup(){
        $roles = RoleList::withCount('users')->get();
        return $roles;
    }
    public function getPermission(Request $request){
        $menus = UserPermission::with('menus')->where('role_id',$request->role_id)->get();
        return $menus;
    }
    public function updatePermission(Request $request,$id){
        $user_permission = UserPermission::findOrFail($id);
        $user_permission->value = $request->value;
       if($user_permission->save()){
        echo "updated permisson";
       }
    }
    public function getUsers(Request $request){
        $users = User::where('role_id',$request->role_id)->get();

        return $users;
    }
    public function updateUserActive(Request $request,$id){
        $users = User::findOrFail($id);
        $users->active = $request->active;
        if($users->save()){
            return "update active in users";
        }
       
    }
    public function updateUserBranch(Request $request,$id){
            $users = User::findOrFail($id);
            $users->branch_id = $request->branch_id;
            if($users->save()){
                return "update branch_id in users";
            }
    }
    public function updateUser(Request $request,$id){
        
        $request->validate([
            'phone'=>'required',
            'name'=>'required',
            'password'=>'required',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->active = $request->active;
        if($user->save()){
            echo "user updated";
        };
        return true;
    }
    public function createUser(Request $request){
        $request->validate([
            'phone'=>'required',
            'name'=>'required',
        ]);
        $user = new User();
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->active = $request->active;
        $user->role_id = $request->role_id;
        $user->branch_id = $request->branch_id;
        if($user->save()){
            echo "user created";
        };
    }

    public function getAllUsers(Request $request){
        $search = $request['search']??"";
        $pageCount = $request['page']??"10";
        $users = User::with(['carModel','userPermission'])->where('name','LIKE',"%$search%")->paginate($pageCount);
        return $users;
    }

}
