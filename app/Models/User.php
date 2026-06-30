<?php
namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'must_change_password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function parent(): HasOne
    {
        return $this->hasOne(SchoolParent::class, 'user_id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    /** Schools this user belongs to (Filament tenancy). */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_user')->withTimestamps();
    }

    // ── Filament HasTenants contract ──────────────────────────────────────
    public function getTenants(Panel $panel): Collection
    {
        return $this->schools;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->schools()->whereKey($tenant->getKey())->exists();
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isParent(): bool   { return $this->role === 'parent'; }
    public function isTeacher(): bool  { return in_array($this->role, ['teacher', 'employee'], true); }

    /**
     * Which Filament panel each role may enter.
     * - admin    -> every panel (convenience)
     * - parent   -> /parent
     * - teacher/employee -> /staff
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'  => $this->role === 'admin',
            'parent' => in_array($this->role, ['parent', 'admin'], true),
            'staff'  => in_array($this->role, ['teacher', 'employee', 'admin'], true),
            'spike'  => $this->role === 'admin', // PHASE 0 SPIKE — throwaway tenancy panel
            default  => false,
        };
    }
}
