<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id','data_pub','uniq_day','user_id','order_id','online','order_date',
        'date_create','document_id','provodka','content','orienter','client','client_id',
        'group_price','vid_oplata','id_1c','oplachena','step_one','step_two','step_six',
        'step','status','dallon','car_model_id','branch_id','change_date','change_status',
        'config_time_id','end_time','status_time','different_status_time','add_hours',
        'delivery_type','delivered_branch','confirm_cancelled','driver_manager'
    ];

    public function agent(){
        return $this->hasOne(Agent::class, 'agent_id', 'agent_id');
    }
    public function branch(){
        return $this->hasOne(BranchList::class, 'id', 'branch_id');
    }
    public function branch_sale(){
        return $this->hasOne(BranchList::class, 'id', 'branch_sale_id');
    }
    public function files(){
        return $this->hasOne(Files::class, 'app_id', 'id');
    }
    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function pickup_time(){
        return $this->hasOne(PickupTime::class, 'app_id', 'id');
    }
    public function delivery_product(){
        return $this->hasOne(DeliveryProduct::class, 'delivery_id', 'id');
    }
    public function delivery_client(){
        return $this->hasOne(Client::class, 'client_id', 'client_id');
    }
}
