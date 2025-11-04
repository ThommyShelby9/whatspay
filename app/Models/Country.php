<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory, SoftDeletes;
    use HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'iso3',
        'iso2',
        'numeric_code',
        'phone_code',
        'capital',
        'currency',
        'currency_name',
        'currency_symbol',
        'tld',
        'native',
        'nationality',
        'latitude',
        'longitude',
        'emoji',
        'emojiu',
        'timezones',
        'translations',
        'enabled'
    ];


    public function getIso2Attribute($value)
    {
        return strtolower($value);
    }

    protected $casts = [
        'translations' => 'array',
        'timezones' => 'array',
    ];
}
