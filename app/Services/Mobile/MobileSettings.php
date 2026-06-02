<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Strongly-typed accessor for every mobile-tunable threshold.
 *
 * All values originate from the `settings` table (seeded by
 * MobileSettingSeeder under modules `mobile_academy`,
 * `mobile_my_learning`, `mobile_attendance`, `mobile_rating`).
 * Nothing in the mobile services hardcodes a number — they all flow
 * through this class so a single SQL UPDATE can re-tune the platform.
 *
 * The reads are cached for the duration of a request (the result of
 * `getAll()` is memoized in the platform cache for 10 minutes —
 * matching the existing `cms.settings.map` cache pattern in
 * AppServiceProvider). Mutating any mobile setting from the admin
 * panel is expected to flush `mobile.settings.map`.
 *
 * Every accessor takes a *guaranteed-positive* fallback closure
 * (`?? throw`) because absence here is a deploy/seeding bug — not
 * something the user should silently see in production.
 */
final class MobileSettings
{
    private const CACHE_KEY = 'mobile.settings.map';
    private const CACHE_TTL_MINUTES = 10;

    /**
     * Flat `key => value` map of every `mobile_*` module setting.
     *
     * @var array<string, string>|null
     */
    private ?array $map = null;

    public function flush(): void
    {
        $this->map = null;
        Cache::forget(self::CACHE_KEY);
    }

    // ────────────────────────────────────────────────────────────
    // Academy (S-01 / S-02)
    // ────────────────────────────────────────────────────────────

    public function academyPerPage(): int
    {
        return $this->positiveInt('academy_per_page');
    }

    public function academySearchMinChars(): int
    {
        return $this->nonNegativeInt('academy_search_min_chars');
    }

    public function academyQualificationOverflowThreshold(): int
    {
        return $this->nonNegativeInt('academy_qualification_overflow_threshold');
    }

    public function academyDeadlineWarningDays(): int
    {
        return $this->nonNegativeInt('academy_deadline_warning_days');
    }

    public function academyDeadlineCriticalDays(): int
    {
        return $this->nonNegativeInt('academy_deadline_critical_days');
    }

    public function academyDefaultCloseOffsetDays(): int
    {
        return $this->nonNegativeInt('academy_default_close_offset_days');
    }

    /**
     * How many days before its start date a `scheduled` cohort becomes
     * visible in the Academy (Figma 332:10708 — "automatically open 1
     * month before the start date"). `open_for_enrollment` cohorts ignore
     * this and appear immediately.
     *
     * Reads a safe fallback (30 ≈ one month) when the setting row hasn't
     * been seeded yet so the Academy never goes blank on an un-migrated
     * environment.
     */
    public function academyScheduledVisibilityDays(): int
    {
        return $this->nonNegativeIntOr('academy_scheduled_visibility_days', 30);
    }

    // ────────────────────────────────────────────────────────────
    // My Learning (S-05)
    // ────────────────────────────────────────────────────────────

    public function myLearningActivePreviewCount(): int
    {
        return $this->positiveInt('my_learning_active_preview_count');
    }

    public function myLearningQualificationsPreviewCount(): int
    {
        return $this->positiveInt('my_learning_qualifications_preview_count');
    }

    public function myLearningCertificatesPreviewCount(): int
    {
        return $this->positiveInt('my_learning_certificates_preview_count');
    }

    public function myLearningActivePerPage(): int
    {
        return $this->positiveInt('my_learning_active_per_page');
    }

    public function myLearningCertificatesPerPage(): int
    {
        return $this->positiveInt('my_learning_certificates_per_page');
    }

    // ────────────────────────────────────────────────────────────
    // Attendance (S-06)
    // ────────────────────────────────────────────────────────────

    public function attendancePasscodeLength(): int
    {
        return $this->positiveInt('attendance_passcode_length');
    }

    public function attendanceWindowMinutes(): int
    {
        return $this->positiveInt('attendance_window_minutes');
    }

    public function attendanceSessionOpenBufferMinutes(): int
    {
        return $this->nonNegativeInt('attendance_session_open_buffer_minutes');
    }

    public function attendanceSessionGraceMinutes(): int
    {
        return $this->nonNegativeInt('attendance_session_grace_minutes');
    }

    /**
     * When true the issued passcode stays valid for the entire session
     * window ("Course Attendance = Yes" → "The passcode will remain the
     * same for the entire session"). When false the passcode rotates
     * every `passcodeResetSeconds()` and must be re-issued by the
     * instructor dashboard (Figma — "Passcode will reset each …").
     *
     * Defaults to the static behaviour on un-seeded environments so an
     * older deployment keeps its previous "valid for the window" semantics.
     */
    public function passcodeStaticForSession(): bool
    {
        return $this->boolOr('course_attendance_enabled', true);
    }

    /**
     * Rotation interval (seconds) for the live passcode when it is NOT
     * static. Falls back to 30s on un-seeded environments and clamps to a
     * sane floor so a misconfigured `0` never produces an already-expired
     * code.
     */
    public function passcodeResetSeconds(): int
    {
        $value = $this->nonNegativeIntOr('passcode_reset_seconds', 30);

        return $value > 0 ? $value : 30;
    }

    // ────────────────────────────────────────────────────────────
    // Rating
    // ────────────────────────────────────────────────────────────

    public function ratingMinValue(): int
    {
        return $this->nonNegativeInt('rating_min_value');
    }

    public function ratingMaxValue(): int
    {
        return $this->positiveInt('rating_max_value');
    }

    public function ratingCommentRequiredAtOrBelow(): int
    {
        return $this->nonNegativeInt('rating_comment_required_at_or_below');
    }

    public function ratingCommentMaxLength(): int
    {
        return $this->positiveInt('rating_comment_max_length');
    }

    // ────────────────────────────────────────────────────────────
    // Security (S2S integration)
    // ────────────────────────────────────────────────────────────

    /**
     * Shared bearer token that every mobile API caller (HR system,
     * partner integrations, …) must present in
     * `Authorization: Bearer <token>`.
     *
     * Rotate by issuing a single `UPDATE settings ...` (or via the
     * admin settings UI) and calling `MobileSettings::flush()` —
     * existing clients must then be updated with the new value.
     */
    public function sharedBearerToken(): string
    {
        $map = $this->loadMap();

        if (!array_key_exists('mobile_shared_bearer_token', $map)) {
            throw new \RuntimeException(
                'Missing mobile setting `mobile_shared_bearer_token`. '
                .'Run `php artisan db:seed --class=MobileSettingSeeder`.',
            );
        }

        $value = (string) $map['mobile_shared_bearer_token'];

        if ($value === '') {
            throw new \RuntimeException(
                'Mobile shared bearer token is empty in the settings table.',
            );
        }

        return $value;
    }

    // ────────────────────────────────────────────────────────────
    // Internals
    // ────────────────────────────────────────────────────────────

    private function positiveInt(string $key): int
    {
        $value = $this->intValue($key);

        if ($value <= 0) {
            throw new \RuntimeException(
                "Mobile setting `{$key}` must be a positive integer (got {$value}). "
                ."Run `php artisan db:seed --class=MobileSettingSeeder`.",
            );
        }

        return $value;
    }

    private function nonNegativeInt(string $key): int
    {
        $value = $this->intValue($key);

        if ($value < 0) {
            throw new \RuntimeException(
                "Mobile setting `{$key}` must be a non-negative integer (got {$value}).",
            );
        }

        return $value;
    }

    /**
     * Like nonNegativeInt() but tolerates a missing/blank row, returning
     * the supplied default instead of throwing. Used for settings added
     * after initial seeding so older environments degrade gracefully.
     */
    private function nonNegativeIntOr(string $key, int $default): int
    {
        $map = $this->loadMap();

        if (!array_key_exists($key, $map) || $map[$key] === '' || $map[$key] === null) {
            return $default;
        }

        $value = (int) $map[$key];

        return $value >= 0 ? $value : $default;
    }

    /**
     * Boolean accessor that tolerates a missing/blank row (returning the
     * supplied default). Accepts the usual truthy spellings ("1", "true",
     * "yes", "on") case-insensitively.
     */
    private function boolOr(string $key, bool $default): bool
    {
        $map = $this->loadMap();

        if (!array_key_exists($key, $map) || $map[$key] === '' || $map[$key] === null) {
            return $default;
        }

        return in_array(strtolower((string) $map[$key]), ['1', 'true', 'yes', 'on'], true);
    }

    private function intValue(string $key): int
    {
        $map = $this->loadMap();

        if (!array_key_exists($key, $map)) {
            throw new \RuntimeException(
                "Missing mobile setting `{$key}`. "
                ."Run `php artisan db:seed --class=MobileSettingSeeder`.",
            );
        }

        return (int) $map[$key];
    }

    /**
     * @return array<string, string>
     */
    private function loadMap(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        $this->map = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            static fn (): array => Setting::query()
                ->where('module', 'LIKE', 'mobile\_%')
                ->pluck('value', 'key')
                ->all(),
        );

        return $this->map;
    }
}
