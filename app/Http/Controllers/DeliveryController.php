<?php

namespace App\Http\Controllers;

use App\Events\WebsocketDemoEvent;
use App\Http\Resources\BranchResource;
use App\Models\DeliveryJson;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\DeliveryApp;
use App\Models\Files;
use App\Models\Client;
use App\Models\PickupTime;
use App\Models\DeliveryProduct;
use App\Models\BranchList;
use App\Models\ConfigTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Agent;
use App\Http\Controllers\NotificationController;
use PharIo\Version\Exception;

class DeliveryController extends Controller
{
    public function CreateDelivery(Request $request)
    {
        try {
            //insert log json
            $deliveryJson = new DeliveryJson();
            $deliveryJson->data = $request->all();
            if ($deliveryJson->save()){
                echo "delivery json saved";
            }

            $uuid = Str::uuid()->toString();
            $user = Auth::user();
            $branches = [
                'branch_id' => [],
                'branch_sale_id' => []
            ];
            $branch = BranchList::where('token', $request[0]['SkladID'])->get();
            if ($branch->isEmpty()) {
                $branchList = new BranchList();
                $branchList->title = $request[0]['Sklad'];
                $branchList->token = $request[0]['SkladID'];
                $branchList->save();

                $branch = BranchList::where('token', $request[0]['SkladID'])->get();
                array_push($branches['branch_id'], $branch[0]['id']);
            } else {
                array_push($branches['branch_id'], $branch[0]['id']);
            }

            $branch = BranchList::where('token', $request[0]['SkladSaleID'])->get();
            if ($branch->isEmpty()) {
                $branchList = new BranchList();
                $branchList->title = $request[0]['SkladSale'];
                $branchList->token = $request[0]['SkladSaleID'];
               $branchList->save();

                $branch = BranchList::where('token', $request[0]['SkladSaleID'])->get();
                array_push($branches['branch_sale_id'], $branch[0]['id']);
            } else {
                array_push($branches['branch_sale_id'], $branch[0]['id']);
            }
            $order_date = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $request[0]['DataOrder']);
            $date_order = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $request[0]['DataOrder'])->format('Y-m-d');
            $config_time = ConfigTime::where('active', '1')->get();
            $config_time_id = $config_time[0]['id'] ?? "";

            $delivery = new DeliveryApp();
            $delivery->uuid = $uuid;
            $delivery->agent_id = $request[0]['AGENTID'];
            $delivery->user_id = $user->id;
            $delivery->order_id = $request[0]['NamerOrder'];
            $delivery->online = $request[0]['Online'];
            $delivery->order_date = $order_date;
            $delivery->date_order = $date_order;
            $delivery->date_create = $request[0]['DataCreate'];
            $delivery->document_id = $request[0]['DokumentId'];
            $delivery->provodka = $request[0]['PRAVODKA'];
            $delivery->content = $request[0]['Content'];
            $delivery->orienter = $request[0]['Orinter'];
            $delivery->client = $request[0]['GUID'];
            $delivery->client_id = $request[0]['GUIDID'];
            $delivery->group_price = $request[0]['GroupPrice'];
            $delivery->vip_oplata = $request[0]['VidOplata'];
            $delivery->id_1c = $request[0]['Id1C'];
            $delivery->oplachena = $request[0]['Oplachena'];
            $delivery->status = 1;
            $delivery->branch_id = $branches['branch_id'][0];
            $delivery->branch_sale_id = $branches['branch_sale_id'][0];
            $delivery->config_time_id = $config_time_id;
            $delivery->status_time = 1;
            $delivery->save();

            $agent = Agent::where('agent_id', $request[0]['AGENTID'])->get();
            if ($agent->isEmpty()) {
                $agent = new Agent();
                $agent->agent_id = $request[0]['AGENTID'];
                $agent->agent = $request[0]['AGENT'];
               $agent->save();
            }

            foreach ($request[0]['goods'] as $good) {
                $delivery_products = new DeliveryProduct();
                $delivery_products->delivery_uuid = $uuid;
                $delivery_products->product_name = $good['Good'];
                $delivery_products->product_id = $good['GoodId'];
                $delivery_products->imel = $good['IMEI'];
                $delivery_products->imel_id = $good['IMEIId'];
                $delivery_products->product_amount = $good['amount'];
                $delivery_products->product_code = $good['code'];
                $delivery_products->sales = $good['sales'];
                $delivery_products->sales_id = $good['salesid'];
                $delivery_products->save();
            };
            $delivery_client = new Client();
            $delivery_client->name = $request[0]['GUID'];
            $delivery_client->client_id = $request[0]['GUIDID'];
            $delivery_client->save();

            $deliveryApp = DeliveryApp::with(['pickup_time', 'pickup_time.user', 'pickup_time.user.carModel', 'pickup_time.branch', 'branch', 'branch_sale',
                'files', 'user', 'config_time', 'delivery_product', 'delivery_client', 'agents'])->findOrFail($delivery->id);
            broadcast(new WebsocketDemoEvent(['product' => $deliveryApp]));
            return response()->json([
                'status_code' => 201,
                'message' => 'all data saved'
            ], 201);

        } catch (Exception $exception) {
            echo $exception;
        }
    }

    public function updateDelivery(Request $request, $id)
    {
        $delivery = DeliveryApp::findOrFail($id);
        if ($delivery->status == 1) {
            $pickup_time = new PickupTime();
            $pickup_time->app_uuid = $delivery->uuid;
            $pickup_time->step = 1;
            $pickup_time->created_at = $delivery->order_date;
            $pickup_time->user_id = Auth::user()->id;
            $pickup_time->active = "1";
            $pickup_time->save();
        }
        if ($request->driver_id) {
            $delivery->driver_id = $request->driver_id;
        }
        if ($request->order_date) {
            $order_date = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $request->order_date);
            $date_order = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $request->order_date)->format('Y-m-d');
            $delivery->order_date = $order_date;
            $delivery->date_order = $date_order;
        }
        if ($request->step == 5 && $request->driver_id) {
            //send notification
            $notife = new NotificationController();
            $notife->sendNotification($request->driver_id);
        }
        if ($request->branch_step) {
            $delivery->branch_step = $request->branch_step;
        }
        $delivery->status = $request->step;
        if ($request->status_time) {
            $delivery->status_time = $request->status_time;
        }

        $pickup_time = new PickupTime();
        $pickup_time->app_uuid = $delivery->uuid;
        $pickup_time->step = $request->step;
        if ($request->step == 1) {
            $pickup_time->created_at = $delivery->order_date;
        }
        $pickup_time->active = $request->active;
        if ($request->branch_step) {
            $pickup_time->branch_id = $request->branch_step;
        } else {
            $pickup_time->user_id = $request->user_id;
        }
        if ($request->comment) {
            $pickup_time->comment = $request->comment;
        }
        if ($pickup_time->save() &&  $delivery->save()){
            return response()->json([
                'status_code' => 200,
                'message' => 'updated'
            ], 200);
        }
    }

    public function gettAllDelivery(Request $request)
    {
        $search = $request['search'] ?? "";
        $start_date = $request->start_date;
        $end_date= $request->end_date;

        $deliveries = DeliveryApp::with(['pickup_time', 'pickup_time.user', 'pickup_time.user.carModel', 'pickup_time.branch', 'branch', 'branch_sale',
            'files', 'user', 'config_time', 'delivery_product', 'delivery_client', 'agents', 'driver'])
            ->whereHas('agents', function ($q) use ($search) {
                $q->where('agent', 'LIKE', "%$search%");
            })
            ->whereIn('status', $request->status ?? [1, 5, 10, 15, 20, 25, 30, 35, 40])
            ->whereIn('status_time', $request->status_time ?? [1, 2, 3, 4]);

        if ($start_date) {
            $deliveries->where('order_date', '>=', $start_date);
        }
        if ($end_date) {
            $deliveries->where('order_date', '<=', $end_date);
        }
        if ($start_date && $end_date) {
            $deliveries->whereBetween('order_date', [$start_date, $end_date]);
        }
        if ($request->driver_id) {
            $deliveries->whereIn('driver_id', $request->driver_id);
        }
        if ($request->branch_id) {
            $deliveries->whereIn('branch_id', $request->branch_id);
        }
        if ($request->online) {
            $deliveries->where('online', $request->online);
        }
        return BranchResource::collection($deliveries->paginate($request->perPage));
    }

    public function backStep(Request $request, $id)
    {
        $delivery = DeliveryApp::findOrFail($id);
        $delivery->status = $request->status;
        if ($request->driver_id || $request->driver_id == null) {
            $delivery->driver_id = $request->driver_id;
        }
        $pickup_time = PickupTime::where('app_uuid', $delivery->uuid)->where('step', $request->step)->where('active', '1')->first();
        $pickup_timed = PickupTime::findOrFail($pickup_time->id);
        $pickup_timed->active = $request->active;
        $pickup_timed->save();

        if ($request->step == 5) {
            $pickup_time = PickupTime::where('app_uuid', $delivery->uuid)->where('step', 1)->where('active', '1')->first();
            $pickup_timed = PickupTime::findOrFail($pickup_time->id);
            $pickup_timed->active = $request->active;
            $pickup_timed->save();
        }

        if ($delivery->save()) {
            return response()->json(['success'=>'updated step']);
        }

    }

    public function updatePickupTime(Request $request, $id)
    {
        $delivery = DeliveryApp::findOrFail($id);
        if ($request->driver_id) {
            $delivery->driver_id = $request->driver_id;
            $notife = new NotificationController();
            $notife->sendNotification($request->driver_id);
        }
        $pickup_times = PickupTime::where('app_uuid', $delivery->uuid)->where('step', 5)->where('active','1')->first();
        $pickup_time = PickupTime::findOrFail($pickup_times->id);
        $pickup_time->active = "0";
      if ($pickup_time->save() && $delivery->save()){
        $step_log = new PickupTime();
        $step_log->active = "1";
        $step_log->user_id = $request->driver_id;
        $step_log->app_uuid = $delivery->uuid;
        $step_log->step = 5;
        if ($request->comment) {
            $step_log->comment = $request->comment;
        }
        if ($step_log->save() ) {
            return response()->json(['success'=>'updated step']);
        }
    }
    }
}
