<?php
// app/Models/LibrarySetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Methods
     */
    public function getValueAttribute($value)
    {
        return match($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match($this->type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'json' => json_encode($value),
            default => $value,
        };
    }

    /**
     * Scopes
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Static methods for easy access
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value, $type = 'string', $description = null, $is_public = false)
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            $setting->update([
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $is_public,
            ]);
        } else {
            static::create([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $is_public,
            ]);
        }
    }
}
