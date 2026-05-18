# 2B Academy — API Migration Checklist

> **Legend**
> - ✅ Covered — REST endpoint exists and is implemented
> - ⚠️ Partial — some actions covered, gaps noted
> - ❌ Missing — no API equivalent built yet
> - 🔕 N/A — web-only / file-download / QR-page; not a REST concern

---

## 1. Authentication & Profile

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| User login | `POST /user/login` | `POST /api/v1/auth/user/login` | ✅ |
| User logout | `POST /user/logout` | `POST /api/v1/auth/user/logout` | ✅ |
| User logout all devices | — | `POST /api/v1/auth/user/logout-all` | ✅ |
| Get authenticated user | — | `GET /api/v1/auth/user/me` | ✅ |
| Update user profile | `PUT /admin/profile` (web) | `PUT /api/v1/auth/user/profile` | ✅ |
| Admin login | `POST /login` | `POST /api/v1/auth/admin/login` | ✅ |
| Admin logout | — | `POST /api/v1/auth/admin/logout` | ✅ |
| Get authenticated admin | — | `GET /api/v1/auth/admin/me` | ✅ |
| Admin profile update | `PUT /admin/profile` | ❌ No API endpoint | ❌ |

---

## 2. Users (Admin Management)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List users | `GET /admin/users` | `GET /api/v1/users` | ✅ |
| Show user | `GET /admin/users/{user}` | `GET /api/v1/users/{user}` | ✅ |
| Create user | `POST /admin/users` | `POST /api/v1/users` | ✅ |
| Update user | `PUT /admin/users/{user}` | `PUT /api/v1/users/{user}` | ✅ |
| Delete user | `DELETE /admin/users/{user}` | `DELETE /api/v1/users/{user}` | ✅ |
| Search users | `GET /admin/users?search=…` | `GET /api/v1/users/search` | ✅ |
| Sync employees from HR | `GET /admin/users/sync` | ❌ No API endpoint | ❌ |
| Select2 AJAX autocomplete | `GET /admin/users/ajax/select` | 🔕 N/A (admin UI only) | 🔕 |

---

## 3. Admins

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List admins | `GET /admin/admins` | `GET /api/v1/admins` | ✅ |
| Show admin | `GET /admin/admins/{admin}` | `GET /api/v1/admins/{admin}` | ✅ |
| Create admin | `POST /admin/admins` | `POST /api/v1/admins` | ✅ |
| Update admin | `PUT /admin/admins/{admin}` | `PUT /api/v1/admins/{admin}` | ✅ |
| Delete admin | `DELETE /admin/admins/{admin}` | `DELETE /api/v1/admins/{admin}` | ✅ |
| Admin dashboard stats | `GET /admin/dashboard` | `GET /api/v1/dashboard` | ✅ |
| Update own admin profile | `PUT /admin/profile` | ❌ No API endpoint | ❌ |

---

## 4. Roles & Permissions

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List roles | `GET /admin/roles` | `GET /api/v1/roles` | ✅ |
| List all roles (no pagination) | — | `GET /api/v1/roles/all` | ✅ |
| Show role | `GET /admin/roles/{role}` | `GET /api/v1/roles/{role}` | ✅ |
| Create role | `POST /admin/roles` | `POST /api/v1/roles` | ✅ |
| Update role | `PUT /admin/roles/{role}` | `PUT /api/v1/roles/{role}` | ✅ |
| Delete role | `DELETE /admin/roles/{role}` | `DELETE /api/v1/roles/{role}` | ✅ |

---

## 5. Instructors

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List instructors | `GET /admin/instructors` | `GET /api/v1/instructors` | ✅ |
| List all (no pagination) | — | `GET /api/v1/instructors/all` | ✅ |
| Show instructor | `GET /admin/instructors/{id}` | `GET /api/v1/instructors/{instructor}` | ✅ |
| Create instructor | `POST /admin/instructors` | `POST /api/v1/instructors` | ✅ |
| Update instructor | `PUT /admin/instructors/{id}` | `PUT /api/v1/instructors/{instructor}` | ✅ |
| Delete instructor | `DELETE /admin/instructors/{id}` | `DELETE /api/v1/instructors/{instructor}` | ✅ |

---

## 6. Categories

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List categories | `GET /admin/categories` | `GET /api/v1/categories` | ✅ |
| List active categories | — | `GET /api/v1/categories/active` | ✅ |
| Show category | `GET /admin/categories/{id}` | `GET /api/v1/categories/{category}` | ✅ |
| Create category | `POST /admin/categories` | `POST /api/v1/categories` | ✅ |
| Update category | `PUT /admin/categories/{id}` | `PUT /api/v1/categories/{category}` | ✅ |
| Delete category | `DELETE /admin/categories/{id}` | `DELETE /api/v1/categories/{category}` | ✅ |

---

## 7. Courses

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List courses | `GET /admin/courses` | `GET /api/v1/courses` | ✅ |
| Show course | `GET /admin/courses/{course}` | `GET /api/v1/courses/{course}` | ✅ |
| Create course | `POST /admin/courses` | `POST /api/v1/courses` | ✅ |
| Update course | `PUT /admin/courses/{course}` | `PUT /api/v1/courses/{course}` | ✅ |
| Delete course | `DELETE /admin/courses/{course}` | `DELETE /api/v1/courses/{course}` | ✅ |

---

## 8. Course Sections

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List sections | `GET /admin/courses/{course}/sections` | `GET /api/v1/courses/{course}/sections` | ✅ |
| Create section | `POST /admin/courses/{course}/sections` | `POST /api/v1/courses/{course}/sections` | ✅ |
| Sync sections (bulk replace) | — | `POST /api/v1/courses/{course}/sections/sync` | ✅ |
| Update section | — | `PUT /api/v1/courses/{course}/sections/{section}` | ✅ |
| Delete section | `DELETE /admin/courses/{course}/sections` | `DELETE /api/v1/courses/{course}/sections/{section}` | ✅ |

---

## 9. Course Lectures

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List lectures | `GET /admin/courses/{course}/lectures` | `GET /api/v1/courses/{course}/lectures` | ✅ |
| Create lecture | `POST /admin/courses/{course}/lectures` | `POST /api/v1/courses/{course}/lectures` | ✅ |
| Update lecture | `PUT /admin/courses/{course}/lectures/{id}` | `PUT /api/v1/courses/{course}/lectures/{lecture}` | ✅ |
| Delete lecture | `DELETE /admin/courses/{course}/lectures/{id}` | `DELETE /api/v1/courses/{course}/lectures/{lecture}` | ✅ |

---

## 10. Course Resources (File Attachments)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List resources | `GET /admin/courses/{course}/resources` | ❌ No API endpoint | ❌ |
| Upload resource | `POST /admin/courses/{course}/resources` | ❌ No API endpoint | ❌ |
| Delete all resources | `DELETE /admin/courses/{course}/resources` | ❌ No API endpoint | ❌ |

---

## 11. Course Exams (Admin)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List exams | `GET /admin/courses/{course}/exams` | `GET /api/v1/courses/{course}/exams` | ✅ |
| Show exam | `GET /admin/courses/{course}/exams/{exam}` | `GET /api/v1/courses/{course}/exams/{exam}` | ✅ |
| Create exam | `POST /admin/courses/{course}/exams` | `POST /api/v1/courses/{course}/exams` | ✅ |
| Update exam | `PUT /admin/courses/{course}/exams/{exam}` | `PUT /api/v1/courses/{course}/exams/{exam}` | ✅ |
| Delete exam | `DELETE /admin/courses/{course}/exams/{exam}` | `DELETE /api/v1/courses/{course}/exams/{exam}` | ✅ |

---

## 12. Assignments

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List assignments | `GET /admin/courses/{course}/assignments` | `GET /api/v1/courses/{course}/assignments` | ✅ |
| Create assignment | `POST /admin/courses/{course}/assignments` | `POST /api/v1/courses/{course}/assignments` | ✅ |
| Update assignment | `PUT /admin/courses/{course}/assignments/{id}` | `PUT /api/v1/courses/{course}/assignments/{assignment}` | ✅ |
| Delete assignment | `DELETE /admin/courses/{course}/assignments/{id}` | `DELETE /api/v1/courses/{course}/assignments/{assignment}` | ✅ |
| View all submissions | — | `GET /api/v1/courses/{course}/assignments/{assignment}/submissions` | ✅ |
| Review/grade submission | — | `PUT /api/v1/courses/{course}/assignments/{assignment}/submissions/{submission}/review` | ✅ |
| User: submit assignment | `POST /user/my-assignment/{id}` | `POST /api/v1/courses/{course}/assignments/{assignment}/submit` | ✅ |
| User: view own submission | — | `GET /api/v1/courses/{course}/assignments/{assignment}/my-submission` | ✅ |

---

## 13. User Exam Submission (User-Facing)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Submit exam answers | `POST /course/{course}/exam/{exam}/submit` | `POST /api/v1/courses/{course}/exams/{exam}/submit` | ✅ |
| View exam history | `GET /user/my-exams` | `GET /api/v1/my/exams` | ✅ |
| View exam result with answers | `GET /user/my-exams/answers/{exam}` | `GET /api/v1/my/exams/{id}` | ✅ |
| Admin: delete user exam record | `DELETE /course/user-exam/{id}` | ❌ No API endpoint | ❌ |

---

## 14. Lecture Progress (User-Facing)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Track lecture progress | `POST /course/{course}/lecture/progress` | `POST /api/v1/courses/{course}/lectures/{lecture}/progress` | ✅ |
| View course completion | — | `GET /api/v1/courses/{course}/my-progress` | ✅ |
| Admin: view all progress | `GET /admin/users-courses-progress` | `GET /api/v1/progress` | ✅ |
| Admin: export progress | `GET /admin/users-courses-progress-export` | ❌ No export endpoint | ❌ |

---

## 15. Course Ratings

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| User: submit/update rating | `POST /course/{course}/rating` | `POST /api/v1/courses/{course}/ratings` | ✅ |
| Admin: list ratings for course | `GET /admin/users-courses-ratings` | `GET /api/v1/courses/{course}/ratings` | ✅ |
| Admin: delete rating | `DELETE /course/rating/{id}` | `DELETE /api/v1/courses/{course}/ratings/{rating}` | ✅ |

---

## 16. Lecture Q&A

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| User: ask question | `POST /course/{course}/lecture/{lecture}/question` | `POST /api/v1/courses/{course}/lectures/{lecture}/questions` | ✅ |
| Admin: list questions | `GET /admin/users-lectures-questions` | `GET /api/v1/lecture-questions` | ✅ |
| Admin: answer question | `POST /course/lecture-question/{id}` | `PUT /api/v1/lecture-questions/{question}/answer` | ✅ |
| Admin: delete question | `DELETE /course/lecture-question/{id}` | `DELETE /api/v1/lecture-questions/{question}` | ✅ |
| User: view own questions | `GET /user/my-lectures-questions` | ❌ No dedicated endpoint | ❌ |

---

## 17. Course Evaluations

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Admin: list categories | `GET /admin/evaluation-categories` | `GET /api/v1/evaluation-categories` | ✅ |
| Admin: list all categories | — | `GET /api/v1/evaluation-categories/all` | ✅ |
| Admin: CRUD categories | various | `POST/PUT/DELETE /api/v1/evaluation-categories/…` | ✅ |
| Admin: list evaluation questions | `GET /admin/evaluations` | `GET /api/v1/evaluations` | ✅ |
| Admin: CRUD evaluations | various | `POST/PUT/DELETE /api/v1/evaluations/…` | ✅ |
| User: get evaluation form | `GET /user/course/{course}/evaluation` | `GET /api/v1/courses/{course}/evaluate` | ✅ |
| User: submit evaluation | `POST /user/course/{course}/evaluation` | `POST /api/v1/courses/{course}/evaluate` | ✅ |
| Admin: export per-question | `GET /admin/evaluations-report-per-question` | ❌ No export endpoint | ❌ |
| Admin: export per-category | `GET /admin/evaluations-report-per-category` | ❌ No export endpoint | ❌ |
| Admin: export text answers | `GET /admin/evaluations-report-per-text` | ❌ No export endpoint | ❌ |

---

## 18. Forms & Surveys

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Admin: list forms | `GET /admin/forms` | `GET /api/v1/forms` | ✅ |
| Admin: CRUD forms | various | `POST/PUT/DELETE /api/v1/forms/…` | ✅ |
| Admin: add question | — | `POST /api/v1/forms/{form}/questions` | ✅ |
| Admin: delete question | `DELETE /admin/forms/question/destroy/{id}` | `DELETE /api/v1/forms/{form}/questions/{question}` | ✅ |
| User: start form session | `GET /exam/{form_uuid}` | `GET /api/v1/forms/{formUuid}/start` | ✅ |
| User: submit form answers | `POST /exam/answers/{form_uuid}` | `POST /api/v1/forms/{formUuid}/submit` | ✅ |
| Admin: export form results | `GET /admin/forms/export/{form}` | ❌ No export endpoint | ❌ |
| Admin: export popular questions | `GET /admin/forms/export/questions/{form}` | ❌ No export endpoint | ❌ |
| Admin: export text responses | `GET /admin/forms/export/questions/text/{form}` | ❌ No export endpoint | ❌ |
| Admin: export wrong answers | `GET /admin/forms/export/questions/wrong/{form}` | ❌ No export endpoint | ❌ |

---

## 19. Attendance

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Admin: list attendance | `GET /admin/attendances` | `GET /api/v1/attendance` | ✅ |
| Admin: record / remove attendance | `POST /admin/attendances` | `POST /api/v1/attendance` | ✅ |
| Admin: QR code attendance UI | `GET /admin/attendances/qr` | 🔕 N/A (UI page) | 🔕 |
| Admin: export attendance | `GET /admin/attendances/export/{type}` | ❌ No export endpoint | ❌ |
| Admin: compare attendance dates | `GET /admin/compare-attendance-dates` | ❌ No API endpoint | ❌ |
| Frontend QR attendance form | `GET /2b/attendance` | 🔕 N/A (QR web page) | 🔕 |
| Admin: absences list | `GET /admin/absences` | ❌ No API endpoint | ❌ |
| Admin: export absences | `GET /admin/absences/export` | ❌ No export endpoint | ❌ |
| User: view course attendance | `GET /user/course/{course}/attendance` | ❌ No user attendance view | ❌ |

---

## 20. Online Enrollment

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List online enrollments | `GET /admin/users-courses` | `GET /api/v1/courses/{course}/online-users` | ✅ |
| Attach users to course | `POST /admin/users-courses` | `POST /api/v1/courses/{course}/online-users` | ✅ |
| Sync/update enrollment | `PUT /admin/users-courses/{enrollment}` | `PUT /api/v1/courses/{course}/online-users` | ✅ |
| Detach user from course | — | `DELETE /api/v1/courses/{course}/online-users` | ✅ |

---

## 21. Offline Enrollment (Sessions / Groups)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List sessions | `GET /admin/courses/{course}/sessions` | `GET /api/v1/courses/{course}/sessions` | ✅ |
| Create session | `POST /admin/courses/{course}/sessions` | `POST /api/v1/courses/{course}/sessions` | ✅ |
| Update session | `PUT /admin/courses/{course}/sessions/{id}` | `PUT /api/v1/courses/{course}/sessions/{session}` | ✅ |
| Delete session | `DELETE /admin/courses/{course}/sessions/{id}` | `DELETE /api/v1/courses/{course}/sessions/{session}` | ✅ |
| List offline enrollments | `GET /admin/users-courses-offline` | `GET /api/v1/courses/{course}/enrollments` | ✅ |
| Add offline enrollments | `POST /admin/users-courses-offline` | `POST /api/v1/courses/{course}/enrollments` | ✅ |
| Remove offline enrollment | `DELETE /admin/users-courses-offline/{id}` | `DELETE /api/v1/courses/{course}/enrollments/{enrollment}` | ✅ |
| Get course groups (AJAX) | `GET /admin/course/groups` | 🔕 N/A (select2 only) | 🔕 |

---

## 22. User Dashboard (Self-Service)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Dashboard stats | `GET /user/dashboard` | `GET /api/v1/my/dashboard` | ✅ |
| My enrolled courses | `GET /user/my-courses` | `GET /api/v1/my/courses` | ✅ |
| My exams | `GET /user/my-exams` | `GET /api/v1/my/exams` | ✅ |
| My exam result | `GET /user/my-exams/answers/{exam}` | `GET /api/v1/my/exams/{id}` | ✅ |
| My assignments | `GET /user/my-assignments` | `GET /api/v1/my/assignments` | ✅ |
| My certificates | `GET /user/my-certificates` | `GET /api/v1/my/certificates` | ✅ |
| My course progress | — | `GET /api/v1/my/progress/{courseId}` | ✅ |
| View/download certificate PDF | `GET /user/my-certificates/certificate/{course}` | 🔕 N/A (file download) | 🔕 |
| My ratings | `GET /user/my-ratings` | ❌ No dedicated endpoint | ❌ |
| My lecture questions | `GET /user/my-lectures-questions` | ❌ No dedicated endpoint | ❌ |

---

## 23. Certificates (Admin)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List all certificates | `GET /admin/certificates` | `GET /api/v1/certificates` | ✅ |
| Show certificate | `GET /admin/certificates/{cert}` | ❌ No show endpoint | ❌ |
| Download all certificates | `GET /admin/certificates/download-all` | 🔕 N/A (file download) | 🔕 |

---

## 24. Notifications

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| List notifications | `GET /admin/notifications` | `GET /api/v1/notifications` | ✅ |
| Show notification | `GET /admin/notifications/{id}` | `GET /api/v1/notifications/{notification}` | ✅ |
| Create notification | `POST /admin/notifications` | `POST /api/v1/notifications` | ✅ |
| Update notification | — | ❌ No update endpoint | ❌ |
| Delete notification | — | ❌ No delete endpoint | ❌ |

---

## 25. Settings

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Public settings map | — | `GET /api/v1/settings` | ✅ |
| Admin: full settings list | `GET /admin/settings` | `GET /api/v1/admin/settings` | ✅ |
| Admin: update settings | `POST /admin/settings` | `PUT /api/v1/admin/settings` | ✅ |

---

## 26. CMS Content

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Get about section | `GET /about-us` | `GET /api/v1/about` | ✅ |
| Update about section | — | `POST /api/v1/about` | ✅ |
| List testimonials | `GET /admin/testimonials` | `GET /api/v1/testimonials` | ✅ |
| Active testimonials | — | `GET /api/v1/testimonials/active` | ✅ |
| Show testimonial | `GET /admin/testimonials/{id}` | `GET /api/v1/testimonials/{testimonial}` | ✅ |
| CRUD testimonials | various | `POST/PUT/DELETE /api/v1/testimonials/…` | ✅ |
| List articles | `GET /articles` | `GET /api/v1/articles` | ✅ |
| Show article | `GET /article/{id}/{slug}` | `GET /api/v1/articles/{article}` | ✅ |
| CRUD articles (admin) | various | `POST/PUT/DELETE /api/v1/articles/…` | ✅ |
| Contact form (public) | `POST /store-contact-form` | ❌ No API endpoint | ❌ |
| Admin: list contacts | `GET /admin/contacts` | ❌ No API endpoint | ❌ |
| Admin: delete contact | `DELETE /admin/contacts/{id}` | ❌ No API endpoint | ❌ |
| Facts / stats section | `GET /admin/facts` | ❌ No API endpoint | ❌ |
| Partners logos | `GET /admin/partners` | ❌ No API endpoint | ❌ |
| SEO metadata | `GET /admin/seo` | ❌ No API endpoint | ❌ |
| Blog (separate from articles) | `GET /admin/blogs` | ❌ No API endpoint | ❌ |

---

## 27. Careers (Jobs)

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Admin: list jobs | `GET /admin/careers` | ❌ No API endpoint | ❌ |
| Admin: CRUD jobs | various | ❌ No API endpoints | ❌ |
| Public: list job openings | `GET /careers` | ❌ No API endpoint | ❌ |
| Public: apply to job | `POST /career/{id}/apply` | ❌ No API endpoint | ❌ |
| Admin: view applications | `GET /admin/career-applications` | ❌ No API endpoint | ❌ |

---

## 28. Admin Utilities

| Legacy Feature | Legacy Route | New API Route | Status |
|---|---|---|---|
| Bulk status change | `POST /admin/quickChange` | ❌ No API endpoint | ❌ |
| Bulk delete selected | `POST /admin/deleteSelectedItems` | ❌ No API endpoint | ❌ |
| Sync employees (background job) | `POST /admin/sync-employees-job` | ❌ No API endpoint | ❌ |
| TinyMCE file upload | `POST /admin/upload-tiny-file` | 🔕 N/A (editor upload) | 🔕 |
| List stored videos | `GET /admin/videos/list` | 🔕 N/A (server storage) | 🔕 |
| Admin login audit log | (implicit) | ❌ No API endpoint | ❌ |

---

## Summary

| Status | Count | Notes |
|---|---|---|
| ✅ Covered | ~90 endpoints | Core CRUD + all user self-service flows |
| ⚠️ Partial | 3 areas | Certificates (no show), Notifications (no update/delete), User dashboard (no ratings/questions) |
| ❌ Missing | ~25 endpoints | Listed below |
| 🔕 N/A | ~8 | Web pages, file downloads, AJAX-only UI helpers |

### Missing endpoints (priority order)

#### High Priority — needed for Angular frontend
1. `PUT /api/v1/auth/admin/profile` — admin profile update
2. `GET /api/v1/my/ratings` — user's own course ratings
3. `GET /api/v1/my/lecture-questions` — user's own lecture questions
4. `GET /api/v1/certificates/{certificate}` — show single certificate detail
5. `DELETE /api/v1/notifications/{notification}` — delete notification
6. `PUT /api/v1/notifications/{notification}` — update notification
7. `DELETE /api/v1/my/exams/{exam}` — admin delete user exam record
8. `GET /api/v1/courses/{course}/attendance` — user view own attendance

#### Medium Priority — admin reporting
9. `GET /api/v1/courses/{course}/resources` — list course file resources
10. `POST /api/v1/courses/{course}/resources` — upload course resource
11. `DELETE /api/v1/courses/{course}/resources/{resource}` — delete resource
12. `GET /api/v1/evaluations/export/per-question` — evaluation report export
13. `GET /api/v1/evaluations/export/per-category` — evaluation report export
14. `GET /api/v1/forms/{form}/export` — form results export
15. `GET /api/v1/attendance/export` — attendance export
16. `GET /api/v1/absences` — admin absences list
17. `GET /api/v1/progress/export` — progress export

#### Lower Priority — additional CMS / features
18. `POST /api/v1/contact` — contact form submission
19. `GET /api/v1/contacts` — admin: list contact submissions
20. `DELETE /api/v1/contacts/{contact}` — admin: delete contact
21. `GET /api/v1/facts` / `POST` / `PUT` / `DELETE` — facts/stats CMS
22. `GET /api/v1/partners` / `POST` / `PUT` / `DELETE` — partners CMS
23. `GET /api/v1/seo` / `PUT` — SEO metadata
24. `GET /api/v1/careers` + CRUD — job listings
25. `POST /api/v1/careers/{career}/apply` — job application
26. `POST /api/v1/users/sync` — HR employee sync
