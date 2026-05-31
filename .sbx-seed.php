// 1) Special: link course #7 to qualification skill #3 (Excel) — matches
//    employee 2394's job-title qualifications (#2 Word, #3 Excel).
DB::table('course_qualification_skills')->updateOrInsert(
    ['course_id' => 7, 'qualification_skill_id' => 3],
    []
);
echo 'Linked course #7 -> qual #3 (Excel)' . PHP_EOL;

// 2) General: clone course #7's valid column set into a new for_public
//    course with no qualifications, then give it a future joinable cohort.
$c7 = App\Models\Course::find(7);
$attrs = $c7->getAttributes();
unset($attrs['id'], $attrs['created_at'], $attrs['updated_at']);
$attrs['title'] = json_encode(['en' => 'Effective Communication', 'ar' => 'مهارات التواصل الفعّال'], JSON_UNESCAPED_UNICODE);
$attrs['description'] = json_encode(['en' => '<p>Open soft-skills course anyone can join.</p>', 'ar' => '<p>دورة مهارات عامة متاحة للجميع.</p>'], JSON_UNESCAPED_UNICODE);
$attrs['for_public'] = 1;
$attrs['active'] = 1;
$newId = DB::table('courses')->insertGetId($attrs + ['created_at' => now(), 'updated_at' => now()]);
echo 'Created GENERAL course #' . $newId . ' (for_public=1)' . PHP_EOL;

// Future joinable cohort for the new general course (mirrors sec#31).
$sec31 = App\Models\CourseSection::find(31);
$sattrs = $sec31->getAttributes();
unset($sattrs['id'], $sattrs['created_at'], $sattrs['updated_at']);
$sattrs['course_id'] = $newId;
$sattrs['name'] = json_encode(['en' => 'Cohort A', 'ar' => 'الدفعة أ'], JSON_UNESCAPED_UNICODE);
$sattrs['start_date'] = '2026-07-05';
$sattrs['end_date'] = '2026-08-05';
$sattrs['enrolment_closes_at'] = '2026-06-28';
$sattrs['capacity'] = 30;
$sattrs['status'] = 'scheduled';
$newSec = DB::table('course_sections')->insertGetId($sattrs + ['created_at' => now(), 'updated_at' => now()]);
echo 'Created cohort sec#' . $newSec . ' for general course #' . $newId . PHP_EOL;
