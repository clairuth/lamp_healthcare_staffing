<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'notification_type',
        'title',
        'message',
        'is_read',
        'read_at',
        'related_entity_type',
        'related_entity_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that the notification belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the notification as read.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if (!$this->is_read) {
            $this->is_read = true;
            $this->read_at = now();
            return $this->save();
        }
        
        return true;
    }

    /**
     * Get the related entity based on type.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getRelatedEntity()
    {
        if (!$this->related_entity_type || !$this->related_entity_id) {
            return null;
        }

        $modelClass = 'App\\Models\\' . $this->related_entity_type;
        
        if (!class_exists($modelClass)) {
            return null;
        }
        
        return $modelClass::find($this->related_entity_id);
    }
}
