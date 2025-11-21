<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;
    use HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'lastname',
        'firstname',
        'email',
        'password',
        'enabled',
        'twofa_enabled',
        'twofa_code',
        'email_verified_at',
        'lastconnection',
        'country_id',
        'phonecountry_id',
        'phone',
        'vuesmoyen',
        'locality_id',
        'lang_id',
        'study_id',
        'occupation',
        'occupation_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        //'email_verified_at' => 'datetime',
    ];


    /**
     * Relation avec le pays
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Relation avec la localité
     */
    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    /**
     * Relation avec les études
     */
    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * Relation avec la langue
     */
    public function language()
    {
        return $this->belongsTo(Lang::class, 'lang_id');
    }

    /**
     * Relation avec l'occupation
     */
    public function occupation()
    {
        return $this->belongsTo(Occupation::class);
    }

    /**
     * Relation avec les catégories
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Relation avec les types de contenu
     */
    public function contentTypes()
    {
        return $this->belongsToMany(Contenttype::class);
    }

    /**
     * Relation avec les rôles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Relation avec les tâches
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'client_id');
    }

    /**
     * Les tâches assignées à l'utilisateur en tant qu'agent (diffuseur).
     */
    public function assignmentsAsAgent()
    {
        return $this->hasMany(Assignment::class, 'agent_id');
    }

    /**
     * Les tâches que l'utilisateur a assignées (si admin/modérateur).
     */
    public function assignmentsGiven()
    {
        return $this->hasMany(Assignment::class, 'assigner_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }
}
