<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active ?? true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $color = '%23f59e0b';
        $bg = '%230f172a';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="'.$bg.'"/><circle cx="50" cy="50" r="46" stroke="'.$color.'" stroke-width="3" fill="none"/><path d="M50 48a16 16 0 1 0 0-32 16 16 0 0 0 0 32zm0 8c-18.7 0-34 11.3-34 26 0 2.2 1.8 4 4 4h60c2.2 0 4-1.8 4-4 0-14.7-15.3-26-34-26z" fill="'.$color.'"/></svg>';
        return 'data:image/svg+xml;utf8,' . $svg;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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
