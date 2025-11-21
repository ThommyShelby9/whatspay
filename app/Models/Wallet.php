<?php
// File: app/Models/Wallet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'balance',
        'currency',
        'status'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function credit(float $amount)
    {
        $this->balance += $amount;
        $this->save();
    }

    public function debit(float $amount)
    {
        if ($this->balance < $amount) {
            throw new \Exception("Solde insuffisant");
        }

        $this->balance -= $amount;
        $this->save();
    }
}
