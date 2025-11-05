<?php
// File: app/Models/PlanSubscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanSubscription extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'plan_id',
        'transaction_id',
        'status',
        'started_at',
        'valid_until',
        'canceled_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'valid_until' => 'datetime',
        'canceled_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}