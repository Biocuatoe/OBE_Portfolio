<?php /* app/Views/admin/plos.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<!-- PAGE WRAPPER — 2-column split layout -->
<div class="plo-page-wrapper">

  <!-- ── LEFT COLUMN: Program Summary & Stats (30%) ── -->
  <aside class="plo-sidebar">

    <!-- Back button -->
    <a href="/admin/programs" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
      Quay lại danh sách CTĐT
    </a>

    <!-- Program Header Card -->
    <div class="program-summary-card">
      <div class="program-meta">
        <span class="program-code-badge"><?= htmlspecialchars($program_code) ?></span>
        <h1 class="program-title"><?= htmlspecialchars($program_name) ?></h1>
        <p class="program-subtitle">Trang quản lý chuẩn đầu ra PLO</p>
      </div>
    </div>

    <!-- Stats Card -->
    <div class="stats-card">
      <div class="stats-header">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>
        </svg>
        Thống kê PLO
      </div>
      <div class="stat-big">
        <span class="stat-number" id="stat-total"><?= count($plos) ?></span>
        <span class="stat-label">Tổng số PLO</span>
      </div>
      <div class="stats-divider"></div>
      <div class="category-breakdown">
        <?php
        $cats = ['Knowledge' => 'badge-blue', 'Skill' => 'badge-green', 'Attitude' => 'badge-amber'];
        $counts = ['Knowledge' => 0, 'Skill' => 0, 'Attitude' => 0];
        foreach ($plos as $p) { if (isset($counts[$p['category']])) $counts[$p['category']]++; }
        foreach ($cats as $cat => $badgeClass): ?>
          <div class="cat-row">
            <span class="<?= $badgeClass ?>"><?= htmlspecialchars($cat) ?></span>
            <span class="cat-count"><?= $counts[$cat] ?? 0 ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Add PLO Button (left column) -->
    <button class="btn btn-primary btn-full" id="btnAddPlo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
      </svg>
      Thêm PLO mới
    </button>

  </aside>

  <!-- ── RIGHT COLUMN: PLO Cards List (70%) ── -->
  <main class="plo-main">

    <!-- Page header -->
    <div class="plo-main-header">
      <h2 class="section-title">Danh sách chuẩn đầu ra PLO</h2>
      <span class="badge-count"><?= count($plos) ?> PLO</span>
    </div>

    <?php if (empty($plos)): ?>
      <!-- EMPTY STATE -->
      <div class="plo-empty-state">
        <div class="empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="56" height="56">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
        </div>
        <h3 class="empty-title">Chương trình <?= htmlspecialchars($program_name) ?> chưa có chuẩn đầu ra PLO nào.</h3>
        <p class="empty-desc">Chuẩn đầu ra PLO là nền tảng của hệ thống OBE. Hãy thêm PLO để bắt đầu thiết kế ma trận năng lực cho sinh viên khi tốt nghiệp.</p>
        <button class="btn btn-primary btn-lg" id="btnAddPloEmpty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Thêm PLO đầu tiên
        </button>
      </div>
    <?php else: ?>
      <!-- PLO CARDS GRID -->
      <div class="plo-cards-list" id="ploCardsList">
        <?php
        $catColors = [
          'Knowledge' => ['border' => '#3b82f6', 'badge' => 'badge-blue'],
          'Skill'     => ['border' => '#10b981', 'badge' => 'badge-green'],
          'Attitude'  => ['border' => '#f59e0b', 'badge' => 'badge-amber'],
        ];
        foreach ($plos as $plo):
          $color = $catColors[$plo['category']] ?? ['border' => '#94a3b8', 'badge' => 'badge-slate'];
        ?>
          <div class="plo-card" style="border-left-color: <?= $color['border'] ?>">
            <div class="plo-card-header">
              <div class="plo-card-title-row">
                <strong class="plo-code"><?= htmlspecialchars($plo['code']) ?></strong>
                <span class="<?= $color['badge'] ?>"><?= htmlspecialchars($plo['category']) ?></span>
              </div>
              <div class="plo-card-actions">
                <button class="action-btn action-btn--edit plo-edit-btn"
                  data-id="<?= (int)$plo['id'] ?>"
                  data-code="<?= htmlspecialchars($plo['code']) ?>"
                  data-description="<?= htmlspecialchars($plo['description']) ?>"
                  data-category="<?= htmlspecialchars($plo['category']) ?>"
                  title="Sửa">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="action-btn action-btn--danger plo-delete-btn"
                  data-id="<?= (int)$plo['id'] ?>"
                  data-code="<?= htmlspecialchars($plo['code']) ?>"
                  title="Xóa">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
              </div>
            </div>
            <p class="plo-description"><?= htmlspecialchars($plo['description']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>
</div>

<!-- ADD / EDIT PLO MODAL -->
<div class="modal-overlay" id="ploModal">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="ploModalTitleLabel">
    <div class="modal-header">
      <h3 class="modal-title" id="ploModalTitleLabel">Thêm PLO mới</h3>
      <button class="modal-close-btn" id="ploModalClose" aria-label="Đóng">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <form class="modal-form" id="ploForm">
      <input type="hidden" name="id" id="plo-id">
      <div class="form-group">
        <label for="plo-code">Mã PLO <span class="required">*</span></label>
        <input type="text" id="plo-code" name="code" class="form-input" placeholder="Ví dụ: PLO1, PLO2..." required maxlength="20">
        <span class="field-error" id="err-plo-code"></span>
      </div>
      <div class="form-group">
        <label for="plo-category">Danh mục năng lực <span class="required">*</span></label>
        <select id="plo-category" name="category" class="form-input" required>
          <option value="">-- Chọn danh mục --</option>
          <option value="Knowledge">Kiến thức (Knowledge)</option>
          <option value="Skill">Kỹ năng (Skill)</option>
          <option value="Attitude">Thái độ (Attitude)</option>
        </select>
        <span class="field-error" id="err-plo-category"></span>
      </div>
      <div class="form-group">
        <label for="plo-description">Mô tả năng lực <span class="required">*</span></label>
        <textarea id="plo-description" name="description" class="form-input" rows="4" placeholder="Mô tả chi tiết chuẩn đầu ra..." required></textarea>
        <span class="field-error" id="err-plo-description"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="ploCancelBtn">Hủy</button>
        <button type="submit" class="btn btn-primary" id="ploSubmitBtn">
          <span class="btn-label">Lưu PLO</span>
          <svg class="btn-spinner" hidden viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/>
          </svg>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-card modal-sm">
    <div class="modal-header">
      <h3 class="modal-title">Xác nhận xóa PLO</h3>
      <button class="modal-close-btn" id="deleteModalClose" aria-label="Đóng">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body-delete">
      <p>Bạn có chắc muốn xóa <strong id="delete-plo-code"></strong>?<br>Hành động này không thể hoàn tác.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="deleteCancelBtn">Hủy</button>
      <button class="btn btn-danger" id="deleteConfirmBtn">
        <span class="btn-label">Xóa</span>
        <svg class="btn-spinner" hidden viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
          <circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/>
        </svg>
      </button>
    </div>
  </div>
</div>

<style>
/* === PLO Control Center Layout === */
.plo-page-wrapper {
  display: flex;
  gap: 24px;
  align-items: flex-start;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 0 40px;
}

/* ── LEFT SIDEBAR ── */
.plo-sidebar {
  width: 280px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: sticky;
  top: 24px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  text-decoration: none;
  padding: 6px 0;
  transition: color 0.15s;
}
.back-link:hover { color: #334155; }

.program-summary-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.program-code-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6366f1;
  background: #eef2ff;
  padding: 3px 10px;
  border-radius: 20px;
  margin-bottom: 10px;
}
.program-title {
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.3;
  margin: 0 0 4px;
}
.program-subtitle {
  font-size: 12px;
  color: #94a3b8;
  margin: 0;
}

.stats-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.stats-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 16px;
}
.stat-big {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 0 16px;
}
.stat-number {
  font-size: 48px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.stat-label {
  font-size: 13px;
  color: #94a3b8;
  margin-top: 4px;
}
.stats-divider {
  height: 1px;
  background: #f1f5f9;
  margin: 12px 0;
}
.category-breakdown { display: flex; flex-direction: column; gap: 10px; }
.cat-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.cat-count {
  font-size: 15px;
  font-weight: 700;
  color: #475569;
}

.btn-full {
  width: 100%;
  justify-content: center;
}

/* ── RIGHT MAIN CONTENT ── */
.plo-main {
  flex: 1;
  min-width: 0;
}
.plo-main-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}
.section-title {
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.badge-count {
  font-size: 13px;
  font-weight: 600;
  color: #6366f1;
  background: #eef2ff;
  padding: 4px 12px;
  border-radius: 20px;
}

/* ── EMPTY STATE ── */
.plo-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 60px 40px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 1px 6px rgba(0,0,0,0.05);
}
.empty-icon {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border-radius: 50%;
  margin-bottom: 24px;
  color: #d97706;
}
.empty-title {
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 12px;
  max-width: 400px;
}
.empty-desc {
  font-size: 14px;
  color: #64748b;
  margin: 0 0 28px;
  max-width: 440px;
  line-height: 1.6;
}

/* ── PLO CARDS ── */
.plo-cards-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.plo-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-left: 4px solid #6366f1;
  border-radius: 12px;
  padding: 18px 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  transition: box-shadow 0.2s, transform 0.15s;
}
.plo-card:hover {
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
  transform: translateY(-1px);
}
.plo-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
.plo-card-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
}
.plo-code {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}
.plo-description {
  font-size: 14px;
  color: #475569;
  line-height: 1.6;
  margin: 0;
}
.plo-card-actions {
  display: flex;
  gap: 6px;
  flex-shrink: 0;
}

/* ── BADGES ── */
.badge-blue   { background: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 600; padding: 2px 10px; border-radius: 20px; }
.badge-green  { background: #d1fae5; color: #065f46; font-size: 12px; font-weight: 600; padding: 2px 10px; border-radius: 20px; }
.badge-amber  { background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 600; padding: 2px 10px; border-radius: 20px; }
.badge-slate  { background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 600; padding: 2px 10px; border-radius: 20px; }

/* ── MODAL OVERLAY ── */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,0.45);
  backdrop-filter: blur(4px);
  z-index: 1000;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-overlay.open { display: flex; }
.modal-card {
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.18);
  width: 100%;
  max-width: 520px;
  overflow: hidden;
  animation: modalIn 0.2s ease;
}
.modal-sm { max-width: 400px; }
@keyframes modalIn {
  from { opacity: 0; transform: scale(0.95) translateY(-8px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}
.modal-title {
  font-size: 17px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.modal-close-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 4px;
  border-radius: 6px;
  transition: color 0.15s, background 0.15s;
}
.modal-close-btn:hover { color: #334155; background: #f1f5f9; }
.modal-form { padding: 24px; display: flex; flex-direction: column; gap: 18px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}
.required { color: #ef4444; }
.form-input {
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 14px;
  font-family: inherit;
  color: #0f172a;
  background: #ffffff;
  transition: border-color 0.15s, box-shadow 0.15s;
  width: 100%;
  box-sizing: border-box;
}
.form-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}
.form-input.input--error { border-color: #ef4444; }
textarea.form-input { resize: vertical; min-height: 100px; }
select.form-input { cursor: pointer; }
.field-error { font-size: 12px; color: #ef4444; display: none; }
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 24px;
  border-top: 1px solid #f1f5f9;
  background: #fafafa;
}
.modal-body-delete { padding: 20px 24px; font-size: 14px; color: #475569; line-height: 1.6; }

/* ── BUTTONS ── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 18px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
}
.btn:active { transform: scale(0.98); }
.btn-primary { background: #4f46e5; color: #ffffff; }
.btn-primary:hover { background: #4338ca; box-shadow: 0 2px 8px rgba(79,70,229,0.35); }
.btn-secondary { background: #f1f5f9; color: #475569; }
.btn-secondary:hover { background: #e2e8f0; }
.btn-danger { background: #ef4444; color: #ffffff; }
.btn-danger:hover { background: #dc2626; }
.btn-lg { padding: 12px 24px; font-size: 15px; border-radius: 12px; }
.btn-sm { padding: 6px 14px; font-size: 13px; border-radius: 8px; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-spinner { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── ACTION BUTTONS ── */
.action-btn {
  width: 32px; height: 32px;
  display: inline-flex; align-items: center; justify-content: center;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #ffffff;
  cursor: pointer;
  color: #64748b;
  transition: all 0.15s;
}
.action-btn svg { width: 14px; height: 14px; }
.action-btn--edit:hover { border-color: #4f46e5; color: #4f46e5; background: rgba(79,70,229,0.06); }
.action-btn--danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.06); }

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
  .plo-page-wrapper { flex-direction: column; }
  .plo-sidebar { width: 100%; position: static; }
}
</style>

<script>
(function() {
    // ── Modal State ──────────────────────────────────────────────
    const modal      = document.getElementById('ploModal');
    const form       = document.getElementById('ploForm');
    const modalTitle = document.getElementById('ploModalTitleLabel');
    const submitBtn  = document.getElementById('ploSubmitBtn');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let editPloId = null;

    function openPloModal(id, data) {
        editPloId = id;
        modalTitle.textContent = id ? 'Sửa PLO' : 'Thêm PLO mới';
        submitBtn.querySelector('.btn-label').textContent = id ? 'Lưu thay đổi' : 'Lưu PLO';
        clearErrors();
        if (id && data) {
            document.getElementById('plo-id').value = id;
            document.getElementById('plo-code').value = data.code || '';
            document.getElementById('plo-category').value = data.category || '';
            document.getElementById('plo-description').value = data.description || '';
        } else {
            form.reset();
            document.getElementById('plo-id').value = '';
        }
        modal.classList.add('open');
        document.getElementById('plo-code').focus();
    }

    function closePloModal() {
        modal.classList.remove('open');
        editPloId = null;
        form.reset();
        clearErrors();
    }

    function clearErrors() {
        ['code','category','description'].forEach(id => setFieldError(id, ''));
    }

    function setFieldError(id, msg) {
        const el = document.getElementById('err-plo-' + id);
        if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
        const input = document.getElementById('plo-' + id);
        if (input) input.classList.toggle('input--error', !!msg);
    }

    function setLoading(on) {
        submitBtn.disabled = on;
        submitBtn.querySelector('.btn-label').textContent = on ? 'Đang xử lý...' : (editPloId ? 'Lưu thay đổi' : 'Lưu PLO');
        submitBtn.querySelector('.btn-spinner').hidden = !on;
    }

    // ── Event Listeners ──────────────────────────────────────────
    document.getElementById('btnAddPlo')?.addEventListener('click', () => openPloModal(null));
    document.getElementById('btnAddPloEmpty')?.addEventListener('click', () => openPloModal(null));
    document.getElementById('ploModalClose')?.addEventListener('click', closePloModal);
    document.getElementById('ploCancelBtn')?.addEventListener('click', closePloModal);
    modal?.addEventListener('click', e => { if (e.target === modal) closePloModal(); });

    // Escape key closes modal
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closePloModal();
            closeDeleteModal();
        }
    });

    // Edit buttons
    document.querySelectorAll('.plo-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            openPloModal(+btn.dataset.id, {
                code: btn.dataset.code,
                category: btn.dataset.category,
                description: btn.dataset.description
            });
        });
    });

    // Delete buttons
    const deleteModal = document.getElementById('deleteModal');
    let deletePloId = null;

    function closeDeleteModal() {
        deleteModal?.classList.remove('open');
        deletePloId = null;
    }

    document.querySelectorAll('.plo-delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            deletePloId = +btn.dataset.id;
            document.getElementById('delete-plo-code').textContent = btn.dataset.code;
            deleteModal?.classList.add('open');
        });
    });

    document.getElementById('deleteCancelBtn')?.addEventListener('click', closeDeleteModal);
    document.getElementById('deleteModalClose')?.addEventListener('click', closeDeleteModal);
    deleteModal?.addEventListener('click', e => { if (e.target === deleteModal) closeDeleteModal(); });

    document.getElementById('deleteConfirmBtn')?.addEventListener('click', async () => {
        if (!deletePloId) return;
        const btn = document.getElementById('deleteConfirmBtn');
        btn.disabled = true;
        btn.querySelector('.btn-label').textContent = 'Đang xóa...';
        btn.querySelector('.btn-spinner').hidden = false;
        try {
            const res = await fetch(`/admin/plo/${deletePloId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: csrfToken })
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && (data.status === 'success' || res.status === 200)) {
                window.Toast?.success('Đã xóa PLO thành công!');
                closeDeleteModal();
                setTimeout(() => location.reload(), 600);
            } else {
                window.Toast?.error(data.error || 'Xóa thất bại.');
                btn.disabled = false;
                btn.querySelector('.btn-label').textContent = 'Xóa';
                btn.querySelector('.btn-spinner').hidden = true;
            }
        } catch {
            window.Toast?.error('Đã xảy ra lỗi khi xóa.');
            btn.disabled = false;
            btn.querySelector('.btn-label').textContent = 'Xóa';
            btn.querySelector('.btn-spinner').hidden = true;
        }
    });

    // ── Form Submit ──────────────────────────────────────────────
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();
        setLoading(true);

        const payload = {
            code: document.getElementById('plo-code').value.trim(),
            category: document.getElementById('plo-category').value,
            description: document.getElementById('plo-description').value.trim(),
            _token: csrfToken
        };

        if (!payload.code) { setFieldError('code', 'Mã PLO là bắt buộc.'); setLoading(false); return; }
        if (!payload.category) { setFieldError('category', 'Danh mục là bắt buộc.'); setLoading(false); return; }
        if (!payload.description) { setFieldError('description', 'Mô tả là bắt buộc.'); setLoading(false); return; }

        const url = editPloId ? `/admin/plo/${editPloId}/update` : '/admin/plos';
        const method = 'POST';

        try {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (res.ok && (data.status === 'success' || res.status === 200)) {
                window.Toast?.success(editPloId ? 'Cập nhật PLO thành công!' : 'Thêm PLO mới thành công!');
                closePloModal();
                setTimeout(() => location.reload(), 600);
            } else {
                if (data.fields) {
                    Object.entries(data.fields).forEach(([k, v]) => setFieldError(k, Array.isArray(v) ? v[0] : v));
                } else {
                    window.Toast?.error(data.error || 'Đã xảy ra lỗi.');
                }
                setLoading(false);
            }
        } catch {
            window.Toast?.error('Đã xảy ra lỗi kết nối.');
            setLoading(false);
        }
    });
})();
</script>
