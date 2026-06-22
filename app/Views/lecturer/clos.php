<?php /* app/Views/lecturer/clos.php */ ?>
<?php $pageTitle = 'Chuẩn đầu ra môn - ' . htmlspecialchars($assignment['code']); ?>

<!-- Breadcrumb -->
<nav class="breadcrumb-nav">
    <a href="/lecturer/dashboard" class="breadcrumb-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Tổng quan
    </a>
    <span class="breadcrumb-divider">/</span>
    <a href="#" class="breadcrumb-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M12 2v20m8-8H4"/>
        </svg>
        CLO - <?= htmlspecialchars($assignment['code']) ?>
    </a>
</nav>

<!-- Header -->
<div class="clo-header">
    <div class="clo-header-info">
        <h2 class="clo-title">Chuẩn đầu ra môn (CLO)</h2>
        <p class="clo-subtitle">
            <span class="course-code"><?= htmlspecialchars($assignment['code']) ?></span>
            <span class="course-name"><?= htmlspecialchars($assignment['name']) ?></span>
        </p>
    </div>
    <button class="btn btn-primary" id="btnNewClo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Thêm CLO mới
    </button>
</div>

<!-- CLO Table -->
<div class="clo-table-card">
    <div class="table-scroll">
        <table class="clo-table">
            <thead>
                <tr>
                    <th class="th-code">Mã CLO</th>
                    <th class="th-description">Mô tả</th>
                    <th class="th-bloom">Bloom Level</th>
                    <th class="th-plos">Ánh xạ PLO</th>
                    <th class="th-actions">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clos)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted); font-size:13px;">
                        Chưa có CLO nào. <a href="#" onclick="openCloModal()" style="color:var(--accent); cursor:pointer;">Tạo cái đầu tiên</a>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($clos as $clo): ?>
                    <tr class="clo-row" data-clo-id="<?= $clo['id'] ?>">
                        <td class="td-code">
                            <span class="clo-code-badge"><?= htmlspecialchars($clo['code']) ?></span>
                        </td>
                        <td class="td-description">
                            <div class="clo-desc-text" title="<?= htmlspecialchars($clo['description']) ?>">
                                <?= htmlspecialchars(mb_substr($clo['description'], 0, 80)) ?>
                                <?php if (mb_strlen($clo['description']) > 80): ?><span class="ellipsis">...</span><?php endif; ?>
                            </div>
                        </td>
                        <td class="td-bloom">
                            <?php if ($clo['bloom_level']): ?>
                                <span class="bloom-badge bloom--<?= $clo['bloom_level'] ?>" title="<?= $bloom_levels[$clo['bloom_level']] ?? 'Unknown' ?>">
                                    <?= $bloom_levels[$clo['bloom_level']] ?? 'N/A' ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">Không xác định</span>
                            <?php endif; ?>
                        </td>
                        <td class="td-plos">
                            <span class="plo-count-badge"><?= $clo['mapped_plo_count'] ?></span>
                        </td>
                        <td class="td-actions">
                            <button class="action-icon-btn" onclick="editClo(<?= $clo['id'] ?>, '<?= addslashes($clo['code']) ?>', '<?= addslashes($clo['description']) ?>', <?= $clo['bloom_level'] ?? 'null' ?>)" title="Chỉnh sửa">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <button class="action-icon-btn action-icon-btn--danger" onclick="deleteClo(<?= $clo['id'] ?>)" title="Xóa">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create/Edit CLO -->
<div class="modal" id="cloModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Thêm/Sửa Chuẩn đầu ra môn</h3>
            <button class="modal-close" onclick="closeCloModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="cloForm" class="modal-body">
            <input type="hidden" name="id" id="cloId">
            <input type="hidden" name="course_id" value="<?= $assignment['course_id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="cloCode" class="form-label">Mã CLO *</label>
                <input type="text" id="cloCode" name="code" class="form-input" placeholder="VD: CLO1" required>
                <small class="form-hint">Mã duy nhất trong môn (VD: CLO1, CLO2, ...)</small>
            </div>

            <div class="form-group">
                <label for="cloDescription" class="form-label">Mô tả *</label>
                <textarea id="cloDescription" name="description" class="form-input" rows="4" placeholder="Mô tả chi tiết CLO..." required></textarea>
                <small class="form-hint">Mô tả chuẩn đầu ra cụ thể</small>
            </div>

            <div class="form-group">
                <label for="cloBloom" class="form-label">Bloom Taxonomy Level</label>
                <select id="cloBloom" name="bloom_level" class="form-input">
                    <option value="">-- Chọn mức độ --</option>
                    <?php foreach ($bloom_levels as $level => $label): ?>
                    <option value="<?= $level ?>" title="<?= htmlspecialchars($label) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="form-hint">
                    <strong>Bloom Taxonomy:</strong> 1=Nhớ | 2=Hiểu | 3=Áp dụng | 4=Phân tích | 5=Đánh giá | 6=Sáng tạo
                </small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCloModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu CLO</button>
            </div>
        </form>
    </div>
</div>

<!-- Bloom Descriptions Reference (hidden) -->
<div class="bloom-descriptions" style="display:none;">
    <div class="bloom-item" data-level="1">
        <strong>Nhớ (Remember)</strong>: Ghi nhớ, xác định, nhận diện
    </div>
    <div class="bloom-item" data-level="2">
        <strong>Hiểu (Understand)</strong>: Diễn giải, phân loại, tóm tắt
    </div>
    <div class="bloom-item" data-level="3">
        <strong>Áp dụng (Apply)</strong>: Sử dụng, thực hiện, minh họa
    </div>
    <div class="bloom-item" data-level="4">
        <strong>Phân tích (Analyze)</strong>: Phân biệt, so sánh, phân loại
    </div>
    <div class="bloom-item" data-level="5">
        <strong>Đánh giá (Evaluate)</strong>: Kiểm chứng, phê bình, so sánh
    </div>
    <div class="bloom-item" data-level="6">
        <strong>Sáng tạo (Create)</strong>: Tạo ra, thiết kế, sáng tác
    </div>
</div>

<style>
.breadcrumb-divider { color: #94a3b8; margin: 0 8px; }

.clo-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
}
.clo-header-info { display: flex; flex-direction: column; gap: 4px; }
.clo-title { font-family: 'Lexend Deca', sans-serif; font-weight: 700; font-size: 18px; color: #0f172a; }
.clo-subtitle { font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 12px; }
.course-code {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    padding: 3px 10px;
    background: rgba(79,70,229,0.08);
    color: #4f46e5;
    border-radius: 6px;
}
.course-name { font-size: 13px; color: #0f172a; }

.clo-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
}
.table-scroll { overflow-x: auto; }
.clo-table { width: 100%; border-collapse: collapse; }
.clo-table th {
    background: #f8fafc;
    padding: 12px 14px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 11px;
    font-weight: 700;
    text-align: left;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.th-code { width: 10%; min-width: 80px; }
.th-description { width: 45%; min-width: 200px; }
.th-bloom { width: 20%; min-width: 120px; }
.th-plos { width: 10%; min-width: 80px; }
.th-actions { width: 15%; min-width: 100px; text-align: center; }

.clo-table td {
    padding: 14px;
    border-bottom: 1px solid rgba(226,232,240,0.8);
    vertical-align: middle;
}
.clo-row:last-child td { border-bottom: none; }
.clo-row:hover td { background: rgba(226,232,240,0.4); }

.clo-code-badge {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    padding: 4px 10px;
    background: rgba(79,70,229,0.08);
    color: #4f46e5;
    border-radius: 6px;
    white-space: nowrap;
}
.clo-desc-text {
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
    max-width: 100%;
    white-space: normal;
    word-wrap: break-word;
}
.ellipsis { color: #94a3b8; }

.bloom-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.bloom--1 { background: rgba(99,102,241,.2);   color: #4f46e5; }
.bloom--2 { background: rgba(14,165,233,.2);   color: #0ea5e9; }
.bloom--3 { background: rgba(16,185,129,.2);   color: #10b981; }
.bloom--4 { background: rgba(245,158,11,.2);   color: #f59e0b; }
.bloom--5 { background: rgba(244,63,94,.2);    color: #ef4444; }
.bloom--6 { background: rgba(139,92,246,.2);   color: #7c3aed; }

.plo-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 50%;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: #4f46e5;
}

.td-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.action-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: none;
    border: none;
    border-radius: 6px;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.2s ease;
}
.action-icon-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.action-icon-btn--danger:hover {
    background: rgba(239,68,68,0.1);
    color: #ef4444;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    animation: fadeIn .2s ease;
}
.modal.active { display: flex; }
.modal-content {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-sm { width: 90%; max-width: 500px; }
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
}
.modal-title { font-family: 'Lexend Deca', sans-serif; font-weight: 700; font-size: 16px; color: #0f172a; }
.modal-close {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.modal-close:hover { background: #e2e8f0; color: #0f172a; }

.modal-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 16px 20px;
    border-top: 1px solid #e2e8f0;
}
.btn-secondary {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #64748b;
}
.btn-secondary:hover { border-color: #cbd5e1; color: #0f172a; }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 13px; font-weight: 600; color: #64748b; }
.form-hint { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.form-input {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #0f172a;
    padding: 10px 12px;
    font-family: inherit;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
}
.form-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.08); }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.bloom-descriptions { display: none; }
</style>

<script>
const cloForm = document.getElementById('cloForm');
const cloModal = document.getElementById('cloModal');
const btnNewClo = document.getElementById('btnNewClo');

// Open modal for new CLO
btnNewClo.addEventListener('click', openCloModal);

function openCloModal() {
    cloForm.reset();
    document.getElementById('cloId').value = '';
    cloModal.classList.add('active');
    document.getElementById('cloCode').focus();
}

function closeCloModal() {
    cloModal.classList.remove('active');
}

// Edit CLO
function editClo(id, code, description, bloomLevel) {
    document.getElementById('cloId').value = id;
    document.getElementById('cloCode').value = code;
    document.getElementById('cloDescription').value = description;
    document.getElementById('cloBloom').value = bloomLevel || '';
    openCloModal();
}

// Save CLO
cloForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const cloId = document.getElementById('cloId').value;
    const isNew = !cloId;

    const data = {
        course_id: document.querySelector('input[name="course_id"]').value,
        code: document.getElementById('cloCode').value.trim(),
        description: document.getElementById('cloDescription').value.trim(),
        bloom_level: document.getElementById('cloBloom').value || null,
        csrf_token: document.querySelector('input[name="csrf_token"]').value,
    };

    if (!data.code || !data.description) {
        Toast.error('Vui lòng điền đầy đủ thông tin bắt buộc');
        return;
    }

    try {
        const response = await fetch('/lecturer/clo/store', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        const result = await response.json();

        if (!response.ok) {
            Toast.error(result.error || 'Lỗi khi lưu CLO');
            return;
        }

        Toast.success(isNew ? 'CLO mới đã được tạo' : 'CLO đã được cập nhật');
        closeCloModal();
        setTimeout(() => location.reload(), 1000);
    } catch (err) {
        Toast.error('Lỗi: ' + err.message);
    }
});

// Delete CLO
async function deleteClo(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa CLO này?')) return;

    try {
        const response = await fetch(`/lecturer/clo/${id}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('input[name="csrf_token"]').value,
            },
        });
        const result = await response.json();

        if (!response.ok) {
            Toast.error(result.error || 'Không thể xóa CLO');
            return;
        }

        Toast.success('CLO đã được xóa');
        setTimeout(() => location.reload(), 1000);
    } catch (err) {
        Toast.error('Lỗi: ' + err.message);
    }
}

// Close modal on outside click
cloModal.addEventListener('click', (e) => {
    if (e.target === cloModal) closeCloModal();
});

// Close modal on Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && cloModal.classList.contains('active')) closeCloModal();
});
</script>
