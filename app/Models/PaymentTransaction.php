<?php
// File: app/Models/PaymentTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'payment_method_id',
        'amount',
        'currency',
        'status',
        'reference',
        'expires_at',
        'completed_at',
        'callback_url',
        'payload',
        'gateway_response'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'payload' => 'array',
        'gateway_response' => 'array'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}