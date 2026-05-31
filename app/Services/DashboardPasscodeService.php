<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CourseSession;
use App\Services\Mobile\MobileSettings;
use App\Services\Mobile\SessionPasscodeService;
use Illuminate\Support\Carbon;

/**
 * Composes the instructor-dashboard "Passcode" widget view-model
 * (Figma node 515:34995 / 515:37969 / 515:35489).
 *
 * The dashboard never needs a session id up-front — it resolves the
 * "current" session itself:
 *
 *   • a session that already carries an ACTIVE passcode  → state "live"
 *   • a session whose passcode was issued today but has
 *     since expired                                       → state "ended"
 *   • a session that is live-now by schedule (no code yet) → state "idle"
 *   • nothing relevant today                               → state "idle"
 *
 * "Generate Passcode" then targets the live-now session (or the
 * earliest session scheduled for today as a fallback) and delegates
 * the actual code generation to the existing SessionPasscodeService so
 * the admin dashboard and the mobile S-06 flow stay in lock-step.
 *
 * This is an ADDITIVE service — it never touches MVC and reuses the
 * same attendance-window buffers as MyLearningService::liveSessionFor()
 * so "Live Now" on mobile and "Generate Passcode" here agree.
 */
final class DashboardPasscodeService
{
    public function __construct(
        private readonly MobileSettings $settings,
        private readonly SessionPasscodeService $passcodes,
    ) {}

    /**
     * Read-only widget state for the dashboard header, scoped to the
     * sessions the logged-in instructor actually teaches.
     *
     * @param  array<int, int>|null  $courseIds  Courses the instructor
     *         teaches. `null` = the viewer is not an instructor (or
     *         teaches nothing) → the widget is unavailable and hidden.
     * @return array<string, mixed>
     */
    public function currentState(?array $courseIds): array
    {
        $length = $this->settings->attendancePasscodeLength();

        // Not an instructor (or teaches nothing) → widget hidden.
        if (empty($courseIds)) {
            return [
                'available'       => false,
                'state'           => 'idle',
                'passcode_length' => $length,
                'session'         => null,
                'passcode'        => null,
            ];
        }

        $now = now();

        // 1. A session with an active (non-expired) passcode → "live".
        $active = CourseSession::query()
            ->with(['section', 'course'])
            ->whereIn('course_id', $courseIds)
            ->whereNotNull('passcode')
            ->whereNotNull('passcode_expires_at')
            ->where('passcode_expires_at', '>', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('session_date')
                    ->orWhereDate('session_date', $now->toDateString());
            })
            ->orderByDesc('passcode_issued_at')
            ->first();

        if ($active) {
            return $this->compose($active, 'live', $length);
        }

        // 2. A session whose passcode was issued today but already
        //    expired → "ended" (digits stay visible + "Session ended").
        $ended = CourseSession::query()
            ->with(['section', 'course'])
            ->whereIn('course_id', $courseIds)
            ->whereNotNull('passcode')
            ->whereDate('passcode_issued_at', $now->toDateString())
            ->orderByDesc('passcode_issued_at')
            ->first();

        if ($ended) {
            return $this->compose($ended, 'ended', $length);
        }

        // 3. A session that is live-now by schedule but has no passcode
        //    yet → "idle" (the "Generate Passcode" affordance).
        $live = $this->liveNowSession($now, $courseIds);
        if ($live) {
            return $this->compose($live, 'idle', $length);
        }

        // 4. Instructor with no live session right now — widget is shown
        //    but the "Generate" button is disabled client-side.
        return [
            'available'       => true,
            'state'           => 'idle',
            'passcode_length' => $length,
            'session'         => null,
            'passcode'        => null,
        ];
    }

    /**
     * Resolve the session a freshly-pressed "Generate Passcode" should
     * target: the instructor's live-now session, falling back to the
     * earliest session scheduled for today. Null = nothing to generate.
     *
     * @param  array<int, int>  $courseIds
     */
    public function resolveTargetSession(array $courseIds): ?CourseSession
    {
        if (empty($courseIds)) {
            return null;
        }

        $now = now();

        return $this->liveNowSession($now, $courseIds)
            ?? $this->earliestTodaySession($now, $courseIds);
    }

    /**
     * Issue (or rotate) a passcode for the resolved session and return
     * the freshly-composed "live" widget state for the modal.
     *
     * @return array<string, mixed>
     */
    public function issueFor(CourseSession $session, ?int $length, ?Carbon $expiresAt): array
    {
        $fresh = $this->passcodes->issue($session, $length, $expiresAt);

        // Re-attach the relations the freshly-pulled model lost so
        // compose() doesn't fire two extra lazy queries.
        $fresh->setRelation('section', $session->section);
        $fresh->setRelation('course', $session->course);

        return $this->compose($fresh, 'live', $this->settings->attendancePasscodeLength());
    }

    // ────────────────────────────────────────────────────────────
    // Internals
    // ────────────────────────────────────────────────────────────

    /**
     * Session whose attendance window is open right now. Mirrors the
     * predicate in MyLearningService::liveSessionFor() but is not
     * scoped to a single learner/course (the dashboard is global).
     */
    private function liveNowSession(Carbon $now, array $courseIds): ?CourseSession
    {
        $openBuf  = $this->settings->attendanceSessionOpenBufferMinutes();
        $graceBuf = $this->settings->attendanceSessionGraceMinutes();

        return CourseSession::query()
            ->with(['section', 'course'])
            ->whereIn('course_id', $courseIds)
            ->where(function ($q) use ($now) {
                $q->whereNull('session_date')
                    ->orWhereDate('session_date', $now->toDateString());
            })
            ->where(function ($q) use ($now, $openBuf, $graceBuf) {
                $q->where(function ($q2) {
                    $q2->whereNull('time_from')->whereNull('time_to');
                })->orWhere(function ($q2) use ($now, $openBuf, $graceBuf) {
                    $q2->whereRaw(
                        'TIMESTAMPDIFF(MINUTE, ?, time_from) <= ?',
                        [$now->format('H:i:s'), $openBuf],
                    )->whereRaw(
                        'TIMESTAMPDIFF(MINUTE, time_to, ?) <= ?',
                        [$now->format('H:i:s'), $graceBuf],
                    );
                });
            })
            ->orderBy('time_from')
            ->orderBy('id')
            ->first();
    }

    private function earliestTodaySession(Carbon $now, array $courseIds): ?CourseSession
    {
        return CourseSession::query()
            ->with(['section', 'course'])
            ->whereIn('course_id', $courseIds)
            ->whereDate('session_date', $now->toDateString())
            ->orderBy('time_from')
            ->orderBy('id')
            ->first();
    }

    /**
     * 1-based position of the session inside its cohort, ordered the
     * way a human reads a syllabus (date → start time → id). Drives the
     * "Session: 3" line in the Live Passcode modal.
     */
    private function sessionOrdinal(CourseSession $session): int
    {
        if ($session->section_id === null) {
            return 1;
        }

        $ids = CourseSession::query()
            ->where('section_id', $session->section_id)
            ->orderByRaw('session_date IS NULL')
            ->orderBy('session_date')
            ->orderBy('time_from')
            ->orderBy('id')
            ->pluck('id');

        $index = $ids->search($session->id);

        return $index === false ? 1 : ((int) $index + 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function compose(CourseSession $session, string $state, int $length): array
    {
        $locale  = app()->getLocale();
        $expires = $session->passcode_expires_at
            ? Carbon::parse((string) $session->passcode_expires_at)
            : null;

        $passcode = $session->passcode
            ? [
                'code'       => (string) $session->passcode,
                'issued_at'  => $session->passcode_issued_at
                    ? Carbon::parse((string) $session->passcode_issued_at)->toIso8601String()
                    : null,
                'expires_at' => $expires?->toIso8601String(),
                'expired'    => $expires ? $expires->lte(now()) : false,
            ]
            : null;

        return [
            'available'       => true,
            'state'           => $state,
            'passcode_length' => $length,
            'session'         => [
                'id'           => (int) $session->id,
                'number'       => $this->sessionOrdinal($session),
                'title'        => $session->title,
                'date'         => $session->session_date
                    ? Carbon::parse((string) $session->session_date)->toDateString()
                    : null,
                'time_from'    => $this->formatTime($session->time_from),
                'time_to'      => $this->formatTime($session->time_to),
                'course_id'    => (int) $session->course_id,
                'course_title' => $session->course?->getTranslation('title', $locale),
                'cohort_id'    => $session->section_id !== null ? (int) $session->section_id : null,
                'cohort_name'  => $session->section?->getTranslation('name', $locale),
            ],
            'passcode'        => $passcode,
        ];
    }

    private function formatTime(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        return substr($time, 0, 5);
    }
}
