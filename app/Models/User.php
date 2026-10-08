<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * SECURITY: role, status, referred_by and commission_rate_override are deliberately NOT
 * mass-assignable, so no request payload can ever elevate an account. Privileged code
 * sets them explicitly (forceFill / attribute assignment).
 */
#[Fillable(['name', 'email', 'password', 'phone', 'gender', 'bank_name', 'account_name', 'account_number', 'avatar'])]
#[Hidden(['password', 'remember_token', 'account_number', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Roles that sell and earn: 'realtor' is the standard; 'downliner' is kept for older data. */
    public const AFFILIATE_ROLES = ['realtor', 'downliner'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'commission_rate_override' => 'decimal:2',
            'last_login_at' => 'datetime',
            'account_number' => 'encrypted',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_grace_ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode($user->name);
            }
        });
    }

    /**
     * Referral codes are short and sequential, e.g. OK001, OK002, OK003. The next number follows the highest
     * existing OK code, so every code is unique and easy to read out, print on a card or type into a link.
     */
    public static function generateUniqueReferralCode(string $name = ''): string
    {
        $highest = static::where('referral_code', 'like', 'OK%')
            ->pluck('referral_code')
            ->map(fn (string $code) => ctype_digit(substr($code, 2)) ? (int) substr($code, 2) : 0)
            ->max() ?? 0;

        $number = $highest + 1;
        while (static::where('referral_code', sprintf('OK%03d', $number))->exists()) {
            $number++;
        }

        return sprintf('OK%03d', $number);
    }

    /* ---------- Roles & state ---------- */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRealtor(): bool
    {
        return $this->role === 'realtor';
    }

    public function isDownliner(): bool
    {
        return $this->role === 'downliner';
    }

    /** Realtors and downliners share the affiliate area. */
    public function isAffiliate(): bool
    {
        return in_array($this->role, self::AFFILIATE_ROLES, true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeAffiliates(Builder $query): Builder
    {
        return $query->whereIn('role', self::AFFILIATE_ROLES);
    }

    /** Where this user lands after signing in. */
    public function homeRoute(): string
    {
        return $this->isAdmin() ? 'admin.dashboard' : 'realtor.dashboard';
    }

    /* ---------- Two-factor ---------- */

    public function hasTwoFactorEnabled(): bool
    {
        return ! empty($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    /* ---------- Display helpers ---------- */

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->filter()->take(2)->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)))->implode('');
    }

    public function firstName(): string
    {
        return Str::before($this->name, ' ');
    }

    /** Personalised ("Hello, Name!") rather than Laravel's generic "Hello!". */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }

    /** Personalised ("Hello, Name!") rather than Laravel's generic "Hello!". */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPassword($token));
    }

    public function avatarUrl(): ?string
    {
        return SiteSetting::media($this->avatar);
    }

    /** "•••• 1234" so the full number never renders on screen by default. */
    public function maskedAccountNumber(): ?string
    {
        return $this->account_number ? '•••• '.substr($this->account_number, -4) : null;
    }

    /** The shareable referral link, optionally landing on a specific path (e.g. a property). */
    public function referralLink(?string $to = null): string
    {
        $url = route('referral', $this->referral_code);

        return $to ? $url.'?to='.rawurlencode($to) : $url;
    }

    /* ---------- Relations ---------- */

    /** The user (realtor) who referred this user, if any. */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /** Direct downliners recruited by this user. */
    public function downlines(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /** Every visitor click/registration attributed to this user's referral link. */
    public function referralVisits(): HasMany
    {
        return $this->hasMany(ReferralVisit::class, 'referrer_id');
    }

    /** Enquiries that arrived through this user's referral link. */
    public function referredLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'referrer_id');
    }

    /**
     * Leads that came through this realtor's link, as the REALTOR may see them: which property, what kind,
     * the status and when. Only these columns are ever loaded, so a person's name, phone, email and message
     * cannot reach a realtor screen even by mistake. (Admins use referredLeads() / Lead directly.)
     */
    public function referredBookings(): HasMany
    {
        return $this->hasMany(Lead::class, 'referrer_id')->select(['id', 'referrer_id', 'property_id', 'type', 'status', 'created_at']);
    }

    /** Sales this user closed as the primary realtor. */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'realtor_id');
    }

    /** Commission payouts earned by this user across all tiers. */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Earned commission not yet paid out and not already tied up in a pending withdrawal request.
     * This is what the realtor can newly request right now.
     */
    public function availableBalance(): float
    {
        $pendingCommissions = (float) $this->commissions()->where('status', 'pending')->sum('amount');
        $tiedUp = (float) $this->withdrawals()->where('status', 'pending')->sum('amount');

        return max(0, $pendingCommissions - $tiedUp);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'created_by');
    }

    /**
     * Full upline chain, nearest referrer first, used to fan out
     * multi-tier commissions when a sale is approved.
     */
    public function uplineChain(int $maxTiers = 5): array
    {
        $chain = [];
        $current = $this->referrer;
        $tier = 1;
        $seen = [$this->id => true]; // guards against a corrupted referral loop

        while ($current && $tier <= $maxTiers && ! isset($seen[$current->id])) {
            $chain[] = ['user' => $current, 'tier' => $tier];
            $seen[$current->id] = true;
            $current = $current->referrer;
            $tier++;
        }

        return $chain;
    }
}
