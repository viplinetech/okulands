<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Writes the audit trail. Never throws: a logging failure must not break the action being logged.
 */
class Audit
{
    /**
     * @param  string  $action  dot-notation, e.g. "sale.approved", "auth.login", "user.suspended"
     * @param  array<string, mixed>  $properties  extra context (never secrets or passwords)
     */
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = [], ?int $userId = null): void
    {
        try {
            $request = request();

            ActivityLog::create([
                'user_id' => $userId ?? Auth::id(),
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'description' => $description ? Str::limit($description, 490, '') : null,
                'properties' => $properties ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => Str::limit((string) $request?->userAgent(), 290, ''),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
