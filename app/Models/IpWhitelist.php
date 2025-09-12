<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpWhitelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'label',
        'description',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this IP whitelist entry
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if IP is whitelisted and active
     */
    public static function isIpAllowed(string $ipAddress): bool
    {
        return self::where('ip_address', $ipAddress)
                  ->where('is_active', 1)
                  ->exists();
    }

    /**
     * Get all active IP addresses
     */
    public static function getActiveIps(): array
    {
        return self::where('is_active', 1)
                  ->pluck('ip_address')
                  ->toArray();
    }

    /**
     * Validate IP address format
     */
    public static function validateIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== 0;
    }

    /**
     * Scope for active IPs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope for inactive IPs
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }
}