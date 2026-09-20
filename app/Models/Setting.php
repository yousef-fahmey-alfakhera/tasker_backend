<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'default',
        'description',
    ];

    /**
     * User settings that reference this setting.
     */
    public function userSettings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    /**
     * Cast raw value to appropriate type based on setting definition.
     */
    public function castValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($this->type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'num' => is_numeric($value) ? ($value == (int) $value ? (int) $value : (float) $value) : 0,
            default => (string) $value,
        };
    }
}
