<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\CourseSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Admin-side passcode generation / rotation for a CourseSession.
 *
 * Length defaults to the platform `attendance_passcode_length` but
 * can be overridden per-call (the IssuePasscodeRequest exposes the
 * override). Expiry defaults to now() + `attendance_window_minutes`
 * but can be pinned via a caller-supplied timestamp.
 *
 * The generator favors numeric-only codes (matches the S-06 numeric
 * keypad) and uses random_int() so it's safe for the modest entropy
 * we need (5–6 digits over a short validity window).
 */
final class SessionPasscodeService
{
    public function __construct(private readonly MobileSettings $settings) {}

    public function issue(CourseSession $session, ?int $lengthOverride, ?Carbon $expiresAt): CourseSession
    {
        $length = $lengthOverride !== null && $lengthOverride > 0
            ? $lengthOverride
            : $this->settings->attendancePasscodeLength();

        $windowMinutes = $this->settings->attendanceWindowMinutes();
        $effectiveExpiry = $expiresAt ?? now()->addMinutes($windowMinutes);

        $session->forceFill([
            'passcode'                  => $this->randomNumericCode($length),
            'passcode_issued_at'        => now(),
            'passcode_expires_at'       => $effectiveExpiry,
            'attendance_window_minutes' => $expiresAt === null ? $windowMinutes : null,
        ])->save();

        return $session->fresh();
    }

    public function revoke(CourseSession $session): CourseSession
    {
        $session->forceFill([
            'passcode'                  => null,
            'passcode_issued_at'        => null,
            'passcode_expires_at'       => null,
            'attendance_window_minutes' => null,
        ])->save();

        return $session->fresh();
    }

    private function randomNumericCode(int $length): string
    {
        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= (string) random_int(0, 9);
        }
        // Defensive: ensure the leading digit isn't zero so the
        // mobile client doesn't accidentally treat the code as an
        // integer and drop the leading zero in storage.
        if ($digits[0] === '0') {
            $digits[0] = (string) random_int(1, 9);
        }
        return $digits;
    }
}
