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

<!-- Filter & Search Bar -->
<div class="filter-bar">
    <form method="GET" action="/admin/programs" class="search-bar" id="searchForm" style="margin-left:auto">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" class="form-control" style="padding-left:36px;min-width:220px" placeholder="Tìm theo mã, tên chương trình..." value="<?= htmlspecialchars($search_query ?? '') ?>">
        <?php if (!empty($search_query)): ?>
            <a href="/admin/programs" class="btn-icon" title="Xóa tìm kiếm" style="position:absolute;right:8px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Programs Table Card -->
<div class="card">
    <div class="table-info-bar">
        <?php
        $total  = ($total_pages ?? 1) * 20;
        $from   = (($current_page - 1) * 20) + 1;
        $to     = min($current_page * 20, count($programs));
        ?>
        Hiển thị <?= max(1, $from) ?>-<?= $to ?> trên <?= $total ?> kết quả
    </div>

    <div class="table-wrap">
        <table class="data-table striped" id="programsTable">
            <?php
            function th_prog(string $label, string $col): string {
                global $sort_col, $sort_dir, $search_query;
                $isActive = $col === ($sort_col ?? 'code');
                $newDir   = $isActive && ($sort_dir ?? 'ASC') === 'ASC' ? 'DESC' : 'ASC';
                $arrow    = $isActive ? (($sort_dir ?? 'ASC') === 'ASC' ? ' &#9650;' : ' &#9660;') : '';
                $qs       = http_build_query(array_filter(['sort' => $col, 'dir' => $newDir, 'search' => $search_query]));
                return '<th><a href="/admin/programs?'.$qs.'" class="th-sort'.($isActive ? ' th-sort--active' : '').'" title="Sắp xếp theo '.$label.'">'.$label.$arrow.'</a></th>';
            }
            ?>
            <thead>
                <tr>
                    <th><?= th_prog('Mã', 'code') ?></th>
                    <th><?= th_prog('Tên chương trình', 'name') ?></th>
                    <th class="text-center"><?= th_prog('Môn học', 'course_count') ?></th>
                    <th class="text-center"><?= th_prog('PLO', 'plo_count') ?></th>
                    <th class="text-center"><?= th_prog('GV phụ trách', 'assignment_count') ?></th>
                    <th class="text-center"><?= th_prog('Sinh viên', 'student_count') ?></th>
                    <th class="text-center"><?= th_prog('Ngày tạo', 'created_at') ?></th>
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
                                <span class="badge badge-accent"><?= htmlspecialchars($p['code']) ?></span>
                            </td>
                            <td>
                                <div class="cell-primary"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="cell-sub"><?= htmlspecialchars($p['admin_name']) ?> · <?= htmlspecialchars(mb_substr($p['description'], 0, 60)) ?: 'Không có mô tả' ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-sky"><?= $p['course_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-amber"><?= $p['plo_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-emerald"><?= $p['assignment_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-accent"><?= $p['student_count'] ?></span>
                            </td>
                            <td class="text-center text-muted text-sm">
                                <?= date('d/m/Y', strtotime($p['created_at'] ?? 'now')) ?>
                            </td>
                            <td class="text-right">
                                <div class="row-actions">
                                    <a href="/admin/plos/<?= $p['id'] ?>" class="action-btn action-btn--blue" title="Quản lý PLO">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </a>
                                    <button class="action-btn action-btn--edit" title="Sửa"
                                        data-id="<?= htmlspecialchars($p['id']) ?>"
                                        data-code="<?= htmlspecialchars($p['code']) ?>"
                                        data-name="<?= htmlspecialchars($p['name']) ?>"
                                        data-description="<?= htmlspecialchars($p['description']) ?>">
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

    <!-- Pagination -->
    <?php if (($total_pages ?? 1) > 1): ?>
        <div class="pagination">
            <span class="pagination-info">
                Hiển thị <?= max(1, $from) ?>-<?= $to ?> trên <?= $total ?> kết quả
            </span>
            <?php
            $baseUrl = '/admin/programs?';
            $qsParts = array_filter([
                'search' => $search_query,
                'sort'   => $sort_col ?? 'code',
                'dir'    => $sort_dir ?? 'ASC',
            ]);
            $baseQs  = http_build_query($qsParts);
            ?>
            <?php if ($current_page > 1): ?>
                <a href="<?= $baseUrl . http_build_query(array_merge($qsParts, ['page' => $current_page - 1])) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            <?php else: ?>
                <span class="disabled"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg></span>
            <?php endif; ?>

            <?php
            $start = max(1, $current_page - 2);
            $end   = min($total_pages, $current_page + 2);
            if ($start > 1): ?>
                <a href="<?= $baseUrl . http_build_query(array_merge($qsParts, ['page' => 1])) ?>">1</a>
                <?php if ($start > 2): ?><span class="disabled">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $current_page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $baseUrl . http_build_query(array_merge($qsParts, ['page' => $i])) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span class="disabled">…</span><?php endif; ?>
                <a href="<?= $baseUrl . http_build_query(array_merge($qsParts, ['page' => $total_pages])) ?>"><?= $total_pages ?></a>
            <?php endif; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="<?= $baseUrl . http_build_query(array_merge($qsParts, ['page' => $current_page + 1])) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            <?php else: ?>
                <span class="disabled"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
                <button type="button" class="btn btn-secondary" id="modalCancel">Hủy</button>
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
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Xác nhận xóa</h3>
            <button class="modal-close" id="deleteModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="text-align:center">
            <div class="confirm-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <p class="confirm-text">Xóa chương trình <strong id="deleteProgramName"></strong>?</p>
            <p class="confirm-sub">Hành động này không thể hoàn tác.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="deleteCancel">Hủy</button>
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
        }).then(r => {
            return r.json().then(d => {
                if (!r.ok) throw d;
                return d;
            });
        });
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

    function openModal(id, source) {
        editId = id;
        modalTitle.textContent = id ? 'Sửa chương trình' : 'Thêm chương trình';
        submitBtn.querySelector('.btn-label').textContent = id ? 'Lưu thay đổi' : 'Tạo mới';

        if (id && source) {
            document.getElementById('prog-code').value = source.dataset.code || '';
            document.getElementById('prog-name').value = source.dataset.name || '';
            document.getElementById('prog-desc').value = source.dataset.description || '';
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
            openModal(+btn.dataset.id, btn);
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
            const url  = editId ? `/admin/program/${editId}/update` : '/admin/programs';
            const data = await api(url, 'POST', payload);

            if (data.error && data.fields) {
                Object.entries(data.fields).forEach(([k, v]) => setFieldError(k, v));
                window.Toast?.error(data.error);
            } else if (data.error) {
                window.Toast?.error(data.error);
            } else {
                window.Toast?.success(editId ? 'Đã cập nhật chương trình đào tạo.' : 'Đã tạo chương trình đào tạo mới.');
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
    color: #0f172a;
    margin-bottom: 2px;
}
.page-sub { font-size: 13px; color: #94a3b8; }
.mt-8 { margin-top: 8px; }

.cell-primary { font-weight: 500; font-size: 13px; color: #0f172a; }
.cell-sub { font-size: 11px; color: #94a3b8; margin-top: 2px; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Sortable table headers */
.th-sort {
    color: #64748b;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 600;
    white-space: nowrap;
    transition: color 0.15s ease;
}
.th-sort:hover { color: #0f172a; }
.th-sort--active { color: #4f46e5; }

.table-info-bar {
    padding: 10px 14px;
    font-size: 12px;
    color: #94a3b8;
    border-bottom: 1px solid rgba(226,232,240,0.8);
}

/* Search bar */
.search-bar { position: relative; display: flex; align-items: center; }
.search-bar svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
    z-index: 1;
}
.search-bar input {
    padding-left: 36px;
    min-width: 240px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #0f172a;
    padding-top: 8px;
    padding-bottom: 8px;
    font-size: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-bar input::placeholder { color: #94a3b8; }
.search-bar input:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
    outline: none;
}

.row-actions { display: flex; gap: 4px; justify-content: flex-end; }
.action-btn {
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}
.action-btn svg { width: 14px; height: 14px; }
.action-btn--edit:hover { border-color: #4f46e5; color: #4f46e5; background: rgba(79,70,229,0.08); }
.action-btn--danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.06); }
.action-btn--blue { color: #f59e0b; }
.action-btn--blue:hover { border-color: #f59e0b; background: rgba(245,158,11,0.06); }

.text-center { text-align: center; }
.text-right  { text-align: right; }
.text-muted  { color: #94a3b8; }
.text-sm     { font-size: 12px; }

.confirm-icon {
    width: 52px; height: 52px;
    background: rgba(245,158,11,0.08);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #f59e0b;
    margin: 0 auto 16px;
}
.confirm-icon svg { width: 24px; height: 24px; }
.confirm-text { font-size: 15px; color: #0f172a; margin-bottom: 6px; }
.confirm-sub { font-size: 12px; color: #94a3b8; }

/* Buttons */
.btn-spinner svg { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Form elements */
.required { color: #ef4444; }
.form-textarea { resize: vertical; min-height: 72px; }
.field-error {
    display: none;
    font-size: 11px; color: #ef4444;
    margin-top: 4px;
}
.input--error { border-color: #ef4444 !important; }

@media (max-width: 640px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .data-table th:nth-child(5),
    .data-table td:nth-child(5),
    .data-table th:nth-child(6),
    .data-table td:nth-child(6) { display: none; }
}
</style>
