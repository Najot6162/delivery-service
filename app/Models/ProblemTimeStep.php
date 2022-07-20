<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemTimeStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'problem_uuid','step','user_id','branch_id','month_uniq','comment','active'
    ];

    public function problemApp(){
        return $this->belongsTo(ProblemApp::class);
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
