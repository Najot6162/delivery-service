<?php

namespace App\Http\Controllers;

use App\Http\Resources\BranchResource;
use App\Models\RelocationJson;
use Illuminate\Support\Carbon;
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
use App\Models\Agent;
use App\Http\Controllers\NotificationController;

class RelocationController extends Controller
{
    public function CreateRelocation(Request $request)
    {
        //insert log json
        $relocationJson = new RelocationJson();
        $relocationJson->data = $request->all();
        if ($relocationJson->save()){
            echo "relocation json saved";
        }

        $uuid = Str::uuid()->toString();
        $branches = [
            'branch_send_id' => [],
            'branch_receive_id'=>[]

        ];
        $branch = BranchList::where('token', $request[0]['SkladSendID'])->get();

        if ($branch->isEmpty()) {
            $branchList = new BranchList();

            $branchList->title = $request[0]['SkladSend'];
            $branchList->token = $request[0]['SkladSendID'];
            $branchList->save();

            $branch = BranchList::where('token', $request[0]['SkladSendID'])->get();
            array_push($branches['branch_send_id'], $branch[0]['id']);
        } else {
            array_push($branches['branch_send_id'], $branch[0]['id']);
        }

        $branch = BranchList::where('token', $request[0]['SkladRecieveID'])->get();
        if ($branch->isEmpty()) {
            $branchList = new BranchList();
            $branchList->title = $request[0]['SkladRecieve'];
            $branchList->token = $request[0]['SkladRecieveID'];
            $branchList->save();

            $branch = BranchList::where('token', $request[0]['SkladRecieveID'])->get();
            array_push($branches['branch_receive_id'], $branch[0]['id']);
        } else {
            array_push($branches['branch_receive_id'], $branch[0]['id']);
        }

        $order_date = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $request[0]['DataOrder']);
        $date_order = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $request[0]['DataOrder'])->format('Y-m-d');
        $config_time = ConfigTime::where('active', '1')->get();
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
        $relocation->order_date=$date_order;
        $relocation->date_recieve = $request[0]['DataRecieve'];
        $relocation->content = $request[0]['Content'];
        $relocation->branch_send_id = $branches['branch_send_id'][0];
        $relocation->branch_receive_id = $branches['branch_receive_id'][0];
        $relocation->namer_order = $request[0]['NamerOrder'];
        $relocation->id_1c = $request[0]['Id1C'];
        $relocation->config_time_id = $config_time_id;
        $relocation->status = 1;
        $relocation->status_time = 1;
        $relocation->save();

        $agent = Agent::where('agent_id', $request[0]['AGENTID'])->get();
        if ($agent->isEmpty()) {
            $agent = new Agent();
            $agent->agent_id = $request[0]['AGENTID'];
            $agent->agent = $request[0]['AGENT'];
            $agent->save();
        }

        foreach ($request[0]['goods'] as $good) {
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
        return response()->json([
            'status_code' => 201,
            'message' => 'all data saved'
        ], 201);
    }

    public function getAllRelocation(Request $request)
    {
        $search = $request['search'] ?? "";
        $start_date = Carbon::parse("$request->start_date 00:00:00")->format('Y-m-d H:i:s');
        $end_date= Carbon::parse("$request->end_date 23:59:59")->format('Y-m-d H:i:s');

        $branchs = BranchList::get();
        $send_branches = array();
        $receive_branches = array();
        foreach ($branchs as $branch) {
            array_push($send_branches, $branch->id);
        }
        foreach ($branchs as $branch) {
            array_push($receive_branches, $branch->id);
        }
        $relocations = RelocationApp::with(['relocation_product', 'config_time', 'relocation_time_step',
            'relocation_time_step.user','relocation_time_step.user.carModel', 'agents','send_branch','receive_branch','driver'])
            ->withCount('relocation_product')
            ->whereHas('agents', function ($q) use ($search) {
                $q->where('agent', 'LIKE', "%$search%");
            })
            //->where('order_id', 'LIKE', "%$search%")
//            ->whereBetween('date_order', [$start_date, $end_date])
            ->whereIn('status', $request->status ?? [1, 5, 10, 15, 20])
            ->whereIn('branch_send_id', $request->branch_send_id ?? $send_branches)
            ->whereIn('branch_receive_id', $request->branch_recieve_id ?? $receive_branches);

        if ($start_date) {
            $relocations->where('order_date', '>=', $start_date);
        }
        if ($end_date) {
            $relocations->where('order_date', '<=', $end_date);
        }
        if ($start_date && $end_date) {
            $relocations->whereBetween('order_date', [$start_date, $end_date]);
        }
        if ($request->driver_id) {
            $relocations->whereIn('driver_id', $request->driver_id);
        }
        return BranchResource::collection($relocations->paginate($request->perPage));
    }

    public function updateRelocation(Request $request, $id)
    {
        $user = Auth::user();
        $relocation = RelocationApp::findOrFail($id);
        if ($request->step == 5) {
            $relocation->driver_id = $request->driver_id;

            //send notification
            $notife = new NotificationController();
            $notife->sendNotification($request->driver_id);
        }
        $relocation->status = $request->step;
        if($request->status_time){
            $relocation->status_time = $request->status_time;
        }

        $time_step = new RelocationTimeStep();
        $time_step->relocation_uuid = $relocation->uuid;
        $time_step->step = $request->step;
        $time_step->user_id = $user->id;
        if ($request->comment){
            $time_step->comment = $request->comment;
        }
        if ($time_step->save() && $relocation->save() ) {
            return response()->json(['success'=>'Relocation updated']);
        }
    }

    public function getRelocation(Request $request, $id)
    {
        $search = $request['search'] ?? "";
        $pageCount = $request['page'] ?? "10";
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $branchs = BranchList::get();
        $send_branches = array();
        $recieve_branches = array();
        foreach ($branchs as $branch) {
            array_push($send_branches, $branch->id);
        }
        foreach ($branchs as $branch) {
            array_push($recieve_branches, $branch->token);
        }
        $relocation = RelocationApp::where('driver_id', $id)
            ->where('status_time', 'LIKE', "%$search%")
            ->whereIn('status', $request->status ?? [1, 5, 10, 15, 20])
            ->whereIn('status_time', $request->status_time ?? [1, 2, 3, 4])
            ->whereIn('branch_send_id', $request->branch_send_id ?? $send_branches)
            ->whereIn('branch_recieve_id', $request->branch_recieve_id ?? $recieve_branches);

        if ($start_date) {
            $relocation->where('date_order', '>=', $start_date);
        }
        if ($end_date) {
            $relocation->where('date_order', '<=', $end_date);
        }
        if ($start_date && $end_date) {
            $relocation->whereBetween('date_order', [$start_date, $end_date]);
        }

        return BranchResource::collection($relocation->paginate($request->perPage));
    }
}
