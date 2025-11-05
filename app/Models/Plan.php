<?php
// File: app/Models/Plan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'price',
        'duration_days',
        'status'
    ];

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(PlanSubscription::class);
    }

    public function features()
    {
        return $this->hasMany(PlanFeature::class);
    }
}