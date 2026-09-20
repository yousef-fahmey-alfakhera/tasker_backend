<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'setting_id',
        'value',
    ];

    /**
     * User owning this setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Target setting definition.
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class);
    }

    /**
     * Accessor for value casted to target setting type.
     */
    protected function castedValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->relationLoaded('setting') && $this->setting
                ? $this->setting->castValue($this->value)
                : $this->value
        );
    }
}
