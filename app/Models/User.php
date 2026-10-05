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
     * Tạo mã OTP xác thực 6 số ngẫu nhiên và gửi về email
     */
    public function generateVerificationOtp(bool $sendEmail = true): string
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

        if ($sendEmail) {
            $this->sendOtpEmail($otp);
        }

        return $otp;
    }

    /**
     * Gửi email chứa mã OTP đến người dùng (Ưu tiên Gmail API)
     */
    public function sendOtpEmail(?string $otp = null): bool
    {
        $otp = $otp ?: $this->getActiveOtp();
        $user = $this;

        // 1. Thử gửi qua Mắt Bão / Custom SMTP nếu được cấu hình
        try {
            $smtp = \App\Services\EmailConfigService::getCustomSmtp();
            if (!empty($smtp['is_enabled']) && !empty($smtp['host']) && !empty($smtp['username']) && !empty($smtp['password'])) {
                try {
                    config([
                        'mail.mailers.custom_smtp_runner' => [
                            'transport' => 'smtp',
                            'host' => $smtp['host'],
                            'port' => $smtp['port'],
                            'encryption' => $smtp['encryption'],
                            'username' => $smtp['username'],
                            'password' => $smtp['password'],
                            'timeout' => 5, // 5s timeout để không bị treo nếu cổng bị chặn
                        ]
                    ]);

                    $fromAddress = !empty($smtp['from_address']) ? $smtp['from_address'] : $smtp['username'];
                    $fromName = !empty($smtp['from_name']) ? $smtp['from_name'] : 'Auto Car Vietnam';

                    \Illuminate\Support\Facades\Mail::mailer('custom_smtp_runner')->send([], [], function ($message) use ($user, $otp, $fromAddress, $fromName) {
                        $html = view('emails.verify-otp', [
                            'userName' => $user->name,
                            'otp' => $otp,
                            'email' => $user->email,
                        ])->render();

                        $message->to($user->email, $user->name)
                            ->from($fromAddress, $fromName)
                            ->subject("🔑 [Auto Car] Mã xác thực OTP của bạn: {$otp}")
                            ->html($html);
                    });

                    \Illuminate\Support\Facades\Log::info("OTP email sent successfully via Mắt Bão SMTP to {$user->email}");
                    return true;
                } catch (\Throwable $smtpEx) {
                    \Illuminate\Support\Facades\Log::warning("Mắt Bão SMTP failed: " . $smtpEx->getMessage() . ", falling back to Gmail API...");
                }
            }
        } catch (\Throwable $e) {
            // bỏ qua
        }

        // 2. Thử gửi qua Google Gmail API (Port 443 HTTPS - Hoạt động trên mọi hosting kể cả Render)
        try {
            $savedGmail = \App\Mail\Transports\GmailApiTransport::getSavedCredentials();
            $hasGmailApi = !empty($savedGmail['refresh_token']) || !empty(env('GMAIL_REFRESH_TOKEN'));

            if ($hasGmailApi) {
                try {
                    $fromAddress = $savedGmail['connected_email'] ?? env('MAIL_FROM_ADDRESS', 'hntanhhung@gmail.com');
                    $fromName = env('MAIL_FROM_NAME', 'Auto Car Vietnam');

                    \Illuminate\Support\Facades\Mail::mailer('gmail')->send([], [], function ($message) use ($user, $otp, $fromAddress, $fromName) {
                        $html = view('emails.verify-otp', [
                            'userName' => $user->name,
                            'otp' => $otp,
                            'email' => $user->email,
                        ])->render();

                        $message->to($user->email, $user->name)
                            ->from($fromAddress, $fromName)
                            ->subject("🔑 [Auto Car] Mã xác thực OTP của bạn: {$otp}")
                            ->html($html);
                    });

                    \Illuminate\Support\Facades\Log::info("OTP email sent successfully via Gmail API to {$user->email}");
                    return true;
                } catch (\Throwable $gmailEx) {
                    \Illuminate\Support\Facades\Log::warning("Gmail API sending failed, falling back to default mailer: " . $gmailEx->getMessage());
                }
            }

            // 3. Fallback gửi qua Mailer mặc định (Mailtrap / Resend / Brevo)
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($user, $otp) {
                $html = view('emails.verify-otp', [
                    'userName' => $user->name,
                    'otp' => $otp,
                    'email' => $user->email,
                ])->render();

                $fromAddress = env('MAIL_FROM_ADDRESS', 'noreply@banoto.com');
                $fromName = env('MAIL_FROM_NAME', 'Auto Car Vietnam');

                $message->to($user->email, $user->name)
                    ->from($fromAddress, $fromName)
                    ->subject("🔑 [Auto Car] Mã xác thực OTP của bạn: {$otp}")
                    ->html($html);
            });

            \Illuminate\Support\Facades\Log::info("OTP email sent successfully via default mailer to {$user->email}");
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send OTP email to {$user->email}: " . $e->getMessage());
            return false;
        }
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