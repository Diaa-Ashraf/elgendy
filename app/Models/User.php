<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        if (! ($this->is_active ?? true)) {
            return false;
        }

        // لوحة السوبر أدمن مخصصة للمدير العام فقط
        if ($panel->getId() === 'super-admin') {
            return $this->hasRole('super_admin') || $this->email === 'admin@admin.com';
        }

        return true;
    }

    public function getTenants(Panel $panel): array|Collection
    {
        // إذا كان المدير العام (Super Admin) يرى جميع السناتر في لوحة التحكم
        if ($this->hasRole('super_admin') || $this->email === 'admin@admin.com') {
            return Tenant::all();
        }

        return $this->tenant ? collect([$this->tenant]) : collect();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        // المدير العام له حق الوصول لأي سنتر
        if ($this->hasRole('super_admin') || $this->email === 'admin@admin.com') {
            return true;
        }

        return $this->tenant_id === $tenant->id;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
