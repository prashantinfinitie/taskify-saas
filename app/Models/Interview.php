<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'admin_id',
        'candidate_id',
        'interviewer_id',
        'round',
        'scheduled_at',
        'mode',
        'location',
        'status'
    ];

    public function candidate(){
        return $this->belongsTo(Candidate::class);
    }

    public function interviewer(){
        return $this->belongsTo(User::class, 'interviewer_id');
    }


}
