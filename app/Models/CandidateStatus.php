<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateStatus extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'order', 'color','admin_id','workspace_id'];

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'status_id');
    }
}
