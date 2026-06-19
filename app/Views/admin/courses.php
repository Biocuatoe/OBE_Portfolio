<?php /* app/Views/admin/courses.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Quản lý môn học</h2>
        <p class="page-sub">Danh sách môn, phân công giảng viên và học kỳ</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-ghost" id="btnQuickAssign" title="Phân công giảng viên nhanh">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Phân công GV
        </button>
        <button class="btn btn-primary" id="btnCreateCourse">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Thêm môn học
        </button>
    </div>
</div>

<!-- Courses Table -->
<div class="section-card">
    <div class="section-header">
        <div class="section-title-group">
            <h3 class="section-title">Danh sách môn học</h3>
            <span class="section-badge"><?= count($courses) ?> môn</span>
        </div>
        <input type="text" id="courseSearch" class="search-input" placeholder="Tìm kiếm mã, tên môn...">
    </div>

    <div class="table-wrapper">
        <table class="data-table" id="coursesTable">
            <thead>
                <tr>
                    <th>Mã môn</th>
                    <th>Tên môn học</th>
                    <th>Chương trình</th>
                    <th class="text-center">Tín chỉ</th>
                    <th class="text-center">Phân công</th>
                    <th class="text-center">SV đăng ký</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                <p>Chưa có môn học nào.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courses as $c): ?>
                        <tr class="course-row"
                            data-id="<?= $c['id'] ?>"
                            data-code="<?= htmlspecialchars($c['code']) ?>"
                            data-name="<?= htmlspecialchars($c['name']) ?>"
                            data-program-id="<?= $c['program_id'] ?>"
                            data-credits="<?= $c['credits'] ?>"
                            data-description="<?= htmlspecialchars($c['description'] ?? '') ?>">
                            <td><span class="course-badge"><?= htmlspecialchars($c['code']) ?></span></td>
                            <td>
                                <div class="cell-primary"><?= htmlspecialchars($c['name']) ?></div>
                                <?php if (!empty($c['description'])): ?>
                                    <div class="cell-sub"><?= htmlspecialchars(mb_substr($c['description'], 0, 70)) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="cell-primary"><?= htmlspecialchars($c['program_name']) ?></div>
                                <div class="cell-sub text-accent"><?= htmlspecialchars($c['program_code']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="credit-pill"><?= $c['credits'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="stat-pill stat-pill--green"><?= $c['assignment_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="stat-pill stat-pill--primary"><?= $c['student_count'] ?></span>
                            </td>
                            <td class="text-right">
                                <div class="row-actions">
                                    <a href="/admin/course/<?= $c['id'] ?>/matrix" class="action-btn action-btn--blue" title="Ma trận CLO-PLO">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                    </a>
                                    <button class="action-btn action-btn--edit" title="Sửa" data-id="<?= $c['id'] ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="action-btn action-btn--danger" title="Xóa" data-id="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Create / Edit Course Modal ──────────────────────────────────── -->
<div class="modal-overlay" id="courseModal" role="dialog" aria-modal="true">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="courseModalTitle">Thêm môn học</h3>
            <button class="modal-close" id="courseModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="courseForm" novalidate>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="course-program">Chương trình đào tạo <span class="required">*</span></label>
                    <select id="course-program" name="program_id" class="form-select">
                        <option value="">— Chọn chương trình —</option>
                        <?php foreach ($programs as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['code']) ?> — <?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="err-program_id"></span>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label" for="course-code">Mã môn <span class="required">*</span></label>
                        <input type="text" id="course-code" name="code" class="form-input" placeholder="VD: ITEC2201" maxlength="20">
                        <span class="field-error" id="err-code"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="course-credits">Tín chỉ <span class="required">*</span></label>
                        <input type="number" id="course-credits" name="credits" class="form-input" min="1" max="10" value="3" placeholder="1–10">
                        <span class="field-error" id="err-credits"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="course-name">Tên môn học <span class="required">*</span></label>
                    <input type="text" id="course-name" name="name" class="form-input" placeholder="VD: Lập trình hướng đối tượng" maxlength="200">
                    <span class="field-error" id="err-name"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="course-desc">Mô tả</label>
                    <textarea id="course-desc" name="description" class="form-input form-textarea" placeholder="Mô tả nội dung, phương pháp giảng dạy..." rows="3" maxlength="1000"></textarea>
                    <span class="field-error"></span>
                </div>

                <!-- Quick assignment section (shown when creating) -->
                <div class="quick-assign-section" id="quickAssignSection" style="display:none">
                    <div class="assign-divider">
                        <span>Phân công giảng viên (tùy chọn)</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="course-lecturer">Giảng viên</label>
                        <select id="course-lecturer" name="lecturer_id" class="form-select">
                            <option value="">— Chọn giảng viên —</option>
                            <?php foreach ($lecturers as $l): ?>
                                <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="course-semester">Học kỳ</label>
                        <input type="text" id="course-semester" name="semester" class="form-input" placeholder="VD: HK 2024-1">
                        <span class="field-error" id="err-semester"></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" id="courseModalCancel">Hủy</button>
                <button type="submit" class="btn btn-primary" id="courseModalSubmit">
                    <span class="btn-label">Tạo mới</span>
                    <span class="btn-spinner" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="spin"><circle cx="12" cy="12" r="10" stroke-dasharray="30 70"/></svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Quick Assignment Modal ────────────────────────────────────── -->
<div class="modal-overlay" id="assignModal" role="dialog" aria-modal="true">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3 class="modal-title">Phân công giảng viên</h3>
            <button class="modal-close" id="assignModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="assignForm" novalidate>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="assign-course">Môn học <span class="required">*</span></label>
                    <select id="assign-course" name="course_id" class="form-select">
                        <option value="">— Chọn môn học —</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code']) ?> — <?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="err-course_id"></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="assign-lecturer">Giảng viên <span class="required">*</span></label>
                    <select id="assign-lecturer" name="lecturer_id" class="form-select">
                        <option value="">— Chọn giảng viên —</option>
                        <?php foreach ($lecturers as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="err-lecturer_id"></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="assign-semester">Học kỳ <span class="required">*</span></label>
                    <input type="text" id="assign-semester" name="semester" class="form-input" placeholder="VD: HK 2024-1">
                    <span class="field-error" id="err-semester-assign"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" id="assignModalCancel">Hủy</button>
                <button type="submit" class="btn btn-primary" id="assignModalSubmit">
                    <span class="btn-label">Phân công</span>
                    <span class="btn-spinner" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="spin"><circle cx="12" cy="12" r="10" stroke-dasharray="30 70"/></svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Delete Confirm Modal ──────────────────────────────────────── -->
<div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3 class="modal-title">Xác nhận xóa</h3>
            <button class="modal-close" id="deleteModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <p class="confirm-text">Xóa môn học <strong id="deleteCourseName"></strong>?</p>
            <p class="confirm-sub">Hành động này không thể hoàn tác.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" id="deleteCancel">Hủy</button>
            <button class="btn btn-danger" id="deleteConfirm">
                <span class="btn-label">Xóa</span>
                <span class="btn-spinner" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="spin"><circle cx="12" cy="12" r="10" stroke-dasharray="30 70"/></svg>
                </span>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── API helper ────────────────────────────────────────────────────
    function api(url, method, body) {
        return fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ ...body, _token: CSRF }),
        }).then(r => r.json()).then(d => { if (!r.ok) throw d; return d; });
    }

    function setFieldError(id, msg) {
        const el = document.getElementById('err-' + id);
        if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
        const input = document.getElementById('course-' + id) || document.getElementById(id);
        if (input) input.classList.toggle('input--error', !!msg);
    }

    function clearErrors(formId) {
        document.querySelectorAll('#' + formId + ' .field-error').forEach(el => { el.textContent = ''; });
        document.querySelectorAll('#' + formId + ' .input--error').forEach(el => el.classList.remove('input--error'));
    }

    function setLoading(btn, on) {
        btn.disabled = on;
        btn.querySelector('.btn-spinner').hidden = !on;
    }

    // ── Client-side search ─────────────────────────────────────────────
    document.getElementById('courseSearch')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.course-row').forEach(row => {
            const text = [row.dataset.code, row.dataset.name, row.querySelector('.cell-primary')?.textContent]
                .join(' ').toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // ── Course Modal ─────────────────────────────────────────────────
    const courseModal  = document.getElementById('courseModal');
    const courseForm   = document.getElementById('courseForm');
    const modalTitle   = document.getElementById('courseModalTitle');
    const submitBtn    = document.getElementById('courseModalSubmit');
    const assignSection = document.getElementById('quickAssignSection');
    let editId = null;

    function openCourseModal(id, row) {
        editId = id;
        courseForm.reset();
        clearErrors('courseForm');

        if (id && row) {
            modalTitle.textContent = 'Sửa môn học';
            submitBtn.querySelector('.btn-label').textContent = 'Lưu thay đổi';
            assignSection.style.display = 'none';

            document.getElementById('course-program').value  = row.dataset.programId || '';
            document.getElementById('course-code').value     = row.dataset.code || '';
            document.getElementById('course-credits').value  = row.dataset.credits || '3';
            document.getElementById('course-name').value    = row.dataset.name || '';
            document.getElementById('course-desc').value     = row.dataset.description || '';
        } else {
            modalTitle.textContent = 'Thêm môn học';
            submitBtn.querySelector('.btn-label').textContent = 'Tạo mới';
            assignSection.style.display = '';
        }

        courseModal.classList.add('open');
        document.getElementById('course-program').focus();
    }

    function closeCourseModal() {
        courseModal.classList.remove('open');
        editId = null;
    }

    document.getElementById('btnCreateCourse')?.addEventListener('click', () => openCourseModal(null));
    document.getElementById('courseModalClose')?.addEventListener('click', closeCourseModal);
    document.getElementById('courseModalCancel')?.addEventListener('click', closeCourseModal);
    courseModal?.addEventListener('click', e => { if (e.target === courseModal) closeCourseModal(); });

    document.querySelectorAll('.action-btn--edit').forEach(btn => {
        btn.addEventListener('click', () => openCourseModal(+btn.dataset.id, btn.closest('.course-row')));
    });

    courseForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors('courseForm');
        setLoading(submitBtn, true);

        const payload = {
            program_id:  document.getElementById('course-program').value,
            code:        document.getElementById('course-code').value.trim(),
            name:        document.getElementById('course-name').value.trim(),
            credits:     document.getElementById('course-credits').value,
            description: document.getElementById('course-desc').value.trim(),
        };

        // Quick assignment (only on create)
        if (!editId) {
            payload.lecturer_id = document.getElementById('course-lecturer').value;
            payload.semester    = document.getElementById('course-semester').value.trim();
        }

        try {
            const url  = editId ? `/admin/course/${editId}/update` : '/admin/course/store';
            const data = await api(url, 'POST', payload);

            if (data.error && data.fields) {
                Object.entries(data.fields).forEach(([k, v]) => setFieldError(k, v));
                if (data.error !== 'Validation failed') window.Toast?.error(data.error);
            } else if (data.error) {
                window.Toast?.error(data.error);
            } else {
                window.Toast?.success(editId ? 'Đã cập nhật môn học.' : 'Đã tạo môn học mới.');
                closeCourseModal();
                window.location.reload();
            }
        } catch(err) {
            if (err.error && err.fields) {
                Object.entries(err.fields).forEach(([k, v]) => setFieldError(k, v));
            } else {
                window.Toast?.error(err.error || 'Đã xảy ra lỗi.');
            }
        } finally {
            setLoading(submitBtn, false);
        }
    });

    // ── Quick Assign Modal ────────────────────────────────────────────
    const assignModal = document.getElementById('assignModal');
    const assignForm  = document.getElementById('assignForm');
    const assignBtn   = document.getElementById('assignModalSubmit');

    document.getElementById('btnQuickAssign')?.addEventListener('click', () => {
        assignForm.reset();
        clearErrors('assignForm');
        assignModal.classList.add('open');
    });
    document.getElementById('assignModalClose')?.addEventListener('click', () => assignModal.classList.remove('open'));
    document.getElementById('assignModalCancel')?.addEventListener('click', () => assignModal.classList.remove('open'));
    assignModal?.addEventListener('click', e => { if (e.target === assignModal) assignModal.classList.remove('open'); });

    assignForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors('assignForm');
        setLoading(assignBtn, true);

        const payload = {
            course_id:   document.getElementById('assign-course').value,
            lecturer_id: document.getElementById('assign-lecturer').value,
            semester:    document.getElementById('assign-semester').value.trim(),
        };

        try {
            const data = await api('/admin/assignment/store', 'POST', payload);

            if (data.error && data.fields) {
                Object.entries(data.fields).forEach(([k, v]) => {
                    const idMap = { course_id: 'course_id', lecturer_id: 'lecturer_id' };
                    setFieldError(idMap[k] || k, v);
                });
                if (data.error !== 'Validation failed') window.Toast?.error(data.error);
            } else if (data.error) {
                window.Toast?.error(data.error);
            } else {
                window.Toast?.success('Đã phân công giảng viên.');
                assignModal.classList.remove('open');
                window.location.reload();
            }
        } catch(err) {
            if (err.error && err.fields) {
                Object.entries(err.fields).forEach(([k, v]) => setFieldError(k, v));
            } else {
                window.Toast?.error(err.error || 'Đã xảy ra lỗi.');
            }
        } finally {
            setLoading(assignBtn, false);
        }
    });

    // ── Delete Modal ─────────────────────────────────────────────────
    const deleteModal     = document.getElementById('deleteModal');
    const deleteConfirmBtn = document.getElementById('deleteConfirm');
    let deleteId = null;

    function openDeleteModal(id, name) {
        deleteId = id;
        document.getElementById('deleteCourseName').textContent = name;
        deleteModal.classList.add('open');
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('open');
        deleteId = null;
    }

    document.querySelectorAll('.action-btn--danger').forEach(btn => {
        btn.addEventListener('click', () => openDeleteModal(+btn.dataset.id, btn.dataset.name));
    });
    document.getElementById('deleteModalClose')?.addEventListener('click', closeDeleteModal);
    document.getElementById('deleteCancel')?.addEventListener('click', closeDeleteModal);
    deleteModal?.addEventListener('click', e => { if (e.target === deleteModal) closeDeleteModal(); });

    deleteConfirmBtn?.addEventListener('click', async function() {
        if (!deleteId) return;
        setLoading(this, true);
        try {
            await api(`/admin/course/${deleteId}/delete`, 'POST', {});
            window.Toast?.success('Đã xóa môn học.');
            closeDeleteModal();
            window.location.reload();
        } catch(err) {
            window.Toast?.error(err.error || 'Xóa thất bại.');
        } finally {
            setLoading(deleteConfirmBtn, false);
        }
    });

    // Escape closes all modals
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeCourseModal();
            assignModal?.classList.remove('open');
            closeDeleteModal();
        }
    });
})();
</script>

<style>
.page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; margin-bottom: 20px;
}
.page-heading {
    font-family: 'Lexend Deca', sans-serif;
    font-size: 20px; font-weight: 700;
    color: var(--text-primary); margin-bottom: 2px;
}
.page-sub { font-size: 13px; color: var(--text-muted); }
.header-actions { display: flex; gap: 8px; flex-shrink: 0; }

.search-input {
    background: var(--surface-0); border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm); color: var(--text-primary);
    padding: 8px 14px; font-size: 13px; outline: none;
    width: 260px; transition: border-color var(--transition);
}
.search-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
.search-input::placeholder { color: var(--text-muted); }

.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    text-align: left; padding: 10px 14px;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px;
    color: var(--text-muted); border-bottom: 1px solid var(--surface-2);
    white-space: nowrap;
}
.data-table td { padding: 12px 14px; border-bottom: 1px solid rgba(51,65,85,.4); vertical-align: middle; }
.data-table tbody tr:hover td { background: rgba(51,65,85,.25); }
.data-table tbody tr:last-child td { border-bottom: none; }

.course-badge {
    font-family: 'Lexend Deca', sans-serif; font-weight: 700; font-size: 12px;
    color: var(--accent); background: var(--accent-soft);
    padding: 2px 8px; border-radius: 4px; white-space: nowrap;
}
.credit-pill {
    font-family: 'Lexend Deca', sans-serif; font-weight: 700; font-size: 12px;
    color: var(--amber); background: rgba(245,158,11,.12);
    padding: 2px 8px; border-radius: 20px;
}
.cell-primary { font-weight: 500; font-size: 13px; color: var(--text-primary); }
.cell-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.text-accent { color: var(--accent) !important; }

.stat-pill {
    display: inline-flex; align-items: center; justify-content: center;
    font-family: 'Lexend Deca', sans-serif; font-weight: 700; font-size: 12px;
    min-width: 28px; padding: 2px 8px; border-radius: 20px;
}
.stat-pill--primary { background: var(--accent-soft); color: #a5b4fc; }
.stat-pill--green   { background: rgba(16,185,129,.12); color: var(--emerald); }

.row-actions { display: flex; gap: 4px; justify-content: flex-end; }
.action-btn {
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    background: none; border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm); color: var(--text-muted);
    cursor: pointer; transition: all var(--transition); text-decoration: none;
}
.action-btn svg { width: 14px; height: 14px; }
.action-btn--edit:hover  { border-color: var(--accent); color: var(--accent); background: var(--accent-soft); }
.action-btn--danger:hover { border-color: var(--rose); color: var(--rose); background: rgba(244,63,94,.1); }
.action-btn--blue { color: var(--sky); }
.action-btn--blue:hover { border-color: var(--sky); background: rgba(14,165,233,.1); }

.text-center { text-align: center; }
.text-right  { text-align: right; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.6); backdrop-filter: blur(4px);
    z-index: 1000; display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity .25s;
}
.modal-overlay.open { opacity: 1; pointer-events: auto; }
.modal {
    background: var(--surface-1); border: 1px solid var(--surface-2);
    border-radius: var(--radius-lg); width: 100%; max-width: 500px;
    box-shadow: var(--shadow-card);
    transform: translateY(12px) scale(.98); transition: transform .25s; overflow: hidden;
}
.modal-overlay.open .modal { transform: translateY(0) scale(1); }
.modal--sm .modal-body { text-align: center; }

.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px 16px; border-bottom: 1px solid var(--surface-2);
}
.modal-title {
    font-family: 'Lexend Deca', sans-serif; font-weight: 700;
    font-size: 16px; color: var(--text-primary);
}
.modal-close {
    background: none; border: none; color: var(--text-muted);
    cursor: pointer; padding: 4px; border-radius: var(--radius-sm); transition: all var(--transition);
}
.modal-close svg { width: 18px; height: 18px; }
.modal-close:hover { background: var(--surface-2); color: var(--text-primary); }
.modal-body { padding: 20px; }
.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 20px; border-top: 1px solid var(--surface-2);
}

/* Form elements */
.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }
.form-label {
    display: block; font-size: 13px; font-weight: 500;
    color: var(--text-secondary); margin-bottom: 6px;
}
.required { color: var(--rose); }
.form-textarea { resize: vertical; min-height: 72px; }
.form-select {
    width: 100%; background: var(--surface-0); border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm); color: var(--text-primary); padding: 10px 12px;
    font-family: inherit; font-size: 14px; outline: none; cursor: pointer;
    transition: border-color var(--transition);
}
.form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
.field-error { display: none; font-size: 11px; color: var(--rose); margin-top: 4px; }
.input--error { border-color: var(--rose) !important; }

.assign-divider {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 16px; font-size: 11px; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .5px;
}
.assign-divider::before, .assign-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--surface-2);
}

/* Buttons */
.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 18px; border-radius: var(--radius-sm);
    font-family: 'Lexend Deca', sans-serif; font-weight: 600; font-size: 13px;
    cursor: pointer; border: none; text-decoration: none; transition: all var(--transition);
}
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { background: var(--accent-hover); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.btn-ghost { background: none; color: var(--text-secondary); border: 1px solid var(--surface-2); }
.btn-ghost:hover { border-color: var(--surface-3); color: var(--text-primary); }
.btn-danger { background: var(--rose); color: white; }
.btn-danger:hover { background: #e11d48; }
.btn-danger:disabled { opacity: .6; cursor: not-allowed; }
.btn-spinner svg { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Confirm modal extras */
.confirm-icon {
    width: 52px; height: 52px; background: rgba(245,158,11,.12);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    color: var(--amber); margin: 0 auto 16px;
}
.confirm-icon svg { width: 24px; height: 24px; }
.confirm-text { font-size: 15px; color: var(--text-primary); margin-bottom: 6px; }
.confirm-sub { font-size: 12px; color: var(--text-muted); }

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .header-actions { flex-wrap: wrap; }
    .search-input { width: 100%; }
    .data-table th:nth-child(6),
    .data-table td:nth-child(6) { display: none; }
}
</style>
