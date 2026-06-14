<?php
namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isParent(): bool   { return $this->role === 'parent'; }
    public function isTeacher(): bool  { return $this->role === 'teacher'; }
}
