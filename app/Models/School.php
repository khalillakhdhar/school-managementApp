<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * The tenant model. One row = one school (establishment).
 *
 * Implements the Filament branding contracts so the tenant switcher and the
 * panel brand render the school's own name + logo:
 *  - HasName               → label in the tenant switcher
 *  - HasAvatar             → logo in the tenant switcher
 *  - HasCurrentTenantLabel → small "current tenant" caption
 */
class School extends Model implements HasName, HasAvatar, HasCurrentTenantLabel
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_TRIAL     = 'trial';

    protected $fillable = [
        'name', 'slug', 'subdomain', 'logo_path', 'primary_color',
        'email', 'phone', 'city', 'country',
        'status', 'plan', 'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'date',
    ];

    protected static function booted(): void
    {
        // Auto-slug on create when none provided.
        static::creating(function (School $school): void {
            if (blank($school->slug)) {
                $school->slug = static::uniqueSlug($school->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'ecole';
        $slug = $base;
        $i = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    // ── Relations ─────────────────────────────────────────────────────────

    /** Members of this school (Filament tenancy). */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_user')->withTimestamps();
    }

    /** Ownership relationship used by Filament tenant scoping (pilot: students). */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(SchoolSetting::class);
    }

    // ── Status / subscription helpers ─────────────────────────────────────

    public function isActive(): bool    { return $this->status === self::STATUS_ACTIVE; }
    public function isSuspended(): bool { return $this->status === self::STATUS_SUSPENDED; }
    public function isOnTrial(): bool   { return $this->status === self::STATUS_TRIAL; }

    public function trialHasExpired(): bool
    {
        return $this->isOnTrial()
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isPast();
    }

    // ── Branding ──────────────────────────────────────────────────────────

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function brandColor(): string
    {
        return $this->primary_color ?: '#2563EB';
    }

    // ── Filament branding contracts ───────────────────────────────────────

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logoUrl();
    }

    public function getCurrentTenantLabel(): string
    {
        return __('Établissement courant');
    }
}
