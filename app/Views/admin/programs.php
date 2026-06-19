<?php /* app/Views/admin/programs.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Chương trình đào tạo</h2>
        <p class="page-sub">Quản lý CTĐT, PLO và thống kê tổng quan</p>
    </div>
    <button class="btn btn-primary" id="btnCreateProgram">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm chương trình
    </button>
</div>

<!-- Programs Table Card -->
<div class="section-card">
    <div class="section-header">
        <div class="section-title-group">
            <h3 class="section-title">Danh sách chương trình</h3>
            <span class="section-badge"><?= count($programs) ?> CTĐT</span>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table" id="programsTable">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên chương trình</th>
                    <th class="text-center">Môn học</th>
                    <th class="text-center">PLO</th>
                    <th class="text-center">GV phụ trách</th>
                    <th class="text-center">Sinh viên</th>
                    <th class="text-center">Ngày tạo</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($programs)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                <p>Chưa có chương trình đào tạo nào.</p>
                                <button class="btn btn-primary btn-sm mt-8" id="btnCreateProgramEmpty">Tạo chương trình đầu tiên</button>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($programs as $p): ?>
                        <tr class="program-row" data-id="<?= $p['id'] ?>">
                            <td>
                                <span class="program-badge"><?= htmlspecialchars($p['code']) ?></span>
                            </td>
                            <td>
                                <div class="cell-primary"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="cell-sub"><?= htmlspecialchars($p['admin_name']) ?> · <?= htmlspecialchars(mb_substr($p['description'], 0, 60)) ?: 'Không có mô tả' ?></div>
                            </td>
                            <td class="text-center">
                                <span class="stat-pill stat-pill--blue"><?= $p['course_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="stat-pill stat-pill--amber"><?= $p['plo_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="stat-pill stat-pill--green"><?= $p['assignment_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="stat-pill stat-pill--primary"><?= $p['student_count'] ?></span>
                            </td>
                            <td class="text-center text-muted text-sm">
                                <?= date('d/m/Y', strtotime($p['created_at'] ?? 'now')) ?>
                            </td>
                            <td class="text-right">
                                <div class="row-actions">
                                    <a href="/admin/plos/<?= $p['id'] ?>" class="action-btn action-btn--blue" title="Quản lý PLO">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </a>
                                    <button class="action-btn action-btn--edit" title="Sửa" data-id="<?= $p['id'] ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="action-btn action-btn--danger" title="Xóa" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>">
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

<!-- ── Create / Edit Modal ─────────────────────────────────────────── -->
<div class="modal-overlay" id="programModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Thêm chương trình</h3>
            <button class="modal-close" id="modalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="programForm" novalidate>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="prog-code">Mã chương trình <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        <input type="text" id="prog-code" name="code" class="form-input" placeholder="VD: ITEC2019" maxlength="20">
                    </div>
                    <span class="field-error" id="err-code"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="prog-name">Tên chương trình <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" id="prog-name" name="name" class="form-input" placeholder="VD: Công nghệ thông tin 2019" maxlength="200">
                    </div>
                    <span class="field-error" id="err-name"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="prog-desc">Mô tả</label>
                    <textarea id="prog-desc" name="description" class="form-input form-textarea" placeholder="Mô tả ngắn về chương trình đào tạo..." rows="3" maxlength="1000"></textarea>
                    <span class="field-error"></span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" id="modalCancel">Hủy</button>
                <button type="submit" class="btn btn-primary" id="modalSubmit">
                    <span class="btn-label">Tạo mới</span>
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
            <p class="confirm-text">Xóa chương trình <strong id="deleteProgramName"></strong>?</p>
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

    // ── Helpers ───────────────────────────────────────────────────────
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
        const input = document.getElementById('prog-' + id);
        if (input) input.classList.toggle('input--error', !!msg);
    }

    function clearErrors() {
        ['code','name'].forEach(id => setFieldError(id, ''));
    }

    function setLoading(btn, on) {
        btn.disabled = on;
        btn.querySelector('.btn-label').textContent = on ? 'Đang xử lý...' : (editId ? 'Lưu thay đổi' : 'Tạo mới');
        btn.querySelector('.btn-spinner').hidden = !on;
    }

    // ── Modal logic ───────────────────────────────────────────────────
    const modal     = document.getElementById('programModal');
    const form      = document.getElementById('programForm');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('modalSubmit');
    let editId = null;

    function openModal(id, row) {
        editId = id;
        modalTitle.textContent = id ? 'Sửa chương trình' : 'Thêm chương trình';
        submitBtn.querySelector('.btn-label').textContent = id ? 'Lưu thay đổi' : 'Tạo mới';

        if (id && row) {
            document.getElementById('prog-code').value = row.dataset.code || '';
            document.getElementById('prog-name').value = row.dataset.name || '';
            document.getElementById('prog-desc').value = row.dataset.description || '';
        } else {
            form.reset();
        }
        clearErrors();
        modal.classList.add('open');
        document.getElementById('prog-code').focus();
    }

    function closeModal() {
        modal.classList.remove('open');
        editId = null;
        form.reset();
        clearErrors();
    }

    document.getElementById('btnCreateProgram')?.addEventListener('click', () => openModal(null));
    document.getElementById('btnCreateProgramEmpty')?.addEventListener('click', () => openModal(null));
    document.getElementById('modalClose')?.addEventListener('click', closeModal);
    document.getElementById('modalCancel')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // ── Edit buttons ──────────────────────────────────────────────────
    document.querySelectorAll('.action-btn--edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.program-row');
            openModal(+btn.dataset.id, row);
        });
    });

    // ── Store / Update ─────────────────────────────────────────────────
    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();
        setLoading(submitBtn, true);

        const payload = {
            code:        document.getElementById('prog-code').value.trim(),
            name:        document.getElementById('prog-name').value.trim(),
            description: document.getElementById('prog-desc').value.trim(),
        };

        try {
            const url  = editId ? `/admin/program/${editId}/update` : '/admin/program/store';
            const data = await api(url, 'POST', payload);

            if (data.error && data.fields) {
                Object.entries(data.fields).forEach(([k, v]) => setFieldError(k, v));
                if (data.error !== 'Validation failed') window.Toast?.error(data.error);
            } else if (data.error) {
                window.Toast?.error(data.error);
            } else {
                window.Toast?.success(editId ? 'Đã cập nhật chương trình.' : 'Đã tạo chương trình mới.');
                closeModal();
                window.location.reload();
            }
        } catch(err) {
            if (err.error && err.fields) {
                Object.entries(err.fields).forEach(([k, v]) => setFieldError(k, v));
            } else {
                window.Toast?.error(err.error || 'Đã xảy ra lỗi. Vui lòng thử lại.');
            }
        } finally {
            setLoading(submitBtn, false);
        }
    });

    // ── Delete ────────────────────────────────────────────────────────
    const deleteModal = document.getElementById('deleteModal');
    const deleteConfirmBtn = document.getElementById('deleteConfirm');

    function openDeleteModal(id, name) {
        deleteId = id;
        document.getElementById('deleteProgramName').textContent = name;
        deleteModal.classList.add('open');
    }

    let deleteId = null;

    document.querySelectorAll('.action-btn--danger').forEach(btn => {
        btn.addEventListener('click', () => openDeleteModal(+btn.dataset.id, btn.dataset.name));
    });

    function closeDeleteModal() {
        deleteModal.classList.remove('open');
        deleteId = null;
    }

    document.getElementById('deleteModalClose')?.addEventListener('click', closeDeleteModal);
    document.getElementById('deleteCancel')?.addEventListener('click', closeDeleteModal);
    deleteModal?.addEventListener('click', e => { if (e.target === deleteModal) closeDeleteModal(); });

    deleteConfirmBtn?.addEventListener('click', async function() {
        if (!deleteId) return;
        setLoading(this, true);
        try {
            const data = await api(`/admin/program/${deleteId}/delete`, 'POST', {});
            window.Toast?.success('Đã xóa chương trình.');
            closeDeleteModal();
            window.location.reload();
        } catch(err) {
            window.Toast?.error(err.error || 'Xóa thất bại.');
        } finally {
            setLoading(deleteConfirmBtn, false);
        }
    });

    // Escape key closes modals
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeModal(); closeDeleteModal(); }
    });
})();
</script>

<style>
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}
.page-heading {
    font-family: 'Lexend Deca', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.page-sub { font-size: 13px; color: var(--text-muted); }

.btn-sm { font-size: 12px; padding: 6px 14px; }
.mt-8 { margin-top: 8px; }

.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    text-align: left;
    padding: 10px 14px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--surface-2);
    white-space: nowrap;
}
.data-table td {
    padding: 12px 14px;
    border-bottom: 1px solid rgba(51,65,85,.4);
    vertical-align: middle;
}
.data-table tbody tr:hover td { background: rgba(51,65,85,.25); }
.data-table tbody tr:last-child td { border-bottom: none; }

.program-badge {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--accent);
    background: var(--accent-soft);
    padding: 2px 8px;
    border-radius: 4px;
    white-space: nowrap;
}
.cell-primary { font-weight: 500; font-size: 13px; color: var(--text-primary); }
.cell-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.stat-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    min-width: 28px;
    padding: 2px 8px;
    border-radius: 20px;
}
.stat-pill--primary { background: var(--accent-soft); color: #a5b4fc; }
.stat-pill--blue    { background: rgba(14,165,233,.12); color: var(--sky); }
.stat-pill--amber   { background: rgba(245,158,11,.12); color: var(--amber); }
.stat-pill--green   { background: rgba(16,185,129,.12); color: var(--emerald); }

.row-actions { display: flex; gap: 4px; justify-content: flex-end; }
.action-btn {
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    background: none;
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    cursor: pointer;
    transition: all var(--transition);
    text-decoration: none;
}
.action-btn svg { width: 14px; height: 14px; }
.action-btn--edit:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-soft); }
.action-btn--danger:hover { border-color: var(--rose); color: var(--rose); background: rgba(244,63,94,.1); }
.action-btn--blue { color: var(--amber); }
.action-btn--blue:hover { border-color: var(--amber); background: rgba(245,158,11,.1); }

.text-center { text-align: center; }
.text-right  { text-align: right; }
.text-muted  { color: var(--text-muted); }
.text-sm     { font-size: 12px; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity .25s;
}
.modal-overlay.open { opacity: 1; pointer-events: auto; }

.modal {
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-lg);
    width: 100%; max-width: 480px;
    box-shadow: var(--shadow-card);
    transform: translateY(12px) scale(.98);
    transition: transform .25s;
    overflow: hidden;
}
.modal-overlay.open .modal { transform: translateY(0) scale(1); }
.modal--sm .modal-body { text-align: center; }

.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px 16px;
    border-bottom: 1px solid var(--surface-2);
}
.modal-title {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 16px;
    color: var(--text-primary);
}
.modal-close {
    background: none; border: none;
    color: var(--text-muted); cursor: pointer;
    padding: 4px; border-radius: var(--radius-sm);
    transition: all var(--transition);
}
.modal-close svg { width: 18px; height: 18px; }
.modal-close:hover { background: var(--surface-2); color: var(--text-primary); }

.modal-body { padding: 20px; }

.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid var(--surface-2);
}

/* Buttons */
.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 18px;
    border-radius: var(--radius-sm);
    font-family: 'Lexend Deca', sans-serif; font-weight: 600; font-size: 13px;
    cursor: pointer; border: none; text-decoration: none;
    transition: all var(--transition);
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

/* Form elements */
.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }
.form-label {
    display: block;
    font-size: 13px; font-weight: 500;
    color: var(--text-secondary); margin-bottom: 6px;
}
.required { color: var(--rose); }
.form-textarea { resize: vertical; min-height: 72px; }
.field-error {
    display: none;
    font-size: 11px; color: var(--rose);
    margin-top: 4px;
}
.input--error { border-color: var(--rose) !important; }

.confirm-icon {
    width: 52px; height: 52px;
    background: rgba(245,158,11,.12);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: var(--amber);
    margin: 0 auto 16px;
}
.confirm-icon svg { width: 24px; height: 24px; }
.confirm-text { font-size: 15px; color: var(--text-primary); margin-bottom: 6px; }
.confirm-sub { font-size: 12px; color: var(--text-muted); }

@media (max-width: 640px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .data-table th:nth-child(5),
    .data-table td:nth-child(5),
    .data-table th:nth-child(6),
    .data-table td:nth-child(6) { display: none; }
}
</style>
