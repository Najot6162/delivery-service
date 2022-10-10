<?php

namespace App\Http\Controllers;

use App\Models\DeliveryApp;
use Illuminate\Support\Carbon;
use App\Models\BranchList;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function getCounts(Request $request): array
    {
        $start_date = \Carbon\Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = \Carbon\Carbon::parse($request->end_date)->addDay()->format('Y-m-d');

        //Calculate Count by Date
        $currentDateTime = Carbon::now()->format('Y-m-d');
        $currentDateTimePlus = Carbon::now()->addDay()->format('Y-m-d');
        $week = Carbon::now()->subDays(7)->format('Y-m-d');
        $month = Carbon::now()->subDays(30)->format('Y-m-d');
        $year = Carbon::now()->subYear()->format('Y-m-d');
        $count_day = DeliveryApp::whereBetween('date_order', [$currentDateTime, $currentDateTimePlus])->count();
        $count_week = DeliveryApp::whereBetween('date_order', [$week, $currentDateTime])->count();
        $count_month = DeliveryApp::whereBetween('date_order', [$month, $currentDateTime])->count();
        $count_year = DeliveryApp::whereBetween('date_order', [$year, $currentDateTime])->count();

        $date_count_arr = [
            "count_day" => $count_day,
            "count_week" => $count_week,
            "count_month" => $count_month,
            "count_year" => $count_year
        ];

        //Calculate All Orders By date
        $start_time = \Carbon\Carbon::parse($request->start_date);
        $finish_time = \Carbon\Carbon::parse($request->end_date);

        $all_orders_count = array();
        for ($i = $start_time; $i <= $finish_time; $i->modify('+1 day')) {
            $start_day = date("Y-m-d", strtotime($start_time));
            $count_all_orders = DeliveryApp::whereBetween('date_order', [$start_day, $i->format("Y-m-d")])->count();
            $all_orders_count[$start_day] = $count_all_orders;
        }

        //Calculate count by status
        $status_one = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])
            ->where('status', 1)->count();
        $status_five = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])
            ->where('status', 5)->count();
        $status_ten = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])
            ->where('status', 10)->count();
        $status_fifteen = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])
            ->where('status', 15)->count();
        $status_forty = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])
            ->where('status', 40)->count();
        $status_thirty = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])
            ->where('status', 30)->count();
        $status_twenty = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])
            ->where('status', 20)->count();
        $twenty_five = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])
            ->where('status', 25)->count();
        $all_orders = DeliveryApp::whereBetween('order_date', [$start_date, $end_date])->count();

        $status_count_arr = [
            'count_status_one' => $status_one,
            'count_status_five' => $status_five,
            'count_status_ten' => $status_ten,
            'count_status_fifteen' => $status_fifteen,
            'count_status_forty' => $status_forty,
            'count_status_thirty' => $status_thirty,
            'count_status_twenty' => $status_twenty,
            'count_status_twenty_five' => $twenty_five,
            'all_orders' => $all_orders
        ];

        // Calculate Delivery Counts
        //All count
        $all_count_driver = User::withCount(['deliveryApp',
            'deliveryApp as deliveryApp_count_status_time_one' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 1);
            },
            'deliveryApp as deliveryApp_count_status_time_two' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 2);
            },
            'deliveryApp as deliveryApp_count_status_time_three' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 3);
            },
            'deliveryApp as deliveryApp_count_status_time_four' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 4);
            },
            'deliveryApp as deliveryApp_all_count' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
            },
            'relocationApp',
            'relocationApp as relocationApp_count_status_time_one' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 1);
            },
            'relocationApp as relocationApp_count_status_time_two' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 2);
            },
            'relocationApp as relocationApp_count_status_time_three' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 3);
            },
            'relocationApp as relocationApp_count_status_time_four' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 4);
            },
            'relocationApp as relocationApp_all_count' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
            },
        ])->orderBy('deliveryApp_count_status_time_one', 'desc')->where('role_id', 4)->get();

        //Delivery Count
        $delivery_count_driver = User::withCount(['deliveryApp',
            'deliveryApp as deliveryApp_count_status_time_one' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 1);
            },
            'deliveryApp as deliveryApp_count_status_time_two' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 2);
            },
            'deliveryApp as deliveryApp_count_status_time_three' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 3);
            },
            'deliveryApp as deliveryApp_count_status_time_four' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 4);
            },
            'deliveryApp as deliveryApp_all_count' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
            },
        ])->orderBy('deliveryApp_count_status_time_one', 'desc')->where('role_id', 4)->get();

        //Relocation Count
        $relocation_count_driver = User::withCount([
            'relocationApp',
            'relocationApp as relocationApp_count_status_time_one' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 1);
            },
            'relocationApp as relocationApp_count_status_time_two' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 2);
            },
            'relocationApp as relocationApp_count_status_time_three' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 3);
            },
            'relocationApp as relocationApp_count_status_time_four' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
                $query->where('status_time', 4);
            },
            'relocationApp as relocationApp_all_count' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
            },
        ])->orderBy('relocationApp_count_status_time_one', 'desc')->where('role_id', 4)->get();
        $CountDelviery = [
            'all_counts' => $all_count_driver,
            'delviery_count' => $delivery_count_driver,
            'relocation_count' => $relocation_count_driver
        ];

        //Calculate Branch Count
        $count_branches = BranchList::withCount([
            'delivery_app',
            'delivery_app as deliveryApp_count_by_date' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
            },
            'delivery_app as delivery_app_count_not_ready' => function (Builder $query) {
                $query->where('status', 1);
            },
            'delivery_app as delivery_app_count_ready' => function (Builder $query) {
                $query->whereIn('status', [5, 10, 15, 20, 25, 30, 35, 40]);
            },
            'relocation_app',
            'relocation_app as relocationApp_count_by_date' => function (Builder $query) use ($start_date,$end_date) {
                $query->whereBetween('order_date', [$start_date, $end_date]);
            },
            'relocation_app as relocation_app_count_not_ready' => function (Builder $query) {
                $query->where('status', 1);
            },
            'relocation_app as relocation_app_count_ready' => function (Builder $query) {
                $query->whereIn('status', [5, 10, 15, 20]);
            },
        ])->get();

        $allCount = [
            'count_days' => $date_count_arr,
            'count_select_date_all_orders_count' => $all_orders_count,
            'count_statuses' => $status_count_arr,
            'count_delvieries' => $CountDelviery,
            'count_branches' => $count_branches
        ];
        return $allCount;
    }


}
