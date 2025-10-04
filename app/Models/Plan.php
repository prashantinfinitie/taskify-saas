<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'max_projects',
        'max_clients',
        'max_team_members',
        'max_workspaces',
        'plan_type',
        'modules',
        'monthly_price',
        'monthly_discounted_price',
        'yearly_price',
        'yearly_discounted_price',
        'lifetime_price',
        'lifetime_discounted_price',
    ];

    protected $casts = [
        'modules' => 'json',
        'monthly_price' => 'decimal:2',
        'monthly_discounted_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'yearly_discounted_price' => 'decimal:2',
        'lifetime_price' => 'decimal:2',
        'lifetime_discounted_price' => 'decimal:2',
    ];
    public function getlink()
    {
        return str(route('plans.edit', ['id' => $this->id]));
    }
    public function getresult()
    {

        return substr($this->name, 0, 100);
    }
}
