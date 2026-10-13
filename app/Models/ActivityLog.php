<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an action conveniently.
     */
    public static function record(
        string $action,
        ?int $userId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $meta = null
    ): self {
        return static::create([
            'user_id'      => $userId ?? auth()->id(),
            'action'       => $action,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'meta'         => $meta,
            'created_at'   => now(),
        ]);
    }
}
