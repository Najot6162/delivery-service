<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent', 'agent_id'
    ];

    public function deliveryApp()
    {
        return $this->belongsTo(DeliveryApp::class);
    }

    public function problemApp()
    {
        return $this->belongsTo(ProblemApp::class);
    }
}
