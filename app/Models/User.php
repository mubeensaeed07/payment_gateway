<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'role',
        'prefix_number',
        'invitation_token',
        'invited_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
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
            'invited_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is reseller
     */
    public function isReseller(): bool
    {
        return $this->role === 'reseller';
    }

    /**
     * Get all customers for this admin
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'admin_id');
    }

    /**
     * Get all invoices created by this admin
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'admin_id');
    }

    /**
     * Get all slabs for this admin
     */
    public function slabs()
    {
        return $this->hasMany(Slab::class, 'admin_id')->orderBy('slab_number');
    }
}
