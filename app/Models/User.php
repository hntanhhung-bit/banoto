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
        'verification_otp',
        'verification_otp_expires_at',
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
        'verification_otp',
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
            'verification_otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Tạo mã OTP xác thực 6 số ngẫu nhiên
     */
    public function generateVerificationOtp(): string
    {
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        
        // Lưu vào Session và Cache để luôn hoạt động kể cả khi chưa chạy migration
        session([
            'email_verification_otp_' . $this->id => $otp,
            'email_verification_otp_expires_' . $this->id => now()->addMinutes(15),
        ]);

        try {
            if ($this->exists) {
                $this->forceFill([
                    'verification_otp' => $otp,
                    'verification_otp_expires_at' => now()->addMinutes(15),
                ])->save();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info('Could not save OTP to DB table: ' . $e->getMessage());
        }

        return $otp;
    }

    /**
     * Lấy mã OTP đang có hiệu lực của user
     */
    public function getActiveOtp(): string
    {
        // Kiểm tra xem trong DB hoặc Session có mã nào còn hạn không
        $sessionOtp = session('email_verification_otp_' . $this->id);
        $sessionExpires = session('email_verification_otp_expires_' . $this->id);

        if ($sessionOtp && $sessionExpires && now()->lt($sessionExpires)) {
            return $sessionOtp;
        }

        if (!empty($this->verification_otp) && $this->verification_otp_expires_at && now()->lt($this->verification_otp_expires_at)) {
            session([
                'email_verification_otp_' . $this->id => $this->verification_otp,
                'email_verification_otp_expires_' . $this->id => $this->verification_otp_expires_at,
            ]);
            return $this->verification_otp;
        }

        return $this->generateVerificationOtp();
    }

    /**
     * Xác thực mã OTP
     */
    public function verifyOtp(string $inputOtp): bool
    {
        $inputOtp = trim($inputOtp);
        $activeOtp = $this->getActiveOtp();

        if (empty($inputOtp) || empty($activeOtp)) {
            return false;
        }

        if ($inputOtp === $activeOtp) {
            $this->markEmailAsVerified();

            // Xoá OTP sau khi xác thực thành công
            session()->forget([
                'email_verification_otp_' . $this->id,
                'email_verification_otp_expires_' . $this->id,
            ]);

            try {
                if ($this->exists) {
                    $this->forceFill([
                        'verification_otp' => null,
                        'verification_otp_expires_at' => null,
                    ])->save();
                }
            } catch (\Throwable $e) {
                // Ignore DB error if columns not ready
            }

            return true;
        }

        return false;
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