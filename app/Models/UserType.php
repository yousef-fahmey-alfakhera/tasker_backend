<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'respnsapity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'respnsapity' => 'array',
        ];
    }

    /**
     * User owning this user type.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
