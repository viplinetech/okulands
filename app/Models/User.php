<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'referred_by', 'commission_rate_override', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'commission_rate_override' => 'decimal:2',
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

    public static function generateUniqueReferralCode(string $name): string
    {
        $base = Str::slug($name) ?: 'user';
        $code = $base;
        $suffix = 1;

        while (static::where('referral_code', $code)->exists()) {
            $code = "{$base}-{$suffix}";
            $suffix++;
        }

        return $code;
    }

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

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'created_by');
    }

    /**
     * Full upline chain, nearest referrer first — used to fan out
     * multi-tier commissions when a sale is approved.
     */
    public function uplineChain(int $maxTiers = 5): array
    {
        $chain = [];
        $current = $this->referrer;
        $tier = 1;

        while ($current && $tier <= $maxTiers) {
            $chain[] = ['user' => $current, 'tier' => $tier];
            $current = $current->referrer;
            $tier++;
        }

        return $chain;
    }
}
