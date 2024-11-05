<?php

namespace App\Http\Controllers\Tasks\Models;

use App\Models\User;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

/**
 * @property int $user_id
 * @property int $id
 */
class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'repeat_type',
        'repeat_end_date',
        'tags',
        'user_id'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'repeat_end_date' => 'datetime',
        'tags' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }
}
