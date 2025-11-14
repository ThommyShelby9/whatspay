<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    use HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'descriptipon',
        'files',
        'status',
        'client_id',
        'validation_date',
        'validateur_id',
        'startdate',
        'enddate',
        'budget',
        'type',
        'url',
        'text',
        'schedule',
        //Nouveaux champs
        'media_type',
        'locality_id',
        'occupation_id',
        'legend',
        'task_id',
        'budget_reserved_at',
        'budget_released_at'
    ];

    /**
     * Relation avec les localités
     */
    // Dans App\Models\Task
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function localities()
    {
        return $this->belongsToMany(Locality::class);
    }

    public function occupations()
    {
        return $this->belongsToMany(Occupation::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
