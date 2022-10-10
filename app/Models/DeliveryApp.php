<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'agent_id', 'uniq_day', 'user_id', 'order_id', 'online', 'order_date',
        'date_create', 'document_id', 'provodka', 'content', 'orienter', 'client', 'client_id',
        'group_price', 'vid_oplata', 'id_1c', 'oplachena', 'driver_id', 'status', 'dallon',
        'car_model_id', 'branch_id', 'change_date', 'change_status',
        'config_time_id', 'end_time', 'status_time', 'different_status_time', 'add_hours',
        'delivery_type', 'delivered_branch', 'confirm_cancelled', 'driver_manager'
    ];

    public function agents()
    {
        return $this->hasOne(Agent::class, 'agent_id', 'agent_id');
    }

    public function branch()
    {
        return $this->hasOne(BranchList::class, 'id', 'branch_id');
    }

    public function branch_sale()
    {
        return $this->hasOne(BranchList::class, 'id', 'branch_sale_id');
    }

    public function branch_step()
    {
        return $this->hasOne(BranchList::class, 'id', 'branch_step');
    }

    public function files()
    {
        return $this->hasMany(Files::class, 'app_uuid', 'uuid');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function pickup_time()
    {
        return $this->hasMany(PickupTime::class, 'app_uuid', 'uuid')->where('active','1')->orderBy('step','asc');
    }

    public function delivery_product()
    {
        return $this->hasMany(DeliveryProduct::class, 'delivery_uuid', 'uuid');
    }

    public function delivery_client()
    {
        return $this->hasOne(Client::class, 'client_id', 'client_id');
    }

    public function config_time()
    {
        return $this->hasOne(ConfigTime::class, 'id', 'config_time_id');
    }

    public function car_model()
    {
        return $this->hasOne(CarModel::class, 'id', 'car_model_id');
    }
    public function driver(){
        return $this->hasOne(User::class, 'id', 'driver_id')->with('carModel');
    }
}
