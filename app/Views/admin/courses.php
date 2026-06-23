<?php /* app/Views/admin/courses.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Quản lý môn học</h2>
        <p class="page-sub">Danh sách môn, phân công giảng viên và học kỳ</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-secondary" id="btnAssignLecturer" title="Phân công giảng viên">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Phân công GV
        </button>
        <button class="btn btn-primary" id="btnAddCourse">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Thêm môn học
        </button>
    </div>
</div>

<!-- Courses Table -->
<div class="card">
    <div class="card-header">
        <div class="section-title-group">
            <h3 class="card-title">Danh sách môn học</h3>
            <span class="badge badge-gray"><?= count($courses) ?> môn</span>
        </div>
        <input type="text" id="courseSearch" class="form-control search-bar-input" placeholder="Tìm kiếm mã, tên môn...">
    </div>

    <!-- TABLE WRAPPER -->
    <div class="table-wrapper">
        <table class="data-table" id="coursesTable">
            <thead>
                <tr>
                    <th class="th-fixed">STT</th>
                    <th>Mã môn</th>
                    <th>Tên môn học</th>
                    <th class="text-center">Tín chỉ</th>
                    <th class="text-center">CTĐT</th>
                    <th class="text-center">Giảng viên</th>
                    <th class="text-center">Học kỳ</th>
                    <th class="text-center">SV Đăng ký</th>
                    <th class="text-center th-actions">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr><td colspan="9" class="td-empty">Chưa có môn học nào.</td></tr>
                <?php else: foreach ($courses as $i => $course): ?>
                    <?php
                        $lecturerName = $course['lecturer_name'] ?? '—';
                        $studentCount = $course['student_count'] ?? 0;
                        $semester = $course['semester'] ?? null;
                    ?>
                    <tr class="data-row">
                        <td class="td-stt"><?= $i + 1 ?></td>
                        <td class="td-code"><code><?= htmlspecialchars($course['code']) ?></code></td>
                        <td class="td-name">
                            <span class="course-name"><?= htmlspecialchars($course['name']) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-credits"><?= (int)$course['credits'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-program"><?= htmlspecialchars($course['program_code'] ?? '—') ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $lecturerName !== '—' ? 'badge-lecturer' : 'badge-empty' ?>">
                                <?= htmlspecialchars($lecturerName) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $semester ? 'badge-semester' : 'badge-empty' ?>"><?= $semester ? htmlspecialchars($semester) : '—' ?></span>
                        </td>
                        <td class="text-center">
                            <button class="badge badge-students btn-enroll-students"
                                data-id="<?= (int)$course['id'] ?>"
                                data-name="<?= htmlspecialchars($course['name']) ?>"
                                data-code="<?= htmlspecialchars($course['code']) ?>"
                                title="Quản lý danh sách sinh viên">
                                <?= $studentCount ?>
                            </button>
                        </td>
                        <td class="td-actions">
                            <div class="action-group">
                                <a href="/admin/course/<?= (int)$course['id'] ?>/matrix" class="action-btn action-btn--matrix" title="Ma trận CLO-PLO">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                </a>
                                <button class="action-btn action-btn--edit btn-edit-course"
                                    data-id="<?= (int)$course['id'] ?>"
                                    data-code="<?= htmlspecialchars($course['code']) ?>"
                                    data-name="<?= htmlspecialchars($course['name']) ?>"
                                    data-credits="<?= (int)$course['credits'] ?>"
                                    data-program-id="<?= (int)$course['program_id'] ?>"
                                    data-program-name="<?= htmlspecialchars($course['program_name'] ?? '') ?>"
                                    data-description="<?= htmlspecialchars($course['description'] ?? '') ?>"
                                    title="Sửa môn học">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="action-btn action-btn--danger btn-delete-course"
                                    data-id="<?= (int)$course['id'] ?>"
                                    data-code="<?= htmlspecialchars($course['code']) ?>"
                                    title="Xóa môn học">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD / EDIT COURSE MODAL -->
<div class="modal-overlay" id="courseModal">
    <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
            <h3 class="modal-title" id="courseModalTitle">Thêm môn học mới</h3>
            <button class="modal-close-btn" id="courseModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form class="modal-form" id="courseForm">
            <input type="hidden" name="id" id="course-id">
            <div class="form-row">
                <div class="form-group">
                    <label for="course-code">Mã môn học <span class="required">*</span></label>
                    <input type="text" id="course-code" name="code" class="form-input" placeholder="Ví dụ: CS101" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="course-credits">Số tín chỉ <span class="required">*</span></label>
                    <input type="number" id="course-credits" name="credits" class="form-input" placeholder="3" min="1" max="20" required>
                </div>
            </div>
            <div class="form-group">
                <label for="course-name">Tên môn học <span class="required">*</span></label>
                <input type="text" id="course-name" name="name" class="form-input" placeholder="Tên đầy đủ của môn học" required>
            </div>
            <div class="form-group">
                <label for="course-program">Chương trình đào tạo <span class="required">*</span></label>
                <select id="course-program" name="program_id" class="form-input" required>
                    <option value="">-- Chọn CTĐT --</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['code']) ?> — <?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="course-description">Mô tả</label>
                <textarea id="course-description" name="description" class="form-input" rows="3" placeholder="Mô tả ngắn về nội dung môn học..."></textarea>
            </div>
            <div class="field-error-box" id="course-error-box"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="courseCancelBtn">Hủy</button>
                <button type="submit" class="btn btn-primary" id="courseSubmitBtn">
                    <span class="btn-label">Lưu môn học</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ASSIGN LECTURER MODAL -->
<div class="modal-overlay" id="lecturerModal">
    <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
            <h3 class="modal-title">Phân công giảng viên</h3>
            <button class="modal-close-btn" id="lecturerModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form class="modal-form" id="lecturerForm">
            <div class="form-group">
                <label for="lecturer-course">Môn học <span class="required">*</span></label>
                <select id="lecturer-course" name="course_id" class="form-input" required>
                    <option value="">-- Chọn môn học --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['code']) ?> — <?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="lecturer-user">Giảng viên <span class="required">*</span></label>
                <select id="lecturer-user" name="lecturer_id" class="form-input" required>
                    <option value="">-- Chọn giảng viên --</option>
                    <?php foreach ($lecturers as $l): ?>
                        <option value="<?= (int)$l['id'] ?>"><?= htmlspecialchars($l['full_name']) ?> (<?= htmlspecialchars($l['email'] ?? '') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="lecturer-semester">Học kỳ <span class="required">*</span></label>
                <input type="text" id="lecturer-semester" name="semester" class="form-input" placeholder="Ví dụ: 2024-1" required>
            </div>
            <div class="field-error-box" id="lecturer-error-box"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="lecturerCancelBtn">Hủy</button>
                <button type="submit" class="btn btn-primary" id="lecturerSubmitBtn">
                    <span class="btn-label">Phân công</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ENROLL STUDENTS MODAL -->
<div class="modal-overlay" id="enrollModal">
    <div class="modal-card modal-lg" role="dialog" aria-modal="true">
        <div class="modal-header">
            <h3 class="modal-title" id="enrollModalTitle">Danh sách Đăng ký môn học</h3>
            <button class="modal-close-btn" id="enrollModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body-enroll">
            <!-- Enroll form -->
            <div class="enroll-form-section">
                <p class="enroll-form-label">Đăng ký sinh viên mới</p>
                <div class="enroll-form-row">
                    <select id="enroll-student-select" class="form-input">
                        <option value="">-- Chọn sinh viên --</option>
                    </select>
                    <button type="button" class="btn btn-primary" id="btnEnrollStudent">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Đăng ký
                    </button>
                </div>
            </div>
            <!-- Current students table -->
            <div class="enrolled-students-section">
                <p class="enrolled-label">Sinh viên đã đăng ký <span class="badge badge-students" id="enrolledCount">0</span></p>
                <div class="table-wrapper" style="margin-top:12px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="th-fixed">STT</th>
                                <th>Mã SV</th>
                                <th>Họ và tên</th>
                                <th class="text-center th-actions">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="enrolledStudentsBody">
                            <tr><td colspan="4" class="td-empty">Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal-overlay" id="deleteCourseModal">
    <div class="modal-card modal-sm" role="dialog" aria-modal="true">
        <div class="modal-header">
            <h3 class="modal-title">Xác nhận xóa môn học</h3>
        </div>
        <div class="modal-body-delete">
            <p>Bạn có chắc muốn xóa môn <strong id="delete-course-code"></strong>?<br>Hành động này không thể hoàn tác.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="deleteCourseCancelBtn">Hủy</button>
            <button class="btn btn-danger" id="deleteCourseConfirmBtn">Xóa</button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Helpers ─────────────────────────────────────────────────────
    function $(id) { return document.getElementById(id); }
    function qsa(sel, ctx) { return [...(ctx || document).querySelectorAll(sel)]; }
    function openModal(el) { el?.classList.add('open'); }
    function closeModal(el) { el?.classList.remove('open'); }

    function setCourseLoading(btn, label, on) {
        btn.disabled = on;
        qsa('.btn-label', btn).forEach(el => el.textContent = on ? 'Đang xử lý...' : label);
    }

    // ── Client-side search ───────────────────────────────────────────
    $('courseSearch')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        qsa('.data-row').forEach(row => {
            const text = [
                row.querySelector('.td-code code')?.textContent || '',
                row.querySelector('.course-name')?.textContent || '',
            ].join(' ').toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // ── Course Add / Edit Modal ─────────────────────────────────────
    const courseModal = $('courseModal');
    const courseForm  = $('courseForm');

    function openCourseModal(id, data) {
        $('courseModalTitle').textContent = id ? 'Sửa môn học' : 'Thêm môn học mới';
        $('courseSubmitBtn').querySelector('.btn-label').textContent = id ? 'Lưu thay đổi' : 'Lưu môn học';
        $('course-error-box').textContent = '';
        $('course-error-box').style.display = 'none';

        if (id && data) {
            $('course-id').value = id;
            $('course-code').value = data.code ?? '';
            $('course-name').value = data.name ?? '';
            $('course-credits').value = data.credits ?? '';
            $('course-program').value = data.programId ?? '';
            $('course-description').value = data.description ?? '';
        } else {
            courseForm.reset();
            $('course-id').value = '';
        }
        openModal(courseModal);
        $('course-code').focus();
    }

    function closeCourseModal() { closeModal(courseModal); courseForm.reset(); }

    $('btnAddCourse')?.addEventListener('click', () => openCourseModal(null));
    $('courseModalClose')?.addEventListener('click', closeCourseModal);
    $('courseCancelBtn')?.addEventListener('click', closeCourseModal);
    courseModal?.addEventListener('click', e => { if (e.target === courseModal) closeCourseModal(); });

    qsa('.btn-edit-course').forEach(btn => {
        btn.addEventListener('click', () => {
            openCourseModal(+btn.dataset.id, {
                code: btn.dataset.code,
                name: btn.dataset.name,
                credits: btn.dataset.credits,
                programId: btn.dataset.programId,
                description: btn.dataset.description,
            });
        });
    });

    courseForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = $('courseSubmitBtn');
        const isEdit = !!$('course-id').value;
        setCourseLoading(btn, isEdit ? 'Lưu thay đổi' : 'Lưu môn học', true);
        $('course-error-box').style.display = 'none';

        const payload = {
            code: $('course-code').value.trim(),
            name: $('course-name').value.trim(),
            credits: parseInt($('course-credits').value),
            program_id: parseInt($('course-program').value),
            description: $('course-description').value.trim(),
        };

        if (!payload.code || !payload.name || !payload.credits || !payload.program_id) {
            $('course-error-box').textContent = 'Vui lòng điền đầy đủ các trường bắt buộc.';
            $('course-error-box').style.display = 'block';
            setCourseLoading(btn, isEdit ? 'Lưu thay đổi' : 'Lưu môn học', false);
            return;
        }

        const url  = isEdit ? `/admin/course/${$('course-id').value}/update` : '/admin/courses';
        const meth = isEdit ? 'POST' : 'POST';

        try {
            const res  = await fetch(url, {
                method: meth,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                window.Toast?.success(isEdit ? 'Cập nhật môn học thành công!' : 'Thêm môn học thành công!');
                closeCourseModal();
                setTimeout(() => location.reload(), 700);
            } else {
                $('course-error-box').textContent = data.error || 'Đã xảy ra lỗi.';
                $('course-error-box').style.display = 'block';
            }
        } catch {
            $('course-error-box').textContent = 'Đã xảy ra lỗi kết nối.';
            $('course-error-box').style.display = 'block';
        }
        setCourseLoading(btn, isEdit ? 'Lưu thay đổi' : 'Lưu môn học', false);
    });

    // ── Delete Course ───────────────────────────────────────────────
    const deleteCourseModal = $('deleteCourseModal');
    let deleteCourseId = null;

    qsa('.btn-delete-course').forEach(btn => {
        btn.addEventListener('click', () => {
            deleteCourseId = +btn.dataset.id;
            $('delete-course-code').textContent = btn.dataset.code;
            openModal(deleteCourseModal);
        });
    });
    $('deleteCourseCancelBtn')?.addEventListener('click', () => closeModal(deleteCourseModal));
    deleteCourseModal?.addEventListener('click', e => { if (e.target === deleteCourseModal) closeModal(deleteCourseModal); });
    $('deleteCourseConfirmBtn')?.addEventListener('click', async () => {
        const btn = $('deleteCourseConfirmBtn');
        btn.disabled = true; btn.textContent = 'Đang xóa...';
        try {
            const res  = await fetch(`/admin/course/${deleteCourseId}/delete`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                window.Toast?.success('Đã xóa môn học!');
                closeModal(deleteCourseModal);
                setTimeout(() => location.reload(), 600);
            } else {
                window.Toast?.error(data.error || 'Xóa thất bại.');
                btn.disabled = false; btn.textContent = 'Xóa';
            }
        } catch {
            window.Toast?.error('Đã xảy ra lỗi kết nối.');
            btn.disabled = false; btn.textContent = 'Xóa';
        }
    });

    // ── Assign Lecturer Modal ───────────────────────────────────────
    const lecturerModal = $('lecturerModal');
    $('btnAssignLecturer')?.addEventListener('click', () => {
        $('lecturer-error-box').style.display = 'none';
        $('lecturerForm').reset();
        openModal(lecturerModal);
    });
    $('lecturerModalClose')?.addEventListener('click', () => closeModal(lecturerModal));
    $('lecturerCancelBtn')?.addEventListener('click', () => closeModal(lecturerModal));
    lecturerModal?.addEventListener('click', e => { if (e.target === lecturerModal) closeModal(lecturerModal); });

    $('lecturerForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = $('lecturerSubmitBtn');
        btn.disabled = true; btn.querySelector('.btn-label').textContent = 'Đang xử lý...';
        $('lecturer-error-box').style.display = 'none';

        const payload = {
            course_id: parseInt($('lecturer-course').value),
            lecturer_id: parseInt($('lecturer-user').value),
            semester: $('lecturer-semester').value.trim(),
        };

        try {
            const res  = await fetch('/admin/courses/assign-lecturer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                window.Toast?.success('Phân công giảng viên thành công!');
                closeModal(lecturerModal);
                setTimeout(() => location.reload(), 700);
            } else {
                $('lecturer-error-box').textContent = data.error || 'Đã xảy ra lỗi.';
                $('lecturer-error-box').style.display = 'block';
            }
        } catch {
            $('lecturer-error-box').textContent = 'Đã xảy ra lỗi kết nối.';
            $('lecturer-error-box').style.display = 'block';
        }
        btn.disabled = false; btn.querySelector('.btn-label').textContent = 'Phân công';
    });

    // ── Enroll Students Modal ────────────────────────────────────────
    const enrollModal = $('enrollModal');
    let enrollCourseId = null;
    let enrollCourseName = '';

    async function loadEnrolledStudents(courseId) {
        const tbody = $('enrolledStudentsBody');
        tbody.innerHTML = '<tr><td colspan="4" class="td-empty">Đang tải...</td></tr>';
        try {
            const res  = await fetch(`/admin/courses/${courseId}/enrolled`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrf },
            });
            const data = await res.json();
            $('enrolledCount').textContent = data.students?.length ?? 0;

            if (!data.students?.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="td-empty">Chưa có sinh viên nào đăng ký.</td></tr>';
                return;
            }
            tbody.innerHTML = data.students.map((s, i) => `
                <tr>
                    <td class="td-stt">${i + 1}</td>
                    <td class="td-code"><code>${s.student_code || s.code || '—'}</code></td>
                    <td>${s.full_name}</td>
                    <td class="td-actions">
                        <button class="action-btn action-btn--danger btn-unenroll"
                            data-course-id="${courseId}" data-user-id="${s.user_id || s.id}"
                            title="Hủy đăng ký">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                    </td>
                </tr>
            `).join('');

            qsa('.btn-unenroll').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const uid = btn.dataset.userId;
                    const cid = btn.dataset.courseId;
                    btn.disabled = true;
                    try {
                        const res = await fetch('/admin/courses/unenroll-student', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ course_id: parseInt(cid), user_id: parseInt(uid) }),
                        });
                        const d = await res.json();
                        if (res.ok && d.status === 'success') {
                            window.Toast?.success('Đã hủy đăng ký.');
                            loadEnrolledStudents(parseInt(cid));
                            setTimeout(() => location.reload(), 800);
                        } else {
                            window.Toast?.error(d.error || 'Hủy đăng ký thất bại.');
                            btn.disabled = false;
                        }
                    } catch {
                        window.Toast?.error('Đã xảy ra lỗi kết nối.');
                        btn.disabled = false;
                    }
                });
            });
        } catch {
            tbody.innerHTML = '<tr><td colspan="4" class="td-empty">Không thể tải danh sách.</td></tr>';
        }
    }

    async function loadAvailableStudents(courseId) {
        const sel = $('enroll-student-select');
        sel.innerHTML = '<option value="">-- Đang tải... --</option>';
        try {
            const res  = await fetch(`/admin/courses/${courseId}/available-students`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrf },
            });
            const data = await res.json();
            const students = data.students ?? [];
            if (!students.length) {
                sel.innerHTML = '<option value="">Không có sinh viên khả dụng</option>';
                return;
            }
            sel.innerHTML = '<option value="">-- Chọn sinh viên --</option>' +
                students.map(s => `<option value="${s.id}">${s.student_code || s.code || ''} — ${s.full_name}</option>`).join('');
        } catch {
            sel.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        }
    }

    qsa('.btn-enroll-students').forEach(btn => {
        btn.addEventListener('click', () => {
            enrollCourseId = +btn.dataset.id;
            enrollCourseName = btn.dataset.name;
            $('enrollModalTitle').textContent = `Danh sách Đăng ký môn học — ${enrollCourseName}`;
            openModal(enrollModal);
            loadEnrolledStudents(enrollCourseId);
            loadAvailableStudents(enrollCourseId);
        });
    });

    $('enrollModalClose')?.addEventListener('click', () => closeModal(enrollModal));
    enrollModal?.addEventListener('click', e => { if (e.target === enrollModal) closeModal(enrollModal); });

    $('btnEnrollStudent')?.addEventListener('click', async () => {
        const sel = $('enroll-student-select');
        const userId = parseInt(sel.value);
        if (!userId || !enrollCourseId) {
            window.Toast?.error('Vui lòng chọn sinh viên.');
            return;
        }
        const btn = $('btnEnrollStudent');
        btn.disabled = true; btn.textContent = 'Đang đăng ký...';
        try {
            const res  = await fetch('/admin/courses/enroll-student', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ course_id: enrollCourseId, user_id: userId }),
            });
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                window.Toast?.success('Đăng ký thành công!');
                loadEnrolledStudents(enrollCourseId);
                loadAvailableStudents(enrollCourseId);
                setTimeout(() => location.reload(), 800);
            } else {
                window.Toast?.error(data.error || 'Đăng ký thất bại.');
            }
        } catch {
            window.Toast?.error('Đã xảy ra lỗi kết nối.');
        }
        btn.disabled = false; btn.textContent = 'Đăng ký';
    });

    // Keyboard: Escape closes modals
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        [courseModal, lecturerModal, enrollModal, deleteCourseModal].forEach(m => closeModal(m));
    });
})();
</script>

<style>
/* ── TABLE ── */
.table-wrapper { overflow-x: auto; border-radius: 14px; border: 1px solid #e2e8f0; background: #fff; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.data-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.data-table thead th.th-fixed { width: 50px; }
.data-table thead th.th-actions { width: 120px; }
.data-table thead th.text-center { text-align: center; }
.data-table tbody td {
    padding: 13px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr.data-row:hover td { background: #fafbfc; }
.td-stt { color: #94a3b8; font-size: 13px; }
.td-code code { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 600; color: #0f172a; background: #f1f5f9; padding: 3px 8px; border-radius: 6px; }
.td-name .course-name { font-weight: 500; color: #1e293b; }
.td-actions { text-align: center; }
.td-empty { text-align: center; color: #94a3b8; padding: 40px !important; font-style: italic; }
.action-group { display: flex; gap: 6px; justify-content: center; align-items: center; }

/* ── BADGES (identical sizing) ── */
.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 26px;
    padding: 0 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    box-sizing: border-box;
}
.badge-credits  { background: #ede9fe; color: #6d28d9; }
.badge-program  { background: #dbeafe; color: #1d4ed8; }
.badge-lecturer { background: #d1fae5; color: #065f46; }
.badge-semester { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.badge-empty    { background: #f1f5f9; color: #94a3b8; }
.badge-students  { background: #f3e8ff; color: #7c3aed; cursor: pointer; border: none; font-weight: 700; }
.badge-students:hover { background: #e9d5ff; box-shadow: 0 2px 6px rgba(124,58,237,0.2); }
.badge-gray { background: #f1f5f9; color: #64748b; }

/* ── ENROLL MODAL ── */
.modal-lg { max-width: 620px; }
.modal-body-enroll { padding: 20px 24px; display: flex; flex-direction: column; gap: 20px; }
.enroll-form-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
.enroll-form-label { font-size: 13px; font-weight: 600; color: #475569; margin: 0 0 10px; }
.enroll-form-row { display: flex; gap: 10px; align-items: center; }
.enroll-form-row select { flex: 1; }
.enrolled-label { font-size: 13px; font-weight: 600; color: #475569; margin: 0; display: flex; align-items: center; gap: 8px; }
.field-error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #dc2626; display: none; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* ── PAGE HEADER ── */
.page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; margin-bottom: 20px;
}
.page-heading {
    font-family: 'Lexend Deca', sans-serif;
    font-size: 20px; font-weight: 700;
    color: #0f172a; margin-bottom: 2px;
}
.page-sub { font-size: 13px; color: #94a3b8; }
.header-actions { display: flex; gap: 8px; flex-shrink: 0; }

/* ── CARD ── */
.card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #f1f5f9; }
.section-title-group { display: flex; align-items: center; gap: 10px; }
.card-title { font-size: 15px; font-weight: 600; color: #0f172a; }
.search-bar-input { width: 260px; padding-left: 36px; }

/* ── ACTION BUTTONS ── */
.action-btn {
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    background: none; border: 1px solid #e2e8f0;
    border-radius: 8px; color: #94a3b8;
    cursor: pointer; transition: all 0.2s ease; text-decoration: none;
}
.action-btn svg { width: 14px; height: 14px; }
.action-btn--edit:hover  { border-color: #4f46e5; color: #4f46e5; background: rgba(79,70,229,0.08); }
.action-btn--danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.06); }
.action-btn--matrix { color: #0ea5e9; }
.action-btn--matrix:hover { border-color: #0ea5e9; background: rgba(14,165,233,0.06); }
.action-btn--danger { color: #ef4444; }

/* ── MODAL OVERLAY & CARD ── */
.modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(15,23,42,0.5);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity 0.2s ease;
    backdrop-filter: blur(2px);
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal-card {
    background: #fff; border-radius: 16px;
    width: 100%; max-width: 520px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(15,23,42,0.15);
    transform: translateY(20px);
    transition: transform 0.2s ease;
}
.modal-overlay.open .modal-card { transform: translateY(0); }
.modal-card.modal-lg { max-width: 620px; }
.modal-card.modal-sm { max-width: 400px; }
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px; border-bottom: 1px solid #f1f5f9;
}
.modal-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
.modal-close-btn {
    background: none; border: none; cursor: pointer;
    color: #94a3b8; display: flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 8px;
    transition: all 0.15s;
}
.modal-close-btn:hover { background: #f1f5f9; color: #0f172a; }

/* ── MODAL FORM ── */
.modal-form { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 13px; font-weight: 600; color: #374151; }
.form-input {
    width: 100%; padding: 9px 12px;
    border: 1px solid #e2e8f0; border-radius: 8px;
    font-size: 14px; color: #0f172a;
    background: #fff; box-sizing: border-box;
    transition: border-color 0.15s;
}
.form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.form-input.input--error { border-color: #ef4444; }
.modal-footer {
    display: flex; gap: 10px; justify-content: flex-end;
    padding: 16px 20px; border-top: 1px solid #f1f5f9;
}

/* ── BUTTONS ── */
.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 8px 16px; border-radius: 8px; border: none;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all 0.15s; text-decoration: none;
}
.btn-primary { background: #4f46e5; color: #fff; }
.btn-primary:hover { background: #4338ca; }
.btn-primary:disabled { background: #a5b4fc; cursor: not-allowed; }
.btn-secondary { background: #f1f5f9; color: #475569; }
.btn-secondary:hover { background: #e2e8f0; }
.btn-danger { background: #ef4444; color: #fff; }
.btn-danger:hover { background: #dc2626; }
.btn-danger:disabled { background: #fca5a5; cursor: not-allowed; }

/* ── DELETE MODAL ── */
.modal-body-delete { padding: 20px; text-align: center; font-size: 14px; color: #475569; }
.modal-body-delete strong { color: #0f172a; }

/* ── REQUIRED ── */
.required { color: #ef4444; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .header-actions { flex-wrap: wrap; }
    .search-bar-input { width: 100%; }
    .data-table th:nth-child(5),
    .data-table td:nth-child(5),
    .data-table th:nth-child(6),
    .data-table td:nth-child(6) { display: none; }
    .form-row { grid-template-columns: 1fr; }
}
</style>
