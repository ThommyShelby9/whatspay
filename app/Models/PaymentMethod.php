<?php
// File: app/Models/PaymentMethod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'code',
        'description',
        'icon',
        'status',
        'config'
    ];

    protected $casts = [
        'config' => 'array'
    ];

    // Relationships
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}