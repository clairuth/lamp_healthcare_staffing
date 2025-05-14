<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CredentialVerification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'credential_verifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'credential_id',
        'verified_by',
        'verification_date',
        'verification_method',
        'verification_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verification_date' => 'datetime',
    ];

    /**
     * Get the credential that was verified.
     */
    public function credential(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
    }

    /**
     * Get the user who verified the credential.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if the verification was done manually.
     *
     * @return bool
     */
    public function isManual(): bool
    {
        return $this->verification_method === 'manual';
    }

    /**
     * Check if the verification was done automatically.
     *
     * @return bool
     */
    public function isAutomated(): bool
    {
        return $this->verification_method === 'automated';
    }

    /**
     * Check if the verification was done by a third party.
     *
     * @return bool
     */
    public function isThirdParty(): bool
    {
        return $this->verification_method === 'third_party';
    }
}
