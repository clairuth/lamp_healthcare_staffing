<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Professional extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'healthcare_professionals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'professional_type',
        'years_experience',
        'bio',
        'hourly_rate_min',
        'availability_status',
        'rating',
        'total_shifts_completed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hourly_rate_min' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_shifts_completed' => 'integer',
        'years_experience' => 'integer',
    ];

    /**
     * Get the user that owns the professional profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the credentials for the professional.
     */
    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class);
    }

    /**
     * Get the skills for the professional.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(ProfessionalSkill::class);
    }

    /**
     * Get the shift applications for the professional.
     */
    public function shiftApplications(): HasMany
    {
        return $this->hasMany(ShiftApplication::class);
    }

    /**
     * Get the skill assessments for the professional.
     */
    public function skillAssessments(): HasMany
    {
        return $this->hasMany(SkillAssessment::class);
    }
}
