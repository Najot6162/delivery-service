<?php

namespace App\Http\Controllers;

use App\Http\Resources\BranchResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\BranchList;
use App\Models\RelocationApp;
use App\Models\RelocationProducts;
use App\Models\ConfigTime;
use App\Models\RelocationTimeStep;
use App\Models\User;

class RelocationController extends Controller
{
    public function CreateRelocation(Request $request){
        $uuid = Str::uuid()->toString();

        Validator::make($request->all(),[
         $request[0]['AGENTID'] => 'required',
         $request[0]['AGENT'] => 'required',
         $request[0]['DokumentId'] => 'required',
         $request[0]['PRAVODKA'] =>'required',
         $request[0]['DataOrder'] => 'required',
         $request[0]['Content'] => 'required',
         $request[0]['DataRecieve'] => 'required',
         $request[0]['AGENTRecieve'] => 'required',
         $request[0]['AGENTRecieveID'] => 'required',
         $request[0]['SkladSend'] => 'required',
         $request[0]['SkladSendID'] => 'required',
         $request[0]['SkladRecieve'] => 'required',
         $request[0]['SkladRecieveID'] => 'required',
         $request[0]['NamerOrder'] => 'required',
         $request[0]['Id1C'] => 'required',
     ]);

     $branches = [
        'branch_id'=>[],
    ];
     $branch = BranchList::where('token', $request[0]['SkladSendID'])->get();
  
     if($branch->isEmpty()){
         $branchList = new BranchList();
     
         $branchList->title = $request[0]['SkladSend'];
         $branchList->token=$request[0]['SkladSendID'];
 
         if($branchList->save()){
             echo "BranchList saved  ";
         };
         $branch = BranchList::where('token', $request[0]['SkladSendID'])->get();
         array_push($branches['branch_id'], $branch[0]['id']);
     }else{   
        array_push($branches['branch_id'], $branch[0]['id']);
     }

     $order_date = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $request[0]['DataOrder']);
     $config_time = ConfigTime::where('active','1')->get();
     $config_time_id = $config_time[0]['id'] ?? "";

     $relocation = new RelocationApp();
     $relocation->uuid = $uuid;
     $relocation->agent = $request[0]['AGENT'];
     $relocation->agent_id = $request[0]['AGENTID'];
     $relocation->agent_recieve_id = $request[0]['AGENTRecieveID'];
     $relocation->agent_recieve = $request[0]['AGENTRecieve'];
     $relocation->document_id = $request[0]['DokumentId'];
     $relocation->provodka = $request[0]['PRAVODKA'];
     $relocation->date_order = $order_date;
     $relocation->date_recieve = $request[0]['DataRecieve'];
     $relocation->content = $request[0]['Content'];
     $relocation->branch_send_id = $branches['branch_id'][0];
     $relocation->branch_recieve = $request[0]['SkladRecieve'];
     $relocation->branch_recieve_id = $request[0]['SkladRecieveID'];
     $relocation->namer_order = $request[0]['NamerOrder'];
     $relocation->id_1c = $request[0]['Id1C'];
     $relocation->config_time_id = $config_time_id; 
     $relocation->status = 1;

     if($relocation->save()){
        echo " Relocation_app  saved  ";
    }

   foreach($request[0]['goods'] as $good){
    $relocation_products = new RelocationProducts(); 
    $relocation_products->relocation_uuid = $uuid;
    $relocation_products->product_name = $good['Good'];
    $relocation_products->product_id = $good['GoodId'];
    $relocation_products->imel = $good['IMEI'];
    $relocation_products->imel_id = $good['IMEIId'];
    $relocation_products->product_amount = $good['amount'];
    $relocation_products->product_code = $good['code'];
    $relocation_products->save();
    };

    echo " Relocation Products saved  ";

    return true;
    }

    public function getAllRelocation(Request $request){
         $search = $request['search']??"";
         $pageCount = $request['page']??"10";
         $start_date = $request->start_date;
         $end_date = $request->end_date; 
         
         $branchs = BranchList::get();
            $send_branches = array();
            $recieve_branches = array();
        foreach ($branchs as $branch){
            array_push($send_branches,$branch->id);
        }
        foreach ($branchs as $branch){
            array_push($recieve_branches,$branch->token);
        } 
              $relocations = RelocationApp::with('relocation_product')->withCount('relocation_product')
                                            ->orwhere('agent','LIKE',"%$search%")
                                            ->whereBetween('date_order', [$start_date,$end_date])
                                            ->whereIn('status',$request->status??[1,2,3,4])
                                            ->whereIn('branch_send_id',$request->branch_send_id??$send_branches)
                                            ->whereIn('branch_recieve_id',$request->branch_recieve_id??$recieve_branches)
                                            ->paginate($pageCount);

        foreach($relocations as $relocation){
            $config_time = $relocation->config_time;
            $time_step = $relocation->relocation_time_step;
            
            foreach($relocation->relocation_time_step as $time_step){
                      $time_step->user;
            }
            $branch = $relocation->branch;
            $car_model = $relocation->car_model;
        }
        return BranchResource::collection($relocations);
    }

    public function updateRelocation(Request $request, $id){
        $user = Auth::user();
        $relocation = RelocationApp::findOrFail($id);
        if($request->step==2){
            $relocation->driver_id = $request->driver_id;
            $user = User::findOrFail($request->driver_id);
            $relocation->car_model_id = $user->car_model_id;
      }
        $relocation->status = $request->step;

        $time_step = new RelocationTimeStep();
        $time_step->relocation_uuid = $relocation->uuid;
        $time_step->step = $request->step;
        $time_step->user_id = $user->id;
        
        if($time_step->save()){
            echo "time_step saved  ";
        };

        if($relocation->save()){
            echo "relocation updated  ";
        };

        return true;
    }
}