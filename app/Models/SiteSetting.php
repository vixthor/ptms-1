<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * The settings keys managed by the "Site Settings" admin screen, with
     * their placeholder defaults.
     */
    public const DEFAULTS = [
        'site_name' => 'PTMS',
        'tagline' => 'Excellence at its peak',
        'enrollment_count' => '0',
        'staff_count' => '0',
        'clubs_count' => '0',
        'subjects_count' => '0',
        'phone' => '',
        'whatsapp' => '',
        'email' => '',
        'address' => '',
        'facebook_url' => '',
        'twitter_url' => '',
        'instagram_url' => '',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::where('key', $key)->value('value');

        return $value !== null ? $value : ($default ?? self::DEFAULTS[$key] ?? null);
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * All settings as a flat [key => value] array, filled in with
     * placeholder defaults for anything not yet set.
     */
    public static function allWithDefaults(): array
    {
        $stored = static::pluck('value', 'key')->toArray();

        return array_merge(self::DEFAULTS, $stored);
    }
}
