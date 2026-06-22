<?php $pageTitle = 'Bài kiểm tra - ' . htmlspecialchars($assignment['code']); ?>
<?php require __DIR__ . '/../layouts/main.php'; ?>

<div class="page-wrapper">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav">
        <a href="/lecturer/dashboard" class="breadcrumb-item">Tổng quan giảng dạy</a>
        <span class="breadcrumb-sep">/</span>
        <a href="/lecturer/assignment/<?= $assignment['id'] ?>/clos" class="breadcrumb-item">
            <?= htmlspecialchars($assignment['code']) ?>
        </a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-item active">Bài kiểm tra</span>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>Bài kiểm tra</h1>
            <p class="header-subtitle">
                <span class="course-code"><?= htmlspecialchars($assignment['code']) ?></span>
                <span class="course-name"><?= htmlspecialchars($assignment['name']) ?></span>
            </p>
        </div>
        <button type="button" class="btn-primary" onclick="openAssessmentModal()">
            <span class="icon">+</span> Thêm bài kiểm tra
        </button>
    </div>

    <!-- Weight Warning -->
    <div id="weightWarning" class="alert alert-warning" style="display:none;">
        <strong>⚠ Cảnh báo:</strong> Tổng trọng số bài kiểm tra vượt quá 100%.
    </div>

    <!-- Filter & Stats -->
    <div class="assessment-controls">
        <div class="filter-group">
            <label for="typeFilter">Lọc theo loại:</label>
            <select id="typeFilter" onchange="filterAssessments()">
                <option value="">Tất cả loại</option>
                <option value="quiz">Trắc nghiệm</option>
                <option value="assignment">Bài tập</option>
                <option value="midterm">Giữa kỳ</option>
                <option value="final">Cuối kỳ</option>
                <option value="project">Dự án</option>
                <option value="lab">Thực hành</option>
            </select>
        </div>
        <div class="assessment-stats">
            <div class="stat-item">
                <span class="stat-label">Tổng cộng:</span>
                <strong id="totalCount"><?= count($assessments) ?></strong> bài
            </div>
            <div class="stat-item">
                <span class="stat-label">Trọng số:</span>
                <strong id="totalWeight"><?= array_sum(array_column($assessments, 'weight')) ?>%</strong>
            </div>
        </div>
    </div>

    <!-- Assessment Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tên bài kiểm tra</th>
                    <th>Loại</th>
                    <th>Trọng số</th>
                    <th>Hạn chót</th>
                    <th>Rubric</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="assessmentTable">
                <?php foreach ($assessments as $a): ?>
                <tr data-type="<?= $a['type'] ?>" class="assessment-row">
                    <td class="title-cell">
                        <span class="truncated" title="<?= htmlspecialchars($a['title']) ?>">
                            <?= htmlspecialchars($a['title']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="assessment-type-badge type-<?= htmlspecialchars($a['type']) ?>">
                            <?= htmlspecialchars(match($a['type']) {
                                'quiz' => 'Trắc nghiệm',
                                'assignment' => 'Bài tập',
                                'midterm' => 'Giữa kỳ',
                                'final' => 'Cuối kỳ',
                                'project' => 'Dự án',
                                'lab' => 'Thực hành',
                                default => $a['type']
                            }) ?>
                        </span>
                    </td>
                    <td>
                        <span class="weight-value <?php 
                            if ($a['weight'] >= 30) echo 'weight-high';
                            elseif ($a['weight'] >= 15) echo 'weight-mid';
                            else echo 'weight-low';
                        ?>">
                            <?= number_format($a['weight'], 1) ?>%
                        </span>
                    </td>
                    <td>
                        <?php if ($a['due_date']): ?>
                            <span class="date-value">
                                <?= date('d/m/Y', strtotime($a['due_date'])) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge-count"><?= $a['rubric_count'] ?? 0 ?></span>
                    </td>
                    <td>
                        <button type="button" class="publish-toggle <?= $a['is_published'] ? 'published' : 'draft' ?>"
                                onclick="togglePublish(<?= $a['id'] ?>, <?= $a['is_published'] ? 0 : 1 ?>)"
                                title="<?= $a['is_published'] ? 'Đã công bố' : 'Nháp' ?>">
                            <span class="toggle-dot"></span>
                            <?= $a['is_published'] ? 'Công bố' : 'Nháp' ?>
                        </button>
                    </td>
                    <td class="actions-cell">
                        <button type="button" class="btn-icon" onclick="editAssessment(<?= $a['id'] ?>)" 
                                title="Chỉnh sửa">
                            <span class="icon-edit">✎</span>
                        </button>
                        <button type="button" class="btn-icon btn-danger" onclick="deleteAssessment(<?= $a['id'] ?>)"
                                title="Xóa">
                            <span class="icon-delete">🗑</span>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Assessment Modal -->
<div id="assessmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Thêm bài kiểm tra</h2>
            <button type="button" class="btn-close" onclick="closeAssessmentModal()">×</button>
        </div>

        <form id="assessmentForm" onsubmit="submitAssessmentForm(event)">
            <div class="modal-body">
                <input type="hidden" id="assessmentId" name="id" value="">
                <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-group">
                    <label for="title">Tên bài kiểm tra <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" 
                           placeholder="VD: Quiz 1 - Chương 1" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Loại <span class="required">*</span></label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="quiz">Trắc nghiệm</option>
                            <option value="assignment">Bài tập</option>
                            <option value="midterm">Giữa kỳ</option>
                            <option value="final">Cuối kỳ</option>
                            <option value="project">Dự án</option>
                            <option value="lab">Thực hành</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="weight">Trọng số (%) <span class="required">*</span></label>
                        <input type="number" id="weight" name="weight" class="form-control" 
                               min="0" max="100" step="0.1" placeholder="VD: 10" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="dueDate">Hạn chót</label>
                    <input type="date" id="dueDate" name="due_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description" class="form-control" 
                              rows="3" placeholder="Mô tả chi tiết về bài kiểm tra..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAssessmentModal()">Hủy</button>
                <button type="submit" class="btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<style>
.assessment-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 2rem;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.filter-group label {
    font-weight: 500;
    color: #64748b;
}

.filter-group select {
    padding: 0.5rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background-color: #ffffff;
    color: #0f172a;
    cursor: pointer;
    min-width: 200px;
}

.assessment-stats {
    display: flex;
    gap: 2rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
}

.stat-item strong {
    font-size: 1.1rem;
    color: #0f172a;
}

.title-cell {
    max-width: 300px;
}

.truncated {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.weight-value {
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    display: inline-block;
}

.weight-value.weight-low {
    background-color: rgba(16, 185, 129, 0.08);
    color: #10b981;
}

.weight-value.weight-mid {
    background-color: rgba(245, 158, 11, 0.08);
    color: #f59e0b;
}

.weight-value.weight-high {
    background-color: rgba(244, 63, 94, 0.08);
    color: #ef4444;
}

.date-value {
    color: #64748b;
}

.text-muted {
    color: #94a3b8;
}

.badge-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background-color: #4f46e5;
    color: white;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.85rem;
}

.publish-toggle {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background-color: #ffffff;
    color: #0f172a;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    font-weight: 500;
}

.publish-toggle:hover {
    opacity: 0.8;
}

.publish-toggle.draft {
    border-color: #6366f1;
    background-color: rgba(99, 102, 241, 0.08);
    color: #6366f1;
}

.publish-toggle.published {
    border-color: #10b981;
    background-color: rgba(16, 185, 129, 0.08);
    color: #10b981;
}

.toggle-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: currentColor;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .assessment-controls {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .table-container {
        overflow-x: auto;
    }
}
</style>

<script>
let editingAssessmentId = null;
const allAssessments = <?= json_encode($assessments) ?>;

function openAssessmentModal() {
    editingAssessmentId = null;
    document.getElementById('modalTitle').textContent = 'Thêm bài kiểm tra';
    document.getElementById('assessmentForm').reset();
    document.getElementById('assessmentId').value = '';
    document.getElementById('assessmentModal').classList.add('active');
}

function closeAssessmentModal() {
    document.getElementById('assessmentModal').classList.remove('active');
    editingAssessmentId = null;
}

function editAssessment(id) {
    const assessment = allAssessments.find(a => a.id === id);
    if (!assessment) return;

    editingAssessmentId = id;
    document.getElementById('modalTitle').textContent = 'Chỉnh sửa bài kiểm tra';
    document.getElementById('assessmentId').value = id;
    document.getElementById('title').value = assessment.title;
    document.getElementById('type').value = assessment.type;
    document.getElementById('weight').value = assessment.weight;
    document.getElementById('dueDate').value = assessment.due_date || '';
    document.getElementById('description').value = assessment.description || '';
    document.getElementById('assessmentModal').classList.add('active');
}

async function submitAssessmentForm(event) {
    event.preventDefault();

    const formData = new FormData(document.getElementById('assessmentForm'));
    const body = {
        id: formData.get('id') || null,
        assignment_id: parseInt(formData.get('assignment_id')),
        title: formData.get('title'),
        type: formData.get('type'),
        description: formData.get('description'),
        weight: parseFloat(formData.get('weight')),
        due_date: formData.get('due_date') || null,
        is_published: 0
    };

    if (!body.title || !body.type || body.weight < 0) {
        showToast('Vui lòng điền đầy đủ thông tin bắt buộc', 'error');
        return;
    }

    try {
        const response = await fetch('/lecturer/assessment/store', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (response.ok) {
            showToast(editingAssessmentId ? 'Cập nhật thành công' : 'Thêm mới thành công', 'success');
            closeAssessmentModal();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.error || 'Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Lỗi kết nối', 'error');
    }
}

async function togglePublish(id, isPublished) {
    try {
        const response = await fetch(`/lecturer/assessment/${id}/toggle-publish`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_published: isPublished })
        });

        if (response.ok) {
            showToast(isPublished ? 'Đã công bố' : 'Chuyển thành nháp', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Lỗi kết nối', 'error');
    }
}

async function deleteAssessment(id) {
    if (!confirm('Bạn chắc chắn muốn xóa bài kiểm tra này?')) return;

    try {
        const response = await fetch(`/lecturer/assessment/${id}/delete`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });

        if (response.ok) {
            showToast('Xóa thành công', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Lỗi kết nối', 'error');
    }
}

function filterAssessments() {
    const typeFilter = document.getElementById('typeFilter').value;
    const rows = document.querySelectorAll('.assessment-row');

    rows.forEach(row => {
        const rowType = row.dataset.type;
        row.style.display = (!typeFilter || rowType === typeFilter) ? '' : 'none';
    });
}

function checkTotalWeight() {
    const totalWeight = allAssessments.reduce((sum, a) => sum + parseFloat(a.weight || 0), 0);
    const warning = document.getElementById('weightWarning');
    warning.style.display = totalWeight > 100 ? 'block' : 'none';
    document.getElementById('totalWeight').textContent = totalWeight + '%';
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    checkTotalWeight();
});

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAssessmentModal();
});

// Close modal on overlay click
document.getElementById('assessmentModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'assessmentModal') closeAssessmentModal();
});
</script>
