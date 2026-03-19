<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'nickname', 'email', 'phone', 'password',
        'google_id', 'facebook_id', 'avatar', 'is_admin',
        'role', 'can_manage_licenses', 'can_manage_contents', 'can_manage_guides',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'is_admin'            => 'boolean',
            'can_manage_licenses' => 'boolean',
            'can_manage_contents' => 'boolean',
            'can_manage_guides'   => 'boolean',
        ];
    }

    // ── Role helpers ─────────────────────────────────────
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return in_array($this->role, ['admin', 'super_admin']) || $this->is_admin; }
    public function canAccessAdmin(): bool { return $this->isAdmin(); }

    // ── Permission helpers ───────────────────────────────
    public function canManageLicenses(): bool { return $this->isSuperAdmin() || $this->can_manage_licenses; }
    public function canManageContents(): bool { return $this->isSuperAdmin() || $this->can_manage_contents; }
    public function canManageGuides(): bool   { return $this->isSuperAdmin() || $this->can_manage_guides; }

    // ── Relationships ─────────────────────────────────────
    public function licenses()   { return $this->hasMany(License::class, 'activated_by'); }
    public function challenges() { return $this->hasMany(Challenge::class); }
}
