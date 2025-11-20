<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;
    use HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'task_id',
        'assignment_date',
        'assigner_id',
        'response_date',
        'agent_id',
        'status',
        'submission_date',
        'vues',
        'files',
        'gain',

        'expected_views',
        'expected_gain',
        'comments'
    ];

    /**
     * La tâche assignée.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * L'agent (diffuseur) qui reçoit la tâche.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * L'utilisateur (admin/modérateur) qui a assigné la tâche.
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }
}
