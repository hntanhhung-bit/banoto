<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Đã bỏ dấu // ở đầu dòng này
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Đã thêm "implements MustVerifyEmail" vào dòng dưới đây
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role', // Đã bổ sung trường role vào đây
        'partner_status',
        'showroom_name',
        'showroom_address',
        'representative_name',
        'id_card_number',
        'tax_code',
        'business_license_image',
        'id_card_image',
        'partner_applied_at',
        'partner_approved_at',
        'partner_reject_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function isPartner(): bool
    {
        return $this->role === 'partner' && ($this->partner_status === 'approved' || empty($this->partner_status));
    }

    public function isPendingPartner(): bool
    {
        return $this->partner_status === 'pending';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function partnerProducts()
    {
        return $this->hasMany(Product::class, 'partner_id');
    }

    public function partnerAppointments()
    {
        return $this->hasMany(Appointment::class, 'partner_id');
    }

    public function partnerRentals()
    {
        return $this->hasMany(Rental::class, 'partner_id');
    }
}